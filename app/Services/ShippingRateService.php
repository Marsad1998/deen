<?php

namespace App\Services;

use App\Exceptions\ShippingRateCalculationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ShippingRateService
{
    /**
     * Create or update a shipping rate with overlap validation and pessimistic locking.
     */
    public function saveRate(array $rateData, ?int $rateId = null): int
    {
        return DB::transaction(function () use ($rateData, $rateId) {
            $methodId = $rateData['shipping_method_id'];
            $region   = $rateData['region'] ?? 'ALL';
            $minAmt   = (float) ($rateData['min_order_amount'] ?? 0.00);
            $maxAmt   = isset($rateData['max_order_amount']) && $rateData['max_order_amount'] !== null
                ? (float) $rateData['max_order_amount']
                : null;

            if ($maxAmt !== null && $maxAmt <= $minAmt) {
                throw new InvalidArgumentException("max_order_amount ({$maxAmt}) must be strictly greater than min_order_amount ({$minAmt}).");
            }

            // Lock existing active rates for the same method and region
            $query = DB::table('shipping_rates')
                ->where('shipping_method_id', $methodId)
                ->where('region', $region)
                ->where('is_active', 1)
                ->lockForUpdate();

            if ($rateId !== null) {
                $query->where('id', '!=', $rateId);
            }

            $existingRates = $query->get();

            foreach ($existingRates as $existing) {
                $eMin = (float) $existing->min_order_amount;
                $eMax = $existing->max_order_amount !== null ? (float) $existing->max_order_amount : null;

                // Check interval overlap: [minAmt, maxAmt] vs [eMin, eMax]
                $overlaps = ($maxAmt === null || $maxAmt >= $eMin) && ($eMax === null || $minAmt <= $eMax);

                if ($overlaps) {
                    $eMaxStr = $eMax !== null ? (string) $eMax : '∞';
                    $newMaxStr = $maxAmt !== null ? (string) $maxAmt : '∞';
                    throw new InvalidArgumentException(
                        "Shipping rate [{$minAmt} - {$newMaxStr}] overlaps with existing rate #{$existing->id} [{$eMin} - {$eMaxStr}] for method #{$methodId} and region '{$region}'."
                    );
                }
            }

            $now = now();
            if ($rateId === null) {
                $rateData['created_at'] = $now;
                $rateData['updated_at'] = $now;
                return DB::table('shipping_rates')->insertGetId($rateData);
            }

            $rateData['updated_at'] = $now;
            DB::table('shipping_rates')->where('id', $rateId)->update($rateData);
            return $rateId;
        });
    }

    /**
     * Resolve shipping rate for checkout.
     *
     * Fails safely: if multiple rates match, throws ShippingRateCalculationException
     * rather than silently choosing with LIMIT 1.
     */
    public function resolveRate(int $shippingMethodId, string $region, float $orderAmount): ?object
    {
        // First check rates specifically matching the region
        $rates = DB::table('shipping_rates')
            ->where('shipping_method_id', $shippingMethodId)
            ->where('region', $region)
            ->where('is_active', 1)
            ->where('min_order_amount', '<=', $orderAmount)
            ->where(function ($q) use ($orderAmount) {
                $q->whereNull('max_order_amount')
                  ->orWhere('max_order_amount', '>=', $orderAmount);
            })
            ->get();

        // If no region-specific rates and region != 'ALL', fall back to 'ALL'
        if ($rates->isEmpty() && $region !== 'ALL') {
            $rates = DB::table('shipping_rates')
                ->where('shipping_method_id', $shippingMethodId)
                ->where('region', 'ALL')
                ->where('is_active', 1)
                ->where('min_order_amount', '<=', $orderAmount)
                ->where(function ($q) use ($orderAmount) {
                    $q->whereNull('max_order_amount')
                      ->orWhere('max_order_amount', '>=', $orderAmount);
                })
                ->get();
        }

        if ($rates->count() > 1) {
            throw new ShippingRateCalculationException(
                "Ambiguous shipping rate configuration: {$rates->count()} rates matched shipping method #{$shippingMethodId}, region '{$region}', order amount {$orderAmount}."
            );
        }

        return $rates->first();
    }
}
