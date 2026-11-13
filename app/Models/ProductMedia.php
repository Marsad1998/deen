<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class ProductMedia extends Model
{
    protected $table = 'product_media';

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'media_type',
        'url',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProductMedia $media) {
            $hasProduct = ! empty($media->product_id);
            $hasVariant = ! empty($media->product_variant_id);

            if (($hasProduct && $hasVariant) || (! $hasProduct && ! $hasVariant)) {
                throw new InvalidArgumentException('ProductMedia must reference either product_id or product_variant_id, exclusively.');
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

    public function isProductMedia(): bool
    {
        return $this->product_id !== null;
    }

    public function isVariantMedia(): bool
    {
        return $this->product_variant_id !== null;
    }
}
