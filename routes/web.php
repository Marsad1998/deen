<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductMediaController;
use App\Http\Controllers\Admin\ProductSpecificationController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\StoreSettingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StorefrontProductController;
use Illuminate\Support\Facades\Route;

// Storefront & Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalog', [StorefrontProductController::class, 'catalog'])->name('storefront.catalog');
Route::get('/search/autocomplete', [StorefrontProductController::class, 'autocomplete'])->name('storefront.search.autocomplete');
Route::get('/product/{slug}', [StorefrontProductController::class, 'show'])->name('storefront.product');

// Shopping Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{key}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Checkout & Order Placement Routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success/{order_number}', [CheckoutController::class, 'success'])->name('checkout.success');

Route::get('/health', [HealthController::class, 'check'])->name('health.check');
Route::get('/health/db', [HealthController::class, 'checkDb'])->name('health.db');

// Administrative Authentication Routes (Guest)
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
});

// Protected Administrative Routes (Requires admin.auth)
Route::prefix('admin')->middleware(['admin.auth'])->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Order Management
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->middleware('admin.permission:orders.view')->name('admin.orders.index');
        Route::get('/{order}', [OrderController::class, 'show'])->middleware('admin.permission:orders.view')->name('admin.orders.show');
        Route::post('/{order}/status', [OrderController::class, 'updateStatus'])->middleware('admin.permission:orders.edit')->name('admin.orders.update-status');
        Route::get('/{order}/invoice', [OrderController::class, 'printInvoice'])->middleware('admin.permission:orders.view')->name('admin.orders.invoice');
    });

    // Product Catalog Management
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->middleware('admin.permission:products.view')->name('admin.products.index');
        Route::get('/create', [ProductController::class, 'create'])->middleware('admin.permission:products.create')->name('admin.products.create');
        Route::post('/', [ProductController::class, 'store'])->middleware('admin.permission:products.create')->name('admin.products.store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->middleware('admin.permission:products.edit')->name('admin.products.edit');
        Route::put('/{product}', [ProductController::class, 'update'])->middleware('admin.permission:products.edit')->name('admin.products.update');
        Route::post('/{product}/archive', [ProductController::class, 'archive'])->middleware('admin.permission:products.delete')->name('admin.products.archive');
        Route::post('/{product}/unarchive', [ProductController::class, 'unarchive'])->middleware('admin.permission:products.delete')->name('admin.products.unarchive');

        // Product Variants
        Route::post('/{product}/variants', [ProductVariantController::class, 'store'])->middleware('admin.permission:products.edit')->name('admin.products.variants.store');
        Route::put('/{product}/variants/{variant}', [ProductVariantController::class, 'update'])->middleware('admin.permission:products.edit')->name('admin.products.variants.update');
        Route::post('/{product}/variants/{variant}/set-default', [ProductVariantController::class, 'setDefault'])->middleware('admin.permission:products.edit')->name('admin.products.variants.set-default');
        Route::delete('/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])->middleware('admin.permission:products.edit')->name('admin.products.variants.destroy');

        // Product Media
        Route::post('/{product}/media', [ProductMediaController::class, 'upload'])->middleware('admin.permission:products.edit')->name('admin.products.media.upload');
        Route::post('/{product}/media/{media}/set-primary', [ProductMediaController::class, 'setPrimary'])->middleware('admin.permission:products.edit')->name('admin.products.media.set-primary');
        Route::post('/{product}/media/reorder', [ProductMediaController::class, 'reorder'])->middleware('admin.permission:products.edit')->name('admin.products.media.reorder');
        Route::delete('/{product}/media/{media}', [ProductMediaController::class, 'destroy'])->middleware('admin.permission:products.edit')->name('admin.products.media.destroy');

        // Product Specifications
        Route::post('/{product}/specifications', [ProductSpecificationController::class, 'store'])->middleware('admin.permission:products.edit')->name('admin.products.specifications.store');
        Route::delete('/{product}/specifications/{specification}', [ProductSpecificationController::class, 'destroy'])->middleware('admin.permission:products.edit')->name('admin.products.specifications.destroy');
    });

    // Categories Management
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('admin.categories.index');
        Route::get('/create', [CategoryController::class, 'create'])->name('admin.categories.create');
        Route::post('/', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
        Route::post('/{category}/toggle-active', [CategoryController::class, 'toggleActive'])->name('admin.categories.toggle-active');
    });

    // Brands Management
    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('admin.brands.index');
        Route::get('/create', [BrandController::class, 'create'])->name('admin.brands.create');
        Route::post('/', [BrandController::class, 'store'])->name('admin.brands.store');
        Route::get('/{brand}/edit', [BrandController::class, 'edit'])->name('admin.brands.edit');
        Route::put('/{brand}', [BrandController::class, 'update'])->name('admin.brands.update');
        Route::delete('/{brand}', [BrandController::class, 'destroy'])->name('admin.brands.destroy');
        Route::post('/{brand}/toggle-active', [BrandController::class, 'toggleActive'])->name('admin.brands.toggle-active');
    });

    // Inventory Management
    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->middleware('admin.permission:inventory.view')->name('admin.inventory.index');
        Route::get('/create', [InventoryController::class, 'create'])->middleware('admin.permission:inventory.adjust')->name('admin.inventory.create');
        Route::post('/store', [InventoryController::class, 'store'])->middleware('admin.permission:inventory.adjust')->name('admin.inventory.store');
        Route::post('/{variant}/adjust', [InventoryController::class, 'adjust'])->middleware('admin.permission:inventory.adjust')->name('admin.inventory.adjust');
    });

    // Storefront CMS Management Hub & Sections
    Route::prefix('cms')->group(function () {
        Route::get('/', [CmsController::class, 'index'])->name('admin.cms.index');
        Route::get('/hero', [CmsController::class, 'hero'])->name('admin.cms.hero');
        Route::post('/hero', [CmsController::class, 'updateHero'])->name('admin.cms.hero.update');
        Route::get('/promo', [CmsController::class, 'promo'])->name('admin.cms.promo');
        Route::post('/promo', [CmsController::class, 'updatePromo'])->name('admin.cms.promo.update');
        Route::get('/trust', [CmsController::class, 'trustFeatures'])->name('admin.cms.trust');
        Route::post('/trust', [CmsController::class, 'updateTrustFeatures'])->name('admin.cms.trust.update');
        Route::get('/about', [CmsController::class, 'showroomAbout'])->name('admin.cms.about');
        Route::post('/about', [CmsController::class, 'updateShowroomAbout'])->name('admin.cms.about.update');
    });

    // Store Configuration & Settings
    Route::prefix('settings')->group(function () {
        Route::get('/store', [StoreSettingController::class, 'index'])->middleware('admin.permission:settings.view')->name('admin.settings.store');
        Route::post('/store', [StoreSettingController::class, 'update'])->middleware('admin.permission:settings.edit')->name('admin.settings.store.update');
    });

    // Payment Methods Management
    Route::prefix('payment-methods')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index'])->middleware('admin.permission:settings.view')->name('admin.payment-methods.index');
        Route::get('/{paymentMethod}/edit', [PaymentMethodController::class, 'edit'])->middleware('admin.permission:settings.edit')->name('admin.payment-methods.edit');
        Route::put('/{paymentMethod}', [PaymentMethodController::class, 'update'])->middleware('admin.permission:settings.edit')->name('admin.payment-methods.update');
        Route::post('/{paymentMethod}/toggle', [PaymentMethodController::class, 'toggle'])->middleware('admin.permission:settings.edit')->name('admin.payment-methods.toggle');
    });

    // Diagnostic & RBAC Verification Routes: restricted strictly to local and testing environments
    if (app()->environment('local', 'testing')) {
        Route::prefix('test')->group(function () {
            Route::get('/super-admin-only', function () {
                return response()->json(['message' => 'Super Admin Authorized']);
            })->middleware('admin.role:Super Admin')->name('admin.test.superadmin');

            Route::get('/products-view', function () {
                return response()->json(['message' => 'Products View Authorized']);
            })->middleware('admin.permission:products.view')->name('admin.test.products.view');

            Route::get('/settings-view', function () {
                return response()->json(['message' => 'Settings View Authorized']);
            })->middleware('admin.permission:settings.view')->name('admin.test.settings.view');

            Route::get('/admins-manage', function () {
                return response()->json(['message' => 'Admins Manage Authorized']);
            })->middleware('admin.permission:admins.manage')->name('admin.test.admins.manage');
        });
    }
});
