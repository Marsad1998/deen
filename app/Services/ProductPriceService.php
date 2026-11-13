<?php

namespace App\Services;

use App\Models\ProductPriceHistory;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProductPriceService
{
    /**
     * Update variant price and record price history atomically if changed.
     */
    public function updatePrice(
        int $variantId,
        float $newPrice,
        ?float $newOriginalPrice = null,
        ?string $reason = null,
        ?int $adminId = null,
        string $currency = 'PKR'
    ): bool {
        if ($newPrice < 0) {
            throw new InvalidArgumentException('Product price cannot be negative.');
        }

        if ($newOriginalPrice !== null && $newOriginalPrice < 0) {
            throw new InvalidArgumentException('Original/compare-at price cannot be negative.');
        }

        return DB::transaction(function () use ($variantId, $newPrice, $newOriginalPrice, $reason, $adminId, $currency) {
            $variant = ProductVariant::where('id', $variantId)->lockForUpdate()->first();
            if (! $variant) {
                throw new InvalidArgumentException("Product variant #{$variantId} not found.");
            }

            $oldPrice = (float) $variant->price;
            $hasPriceChanged = abs($oldPrice - $newPrice) >= 0.001;

            $variant->update([
                'price' => $newPrice,
                'original_price' => $newOriginalPrice,
                'updated_at' => now(),
            ]);

            if ($hasPriceChanged) {
                ProductPriceHistory::create([
                    'product_variant_id' => $variantId,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'currency' => $currency,
                    'effective_date' => now(),
                    'changed_by_admin_id' => $adminId,
                    'reason' => $reason ?? 'Administrative price adjustment',
                    'created_at' => now(),
                ]);
            }

            return true;
        });
    }
}
