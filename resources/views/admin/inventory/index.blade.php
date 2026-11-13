@extends('layouts.admin')

@section('title', 'Inventory Management')

@section('content')
<div class="space-y-6" x-data="{ showAdjustModal: false, selectedVariant: {} }">

    @if(session('status') || session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center space-x-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('status') ?? session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Inventory Management</h1>
            <p class="text-xs text-slate-500 mt-1">Track variant stock quantities, inward receiving, low-stock thresholds, and physical adjustments.</p>
        </div>
        <div class="flex items-center space-x-2">
            @if(Auth::guard('admin')->user()->hasPermission('inventory.adjust'))
                <a href="{{ route('admin.inventory.create') }}" 
                   class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center space-x-1.5 shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add / Receive Inventory</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <label for="search" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <div class="relative">
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                           placeholder="Search by SKU, variant name, or product name..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label for="filter" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Filter Level</label>
                    <select id="filter" name="filter" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <option value="">All Stock Levels</option>
                        <option value="low_stock" {{ request('filter') === 'low_stock' ? 'selected' : '' }}>Low Stock (≤ 10)</option>
                        <option value="out_of_stock" {{ request('filter') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-900 hover:bg-slate-800 text-white transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Product & Variant</th>
                        <th class="py-3 px-4">SKU</th>
                        <th class="py-3 px-4">Selling Price</th>
                        <th class="py-3 px-4">Stock Level</th>
                        <th class="py-3 px-4">Stock Status</th>
                        <th class="py-3 px-4 text-right">Adjustment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($variants as $variant)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">
                                    <a href="{{ route('admin.products.edit', $variant->product_id) }}" class="hover:text-blue-600 transition-colors">
                                        {{ $variant->product?->name ?? 'Unknown Product' }}
                                    </a>
                                </div>
                                <div class="text-[11px] text-slate-500">{{ $variant->name }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono font-medium text-slate-800">{{ $variant->sku }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-900">Rs {{ number_format($variant->price, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-sm {{ $variant->stock_quantity > 10 ? 'text-slate-900' : ($variant->stock_quantity > 0 ? 'text-amber-600' : 'text-red-600') }}">
                                    {{ $variant->stock_quantity }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($variant->stock_quantity > 10)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">In Stock</span>
                                @elseif($variant->stock_quantity > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">Low Stock</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-800">Out of Stock</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if(Auth::guard('admin')->user()->hasPermission('inventory.adjust'))
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <a href="{{ route('admin.inventory.create', ['variant_id' => $variant->id]) }}"
                                           class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition-colors"
                                           title="Receive inward stock shipment for this variant">
                                            + Add Stock
                                        </a>
                                        <button type="button"
                                                @click="selectedVariant = {{ json_encode($variant) }}; showAdjustModal = true"
                                                class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors"
                                                title="Manual count / audit adjustment">
                                            Adjust
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">No inventory items matched your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($variants->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $variants->links() }}
            </div>
        @endif
    </div>

    <!-- Recent Adjustments Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Recent Stock Movements</h2>
                <p class="text-[11px] text-slate-500">Real-time ledger of order dispatches, showroom restocks, and manual adjustments.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600">
                {{ $recentHistories->count() }} logged
            </span>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[10px]">
                    <tr>
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Product / SKU</th>
                        <th class="py-2.5 px-3">Movement</th>
                        <th class="py-2.5 px-3">Resulting Stock</th>
                        <th class="py-2.5 px-3">Reason</th>
                        <th class="py-2.5 px-3">Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentHistories as $item)
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-2 px-3 font-mono text-slate-500">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-2 px-3">
                                <div class="font-semibold text-slate-800">{{ $item->variant?->product?->name ?? 'Standard Product' }}</div>
                                <div class="font-mono text-[10px] text-slate-400">{{ $item->variant?->sku }}</div>
                            </td>
                            <td class="py-2 px-3 font-bold {{ $item->change_qty >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                {{ $item->change_qty >= 0 ? '+' : '' }}{{ $item->change_qty }}
                            </td>
                            <td class="py-2 px-3 font-semibold text-slate-900">{{ $item->new_qty }}</td>
                            <td class="py-2 px-3 text-slate-600">{{ $item->reason }}</td>
                            <td class="py-2 px-3 text-slate-500">{{ $item->admin?->username ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                No stock movements recorded yet. Dispatched orders and restocks will automatically log here.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: ADJUST STOCK -->
    <div x-show="showAdjustModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="showAdjustModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Adjust Stock for <span x-text="selectedVariant.sku" class="font-mono text-blue-600"></span></h3>
                <button type="button" @click="showAdjustModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form method="POST" :action="`{{ url('/admin/inventory') }}/${selectedVariant.id}/adjust`" class="space-y-4">
                @csrf
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 flex justify-between text-xs">
                    <span class="text-slate-500">Current Stock:</span>
                    <span class="font-bold text-slate-900" x-text="selectedVariant.stock_quantity"></span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Adjustment Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="change_qty" required placeholder="e.g. +25 for restock or -5 for damage"
                           class="w-full px-3 py-2 text-sm font-semibold rounded-xl border border-slate-300">
                    <p class="text-[10px] text-slate-400 mt-1">Use a positive integer to increase stock, negative integer to deduct.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reason <span class="text-red-500">*</span></label>
                    <input type="text" name="reason" required placeholder="e.g. Supplier Shipment, Physical Inventory Count, Damaged in Warehouse"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showAdjustModal = false" class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-xl bg-blue-600 text-white">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
