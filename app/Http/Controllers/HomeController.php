<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\HeroCmsService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Display the DEEN Electronics storefront homepage.
     */
    public function index(HeroCmsService $heroCmsService): View
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $featuredProducts = Product::with(['category', 'brand', 'defaultVariant', 'primaryMedia'])
            ->active()
            ->where('is_featured', true)
            ->limit(8)
            ->get();

        $latestProducts = Product::with(['category', 'brand', 'defaultVariant', 'primaryMedia'])
            ->active()
            ->latest('created_at')
            ->limit(8)
            ->get();

        $heroConfig = $heroCmsService->getConfig();
        $promoConfig = $heroCmsService->getPromoConfig();
        $trustConfig = $heroCmsService->getTrustConfig();
        $aboutConfig = $heroCmsService->getAboutConfig();

        return view('storefront.home', [
            'heroConfig'       => $heroConfig,
            'promoConfig'      => $promoConfig,
            'trustConfig'      => $trustConfig,
            'aboutConfig'      => $aboutConfig,
            'categories'       => $categories,
            'featuredProducts' => $featuredProducts,
            'latestProducts'   => $latestProducts,
            'contactPhone'     => $aboutConfig['phone'],
            'contactEmail'     => $aboutConfig['email'],
            'shopAddress'      => $aboutConfig['address'],
            'businessHours'    => $aboutConfig['daily_hours'],
        ]);
    }
}
