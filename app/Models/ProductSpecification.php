<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class ProductSpecification extends Model
{
    protected $table = 'product_specifications';

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'spec_group',
        'spec_key',
        'spec_value',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProductSpecification $spec) {
            $hasProduct = ! empty($spec->product_id);
            $hasVariant = ! empty($spec->product_variant_id);

            if (($hasProduct && $hasVariant) || (! $hasProduct && ! $hasVariant)) {
                throw new InvalidArgumentException('ProductSpecification must reference either product_id or product_variant_id, exclusively.');
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
