<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'badge_text',
        'short_description',
        'full_description',
        'main_image',
        'gallery_images',
        'warranty_text',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'rating',
        'reviews_count',
        'is_featured',
        'is_active',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'gallery_images' => 'array',
            'rating' => 'decimal:2',
            'reviews_count' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class, 'product_id')->where('is_default', true);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class, 'product_id')->orderBy('sort_order')->orderBy('id');
    }

    public function primaryMedia(): HasOne
    {
        return $this->hasOne(ProductMedia::class, 'product_id')->where('is_primary', true);
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class, 'product_id')->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $clean = trim($term);

        return $query->where(function (Builder $q) use ($clean) {
            $q->where('name', 'LIKE', "%{$clean}%")
              ->orWhere('slug', 'LIKE', "%{$clean}%")
              ->orWhere('sku', 'LIKE', "%{$clean}%")
              ->orWhereHas('variants', function (Builder $vq) use ($clean) {
                  $vq->where('sku', 'LIKE', "%{$clean}%")
                     ->orWhere('name', 'LIKE', "%{$clean}%");
              });
        });
    }

    public function getIsArchivedAttribute(): bool
    {
        return $this->archived_at !== null;
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->primaryMedia && $this->primaryMedia->url) {
            return $this->primaryMedia->url;
        }

        if ($this->main_image) {
            return $this->main_image;
        }

        $firstMedia = $this->media->first();
        if ($firstMedia && $firstMedia->url) {
            return $firstMedia->url;
        }

        return '/images/placeholder.svg';
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: "{$this->name} — DEEN Electronics Lahore";
    }

    public function getSeoDescriptionAttribute(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }

        if ($this->short_description) {
            return \Illuminate\Support\Str::limit(strip_tags($this->short_description), 160);
        }

        $brandName = $this->brand?->name ?? 'DEEN Electronics';
        $catName = $this->category?->name ?? 'Home Appliances';
        $skuText = $this->sku ? " SKU: {$this->sku}." : '';

        return \Illuminate\Support\Str::limit("Buy {$this->name} by {$brandName} in {$catName} at DEEN Electronics Lahore.{$skuText} Official warranty and nationwide delivery.", 160);
    }

    public function getJsonLdAttribute(): array
    {
        $defaultVariant = $this->defaultVariant ?: $this->variants->first();
        $price = $defaultVariant ? (float) $defaultVariant->price : 0.0;
        $inStock = $defaultVariant && $defaultVariant->stock_quantity > 0;

        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $this->name,
            'image'       => [asset($this->thumbnail_url)],
            'description' => $this->seo_description,
            'sku'         => $this->sku ?: ('DEEN-' . $this->id),
            'brand'       => [
                '@type' => 'Brand',
                'name'  => $this->brand?->name ?? 'DEEN Electronics',
            ],
            'category'    => $this->category?->name ?? 'Electronics',
            'offers'      => [
                '@type'         => 'Offer',
                'url'           => route('storefront.product', $this->slug),
                'priceCurrency' => 'PKR',
                'price'         => $price,
                'priceValidUntil' => date('Y-12-31'),
                'availability'  => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller'        => [
                    '@type' => 'Store',
                    'name'  => 'DEEN Electronics Lahore',
                ],
            ],
        ];
    }
}
