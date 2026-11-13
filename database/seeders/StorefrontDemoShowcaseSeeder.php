<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StorefrontDemoShowcaseSeeder extends Seeder
{
    /**
     * Seed demonstration products strictly for local/testing storefront preview.
     * Excluded from production environments.
     */
    public function run(): void
    {
        // Enforce development/testing environment isolation
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $items = [
            [
                'category_slug'  => 'air-conditioners',
                'brand_name'     => 'Haier',
                'brand_slug'     => 'haier',
                'name'           => 'Haier 1.5 Ton T3 Inverter Air Conditioner (H-Heat/Cool)',
                'slug'           => 'demo-haier-1-5-ton-t3-inverter-ac',
                'sku'            => 'DEMO-AC-H15T3',
                'badge'          => 'DEMO PREVIEW',
                'short_desc'     => 'Energy efficient T3 inverter air conditioner with fast cooling and heating mode.',
                'full_desc'      => '[DEMO SHOWCASE ITEM — For preview only; final specifications and pricing subject to business confirmation]. T3 tropicalized compressor designed for high ambient temperatures up to 52°C. Low voltage operation capability.',
                'warranty'       => 'Demo: 10 Years Compressor / 1 Year Parts (Subject to manufacturer terms)',
                'price'          => 185000.00,
                'orig_price'     => 195000.00,
                'stock'          => 12,
                'is_featured'    => true,
                'specs'          => [
                    ['spec_group' => 'Cooling', 'spec_key' => 'Capacity', 'spec_value' => '1.5 Ton (18,000 BTU)'],
                    ['spec_group' => 'Technology', 'spec_key' => 'Inverter Type', 'spec_value' => 'DC Inverter T3'],
                    ['spec_group' => 'Performance', 'spec_key' => 'Refrigerant', 'spec_value' => 'R32 Eco-Friendly'],
                    ['spec_group' => 'Electrical', 'spec_key' => 'Operating Voltage', 'spec_value' => '150V – 260V'],
                ],
            ],
            [
                'category_slug'  => 'refrigerators-freezers',
                'brand_name'     => 'Dawlance',
                'brand_slug'     => 'dawlance',
                'name'           => 'Dawlance Double Door Low-Voltage Inverter Refrigerator',
                'slug'           => 'demo-dawlance-double-door-inverter-refrigerator',
                'sku'            => 'DEMO-REF-DW9170',
                'badge'          => 'DEMO PREVIEW',
                'short_desc'     => 'Spacious multi-shelf refrigerator with direct cool technology and hybrid cooling.',
                'full_desc'      => '[DEMO SHOWCASE ITEM — For preview only; final specifications and pricing subject to business confirmation]. Engineered with 55% energy saving inverter technology, anti-fungal gasket, and tempered glass shelves.',
                'warranty'       => 'Demo: 12 Years Compressor Warranty (Subject to manufacturer terms)',
                'price'          => 115000.00,
                'orig_price'     => 122000.00,
                'stock'          => 8,
                'is_featured'    => true,
                'specs'          => [
                    ['spec_group' => 'Dimensions', 'spec_key' => 'Volume Capacity', 'spec_value' => '350 Litres / 14 cu ft'],
                    ['spec_group' => 'Cooling', 'spec_key' => 'Cooling Type', 'spec_value' => 'Direct Cool NatureLock'],
                    ['spec_group' => 'Energy', 'spec_key' => 'Energy Saving', 'spec_value' => 'A+ Grade Inverter'],
                ],
            ],
            [
                'category_slug'  => 'led-tvs',
                'name'           => 'TCL 55-Inch 4K UHD Smart Google TV (Dolby Audio)',
                'slug'           => 'demo-tcl-55-inch-4k-uhd-smart-google-tv',
                'sku'            => 'DEMO-TV-TCL55',
                'badge'          => 'DEMO PREVIEW',
                'short_desc'     => 'Bezel-less 4K Ultra HD smart TV with Google TV OS and built-in voice remote.',
                'full_desc'      => '[DEMO SHOWCASE ITEM — For preview only; final specifications and pricing subject to business confirmation]. Vivid HDR10 display, dual-band Wi-Fi, Chromecast built-in, and preloaded streaming applications.',
                'warranty'       => 'Demo: 2 Years Official Screen Warranty (Subject to manufacturer terms)',
                'price'          => 138000.00,
                'orig_price'     => 145000.00,
                'stock'          => 15,
                'is_featured'    => true,
                'specs'          => [
                    ['spec_group' => 'Display', 'spec_key' => 'Screen Size', 'spec_value' => '55 Inch 4K UHD (3840 x 2160)'],
                    ['spec_group' => 'Software', 'spec_key' => 'Operating System', 'spec_value' => 'Google TV Android 12'],
                    ['spec_group' => 'Audio', 'spec_key' => 'Sound Output', 'spec_value' => '24W Dolby Audio Stereo'],
                    ['spec_group' => 'Connectivity', 'spec_key' => 'HDMI / USB', 'spec_value' => '3x HDMI 2.1, 2x USB 2.0'],
                ],
            ],
            [
                'category_slug'  => 'washing-machines',
                'name'           => 'Super Asia 10kg Top Load Fully Automatic Washing Machine',
                'slug'           => 'demo-super-asia-10kg-top-load-automatic-washing-machine',
                'sku'            => 'DEMO-WM-SA10',
                'badge'          => 'DEMO PREVIEW',
                'short_desc'     => 'Heavy duty rust-proof washing machine with fuzzy logic control and spin dryer.',
                'full_desc'      => '[DEMO SHOWCASE ITEM — For preview only; final specifications and pricing subject to business confirmation]. Powerful pulsator, child lock, water-saving sensors, and stainless steel diamond wash drum.',
                'warranty'       => 'Demo: 2 Years Motor Warranty (Subject to manufacturer terms)',
                'price'          => 82000.00,
                'orig_price'     => 88000.00,
                'stock'          => 10,
                'is_featured'    => true,
                'specs'          => [
                    ['spec_group' => 'Capacity', 'spec_key' => 'Washing Capacity', 'spec_value' => '10.0 KG Top Load'],
                    ['spec_group' => 'Motor', 'spec_key' => 'Motor Type', 'spec_value' => 'High Torque Copper Motor'],
                    ['spec_group' => 'Programs', 'spec_key' => 'Wash Cycles', 'spec_value' => '8 Multi-Wash Programs'],
                ],
            ],
            [
                'category_slug'  => 'scooters',
                'name'           => 'Crown Electric Eco-Scooter 1200W (Lithium Battery Edition)',
                'slug'           => 'demo-crown-electric-eco-scooter-1200w',
                'sku'            => 'DEMO-SCOOT-1200',
                'badge'          => 'DEMO PREVIEW',
                'short_desc'     => 'Urban electric scooter with 80km range per charge. Separate delivery arrangements apply.',
                'full_desc'      => '[DEMO SHOWCASE ITEM — For preview only; final specifications and pricing subject to business confirmation]. Bulky item: Nationwide delivery subject to destination serviceability. Special handling and freight charges apply.',
                'warranty'       => 'Demo: 1 Year Battery & Motor Warranty (Subject to manufacturer terms)',
                'price'          => 245000.00,
                'orig_price'     => 260000.00,
                'stock'          => 5,
                'is_featured'    => true,
                'specs'          => [
                    ['spec_group' => 'Motor', 'spec_key' => 'Motor Power', 'spec_value' => '1200W BLDC Hub Motor'],
                    ['spec_group' => 'Battery', 'spec_key' => 'Battery Pack', 'spec_value' => '72V 30Ah Lithium-ion'],
                    ['spec_group' => 'Performance', 'spec_key' => 'Range per Charge', 'spec_value' => '75 – 85 KM'],
                    ['spec_group' => 'Delivery', 'spec_key' => 'Delivery Note', 'spec_value' => 'Separate freight / handling required'],
                ],
            ],
            [
                'category_slug'  => 'fans',
                'name'           => 'Pak Fan 56-Inch Deluxe Inverter Ceiling Fan (Copper Winding)',
                'slug'           => 'demo-pak-fan-56-inch-deluxe-inverter-ceiling-fan',
                'sku'            => 'DEMO-FAN-PF56',
                'badge'          => 'DEMO PREVIEW',
                'short_desc'     => 'Energy efficient 50W BLDC ceiling fan with RF remote controller.',
                'full_desc'      => '[DEMO SHOWCASE ITEM — For preview only; final specifications and pricing subject to business confirmation]. Built with 99.9% pure electrical copper wire, aerodynamically designed blades, and silent motor operation.',
                'warranty'       => 'Demo: 2 Years Manufacturer Warranty (Subject to manufacturer terms)',
                'price'          => 14500.00,
                'orig_price'     => 16000.00,
                'stock'          => 30,
                'is_featured'    => false,
                'specs'          => [
                    ['spec_group' => 'Electrical', 'spec_key' => 'Power Consumption', 'spec_value' => '50 Watts (BLDC Inverter)'],
                    ['spec_group' => 'Physical', 'spec_key' => 'Blade Sweep', 'spec_value' => '56 Inches (1400 mm)'],
                    ['spec_group' => 'Control', 'spec_key' => 'Speed Control', 'spec_value' => 'RF Remote Control Included'],
                ],
            ],
            [
                'category_slug'  => 'kitchen-appliances',
                'name'           => 'Anex 4-in-1 Deluxe Food Processor & Blender (Stainless Steel)',
                'slug'           => 'demo-anex-4-in-1-deluxe-food-processor',
                'sku'            => 'DEMO-KIT-ANEX4',
                'badge'          => 'DEMO PREVIEW',
                'short_desc'     => 'Multifunction food processor with blender, mincer, citrus juicer, and grinder jars.',
                'full_desc'      => '[DEMO SHOWCASE ITEM — For preview only; final specifications and pricing subject to business confirmation]. Heavy duty 800W motor with 2-speed pulse dial, safety interlock lid, and stainless steel cutting blades.',
                'warranty'       => 'Demo: 2 Years Official Warranty (Subject to manufacturer terms)',
                'price'          => 19500.00,
                'orig_price'     => 21500.00,
                'stock'          => 20,
                'is_featured'    => false,
                'specs'          => [
                    ['spec_group' => 'Power', 'spec_key' => 'Motor Wattage', 'spec_value' => '800 Watts Heavy Duty'],
                    ['spec_group' => 'Functions', 'spec_key' => 'Attachments', 'spec_value' => 'Blender, Chopper, Citrus Juicer, Dry Grinder'],
                ],
            ],
            [
                'category_slug'  => 'geysers-water-dispensers',
                'name'           => 'Canon Instant Gas Water Heater / Geyser (6 Litres)',
                'slug'           => 'demo-canon-instant-gas-water-heater-6l',
                'sku'            => 'DEMO-GEY-CANON6',
                'badge'          => 'DEMO PREVIEW',
                'short_desc'     => 'Instant continuous hot water geyser with digital temperature display and flame-out protection.',
                'full_desc'      => '[DEMO SHOWCASE ITEM — For preview only; final specifications and pricing subject to business confirmation]. Zero water pressure start-up, ultra-low gas consumption, and automatic battery-powered ignition.',
                'warranty'       => 'Demo: 1 Year Official Warranty (Subject to manufacturer terms)',
                'price'          => 22000.00,
                'orig_price'     => 24500.00,
                'stock'          => 14,
                'is_featured'    => false,
                'specs'          => [
                    ['spec_group' => 'Capacity', 'spec_key' => 'Flow Rate', 'spec_value' => '6 Litres / Minute'],
                    ['spec_group' => 'Ignition', 'spec_key' => 'Ignition Type', 'spec_value' => 'Automatic Battery Ignition (2x D Cell)'],
                    ['spec_group' => 'Fuel', 'spec_key' => 'Gas Type', 'spec_value' => 'Natural Gas (NG) / LPG compatible'],
                ],
            ],
        ];

        $brandMap = [
            'DEMO-AC-H15T3'   => ['name' => 'Haier', 'slug' => 'haier'],
            'DEMO-REF-DW9170' => ['name' => 'Dawlance', 'slug' => 'dawlance'],
            'DEMO-TV-TCL55'   => ['name' => 'TCL', 'slug' => 'tcl'],
            'DEMO-WM-SA10'    => ['name' => 'Super Asia', 'slug' => 'super-asia'],
            'DEMO-SCOOT-1200' => ['name' => 'Crown', 'slug' => 'crown'],
            'DEMO-FAN-PF56'   => ['name' => 'Pak Fan', 'slug' => 'pak-fan'],
            'DEMO-KIT-ANEX4'  => ['name' => 'Anex', 'slug' => 'anex'],
            'DEMO-GEY-CANON6' => ['name' => 'Canon', 'slug' => 'canon'],
        ];

        DB::transaction(function () use ($items, $brandMap) {
            foreach ($items as $data) {
                $category = Category::where('slug', $data['category_slug'])->first();

                $brandInfo = $brandMap[$data['sku']] ?? null;
                $brand = null;
                if ($brandInfo) {
                    $brand = Brand::updateOrCreate(
                        ['slug' => $brandInfo['slug']],
                        [
                            'name'        => $brandInfo['name'],
                            'description' => "Official appliances and electronics from {$brandInfo['name']}.",
                            'is_active'   => true,
                        ]
                    );
                }

                $product = Product::updateOrCreate(
                    ['slug' => $data['slug']],
                    [
                        'category_id'       => $category?->id,
                        'brand_id'          => $brand?->id,
                        'name'              => $data['name'],
                        'sku'               => $data['sku'],
                        'badge_text'        => $data['badge'],
                        'short_description' => $data['short_desc'],
                        'full_description'  => $data['full_desc'],
                        'warranty_text'     => $data['warranty'],
                        'is_featured'       => $data['is_featured'],
                        'is_active'         => true,
                        'rating'            => 4.80,
                        'reviews_count'     => 14,
                    ]
                );

                // Create or update default variant
                $variant = ProductVariant::updateOrCreate(
                    ['product_id' => $product->id, 'sku' => $data['sku']],
                    [
                        'name'           => 'Standard',
                        'price'          => $data['price'],
                        'original_price' => $data['orig_price'],
                        'stock_quantity' => $data['stock'],
                        'stock_status'   => $data['stock'] > 0 ? 'in_stock' : 'out_of_stock',
                        'is_default'     => true,
                        'is_active'      => true,
                    ]
                );

                // Add specifications
                ProductSpecification::where('product_id', $product->id)->delete();
                foreach ($data['specs'] as $index => $spec) {
                    ProductSpecification::create([
                        'product_id' => $product->id,
                        'spec_group' => $spec['spec_group'],
                        'spec_key'   => $spec['spec_key'],
                        'spec_value' => $spec['spec_value'],
                        'sort_order' => $index + 1,
                    ]);
                }

                // Add demonstration product media (Primary Thumbnail + Gallery Detail Angle)
                ProductMedia::where('product_id', $product->id)->delete();
                ProductMedia::create([
                    'product_id' => $product->id,
                    'media_type' => 'image',
                    'url'        => '/images/products/thumb_' . $data['sku'] . '.webp',
                    'alt_text'   => $data['name'] . ' — Primary Demonstration View',
                    'sort_order' => 1,
                    'is_primary' => true,
                ]);
                ProductMedia::create([
                    'product_id' => $product->id,
                    'media_type' => 'image',
                    'url'        => '/images/products/gallery_' . $data['sku'] . '_angle.webp',
                    'alt_text'   => $data['name'] . ' — Detail Angle View',
                    'sort_order' => 2,
                    'is_primary' => false,
                ]);
            }
        });
    }
}
