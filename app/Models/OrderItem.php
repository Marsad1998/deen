<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'sku',
        'unit_price',
        'quantity',
        'total_price',
        'attributes_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'          => 'decimal:2',
            'quantity'            => 'integer',
            'total_price'         => 'decimal:2',
            'attributes_snapshot' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Resolve the corresponding ProductVariant model by relation, SKU, or product ID.
     */
    public function resolveVariant(): ?ProductVariant
    {
        if ($this->relationLoaded('variant') && $this->variant) {
            return $this->variant;
        }

        if ($this->product_variant_id) {
            $v = ProductVariant::find($this->product_variant_id);
            if ($v) {
                return $v;
            }
        }

        if (! empty($this->sku)) {
            $v = ProductVariant::where('sku', $this->sku)->first();
            if ($v) {
                return $v;
            }
        }

        if ($this->product_id) {
            return ProductVariant::where('product_id', $this->product_id)->first();
        }

        return null;
    }
}
