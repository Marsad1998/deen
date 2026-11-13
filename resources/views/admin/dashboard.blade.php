@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Header & Quick Actions -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2.5">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                    Welcome back, {{ $admin->full_name ?: $admin->username }}
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    {{ $role?->name ?? 'Admin' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                DEEN Electronics Showroom Operations &amp; Retail Console · Lahore, Pakistan
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if($admin->hasPermission('inventory.adjust'))
                <a href="{{ route('admin.inventory.create') }}" 
                   class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center space-x-1.5 shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Receive Inventory</span>
                </a>
            @endif

            @if($admin->hasPermission('products.create'))
                <a href="{{ route('admin.products.create') }}" 
                   class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center space-x-1.5 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>New Product</span>
                </a>
            @endif

            <a href="{{ route('home') }}" target="_blank"
               class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center space-x-1.5 transition-colors">
                <span>View Storefront</span>
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    <!-- Core High-Level KPI Grid (4 Metrics) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- 1. Orders KPI -->
        <a href="{{ route('admin.orders.index') }}" class="bg-white border border-slate-200 rounded-3xl p-5 shadow-xs hover:border-blue-400 transition-colors group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Total Orders</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalOrdersCount) }}</div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs mt-3">
                <span class="font-bold text-amber-700">{{ $pendingOrdersCount }} Pending Verification</span>
                <span class="text-blue-600 font-semibold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 2. Revenue KPI -->
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-700">Gross Sales Volume</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-mono font-bold text-xs">
                        Rs
                    </div>
                </div>
                <div class="text-3xl font-black text-emerald-700 tracking-tight">
                    Rs {{ number_format($grossSalesVolume, 0) }}
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs mt-3 text-slate-500">
                <span>Avg Order Value:</span>
                <span class="font-mono font-bold text-slate-800">Rs {{ number_format($averageOrderValue, 0) }}</span>
            </div>
        </div>

        <!-- 3. Catalog Products & Categories KPI -->
        <a href="{{ route('admin.products.index') }}" class="bg-white border border-slate-200 rounded-3xl p-5 shadow-xs hover:border-purple-400 transition-colors group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-purple-700">Catalog Size</span>
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline space-x-2">
                    <span class="text-3xl font-black text-slate-900 tracking-tight">{{ $totalProductsCount }}</span>
                    <span class="text-xs text-slate-500 font-medium">products</span>
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs mt-3 text-slate-600">
                <span>{{ $totalCategoriesCount }} Categories · {{ $totalBrandsCount }} Brands</span>
                <span class="text-purple-600 font-semibold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 4. Warehouse Inventory KPI -->
        <a href="{{ route('admin.inventory.index') }}" class="bg-white border border-slate-200 rounded-3xl p-5 shadow-xs hover:border-indigo-400 transition-colors group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-700">Warehouse Inventory</span>
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline space-x-2">
                    <span class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalStockUnits) }}</span>
                    <span class="text-xs text-slate-500 font-medium">units</span>
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs mt-3 text-slate-600">
                <span>Valuation: <strong class="text-slate-900 font-mono">Rs {{ number_format($totalInventoryValuation / 1000000, 2) }}M</strong></span>
                <span class="text-indigo-600 font-semibold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
        </a>

    </div>

    <!-- Fulfillment Pipeline Workflow Ribbon -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900 tracking-tight">Order Fulfillment Pipeline</h2>
                <p class="text-xs text-slate-500">Live operational status across all order stages.</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                Manage Orders Hub &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 pt-2">
            <!-- Pending -->
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" 
               class="p-3.5 rounded-2xl border border-amber-200 bg-amber-50/70 hover:bg-amber-100/80 transition-colors flex flex-col justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-800">Pending Call</span>
                <div class="text-2xl font-black text-amber-900 mt-1">{{ $pendingOrdersCount }}</div>
                <span class="text-[10px] text-amber-700 mt-1">Verification needed</span>
            </a>

            <!-- In Fulfillment -->
            <a href="{{ route('admin.orders.index', ['status' => 'confirmed']) }}" 
               class="p-3.5 rounded-2xl border border-blue-200 bg-blue-50/70 hover:bg-blue-100/80 transition-colors flex flex-col justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-800">In Packaging</span>
                <div class="text-2xl font-black text-blue-900 mt-1">{{ $confirmedOrdersCount }}</div>
                <span class="text-[10px] text-blue-700 mt-1">Confirmed / Packed</span>
            </a>

            <!-- Dispatched -->
            <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" 
               class="p-3.5 rounded-2xl border border-indigo-200 bg-indigo-50/70 hover:bg-indigo-100/80 transition-colors flex flex-col justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-800">Dispatched</span>
                <div class="text-2xl font-black text-indigo-900 mt-1">{{ $shippedOrdersCount }}</div>
                <span class="text-[10px] text-indigo-700 mt-1">With courier / van</span>
            </a>

            <!-- Delivered -->
            <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" 
               class="p-3.5 rounded-2xl border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100/80 transition-colors flex flex-col justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-800">Delivered</span>
                <div class="text-2xl font-black text-emerald-900 mt-1">{{ $deliveredOrdersCount }}</div>
                <span class="text-[10px] text-emerald-700 mt-1">Completed orders</span>
            </a>

            <!-- Cancelled -->
            <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" 
               class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors flex flex-col justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Cancelled</span>
                <div class="text-2xl font-black text-slate-700 mt-1">{{ $cancelledOrdersCount }}</div>
                <span class="text-[10px] text-slate-500 mt-1">Returned / Voided</span>
            </a>
        </div>
    </div>

    <!-- Main Operational Workspace Grid (8 Cols vs 4 Cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Recent Orders & Catalog Breakdown (8 Columns) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Recent Orders Table Card -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Recent Customer Orders</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Incoming customer checkouts needing showroom verification or fulfillment.</p>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-blue-600 hover:underline flex items-center space-x-1">
                        <span>View All Orders ({{ $totalOrdersCount }})</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Order Number</th>
                                <th class="px-5 py-3">Customer</th>
                                <th class="px-5 py-3">City</th>
                                <th class="px-5 py-3">Amount</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentOrders as $ro)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <a href="{{ route('admin.orders.show', $ro->id) }}" class="font-mono font-bold text-blue-600 hover:underline">
                                            {{ $ro->order_number }}
                                        </a>
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $ro->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-slate-900">{{ $ro->customer_name }}</div>
                                        <div class="font-mono text-slate-500 text-[11px]">{{ $ro->customer_phone }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 font-medium text-slate-700">
                                        {{ $ro->shipping_city }}
                                    </td>
                                    <td class="px-5 py-3.5 font-black text-slate-900">
                                        Rs {{ number_format($ro->total_amount, 0) }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold border {{ $ro->status_badge_class }}">
                                            {{ $ro->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <a href="{{ route('admin.orders.show', $ro->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 font-bold text-xs transition-colors">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                        No orders placed yet. Orders submitted via checkout will be displayed here in real time.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Categories & Brands Distribution (Side-by-Side Cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                <!-- Top Categories -->
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Active Categories</h3>
                            <p class="text-[11px] text-slate-500">{{ $activeCategoriesCount }} categories enabled</p>
                        </div>
                        <a href="{{ route('admin.categories.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                            Manage &rarr;
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($topCategories as $cat)
                            <div class="py-2.5 flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-800">{{ $cat->name }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
                                    {{ $cat->products_count }} {{ Str::plural('product', $cat->products_count) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Brands -->
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Authorized Brands</h3>
                            <p class="text-[11px] text-slate-500">{{ $activeBrandsCount }} brands in showroom</p>
                        </div>
                        <a href="{{ route('admin.brands.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                            Manage &rarr;
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($topBrands as $brand)
                            <div class="py-2.5 flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-800">{{ $brand->name }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
                                    {{ $brand->products_count }} {{ Str::plural('product', $brand->products_count) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

        </div>

        <!-- Right Column: Inventory Health & Stock Activity (4 Columns) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Critical Low Stock Alerts Card -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Low Stock Alerts</h3>
                        <p class="text-[11px] text-slate-500">Items requiring warehouse reorder</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $lowStockVariantsCount > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $lowStockVariantsCount }} low stock
                    </span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($criticalStockVariants as $csv)
                        <div class="py-3 flex items-center justify-between gap-3 text-xs">
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-slate-900 truncate">{{ $csv->product?->name }}</div>
                                <div class="font-mono text-[10px] text-slate-400">{{ $csv->sku }}</div>
                            </div>
                            <div class="flex items-center space-x-2 shrink-0">
                                <span class="font-bold {{ $csv->stock_quantity > 0 ? 'text-amber-600' : 'text-red-600' }}">
                                    {{ $csv->stock_quantity }} left
                                </span>
                                @if($admin->hasPermission('inventory.adjust'))
                                    <a href="{{ route('admin.inventory.create', ['variant_id' => $csv->id]) }}" 
                                       class="px-2 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-[10px] font-bold transition-colors">
                                        + Restock
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-center text-xs text-slate-400">
                            All warehouse stock levels are currently healthy!
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Warehouse Movements Feed -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Recent Stock Ledger</h3>
                        <p class="text-[11px] text-slate-500">Inward receiving &amp; dispatch movements</p>
                    </div>
                    <a href="{{ route('admin.inventory.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                        Ledger &rarr;
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($recentStockMovements as $rsm)
                        <div class="flex items-start space-x-3 text-xs border-b border-slate-50 pb-2.5 last:border-0 last:pb-0">
                            <div class="px-2 py-0.5 rounded-lg text-xs font-bold shrink-0 {{ $rsm->change_qty >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $rsm->change_qty >= 0 ? '+' : '' }}{{ $rsm->change_qty }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-slate-800 truncate">{{ $rsm->variant?->product?->name }}</div>
                                <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $rsm->reason }}</div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $rsm->created_at->diffForHumans() }} by {{ $rsm->admin?->username ?? 'System' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-center text-xs text-slate-400">
                            No recent stock movements recorded.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Payment Method Distribution -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs space-y-3 text-xs">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Payment Methods Breakdown</h3>
                <div class="space-y-2.5 pt-1">
                    <div class="flex justify-between items-center py-1 border-b border-slate-100">
                        <span class="text-slate-600 font-medium">Cash on Delivery (COD)</span>
                        <span class="font-bold text-slate-900">{{ $codOrdersCount }} orders</span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-slate-100">
                        <span class="text-slate-600 font-medium">Direct Bank Transfer</span>
                        <span class="font-bold text-slate-900">{{ $bankTransferOrdersCount }} orders</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-slate-600 font-medium">Counter Pickup / Other</span>
                        <span class="font-bold text-slate-900">{{ $otherPaymentOrdersCount }} orders</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
