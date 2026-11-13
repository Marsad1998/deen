<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'image_url',
        'badge_text',
        'models_count_text',
        'bg_color',
        'description',
        'meta_title',
        'meta_description',
        'sort_order',
        'is_active',
        'show_in_nav',
        'show_on_home',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_in_nav' => 'boolean',
            'show_on_home' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Scope for categories enabled in top navigation.
     */
    public function scopeNav($query)
    {
        return $query->where('is_active', true)
            ->where('show_in_nav', true)
            ->orderBy('sort_order');
    }

    /**
     * Scope for categories enabled on storefront homepage.
     */
    public function scopeHomepage($query)
    {
        return $query->where('is_active', true)
            ->where('show_on_home', true)
            ->orderBy('sort_order');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
