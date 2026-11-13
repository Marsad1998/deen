<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;
use App\Services\AuditLogService;
use App\Services\InventoryAdjustmentService;
use App\Services\ProductPriceService;
use App\Services\ProductVariantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ProductVariantService $variantService,
        protected ProductPriceService $priceService,
        protected InventoryAdjustmentService $inventoryService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Display a paginated, searchable, and filterable list of products.
     */
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'brand', 'defaultVariant', 'primaryMedia'])
            ->withCount('variants');

        // Search by product name, SKU, slug, or variant SKU
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Filter by category
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by brand
        if ($brandId = $request->input('brand_id')) {
            $query->where('brand_id', $brandId);
        }

        // Filter by status: active, draft, archived
        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_active', true)->whereNull('archived_at');
            } elseif ($status === 'draft') {
                $query->where('is_active', false)->whereNull('archived_at');
            } elseif ($status === 'archived') {
                $query->whereNotNull('archived_at');
            }
        }

        // Sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (in_array($sortField, ['name', 'created_at', 'rating'], true)) {
            $query->orderBy($sortField, $sortDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(15)->withQueryString();

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Show the product creation form.
     */
    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'brands'));
    }

    /**
     * Store a newly created product with its initial default variant atomically.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $admin = Auth::guard('admin')->user();

        $productData = [
            'category_id'       => $validated['category_id'] ?? null,
            'brand_id'          => $validated['brand_id'] ?? null,
            'name'              => $validated['name'],
            'slug'              => $validated['slug'],
            'sku'               => $validated['sku'] ?? null,
            'badge_text'        => $validated['badge_text'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'full_description'  => $validated['full_description'] ?? null,
            'warranty_text'     => $validated['warranty_text'] ?? null,
            'meta_title'        => $validated['meta_title'] ?? null,
            'meta_description'  => $validated['meta_description'] ?? null,
            'meta_keywords'     => $validated['meta_keywords'] ?? null,
            'is_featured'       => (bool) ($validated['is_featured'] ?? false),
            'is_active'         => $validated['status'] === 'active',
            'archived_at'       => $validated['status'] === 'archived' ? now() : null,
        ];

        // Default variant representation
        $variantSku = $validated['sku'] ?? (Str::slug($validated['name']) . '-DEFAULT');
        $variantData = [
            'sku'            => $variantSku,
            'name'           => $validated['variant_name'] ?? 'Default',
            'price'          => $validated['price'],
            'original_price' => $validated['original_price'] ?? null,
            'stock_quantity' => $validated['stock_quantity'],
            'stock_status'   => $validated['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock',
            'is_default'     => true,
            'is_active'      => true,
        ];

        $creationResult = $this->variantService->createProductWithDefaultVariant($productData, $variantData);
        $productId = $creationResult['product_id'];
        $defaultVariantId = $creationResult['default_variant_id'];

        // Initial inventory history log
        if ($validated['stock_quantity'] > 0) {
            DB::table('inventory_histories')->insert([
                'product_variant_id' => $defaultVariantId,
                'change_qty'         => $validated['stock_quantity'],
                'previous_qty'       => 0,
                'new_qty'            => $validated['stock_quantity'],
                'reason'             => 'Initial product creation',
                'admin_id'           => $admin?->id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        // Attach initial dynamic specifications if provided
        if (! empty($validated['specifications']) && is_array($validated['specifications'])) {
            foreach ($validated['specifications'] as $index => $spec) {
                if (! empty($spec['spec_key']) && ! empty($spec['spec_value'])) {
                    ProductSpecification::create([
                        'product_id' => $productId,
                        'spec_group' => $spec['spec_group'] ?? null,
                        'spec_key'   => trim($spec['spec_key']),
                        'spec_value' => trim($spec['spec_value']),
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        }

        // Audit log
        $this->auditLogService->log(
            action: 'product.created',
            entity: 'Product',
            entityId: $productId,
            changes: [
                'name' => $productData['name'],
                'slug' => $productData['slug'],
                'sku'  => $variantData['sku'],
            ],
            adminId: $admin?->id
        );

        return redirect()
            ->route('admin.products.edit', $productId)
            ->with('status', "Product [{$productData['name']}] created successfully.");
    }

    /**
     * Show the unified product management and editing workspace.
     */
    public function edit(Product $product): View
    {
        $product->load([
            'category',
            'brand',
            'variants.priceHistories.admin',
            'variants.inventoryHistories.admin',
            'media',
            'specifications',
        ]);

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update core product attributes and synchronize default variant details.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $admin = Auth::guard('admin')->user();

        $changes = [];

        $wasActive = $product->is_active && ! $product->is_archived;
        $targetStatus = $validated['status'];

        $productData = [
            'category_id'       => $validated['category_id'] ?? null,
            'brand_id'          => $validated['brand_id'] ?? null,
            'name'              => $validated['name'],
            'slug'              => $validated['slug'],
            'sku'               => $validated['sku'] ?? null,
            'badge_text'        => $validated['badge_text'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'full_description'  => $validated['full_description'] ?? null,
            'warranty_text'     => $validated['warranty_text'] ?? null,
            'meta_title'        => $validated['meta_title'] ?? null,
            'meta_description'  => $validated['meta_description'] ?? null,
            'meta_keywords'     => $validated['meta_keywords'] ?? null,
            'is_featured'       => (bool) ($validated['is_featured'] ?? false),
        ];

        // Status transitions
        if ($targetStatus === 'active') {
            $productData['is_active'] = true;
            $productData['archived_at'] = null;
        } elseif ($targetStatus === 'draft') {
            $productData['is_active'] = false;
            $productData['archived_at'] = null;
        } elseif ($targetStatus === 'archived') {
            $productData['is_active'] = false;
            $productData['archived_at'] = now();
        }

        DB::transaction(function () use ($product, $productData, $validated, $targetStatus, $wasActive, $admin) {
            $product->update($productData);

            // If activating product, verify via ProductVariantService lifecycle rule
            if ($targetStatus === 'active' && ! $wasActive) {
                $this->variantService->activateProduct($product->id);
            }

            // Sync default variant price and stock if provided in basic update form
            $defaultVariant = $product->defaultVariant;
            if ($defaultVariant) {
                if (isset($validated['price'])) {
                    $this->priceService->updatePrice(
                        variantId: $defaultVariant->id,
                        newPrice: (float) $validated['price'],
                        newOriginalPrice: isset($validated['original_price']) ? (float) $validated['original_price'] : null,
                        reason: 'Updated from product details form',
                        adminId: $admin?->id
                    );
                }

                if (isset($validated['stock_quantity']) && (int) $validated['stock_quantity'] !== $defaultVariant->stock_quantity) {
                    $this->inventoryService->setStock(
                        variantId: $defaultVariant->id,
                        newQty: (int) $validated['stock_quantity'],
                        reason: 'Stock updated from product details form',
                        adminId: $admin?->id
                    );
                }
            }
        });

        // Audit log
        $this->auditLogService->log(
            action: 'product.updated',
            entity: 'Product',
            entityId: $product->id,
            changes: [
                'name'   => $productData['name'],
                'status' => $targetStatus,
            ],
            adminId: $admin?->id
        );

        return redirect()
            ->route('admin.products.edit', $product->id)
            ->with('status', "Product [{$product->name}] updated successfully.");
    }

    /**
     * Soft-archive a product and its associated variants.
     */
    public function archive(Product $product): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        DB::transaction(function () use ($product) {
            $now = now();
            $product->update([
                'is_active'   => false,
                'archived_at' => $now,
            ]);

            ProductVariant::where('product_id', $product->id)->update([
                'is_active'   => false,
                'archived_at' => $now,
            ]);
        });

        $this->auditLogService->log(
            action: 'product.archived',
            entity: 'Product',
            entityId: $product->id,
            changes: ['archived_at' => now()->toIso8601String()],
            adminId: $admin?->id
        );

        return redirect()
            ->back()
            ->with('status', "Product [{$product->name}] archived.");
    }

    /**
     * Restore an archived product back to draft status.
     */
    public function unarchive(Product $product): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        DB::transaction(function () use ($product) {
            $product->update([
                'is_active'   => false, // restore as draft for review
                'archived_at' => null,
            ]);

            ProductVariant::where('product_id', $product->id)->update([
                'is_active'   => true,
                'archived_at' => null,
            ]);
        });

        $this->auditLogService->log(
            action: 'product.unarchived',
            entity: 'Product',
            entityId: $product->id,
            changes: ['status' => 'draft'],
            adminId: $admin?->id
        );

        return redirect()
            ->back()
            ->with('status', "Product [{$product->name}] restored to draft.");
    }
}
