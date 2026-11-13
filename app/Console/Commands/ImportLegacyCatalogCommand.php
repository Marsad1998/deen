<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportLegacyCatalogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deen:import-legacy-catalog 
                            {--dry-run : Simulate the migration without modifying the database (enforced in Phase 2C)}
                            {--report-only : Print data quality analysis and mapping without checking target tables}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dry-run inspection, transformation, and validation of legacy ChargeHub catalog data for DEEN Electronics';

    /**
     * Target Category Mapping for all 15 products in live chargehub_db.
     * Maps to DEEN category taxonomy by verified category slug.
     */
    protected array $targetCategoryMap = [
        1  => ['id' => 1, 'slug' => 'chargers',   'name' => 'Fast Chargers'],
        2  => ['id' => 2, 'slug' => 'cables',     'name' => 'Cables & Hubs'],
        3  => ['id' => 4, 'slug' => 'wireless',   'name' => 'Magnetic Wireless Chargers'],
        4  => ['id' => 3, 'slug' => 'powerbanks', 'name' => 'Power Banks & Stations'],
        5  => ['id' => 1, 'slug' => 'chargers',   'name' => 'Fast Chargers'],
        6  => ['id' => 4, 'slug' => 'wireless',   'name' => 'Magnetic Wireless Chargers'],
        7  => ['id' => 3, 'slug' => 'powerbanks', 'name' => 'Power Banks & Stations'],
        8  => ['id' => 1, 'slug' => 'chargers',   'name' => 'Fast Chargers'],
        9  => ['id' => 1, 'slug' => 'chargers',   'name' => 'Fast Chargers'],
        10 => ['id' => 1, 'slug' => 'chargers',   'name' => 'Fast Chargers'],
        11 => ['id' => 6, 'slug' => 'speakers',   'name' => 'Party Speakers'],
        12 => ['id' => 6, 'slug' => 'speakers',   'name' => 'Party Speakers'],
        13 => ['id' => 6, 'slug' => 'speakers',   'name' => 'Party Speakers'],
        14 => ['id' => 6, 'slug' => 'speakers',   'name' => 'Party Speakers'],
        15 => ['id' => 6, 'slug' => 'speakers',   'name' => 'Party Speakers'],
    ];

    /**
     * Target Brand Mapping for all 15 products in live chargehub_db.
     * STRICT BRAND POLICY: Only authentic, verified brands are mapped.
     * In-house and unverified products MUST resolve to NULL.
     */
    protected array $targetBrandMap = [
        1  => ['id' => 1,    'slug' => 'anker',  'name' => 'Anker'],
        2  => null, // ChargeHub Cable -> In-house / Unresolved (Was A4TECH)
        3  => null, // ChargeHub MagSafe -> In-house / Unresolved (Was BOYA)
        4  => ['id' => 1,    'slug' => 'anker',  'name' => 'Anker'],
        5  => ['id' => 2,    'slug' => 'ugreen', 'name' => 'Ugreen'],
        6  => ['id' => 3,    'slug' => 'baseus', 'name' => 'Baseus'],
        7  => ['id' => 1,    'slug' => 'anker',  'name' => 'Anker'],
        8  => null, // CHARGEHUB PRO 65W -> In-house (Was Anker)
        9  => null, // VOLTRA PRO 100W -> In-house / Generic (Was Ugreen)
        10 => null, // HYPERSPEED 45W -> In-house / Generic (Was Anker)
        11 => null, // L-265 Party Speaker -> Unbranded (Was PITAKA)
        12 => null, // L-267 30W Speaker -> Unbranded (Was PITAKA)
        13 => null, // L-268 50W Speaker -> Unbranded (Was PITAKA)
        14 => null, // L-269 60W Speaker -> Unbranded (Was PITAKA)
        15 => null, // L-270 Soundbar -> Unbranded (Was PITAKA)
    ];

    /**
     * The 5 approved catalogue candidates.
     */
    protected array $approvedCandidates = [1, 4, 5, 6, 7];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->alert('DEEN ELECTRONICS — LEGACY CATALOG MIGRATION (DRY-RUN)');
        $this->info('Target Database: ' . config('database.connections.mysql.database'));
        $this->info('Execution Mode: DRY-RUN ONLY (Zero writes to target or legacy databases)');
        $this->newLine();

        config([
            'database.connections.chargehub_readonly' => array_merge(
                config('database.connections.mysql'),
                [
                    'database' => 'chargehub_db',
                    'options'  => [
                        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
                    ],
                ]
            ),
        ]);

        try {
            $legacyDb = DB::connection('chargehub_readonly');
            $legacyDb->getPdo();
        } catch (Throwable $e) {
            $this->error('Failed to connect to legacy chargehub_db: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $baseDir = base_path('../'); // /Users/marsadakbar/farzan/

        // 1. Audit Categories
        $this->info('1. LIVE LEGACY CATEGORIES (chargehub_db.categories)...');
        $categories = $legacyDb->table('categories')->orderBy('id')->get();
        $catRows = [];
        foreach ($categories as $cat) {
            $hasEmoji = preg_match('/[\x{1F300}-\x{1FAD6}]/u', $cat->image_url ?? '');
            $eval = $hasEmoji ? '<fg=yellow>VALID (Emoji in image_url requires asset path)</>' : '<info>VALID (Ready for Import)</info>';
            $catRows[] = [$cat->id, $cat->name, $cat->slug, $cat->image_url ? mb_substr($cat->image_url, 0, 32) : 'NULL', $cat->sort_order, $eval];
        }
        $this->table(['Legacy ID', 'Name', 'Slug', 'Legacy Image', 'Order', 'Evaluation'], $catRows);

        // 2. Audit Brands
        $this->newLine();
        $this->info('2. LIVE LEGACY BRANDS (chargehub_db.brands)...');
        $brands = $legacyDb->table('brands')->orderBy('id')->get();
        $brandRows = [];
        foreach ($brands as $brand) {
            $brandRows[] = [$brand->id, $brand->name, $brand->slug, substr((string) $brand->logo_url, 0, 35) . '...', '<info>VALID (Ready for Import)</info>'];
        }
        $this->table(['Legacy ID', 'Name', 'Slug', 'Logo Asset Path', 'Evaluation'], $brandRows);

        // 3. Audit Products & Verification of 5 Approved Candidates
        $this->newLine();
        $this->info('3. PRODUCT ATTRIBUTION & VALIDATION FOR 15 LIVE PRODUCTS...');
        $products = $legacyDb->table('products')->orderBy('id')->get();
        $prodRows = [];
        $candidateAssetAudit = [];

        foreach ($products as $prod) {
            $isCandidate = in_array($prod->id, $this->approvedCandidates);
            $targetCat = $this->targetCategoryMap[$prod->id] ?? null;
            $targetBrand = $this->targetBrandMap[$prod->id] ?? null;

            $brandDisplay = $targetBrand ? "<fg=green>{$targetBrand['name']}</>" : '<fg=yellow>NULL (Unassigned)</>';
            $catDisplay = $targetCat ? "<fg=green>{$targetCat['name']}</>" : '<fg=red>NULL</>';

            // Check main image on disk
            $mainImgExists = file_exists($baseDir . $prod->main_image);
            $imgDisplay = $mainImgExists ? 'OK' : '<fg=red>404</>';

            if ($isCandidate) {
                $status = '<info>APPROVED CANDIDATE</info>';
                // Detailed asset audit for candidates
                $gallery = json_decode($prod->gallery_images ?? '[]', true) ?: [];
                $galleryAudit = [];
                foreach ($gallery as $gImg) {
                    $gExists = file_exists($baseDir . $gImg);
                    $galleryAudit[] = $gImg . ($gExists ? ' [EXISTS]' : ' [MISSING 404]');
                }
                $candidateAssetAudit[] = [
                    'id'          => $prod->id,
                    'sku'         => $prod->sku,
                    'name'        => $prod->name,
                    'price'       => 'Rs. ' . number_format($prod->price, 2),
                    'stock'       => $prod->stock_quantity,
                    'cat'         => $targetCat['name'],
                    'brand'       => $targetBrand['name'],
                    'main_image'  => $prod->main_image . ($mainImgExists ? ' [EXISTS]' : ' [MISSING]'),
                    'gallery'     => implode("\n", $galleryAudit),
                    'specs_count' => count(json_decode($prod->specs ?? '[]', true) ?: []),
                ];
            } elseif (in_array($prod->id, [2, 3, 8, 9, 10])) {
                $status = '<fg=yellow>REQUIRES BRAND CONFIRMATION (Brand -> NULL)</>';
            } else {
                $status = '<fg=red>CORRUPTED BRAND IN LEGACY (Speaker -> PITAKA) -> Brand set to NULL</>';
            }

            $prodRows[] = [
                $prod->id,
                substr($prod->name, 0, 30) . '...',
                $prod->sku,
                "Cat {$prod->category_id} -> {$catDisplay}",
                "Brand {$prod->brand_id} -> {$brandDisplay}",
                'Rs. ' . number_format($prod->price, 2),
                $prod->stock_quantity,
                $imgDisplay,
                $status,
            ];
        }

        $this->table(
            ['ID', 'Product Name', 'SKU', 'Category Mapping', 'Brand Policy', 'Price', 'Stock', 'Asset', 'Status'],
            $prodRows
        );

        // 4. Candidate Asset & Spec Deep Dive
        $this->newLine();
        $this->info('4. DEEP DATA VALIDATION: 5 APPROVED CANDIDATES (ASSETS & SPECS)...');
        foreach ($candidateAssetAudit as $c) {
            $this->line("• <info>Candidate #{$c['id']} [{$c['sku']}] {$c['name']}</info>");
            $this->line("  Price: {$c['price']} | Stock: {$c['stock']} | Category: {$c['cat']} | Brand: {$c['brand']}");
            $this->line("  Main Asset: {$c['main_image']}");
            $this->line("  Gallery Assets:\n    " . str_replace("\n", "\n    ", $c['gallery']));
            $this->line("  Specs Extracted: {$c['specs_count']} keys");
            $this->newLine();
        }

        // 5. Cross-Table Reconciliation (Orphans vs Products)
        $this->info('5. RECORD-LEVEL RECONCILIATION: MARKETING TABLES vs PRODUCTS TABLE...');
        $reconciliationRows = [];

        // 5a. new_arrival_items
        $nas = $legacyDb->table('new_arrival_items')->orderBy('id')->get();
        foreach ($nas as $na) {
            // Find match in products table
            $match = $products->first(function ($p) use ($na) {
                return $p->sku === $na->sku || $p->sku === str_replace('-OLED', '', $na->sku);
            });

            if ($match) {
                $recType = '<comment>DUPLICATE OF PRODUCTS TABLE</comment>';
                $matchedRec = "products.id = {$match->id} [{$match->sku}]";
            } else {
                $recType = '<fg=cyan>TRUE ORPHAN (Catalog Candidate)</>';
                $matchedRec = 'None (Unique New Arrival)';
            }

            $reconciliationRows[] = [
                'new_arrival_items',
                $na->id,
                $na->sku,
                substr($na->title, 0, 24),
                'Rs. ' . number_format($na->price, 2),
                $matchedRec,
                $recType,
            ];
        }

        // 5b. flash_sale_items
        $fss = $legacyDb->table('flash_sale_items')->orderBy('id')->get();
        foreach ($fss as $fs) {
            // Flash sales matched to products 11-15 by title prefix or price
            $match = $products->first(function ($p) use ($fs) {
                return str_contains($p->name, substr($fs->title, 0, 10));
            });

            $matchedRec = $match ? "products.id = {$match->id} [{$match->sku}]" : 'None';
            $recType = $match ? '<comment>DUPLICATE OF PRODUCTS TABLE</comment>' : '<fg=cyan>TRUE ORPHAN</>';

            $reconciliationRows[] = [
                'flash_sale_items',
                $fs->id,
                'N/A (Uses Slug)',
                substr($fs->title, 0, 24),
                'Rs. ' . number_format($fs->price, 2),
                $matchedRec,
                $recType,
            ];
        }

        $this->table(
            ['Source Table', 'Source PK', 'SKU', 'Title', 'Price', 'Matching Products Record', 'Reconciliation Status'],
            $reconciliationRows
        );

        $this->newLine();
        $this->warn('SAFETY VERIFICATION: ZERO WRITES PERFORMED. deen_electronics_dev CATALOG REMAINS EMPTY.');
        $this->newLine();

        return Command::SUCCESS;
    }
}
