@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="space-y-6">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.orders.index') }}" class="hover:text-blue-600 transition-colors">Orders</a>
        <span>/</span>
        <span class="text-slate-800 font-bold font-mono">{{ $order->order_number }}</span>
    </nav>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center space-x-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
            <div class="flex items-center space-x-2 font-bold">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span>Stock / Validation Error</span>
            </div>
            <ul class="list-disc list-inside pl-1 text-[11px] font-normal space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Top Action Bar -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight font-mono">
                    {{ $order->order_number }}
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold border {{ $order->status_badge_class }}">
                    {{ $order->status_label }}
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold border {{ $order->payment_status_badge_class }}">
                    Payment: {{ ucfirst($order->payment_status) }}
                </span>
                @if($order->inventory_deducted_at)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold border bg-emerald-50 text-emerald-800 border-emerald-200" title="Inventory deducted on {{ $order->inventory_deducted_at->format('M d, Y · h:i A') }}">
                        <svg class="w-3.5 h-3.5 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Stock Deducted
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold border bg-slate-100 text-slate-600 border-slate-200" title="Stock will be automatically deducted from inventory once order is dispatched">
                        Stock Reserved
                    </span>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 mt-1">
                <span>Placed on <strong>{{ $order->created_at->format('M d, Y · h:i A') }}</strong> ({{ $order->created_at->diffForHumans() }})</span>
                @if($order->dispatched_at)
                    <span>•</span>
                    <span class="text-indigo-700 font-semibold">Dispatched on {{ $order->dispatched_at->format('M d, Y · h:i A') }}</span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Print Invoice -->
            <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank"
               class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center space-x-1.5 transition-colors shadow-2xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Invoice</span>
            </a>

            <!-- WhatsApp Customer Direct Action -->
            <a href="{{ $order->customer_whatsapp_url }}" target="_blank" rel="noopener noreferrer"
               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center space-x-1.5 transition-all shadow-sm">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                <span>WhatsApp Customer</span>
            </a>

            <!-- Phone Call Action -->
            <a href="tel:{{ $order->customer_phone }}" 
               class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center space-x-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>
                <span>Call Customer</span>
            </a>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Order Items, Financial Breakdown & Customer Info (8 Columns) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Ordered Items Table Card -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Ordered Items</h2>
                    <span class="text-xs font-semibold text-slate-500">{{ $order->items->count() }} line {{ Str::plural('item', $order->items->count()) }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                        <thead class="text-[11px] font-bold text-slate-400 uppercase">
                            <tr>
                                <th class="py-2.5">Product</th>
                                <th class="py-2.5">SKU</th>
                                <th class="py-2.5 text-right">Unit Price</th>
                                <th class="py-2.5 text-center">Qty</th>
                                <th class="py-2.5 text-center">Warehouse Stock</th>
                                <th class="py-2.5 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($order->items as $item)
                                @php
                                    $variant = $item->resolveVariant();
                                @endphp
                                <tr>
                                    <td class="py-3 pr-3">
                                        <div class="font-bold text-slate-900">{{ $item->product_name }}</div>
                                        @if($item->variant_name)
                                            <div class="text-[11px] text-slate-500">Variant: {{ $item->variant_name }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 font-mono text-[11px] text-slate-500">
                                        {{ $item->sku }}
                                    </td>
                                    <td class="py-3 text-right font-semibold text-slate-800">
                                        Rs {{ number_format($item->unit_price, 0) }}
                                    </td>
                                    <td class="py-3 text-center font-bold text-slate-900">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="py-3 text-center">
                                        @if($variant)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $variant->stock_quantity >= $item->quantity ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' }}">
                                                {{ $variant->stock_quantity }} in stock
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-[11px]">N/A</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right font-black text-slate-900">
                                        Rs {{ number_format($item->total_price, 0) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Financial Breakdown -->
                <div class="border-t border-slate-100 pt-4 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Items Subtotal</span>
                        <span class="font-bold text-slate-900">Rs {{ number_format($order->subtotal, 0) }}</span>
                    </div>

                    <div class="flex justify-between text-slate-600">
                        <span>Shipping &amp; Delivery Freight</span>
                        <span class="font-bold text-slate-900">
                            @if($order->shipping_cost > 0)
                                Rs {{ number_format($order->shipping_cost, 0) }}
                            @else
                                <span class="text-emerald-700 font-semibold">Free Showroom Delivery</span>
                            @endif
                        </span>
                    </div>

                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-rose-600">
                            <span>Promotional Discount ({{ $order->coupon_code ?? 'Voucher' }})</span>
                            <span class="font-bold">- Rs {{ number_format($order->discount_amount, 0) }}</span>
                        </div>
                    @endif

                    <div class="border-t border-slate-200 pt-3 flex justify-between items-baseline">
                        <span class="text-sm font-extrabold text-slate-900">Grand Total</span>
                        <div class="text-right">
                            <span class="text-xl font-black text-rose-600">
                                Rs {{ number_format($order->total_amount, 0) }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono block">PKR</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer & Delivery Details Card -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Customer &amp; Shipping Destination</h2>
                    <p class="text-xs text-slate-500">Contact snapshot provided by the buyer at checkout.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Customer Name</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $order->customer_name }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Primary Phone Number</span>
                        <a href="tel:{{ $order->customer_phone }}" class="font-mono font-bold text-blue-600 hover:underline">
                            {{ $order->customer_phone }}
                        </a>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Email Address</span>
                        <span class="font-medium text-slate-800">{{ $order->customer_email ?: 'Not provided (Guest Checkout)' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Recipient Name &amp; Phone</span>
                        <span class="font-semibold text-slate-800">{{ $order->shipping_recipient_name }} ({{ $order->shipping_recipient_phone }})</span>
                    </div>

                    <div class="sm:col-span-2">
                        <span class="text-slate-400 block mb-0.5">Delivery Address</span>
                        <div class="font-semibold text-slate-800">
                            {{ $order->shipping_address_line1 }}
                            @if($order->shipping_address_line2)
                                , {{ $order->shipping_address_line2 }}
                            @endif
                        </div>
                        <div class="text-slate-600 text-[11px] mt-0.5">
                            {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code ? '— ' . $order->shipping_postal_code : '' }}, {{ $order->shipping_country }}
                        </div>
                    </div>

                    @if($order->delivery_instructions)
                        <div class="sm:col-span-2 p-3 rounded-2xl bg-slate-50 border border-slate-200">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Customer Delivery Instructions</span>
                            <p class="text-slate-800 italic">{{ $order->delivery_instructions }}</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Right: Status Management & Admin Notes (4 Columns) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Order Status & Payment Update Form Card -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Update Fulfillment</h2>
                    <p class="text-xs text-slate-500">Update workflow stage and customer payment status.</p>
                </div>

                <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}" class="space-y-4">
                    @csrf

                    <!-- Order Fulfillment Status -->
                    <div>
                        <label for="status" class="block text-xs font-bold text-slate-700 mb-1">Order Workflow Status</label>
                        <select name="status" id="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none font-semibold">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>⏳ Order Placed (Pending Verification)</option>
                            <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>✓ Confirmed by Showroom</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>📦 Processing &amp; Packaging</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>🚚 Dispatched with Courier</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>🎉 Delivered</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">
                            Selecting <strong>Dispatched</strong> or <strong>Delivered</strong> automatically deducts stock from inventory.
                        </p>
                        @if($order->inventory_deducted_at)
                            <div class="mt-2 p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-[11px] text-emerald-800 flex items-center space-x-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Inventory was deducted on {{ $order->inventory_deducted_at->format('M d, Y h:i A') }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Payment Status -->
                    <div>
                        <label for="payment_status" class="block text-xs font-bold text-slate-700 mb-1">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none font-semibold">
                            <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending Payment</option>
                            <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid / Verified</option>
                            <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Payment Failed</option>
                        </select>
                    </div>

                    <!-- Internal Admin Notes -->
                    <div>
                        <label for="notes" class="block text-xs font-bold text-slate-700 mb-1">
                            Internal Admin &amp; Courier Notes
                        </label>
                        <textarea name="notes" id="notes" rows="4" 
                                  placeholder="e.g. Courier tracking #123456 (Trax / Leopards / Daewoo Express). Showroom confirmation call completed."
                                  class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none font-mono text-slate-800">{{ old('notes', $order->notes) }}</textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Logged in audit trail for showroom staff.</p>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition-colors shadow-sm">
                        Update Order Status
                    </button>
                </form>
            </div>

            <!-- Payment & Security Snapshot Card -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs space-y-3 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Payment Information</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Method</span>
                        <span class="font-bold text-slate-800">{{ $order->payment_method_label }}</span>
                    </div>
                    @if($order->transaction_reference)
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Transaction Ref / TID</span>
                            <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded select-all">{{ $order->transaction_reference }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Currency</span>
                        <span class="font-mono font-semibold text-slate-800">{{ $order->currency }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Total Charged</span>
                        <span class="font-bold text-slate-900">Rs {{ number_format($order->total_amount, 0) }}</span>
                    </div>
                </div>

                @if($order->payment_proof_path)
                    <!-- Payment Proof Screenshot Box -->
                    <div class="mt-4 pt-3 border-t border-slate-100 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-700 flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Customer Payment Screenshot</span>
                            </span>
                            <a href="{{ $order->payment_proof_url }}" target="_blank" class="text-[11px] text-blue-600 hover:text-blue-700 font-bold flex items-center space-x-0.5">
                                <span>View Full</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                        <a href="{{ $order->payment_proof_url }}" target="_blank" class="block group relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 p-1 hover:border-blue-400 transition-all">
                            <img src="{{ $order->payment_proof_url }}" alt="Customer Payment Proof" class="w-full max-h-48 object-contain rounded-xl group-hover:scale-[1.02] transition-transform">
                            <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded-xl">
                                <span class="px-3 py-1.5 rounded-lg bg-white/95 text-slate-900 font-bold text-xs shadow-md">Click to Open Full Size</span>
                            </div>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Workflow & Status Timeline Card -->
            @if(isset($statusHistories) && $statusHistories->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs space-y-4">
                    <div class="border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Status History Timeline</h3>
                    </div>
                    <div class="space-y-3">
                        @foreach($statusHistories as $history)
                            <div class="flex items-start space-x-3 text-xs border-b border-slate-50 pb-2.5 last:border-0 last:pb-0">
                                <div class="w-2 h-2 rounded-full bg-blue-600 mt-1.5 shrink-0"></div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-slate-900 uppercase text-[10px] tracking-wider">{{ $history->status }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ \Carbon\Carbon::parse($history->created_at)->format('d M, h:i A') }}</span>
                                    </div>
                                    @if($history->comment)
                                        <p class="text-slate-600 text-[11px] mt-0.5">{{ $history->comment }}</p>
                                    @endif
                                    <span class="text-[10px] text-slate-400">Updated by {{ $history->admin_username ?? 'System' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
