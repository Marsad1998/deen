<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class ProductVariantService
{
    /**
     * Create a product along with its initial default variant atomically.
     */
    public function createProductWithDefaultVariant(array $productData, array $variantData): array
    {
        return DB::transaction(function () use ($productData, $variantData) {
            $now = now();
            $productData['created_at'] = $now;
            $productData['updated_at'] = $now;

            $productId = DB::table('products')->insertGetId($productData);

            $variantData['product_id'] = $productId;
            $variantData['is_default'] = true;
            $variantData['is_active'] = $variantData['is_active'] ?? true;
            $variantData['created_at'] = $now;
            $variantData['updated_at'] = $now;

            $variantId = DB::table('product_variants')->insertGetId($variantData);

            return [
                'product_id' => $productId,
                'default_variant_id' => $variantId,
            ];
        });
    }

    /**
     * Replace the default variant for a product atomically.
     */
    public function setDefaultVariant(int $productId, int $newDefaultVariantId): void
    {
        DB::transaction(function () use ($productId, $newDefaultVariantId) {
            // Lock the target variant and verify ownership
            $variant = DB::table('product_variants')
                ->where('id', $newDefaultVariantId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (! $variant) {
                throw new InvalidArgumentException("Variant #{$newDefaultVariantId} does not belong to product #{$productId}.");
            }

            if (! $variant->is_active) {
                throw new RuntimeException("Cannot set inactive variant #{$newDefaultVariantId} as default.");
            }

            // Unset all existing default variants for this product
            DB::table('product_variants')
                ->where('product_id', $productId)
                ->where('is_default', 1)
                ->update([
                    'is_default' => 0,
                    'updated_at' => now(),
                ]);

            // Set the new default variant
            DB::table('product_variants')
                ->where('id', $newDefaultVariantId)
                ->update([
                    'is_default' => 1,
                    'updated_at' => now(),
                ]);
        });
    }

    /**
     * Publish or activate a product, ensuring it has an active default variant.
     */
    public function activateProduct(int $productId): void
    {
        DB::transaction(function () use ($productId) {
            $product = DB::table('products')->where('id', $productId)->lockForUpdate()->first();
            if (! $product) {
                throw new InvalidArgumentException("Product #{$productId} not found.");
            }

            $hasActiveDefault = DB::table('product_variants')
                ->where('product_id', $productId)
                ->where('is_default', 1)
                ->where('is_active', 1)
                ->whereNull('archived_at')
                ->exists();

            if (! $hasActiveDefault) {
                throw new RuntimeException("Product #{$productId} cannot be activated without an active, non-archived default variant.");
            }

            DB::table('products')->where('id', $productId)->update([
                'is_active' => 1,
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Guard variant deactivation: prevent deactivating the default variant of an active product.
     */
    public function deactivateVariant(int $variantId): void
    {
        DB::transaction(function () use ($variantId) {
            $variant = DB::table('product_variants')->where('id', $variantId)->lockForUpdate()->first();
            if (! $variant) {
                throw new InvalidArgumentException("Variant #{$variantId} not found.");
            }

            if ($variant->is_default) {
                $product = DB::table('products')->where('id', $variant->product_id)->first();
                if ($product && $product->is_active) {
                    throw new RuntimeException("Cannot deactivate default variant #{$variantId} while product #{$variant->product_id} is active. Assign a new default variant first.");
                }
            }

            DB::table('product_variants')->where('id', $variantId)->update([
                'is_active' => 0,
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Guard variant deletion / archiving: prevent deleting default variant of an active product.
     * When referenced in historical ledgers (inventory, prices, orders), archives rather than violating RESTRICT FK.
     */
    public function deleteVariant(int $variantId): void
    {
        DB::transaction(function () use ($variantId) {
            $variant = DB::table('product_variants')->where('id', $variantId)->lockForUpdate()->first();
            if (! $variant) {
                throw new InvalidArgumentException("Variant #{$variantId} not found.");
            }

            if ($variant->is_default) {
                $product = DB::table('products')->where('id', $variant->product_id)->first();
                if ($product && $product->is_active) {
                    throw new RuntimeException("Cannot delete default variant #{$variantId} while product #{$variant->product_id} is active. Assign a new default variant first.");
                }
            }

            // If referenced in historical ledgers, archive to preserve immutable records
            $hasHistories = DB::table('inventory_histories')->where('product_variant_id', $variantId)->exists()
                || DB::table('product_price_history')->where('product_variant_id', $variantId)->exists()
                || DB::table('order_items')->where('product_variant_id', $variantId)->exists();

            if ($hasHistories) {
                DB::table('product_variants')->where('id', $variantId)->update([
                    'is_active'   => 0,
                    'archived_at' => now(),
                    'updated_at'  => now(),
                ]);
            } else {
                DB::table('product_variants')->where('id', $variantId)->delete();
            }
        });
    }
}
