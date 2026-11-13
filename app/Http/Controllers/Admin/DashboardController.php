<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryHistory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the comprehensive admin dashboard with real-time analytics across
     * orders, revenue, catalog (products, categories, brands), and inventory health.
     */
    public function index(): View
    {
        $admin = Auth::guard('admin')->user();
        $admin->load('role.permissions');

        // 1. Order & Sales Analytics
        $totalOrdersCount = Order::count();
        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $confirmedOrdersCount = Order::whereIn('status', ['confirmed', 'processing'])->count();
        $shippedOrdersCount = Order::where('status', 'shipped')->count();
        $deliveredOrdersCount = Order::where('status', 'delivered')->count();
        $cancelledOrdersCount = Order::where('status', 'cancelled')->count();

        // Revenue from active/fulfilled orders
        $grossSalesVolume = (float) Order::whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])->sum('total_amount');
        $allOrdersVolume = (float) Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $averageOrderValue = $totalOrdersCount > 0 
            ? (float) (Order::where('status', '!=', 'cancelled')->avg('total_amount') ?? 0)
            : 0;

        // Payment method breakdown
        $codOrdersCount = Order::where('payment_method', 'COD')->count();
        $bankTransferOrdersCount = Order::where('payment_method', 'bank_transfer')->count();
        $otherPaymentOrdersCount = $totalOrdersCount - ($codOrdersCount + $bankTransferOrdersCount);

        // 2. Catalog Statistics (Products, Categories, Brands)
        $totalProductsCount = Product::count();
        $activeProductsCount = Product::where('is_active', true)->whereNull('archived_at')->count();
        $archivedProductsCount = Product::whereNotNull('archived_at')->orWhere('is_active', false)->count();

        $totalCategoriesCount = Category::count();
        $activeCategoriesCount = Category::where('is_active', true)->count();
        $topCategories = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(5)
            ->get();

        $totalBrandsCount = Brand::count();
        $activeBrandsCount = Brand::where('is_active', true)->count();
        $topBrands = Brand::withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(5)
            ->get();

        // 3. Inventory & Warehouse Statistics
        $totalStockUnits = (int) ProductVariant::sum('stock_quantity');
        $totalInventoryValuation = (float) (ProductVariant::selectRaw('SUM(stock_quantity * price) as val')->value('val') ?? 0);
        $inStockVariantsCount = ProductVariant::where('stock_quantity', '>', 10)->count();
        $lowStockVariantsCount = ProductVariant::where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10)->count();
        $outOfStockVariantsCount = ProductVariant::where('stock_quantity', '<=', 0)->count();

        // Critical low stock alert variants (<= 10 units)
        $criticalStockVariants = ProductVariant::with('product')
            ->where('stock_quantity', '<=', 10)
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get();

        // 4. Recent Data Feeds
        $recentOrders = Order::withCount('items')
            ->latest()
            ->take(6)
            ->get();

        $recentStockMovements = InventoryHistory::with(['variant.product', 'admin'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'admin'                   => $admin,
            'role'                    => $admin->role,
            // Orders
            'totalOrdersCount'        => $totalOrdersCount,
            'pendingOrdersCount'      => $pendingOrdersCount,
            'confirmedOrdersCount'    => $confirmedOrdersCount,
            'shippedOrdersCount'      => $shippedOrdersCount,
            'deliveredOrdersCount'    => $deliveredOrdersCount,
            'cancelledOrdersCount'    => $cancelledOrdersCount,
            'grossSalesVolume'        => $grossSalesVolume,
            'totalRevenue'            => $grossSalesVolume, // Backward compatibility for tests
            'allOrdersVolume'         => $allOrdersVolume,
            'averageOrderValue'       => $averageOrderValue,
            'codOrdersCount'          => $codOrdersCount,
            'bankTransferOrdersCount' => $bankTransferOrdersCount,
            'otherPaymentOrdersCount' => $otherPaymentOrdersCount,
            // Catalog
            'totalProductsCount'      => $totalProductsCount,
            'activeProductsCount'     => $activeProductsCount,
            'archivedProductsCount'   => $archivedProductsCount,
            'totalCategoriesCount'    => $totalCategoriesCount,
            'activeCategoriesCount'   => $activeCategoriesCount,
            'topCategories'           => $topCategories,
            'totalBrandsCount'        => $totalBrandsCount,
            'activeBrandsCount'       => $activeBrandsCount,
            'topBrands'               => $topBrands,
            // Inventory
            'totalStockUnits'         => $totalStockUnits,
            'totalInventoryValuation' => $totalInventoryValuation,
            'inStockVariantsCount'    => $inStockVariantsCount,
            'lowStockVariantsCount'   => $lowStockVariantsCount,
            'outOfStockVariantsCount' => $outOfStockVariantsCount,
            'criticalStockVariants'   => $criticalStockVariants,
            // Feeds
            'recentOrders'            => $recentOrders,
            'recentStockMovements'    => $recentStockMovements,
        ]);
    }
}
