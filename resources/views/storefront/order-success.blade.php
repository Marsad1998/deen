@extends('layouts.storefront')

@section('title', 'Order Confirmed #' . $order->order_number . ' — DEEN Electronics')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16">
    
    <!-- Success Celebration Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-sm text-center mb-8">
        
        <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-full bg-emerald-50 border-4 border-emerald-100 flex items-center justify-center text-emerald-600 mb-5 shadow-xs">
            <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>

        <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-2">
            Order Successfully Placed
        </span>

        <h1 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">
            Thank You, {{ $order->customer_name }}!
        </h1>

        <p class="text-xs sm:text-sm text-slate-600 max-w-lg mx-auto mt-2 leading-relaxed">
            Your order has been recorded in the DEEN Electronics showroom system. Our team is preparing your allocation and will contact you via WhatsApp / Call at <span class="font-bold text-slate-900 font-mono">{{ $order->customer_phone }}</span> to confirm dispatch.
        </p>

        <!-- Order Reference Badge -->
        <div class="mt-6 inline-flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4 p-3 sm:p-4 rounded-2xl bg-slate-50 border border-slate-200">
            <div class="text-xs text-slate-500 font-medium">Order Reference:</div>
            <div class="text-sm sm:text-base font-black text-rose-600 font-mono tracking-wide">
                #{{ $order->order_number }}
            </div>
            <div class="hidden sm:block text-slate-300">|</div>
            <div class="text-xs text-slate-500">Date: <span class="font-bold text-slate-700">{{ $order->created_at->format('d M Y, h:i A') }}</span></div>
        </div>

        <!-- WhatsApp Quick Confirmation Action -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ $whatsappConfirmationUrl }}" 
               target="_blank" rel="noopener noreferrer"
               class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs sm:text-sm flex items-center justify-center space-x-2 shadow-lg shadow-emerald-600/20 transition-all hover:shadow-xl active:scale-[0.99]">
                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                <span>Instant WhatsApp Confirmation (0300-7769932)</span>
            </a>

            <button type="button" 
                    onclick="window.print()"
                    class="w-full sm:w-auto px-5 py-3.5 rounded-2xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs sm:text-sm flex items-center justify-center space-x-2 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Invoice</span>
            </button>
        </div>

    </div>

    <!-- Payment & Delivery Specific Advice -->
    @if($order->payment_method === 'bank_transfer')
        <div class="mb-8 p-6 rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-950 space-y-3">
            <div class="flex items-center space-x-2 font-bold text-sm">
                <svg class="w-5 h-5 text-emerald-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Showroom Bank Account Transfer Details</span>
            </div>
            <p class="text-xs text-emerald-800 leading-relaxed">
                Please transfer the total amount of <strong class="text-emerald-900 font-mono">Rs {{ number_format($order->total_amount, 2) }}</strong> to DEEN Electronics corporate showroom account using your Order Reference <strong class="font-mono text-emerald-900">#{{ $order->order_number }}</strong> as the payment description:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 text-xs">
                <div class="p-3 bg-white/80 rounded-xl border border-emerald-200">
                    <div class="font-bold text-emerald-900">Meezan Bank Limited</div>
                    <div class="text-[11px] text-slate-600 mt-0.5">Account Title: <strong>DEEN ELECTRONICS</strong></div>
                    <div class="text-[11px] text-slate-600">Account / IBAN: <span class="font-mono font-bold text-slate-800">PK45 MEZN 0001 0203 0405 0607</span></div>
                    <div class="text-[10px] text-slate-500">Branch: Manawan Bata Pur Lahore</div>
                </div>
                <div class="p-3 bg-white/80 rounded-xl border border-emerald-200">
                    <div class="font-bold text-emerald-900">Habib Bank Limited (HBL)</div>
                    <div class="text-[11px] text-slate-600 mt-0.5">Account Title: <strong>MUHAMMAD ASLAM</strong></div>
                    <div class="text-[11px] text-slate-600">Account / IBAN: <span class="font-mono font-bold text-slate-800">PK82 HABB 0002 0304 0506 0708</span></div>
                    <div class="text-[10px] text-slate-500">Branch: Main GT Road Lahore</div>
                </div>
            </div>
            <p class="text-[11px] text-emerald-700">
                After transfer, send a screenshot of the receipt via WhatsApp to <strong>0300-7769932</strong> for immediate order confirmation.
            </p>
        </div>
    @elseif($order->payment_method === 'jazzcash' || $order->payment_method === 'easypaisa')
        <div class="mb-8 p-6 rounded-3xl bg-amber-50 border border-amber-200 text-amber-950 space-y-3">
            <div class="flex items-center space-x-2 font-bold text-sm">
                <svg class="w-5 h-5 text-amber-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Mobile Wallet Transfer Instructions ({{ $order->payment_method_label }})</span>
            </div>
            <p class="text-xs text-amber-800 leading-relaxed">
                Send <strong class="text-amber-900 font-mono">Rs {{ number_format($order->total_amount, 2) }}</strong> to DEEN Electronics registered mobile account:
            </p>
            <div class="p-3 bg-white/80 rounded-xl border border-amber-200 text-xs inline-block">
                <div class="font-bold text-amber-950">Mobile Account: <span class="font-mono text-rose-700 text-sm">0300-7769932</span></div>
                <div class="text-[11px] text-slate-600">Title: <strong>MUHAMMAD ASLAM</strong> (DEEN Electronics)</div>
            </div>
            <p class="text-[11px] text-amber-800">
                Please share the transaction ID (TID) or screenshot on WhatsApp to <strong>0300-7769932</strong> mentioning Order #{{ $order->order_number }}.
            </p>
        </div>
    @elseif($order->payment_method === 'showroom_pickup')
        <div class="mb-8 p-6 rounded-3xl bg-blue-50 border border-blue-200 text-blue-950 space-y-3">
            <div class="flex items-center space-x-2 font-bold text-sm">
                <svg class="w-5 h-5 text-blue-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Showroom Counter Self-Collection</span>
            </div>
            <p class="text-xs text-blue-800 leading-relaxed">
                Your items are reserved for collection. Visit our showroom counter at:
                <br>
                <strong class="text-blue-950">Muhammad Amin's Plaza, Main GT Road, Manawan, Bata Pur, Lahore</strong>
                <br>
                Timings: <strong>10:00 AM – 10:00 PM (Daily)</strong>. You may inspect the products and pay via Cash or POS Card swipe upon collection.
            </p>
        </div>
    @endif

    <!-- Invoice Details & Order Items -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 sm:p-7 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Order Invoice Summary</h2>
                <div class="text-xs text-slate-500">DEEN Electronics Official Invoice</div>
            </div>
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-2"></span>
                    {{ $order->status_label }}
                </span>
            </div>
        </div>

        <!-- Order Information Columns -->
        <div class="p-6 sm:p-7 grid grid-cols-1 sm:grid-cols-3 gap-6 text-xs border-b border-slate-100 bg-slate-50/50">
            <div>
                <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-1">Customer Details</div>
                <div class="font-extrabold text-slate-900">{{ $order->customer_name }}</div>
                <div class="text-slate-600 font-mono mt-0.5">{{ $order->customer_phone }}</div>
                @if($order->customer_email)
                    <div class="text-slate-500 mt-0.5 truncate">{{ $order->customer_email }}</div>
                @endif
            </div>

            <div>
                <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-1">Shipping &amp; Delivery</div>
                <div class="font-bold text-slate-900">{{ $order->shipping_recipient_name }}</div>
                <div class="text-slate-600 mt-0.5 leading-relaxed">
                    {{ $order->shipping_address_line1 }}
                    @if($order->shipping_address_line2), {{ $order->shipping_address_line2 }}@endif
                </div>
                <div class="font-medium text-slate-700 mt-0.5">
                    {{ $order->shipping_city }}, {{ $order->shipping_province }}
                    @if($order->shipping_postal_code) ({{ $order->shipping_postal_code }})@endif
                </div>
            </div>

            <div>
                <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-1">Payment &amp; Logistics</div>
                <div class="font-bold text-slate-900">{{ $order->payment_method_label }}</div>
                <div class="text-slate-600 mt-0.5">
                    Payment Status: <span class="font-bold uppercase text-amber-700">{{ $order->payment_status }}</span>
                </div>
                <div class="text-slate-500 mt-0.5">
                    Showroom Verified
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="p-6 sm:p-7">
            <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-4">Purchased Items</h3>
            <div class="divide-y divide-slate-100">
                @foreach($order->items as $item)
                    <div class="py-3.5 first:pt-0 last:pb-0 flex items-center justify-between space-x-4">
                        <div class="flex-1 min-w-0">
                            <div class="font-extrabold text-slate-900 text-xs sm:text-sm truncate">
                                {{ $item->product_name }}
                            </div>
                            @if($item->variant_name)
                                <div class="text-xs text-slate-500">{{ $item->variant_name }}</div>
                            @endif
                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                SKU: {{ $item->sku }} &bull; Qty: {{ $item->quantity }} &times; Rs {{ number_format($item->unit_price, 2) }}
                            </div>
                        </div>
                        <div class="text-right font-extrabold text-slate-900 text-xs sm:text-sm">
                            Rs {{ number_format($item->total_price, 2) }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Financial Breakdown Table -->
            <div class="mt-6 pt-5 border-t border-slate-200 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-bold text-slate-900">Rs {{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Shipping &amp; Logistics</span>
                    <span class="font-bold text-emerald-600">
                        {{ $order->shipping_cost > 0 ? 'Rs ' . number_format($order->shipping_cost, 2) : 'Free / Included' }}
                    </span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-emerald-700">
                        <span>Discount</span>
                        <span class="font-bold">-Rs {{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="pt-3 border-t border-slate-200 flex justify-between items-baseline">
                    <span class="text-sm font-black text-slate-900">Total Amount</span>
                    <div class="text-right">
                        <span class="text-xl sm:text-2xl font-black text-rose-600">
                            Rs {{ number_format($order->total_amount, 2) }}
                        </span>
                        <div class="text-[10px] text-slate-400">PKR (Taxes included)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer / Contact Note -->
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500">
            <div class="text-center sm:text-left">
                Need immediate help with your order? Call Muhammad Aslam at <strong class="text-slate-900 font-mono">0300-7769932</strong>.
            </div>
            <a href="{{ route('storefront.catalog') }}" class="font-bold text-rose-600 hover:text-rose-500 transition-colors">
                Continue Shopping &rarr;
            </a>
        </div>

    </div>

</div>
@endsection
