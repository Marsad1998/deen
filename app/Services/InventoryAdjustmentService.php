<?php

namespace App\Services;

use App\Models\InventoryHistory;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InventoryAdjustmentService
{
    /**
     * Adjust stock quantity for a variant and record historical movement atomically.
     */
    public function adjustStock(
        int $variantId,
        int $changeQty,
        string $reason,
        ?int $adminId = null
    ): array {
        if ($changeQty === 0) {
            throw new InvalidArgumentException('Inventory adjustment quantity must be non-zero.');
        }

        return DB::transaction(function () use ($variantId, $changeQty, $reason, $adminId) {
            $variant = ProductVariant::where('id', $variantId)->lockForUpdate()->first();
            if (! $variant) {
                throw new InvalidArgumentException("Product variant #{$variantId} not found.");
            }

            $prevQty = $variant->stock_quantity;
            $newQty = $prevQty + $changeQty;

            if ($newQty < 0) {
                throw new RuntimeException("Insufficient stock. Cannot adjust by {$changeQty}; current stock is {$prevQty}.");
            }

            // Determine stock status
            $stockStatus = $newQty > 0 ? 'in_stock' : 'out_of_stock';

            // Update variant stock
            $variant->update([
                'stock_quantity' => $newQty,
                'stock_status' => $stockStatus,
                'updated_at' => now(),
            ]);

            // Record immutable history log
            $history = InventoryHistory::create([
                'product_variant_id' => $variantId,
                'change_qty' => $changeQty,
                'previous_qty' => $prevQty,
                'new_qty' => $newQty,
                'reason' => $reason,
                'admin_id' => $adminId,
                'created_at' => now(),
            ]);

            return [
                'variant_id' => $variantId,
                'previous_qty' => $prevQty,
                'new_qty' => $newQty,
                'change_qty' => $changeQty,
                'history_id' => $history->id,
            ];
        });
    }

    /**
     * Set explicit new stock level and record historical movement.
     */
    public function setStock(
        int $variantId,
        int $newQty,
        string $reason,
        ?int $adminId = null
    ): array {
        if ($newQty < 0) {
            throw new InvalidArgumentException('Stock quantity cannot be negative.');
        }

        $variant = ProductVariant::findOrFail($variantId);
        $diff = $newQty - $variant->stock_quantity;

        if ($diff === 0) {
            return [
                'variant_id' => $variantId,
                'previous_qty' => $variant->stock_quantity,
                'new_qty' => $newQty,
                'change_qty' => 0,
            ];
        }

        return $this->adjustStock($variantId, $diff, $reason, $adminId);
    }
}
