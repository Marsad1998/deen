<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share curated navigation categories across storefront layouts
        View::composer(['layouts.storefront', 'storefront.*'], function ($view) {
            try {
                $navCategories = Category::nav()->get(['id', 'name', 'slug', 'badge_text']);
                if ($navCategories->isEmpty()) {
                    $navCategories = Category::where('is_active', true)->orderBy('sort_order')->limit(7)->get(['id', 'name', 'slug', 'badge_text']);
                }
            } catch (\Throwable $e) {
                $navCategories = collect();
            }

            $view->with('navCategories', $navCategories);
        });
    }
}
