@extends('layouts.admin')

@section('title', 'Order Management')

@section('content')
<div class="space-y-6">

    <!-- Header & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Customer Orders</h1>
            <p class="text-xs text-slate-500 mt-1">
                Manage online storefront orders, verify customer delivery details, and track shipment dispatch from the Lahore showroom.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                Total: {{ $counts['all'] }} Orders
            </span>
            @if($counts['pending'] > 0)
                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 animate-pulse">
                    {{ $counts['pending'] }} Pending Action
                </span>
            @endif
        </div>
    </div>

    <!-- Quick Status Filter Tabs -->
    <div class="flex items-center space-x-1 overflow-x-auto pb-1 border-b border-slate-200 text-xs font-bold">
        @php
            $currentStatus = request('status', 'all');
            $tabs = [
                'all'        => ['label' => 'All Orders', 'count' => $counts['all']],
                'pending'    => ['label' => 'Pending Verification', 'count' => $counts['pending']],
                'confirmed'  => ['label' => 'Confirmed', 'count' => $counts['confirmed']],
                'processing' => ['label' => 'Processing', 'count' => $counts['processing']],
                'shipped'    => ['label' => 'Dispatched', 'count' => $counts['shipped']],
                'delivered'  => ['label' => 'Delivered', 'count' => $counts['delivered']],
                'cancelled'  => ['label' => 'Cancelled', 'count' => $counts['cancelled']],
            ];
        @endphp

        @foreach($tabs as $key => $tab)
            <a href="{{ route('admin.orders.index', array_merge(request()->except(['page']), ['status' => $key])) }}" 
               class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap flex items-center space-x-1.5 {{ $currentStatus === $key ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <span>{{ $tab['label'] }}</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] font-black {{ $currentStatus === $key ? 'bg-slate-800 text-slate-200' : 'bg-slate-200 text-slate-700' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Search & Multi-Criteria Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <!-- Keyword Search -->
            <div class="lg:col-span-2 relative">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Search by Order #, Customer Name, Phone, or City..." 
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <!-- Payment Method -->
            <div>
                <select name="payment_method" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:outline-none">
                    <option value="all">All Payment Methods</option>
                    <option value="COD" {{ request('payment_method') === 'COD' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                    <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="jazzcash" {{ request('payment_method') === 'jazzcash' ? 'selected' : '' }}>JazzCash</option>
                    <option value="easypaisa" {{ request('payment_method') === 'easypaisa' ? 'selected' : '' }}>Easypaisa</option>
                    <option value="showroom_pickup" {{ request('payment_method') === 'showroom_pickup' ? 'selected' : '' }}>Showroom Pickup</option>
                </select>
            </div>

            <!-- Payment Status -->
            <div>
                <select name="payment_status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:outline-none">
                    <option value="all">All Payment Statuses</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-colors shadow-xs">
                    Apply Filter
                </button>
                <a href="{{ route('admin.orders.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Order Details</th>
                        <th class="px-5 py-3.5">Customer & City</th>
                        <th class="px-5 py-3.5">Items</th>
                        <th class="px-5 py-3.5">Total Amount</th>
                        <th class="px-5 py-3.5">Payment</th>
                        <th class="px-5 py-3.5">Fulfillment Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            
                            <!-- Order Number & Date -->
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="font-mono font-bold text-blue-600 hover:underline">
                                    {{ $order->order_number }}
                                </a>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $order->created_at->format('M d, Y · h:i A') }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $order->created_at->diffForHumans() }}
                                </div>
                            </td>

                            <!-- Customer & Destination -->
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $order->customer_name }}</div>
                                <div class="font-mono text-slate-500 text-[11px]">{{ $order->customer_phone }}</div>
                                <div class="text-slate-400 text-[11px] flex items-center space-x-1 mt-0.5">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>{{ $order->shipping_city }}</span>
                                </div>
                            </td>

                            <!-- Items Count -->
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-bold text-[11px]">
                                    {{ $order->items_count }} {{ Str::plural('item', $order->items_count) }}
                                </span>
                            </td>

                            <!-- Total Amount -->
                            <td class="px-5 py-4">
                                <div class="font-black text-slate-900 text-sm">
                                    Rs {{ number_format($order->total_amount, 0) }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">PKR</div>
                            </td>

                            <!-- Payment Method & Payment Status -->
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-700 text-xs">{{ $order->payment_method_label }}</div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $order->payment_status_badge_class }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Fulfillment Status -->
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $order->status_badge_class }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" 
                                       class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 font-bold text-xs transition-colors shadow-2xs">
                                        View &amp; Manage
                                    </a>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <div class="text-sm font-bold text-slate-700">No orders found matching criteria</div>
                                <p class="text-xs text-slate-400 mt-1">Try resetting the search keyword or filter parameters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
