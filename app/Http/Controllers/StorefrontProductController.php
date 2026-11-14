<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontProductController extends Controller
{
    /**
     * Display the searchable and filterable storefront catalogue.
     */
    public function catalog(Request $request): View
    {
        $query = Product::with(['category', 'brand', 'defaultVariant', 'primaryMedia'])
            ->active();

        // 1. Filter by category slug
        $currentCategory = null;
        if ($categorySlug = $request->input('category')) {
            $currentCategory = Category::where('slug', $categorySlug)->first();
            if ($currentCategory) {
                $query->where('products.category_id', $currentCategory->id);
            }
        }

        // 2. Filter by brand slug
        $currentBrand = null;
        if ($brandSlug = $request->input('brand')) {
            $currentBrand = Brand::where('slug', $brandSlug)->first();
            if ($currentBrand) {
                $query->where('products.brand_id', $currentBrand->id);
            }
        }

        // 3. Price Filter (Min / Max PKR)
        $minPrice = $request->filled('min_price') && is_numeric($request->input('min_price')) ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') && is_numeric($request->input('max_price')) ? (float) $request->input('max_price') : null;

        if ($minPrice !== null || $maxPrice !== null) {
            $query->whereHas('variants', function ($vq) use ($minPrice, $maxPrice) {
                $vq->where('is_default', true);
                if ($minPrice !== null) {
                    $vq->where('price', '>=', $minPrice);
                }
                if ($maxPrice !== null) {
                    $vq->where('price', '<=', $maxPrice);
                }
            });
        }

        // 4. In Stock Filter
        $inStockOnly = $request->boolean('in_stock');
        if ($inStockOnly) {
            $query->whereHas('variants', function ($vq) {
                $vq->where('is_default', true)
                   ->where('stock_quantity', '>', 0);
            });
        }

        // 5. Official Warranty Filter
        $hasWarranty = $request->boolean('has_warranty');
        if ($hasWarranty) {
            $query->whereNotNull('products.warranty_text')
                  ->where('products.warranty_text', '!=', '');
        }

        // 6. Featured / Deals Filter
        $featuredOnly = $request->boolean('featured');
        if ($featuredOnly) {
            $query->where('products.is_featured', true);
        }

        // 7. Delivery / Serviceability Type Filter
        $deliveryType = $request->input('delivery_type');
        $bulkySlugs = ['scooters', 'furniture', 'refrigerators-freezers', 'air-conditioners', 'washing-machines'];
        if ($deliveryType === 'bulky') {
            $query->whereHas('category', function ($cq) use ($bulkySlugs) {
                $cq->whereIn('slug', $bulkySlugs);
            });
        } elseif ($deliveryType === 'standard') {
            $query->whereHas('category', function ($cq) use ($bulkySlugs) {
                $cq->whereNotIn('slug', $bulkySlugs);
            });
        }

        // 8. Live Text Search
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // 9. Sorting
        $sortBy = $request->input('sort', 'newest');
        if ($sortBy === 'price_low') {
            $query->join('product_variants as sort_pv', function ($join) {
                $join->on('products.id', '=', 'sort_pv.product_id')
                     ->where('sort_pv.is_default', '=', 1);
            })->orderBy('sort_pv.price', 'asc')->select('products.*');
        } elseif ($sortBy === 'price_high') {
            $query->join('product_variants as sort_pv', function ($join) {
                $join->on('products.id', '=', 'sort_pv.product_id')
                     ->where('sort_pv.is_default', '=', 1);
            })->orderBy('sort_pv.price', 'desc')->select('products.*');
        } elseif ($sortBy === 'name') {
            $query->orderBy('products.name', 'asc');
        } else {
            $query->orderBy('products.created_at', 'desc');
        }

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::withCount(['products' => fn ($q) => $q->active()])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $brands = Brand::withCount(['products' => fn ($q) => $q->active()])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Calculate count of active filters
        $activeFiltersCount = 0;
        if ($currentCategory) $activeFiltersCount++;
        if ($currentBrand) $activeFiltersCount++;
        if ($minPrice !== null || $maxPrice !== null) $activeFiltersCount++;
        if ($inStockOnly) $activeFiltersCount++;
        if ($hasWarranty) $activeFiltersCount++;
        if ($featuredOnly) $activeFiltersCount++;
        if ($deliveryType) $activeFiltersCount++;
        if (! empty($search)) $activeFiltersCount++;

        return view('storefront.catalog', compact(
            'products',
            'categories',
            'brands',
            'currentCategory',
            'currentBrand',
            'minPrice',
            'maxPrice',
            'inStockOnly',
            'hasWarranty',
            'featuredOnly',
            'deliveryType',
            'search',
            'sortBy',
            'activeFiltersCount'
        ));
    }

    /**
     * Provide JSON autocomplete search suggestions with product images.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([
                'query'   => $term,
                'results' => [],
                'total'   => 0,
            ]);
        }

        $baseQuery = Product::active()->search($term);
        $total = (clone $baseQuery)->count();

        $products = $baseQuery->with(['category', 'brand', 'defaultVariant', 'primaryMedia'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('name', 'asc')
            ->limit(6)
            ->get();

        $results = $products->map(function ($product) {
            $default = $product->defaultVariant;
            return [
                'id'             => $product->id,
                'name'           => $product->name,
                'slug'           => $product->slug,
                'url'            => route('storefront.product', $product->slug),
                'sku'            => $product->sku ?: ($default?->sku ?: 'DEEN-ITEM'),
                'category'       => $product->category?->name ?? 'Electronics',
                'brand'          => $product->brand?->name,
                'thumbnail_url'  => asset($product->thumbnail_url),
                'price'          => $default ? number_format($default->price, 0) : null,
                'original_price' => ($default && $default->original_price && $default->original_price > $default->price)
                    ? number_format($default->original_price, 0)
                    : null,
                'badge'          => $product->badge_text,
                'in_stock'       => ($default?->stock_quantity ?? 0) > 0,
                'warranty'       => $product->warranty_text,
            ];
        });

        return response()->json([
            'query'   => $term,
            'results' => $results,
            'total'   => $total,
        ]);
    }

    /**
     * Display the detailed product presentation page.
     */
    public function show(string $slug): View
    {
        $product = Product::with([
            'category',
            'brand',
            'variants' => fn ($q) => $q->where('is_active', true),
            'defaultVariant',
            'media',
            'specifications' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
        ])
        ->where('slug', $slug)
        ->active()
        ->firstOrFail();

        // Related products in the same category
        $relatedProducts = Product::with(['category', 'defaultVariant', 'primaryMedia'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->limit(4)
            ->get();

        // Prepare pre-filled WhatsApp enquiry link
        $skuText = $product->sku ?: ($product->defaultVariant?->sku ?: 'N/A');
        $whatsappMessage = "Assalam o Alaikum DEEN Electronics,\n\nI am interested in:\n*{$product->name}*\nSKU: {$skuText}\n\nPlease share price confirmation, availability, and delivery details.";
        $whatsappUrl = "https://wa.me/923007769932?text=" . urlencode($whatsappMessage);

        return view('storefront.product', compact('product', 'relatedProducts', 'whatsappUrl'));
    }
}
