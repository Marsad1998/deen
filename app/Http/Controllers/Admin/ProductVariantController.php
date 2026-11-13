<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductVariantRequest;
use App\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\AuditLogService;
use App\Services\InventoryAdjustmentService;
use App\Services\ProductPriceService;
use App\Services\ProductVariantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductVariantController extends Controller
{
    public function __construct(
        protected ProductVariantService $variantService,
        protected ProductPriceService $priceService,
        protected InventoryAdjustmentService $inventoryService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Store a new variant for an existing product.
     */
    public function store(StoreProductVariantRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $admin = Auth::guard('admin')->user();

        $variant = DB::transaction(function () use ($product, $validated, $admin) {
            $isDefault = (bool) ($validated['is_default'] ?? false);

            $variant = ProductVariant::create([
                'product_id'     => $product->id,
                'sku'            => $validated['sku'],
                'name'           => $validated['name'],
                'price'          => $validated['price'],
                'original_price' => $validated['original_price'] ?? null,
                'stock_quantity' => $validated['stock_quantity'],
                'stock_status'   => $validated['stock_status'] ?? ($validated['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock'),
                'attributes'     => $validated['attributes'] ?? null,
                'is_default'     => false, // set via ProductVariantService if needed
                'is_active'      => (bool) ($validated['is_active'] ?? true),
            ]);

            // Initial stock movement history
            if ($variant->stock_quantity > 0) {
                DB::table('inventory_histories')->insert([
                    'product_variant_id' => $variant->id,
                    'change_qty'         => $variant->stock_quantity,
                    'previous_qty'       => 0,
                    'new_qty'            => $variant->stock_quantity,
                    'reason'             => 'Initial variant creation',
                    'admin_id'           => $admin?->id,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            // If requested as default, use the atomic service
            if ($isDefault) {
                $this->variantService->setDefaultVariant($product->id, $variant->id);
            }

            return $variant;
        });

        $this->auditLogService->log(
            action: 'variant.created',
            entity: 'ProductVariant',
            entityId: $variant->id,
            changes: [
                'product_id' => $product->id,
                'sku'        => $variant->sku,
                'name'       => $variant->name,
                'price'      => $variant->price,
            ],
            adminId: $admin?->id
        );

        return redirect()
            ->route('admin.products.edit', $product->id)
            ->with('status', "Variant [{$variant->name}] added successfully.");
    }

    /**
     * Update an existing product variant.
     */
    public function update(
        UpdateProductVariantRequest $request,
        Product $product,
        ProductVariant $variant
    ): RedirectResponse {
        $validated = $request->validated();
        $admin = Auth::guard('admin')->user();

        DB::transaction(function () use ($product, $variant, $validated, $admin) {
            $variant->update([
                'sku'          => $validated['sku'],
                'name'         => $validated['name'],
                'stock_status' => $validated['stock_status'] ?? ($validated['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock'),
                'attributes'   => $validated['attributes'] ?? null,
                'is_active'    => (bool) ($validated['is_active'] ?? true),
            ]);

            // Check price change and update via ProductPriceService
            $this->priceService->updatePrice(
                variantId: $variant->id,
                newPrice: (float) $validated['price'],
                newOriginalPrice: isset($validated['original_price']) ? (float) $validated['original_price'] : null,
                reason: $validated['price_change_reason'] ?? 'Administrative update',
                adminId: $admin?->id
            );

            // Check stock change and update via InventoryAdjustmentService
            if ((int) $validated['stock_quantity'] !== $variant->stock_quantity) {
                $this->inventoryService->setStock(
                    variantId: $variant->id,
                    newQty: (int) $validated['stock_quantity'],
                    reason: 'Stock updated from variant editor',
                    adminId: $admin?->id
                );
            }
        });

        $this->auditLogService->log(
            action: 'variant.updated',
            entity: 'ProductVariant',
            entityId: $variant->id,
            changes: [
                'sku'   => $variant->sku,
                'price' => $validated['price'],
            ],
            adminId: $admin?->id
        );

        return redirect()
            ->route('admin.products.edit', $product->id)
            ->with('status', "Variant [{$variant->name}] updated.");
    }

    /**
     * Set a variant as the product's default variant.
     */
    public function setDefault(Product $product, ProductVariant $variant): RedirectResponse
    {
        try {
            $this->variantService->setDefaultVariant($product->id, $variant->id);

            $this->auditLogService->log(
                action: 'variant.set_default',
                entity: 'ProductVariant',
                entityId: $variant->id,
                changes: [
                    'product_id' => $product->id,
                    'sku'        => $variant->sku,
                ],
                adminId: Auth::guard('admin')->id()
            );

            return redirect()
                ->route('admin.products.edit', $product->id)
                ->with('status', "Variant [{$variant->name}] is now the default variant.");
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['variant' => $e->getMessage()]);
        }
    }

    /**
     * Delete a variant using the atomic ProductVariantService guard.
     */
    public function destroy(Product $product, ProductVariant $variant): RedirectResponse
    {
        try {
            $sku = $variant->sku;
            $this->variantService->deleteVariant($variant->id);

            $this->auditLogService->log(
                action: 'variant.deleted',
                entity: 'ProductVariant',
                entityId: $variant->id,
                changes: ['sku' => $sku],
                adminId: Auth::guard('admin')->id()
            );

            return redirect()
                ->route('admin.products.edit', $product->id)
                ->with('status', "Variant [{$sku}] deleted.");
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['variant' => $e->getMessage()]);
        }
    }
}
