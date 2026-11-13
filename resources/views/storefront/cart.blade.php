@extends('layouts.storefront')

@section('title', 'Shopping Cart — DEEN Electronics')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500">
        <a href="{{ route('home') }}" class="hover:text-rose-600 transition-colors">Home</a>
        <span>/</span>
        <a href="{{ route('storefront.catalog') }}" class="hover:text-rose-600 transition-colors">Catalogue</a>
        <span>/</span>
        <span class="text-slate-800 font-semibold">Shopping Cart</span>
    </nav>

    <!-- Page Title Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-5 gap-3">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Shopping Cart</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                    {{ $count }} {{ Str::plural('item', $count) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Review your selected electronics, home appliances, or scooters before confirming with our Lahore sales desk.
            </p>
        </div>

        @if(!empty($items))
            <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('Are you sure you want to clear your cart?');">
                @csrf
                <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800 hover:underline flex items-center space-x-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Clear Cart</span>
                </button>
            </form>
        @endif
    </div>

    @if(empty($items))
        <!-- Empty Cart State -->
        <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-14 text-center max-w-2xl mx-auto shadow-xs space-y-6">
            <div class="w-20 h-20 rounded-full bg-rose-50 text-rose-600 mx-auto flex items-center justify-center">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>

            <div class="space-y-2">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Your Cart is Currently Empty</h2>
                <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                    You haven’t added any appliances or electronics yet. Browse our verified catalogue or select a category below to get started.
                </p>
            </div>

            <div class="pt-2">
                <a href="{{ route('storefront.catalog') }}" 
                   class="inline-flex items-center justify-center px-7 py-3.5 rounded-xl text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-600/30 transition-all space-x-2">
                    <span>Browse Product Catalogue</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            <!-- Quick Category Recommendations -->
            <div class="pt-6 border-t border-slate-100 space-y-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Popular Categories</span>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <a href="{{ route('storefront.catalog', ['category' => 'air-conditioners']) }}" class="px-3 py-1.5 rounded-full text-xs bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-700 font-medium transition-colors border border-slate-200">Air Conditioners</a>
                    <a href="{{ route('storefront.catalog', ['category' => 'refrigerators-freezers']) }}" class="px-3 py-1.5 rounded-full text-xs bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-700 font-medium transition-colors border border-slate-200">Refrigerators</a>
                    <a href="{{ route('storefront.catalog', ['category' => 'washing-machines']) }}" class="px-3 py-1.5 rounded-full text-xs bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-700 font-medium transition-colors border border-slate-200">Washing Machines</a>
                    <a href="{{ route('storefront.catalog', ['category' => 'led-tvs']) }}" class="px-3 py-1.5 rounded-full text-xs bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-700 font-medium transition-colors border border-slate-200">Smart LED TVs</a>
                    <a href="{{ route('storefront.catalog', ['category' => 'scooters']) }}" class="px-3 py-1.5 rounded-full text-xs bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-700 font-medium transition-colors border border-slate-200">Electric Scooters</a>
                </div>
            </div>
        </div>

        <!-- Featured Products on Empty Cart -->
        @if(!empty($suggestedProducts) && $suggestedProducts->isNotEmpty())
            <section class="space-y-4 pt-6">
                <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Featured In-Stock Products</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($suggestedProducts as $sp)
                        @php $spDefault = $sp->defaultVariant; @endphp
                        <div class="bg-white rounded-2xl border border-slate-200 hover:border-slate-300 p-4 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="bg-slate-50 p-3 rounded-xl aspect-4/3 flex items-center justify-center mb-3">
                                <img src="{{ $sp->thumbnail_url }}" alt="{{ $sp->name }}" class="max-h-full max-w-full object-contain">
                            </div>
                            <div class="space-y-1.5 flex-1">
                                <span class="text-[10px] font-bold text-rose-600 uppercase">{{ $sp->category?->name }}</span>
                                <h4 class="text-xs font-bold text-slate-900 line-clamp-2">
                                    <a href="{{ route('storefront.product', $sp->slug) }}" class="hover:text-rose-600">{{ $sp->name }}</a>
                                </h4>
                                <div class="text-sm font-extrabold text-slate-900">
                                    @if($spDefault)
                                        Rs {{ number_format($spDefault->price, 0) }}
                                    @else
                                        <span class="text-xs text-slate-400 font-normal">On Enquiry</span>
                                    @endif
                                </div>
                            </div>
                            <div class="pt-3">
                                <form method="POST" action="{{ route('cart.add') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $sp->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-800 text-xs font-bold transition-colors">
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

    @else
        <!-- Populated Cart View -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left: Cart Items List (8 Columns) -->
            <div class="lg:col-span-8 space-y-4">
                
                <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs divide-y divide-slate-100">
                    @foreach($items as $item)
                        @php
                            $itemSubtotal = $item['price'] * $item['quantity'];
                        @endphp
                        <div class="p-4 sm:p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 transition-colors hover:bg-slate-50/50">
                            
                            <!-- Thumbnail & Main Info -->
                            <div class="flex items-center space-x-3 sm:space-x-4 min-w-0 flex-1">
                                <a href="{{ route('storefront.product', $item['slug']) }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-slate-50 border border-slate-200 p-2 flex items-center justify-center shrink-0 group">
                                    <img src="{{ $item['thumbnail_url'] }}" alt="{{ $item['name'] }}" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform" onerror="this.src='/images/placeholder.svg';">
                                </a>

                                <div class="min-w-0 flex-1 space-y-1">
                                    <div class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-wider text-rose-600">
                                        <span class="truncate">{{ $item['category_name'] }}</span>
                                        @if($item['is_bulky'])
                                            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200 lowercase shrink-0">
                                                bulky freight
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug line-clamp-2">
                                        <a href="{{ route('storefront.product', $item['slug']) }}" class="hover:text-rose-600 transition-colors">
                                            {{ $item['name'] }}
                                        </a>
                                        @if(!empty($item['variant_name']))
                                            <span class="text-xs text-slate-500 font-normal">({{ $item['variant_name'] }})</span>
                                        @endif
                                    </h3>

                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] text-slate-500">
                                        <span class="font-mono text-slate-400">SKU: <span class="text-slate-600">{{ $item['sku'] }}</span></span>
                                        <span class="text-slate-300">•</span>
                                        <span class="font-semibold text-slate-700">Rs {{ number_format($item['price'], 0) }} <span class="text-[10px] text-slate-400 font-normal">/ unit</span></span>
                                    </div>

                                    @if(!empty($item['warranty_text']))
                                        <div class="text-[11px] text-emerald-700 flex items-center space-x-1">
                                            <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            <span class="truncate max-w-xs">{{ $item['warranty_text'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Quantity, Line Subtotal & Remove Controls -->
                            <div class="flex items-center justify-between md:justify-end gap-3 sm:gap-5 shrink-0 w-full md:w-auto pt-3 md:pt-0 border-t md:border-t-0 border-slate-100">
                                
                                <!-- Quantity Increment / Decrement Stepper -->
                                <div class="flex items-center border border-slate-300 rounded-xl bg-white shadow-xs shrink-0">
                                    <!-- Decrement -->
                                    <form method="POST" action="{{ route('cart.update', $item['key']) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                        <button type="submit" 
                                                class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-slate-600 hover:text-rose-600 hover:bg-slate-100 rounded-l-xl transition-colors font-bold text-sm cursor-pointer"
                                                aria-label="Decrease quantity">
                                            -
                                        </button>
                                    </form>

                                    <!-- Current Quantity Display -->
                                    <span class="w-8 sm:w-9 text-center text-xs font-bold text-slate-900 select-none">
                                        {{ $item['quantity'] }}
                                    </span>

                                    <!-- Increment -->
                                    <form method="POST" action="{{ route('cart.update', $item['key']) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                        <button type="submit" 
                                                class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-slate-600 hover:text-rose-600 hover:bg-slate-100 rounded-r-xl transition-colors font-bold text-sm cursor-pointer"
                                                aria-label="Increase quantity">
                                            +
                                        </button>
                                    </form>
                                </div>

                                <!-- Line Subtotal -->
                                <div class="text-right shrink-0 min-w-[95px] sm:min-w-[110px]">
                                    <div class="text-xs sm:text-sm font-black text-rose-600 whitespace-nowrap">
                                        Rs {{ number_format($itemSubtotal, 0) }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-medium">Subtotal</div>
                                </div>

                                <!-- Remove Button -->
                                <form method="POST" action="{{ route('cart.remove', $item['key']) }}" onsubmit="return confirm('Remove {{ addslashes($item['name']) }} from your cart?');" class="shrink-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            title="Remove item" aria-label="Remove item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>

                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- Bottom Shopping Actions -->
                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('storefront.catalog') }}" 
                       class="text-xs font-bold text-slate-700 hover:text-rose-600 flex items-center space-x-1.5 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Continue Shopping</span>
                    </a>
                </div>

                <!-- Delivery Policy & Logistics Advisory -->
                <div class="bg-slate-50 border border-slate-200 rounded-3xl p-5 space-y-2.5 text-xs text-slate-600">
                    <div class="flex items-center space-x-2 font-bold text-slate-900 text-sm">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"/></svg>
                        <span>Nationwide Delivery &amp; Showroom Pickup Information</span>
                    </div>
                    <p class="leading-relaxed text-[11px] text-slate-600">
                        • <strong>Lahore Showroom Pickup</strong>: Free in-store collection at Muhammad Amin's Plaza, Main GT Road, Manawan, Bata Pur.<br>
                        • <strong>Standard Couriers</strong>: Small kitchen appliances, fans, geysers, and electronics delivered nationwide.<br>
                        • <strong>Bulky Freight Handling</strong>: Scooters, refrigerators, furniture, and air conditioners require specialized freight handling and destination confirmation.
                    </p>
                </div>

            </div>

            <!-- Right: Sticky Order Summary (4 Columns) -->
            <div class="lg:col-span-4 space-y-4 lg:sticky lg:top-24">
                
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-5">
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight border-b border-slate-100 pb-3">
                        Order Summary
                    </h2>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal ({{ $count }} {{ Str::plural('item', $count) }})</span>
                            <span class="font-bold text-slate-900">Rs {{ number_format($subtotal, 0) }}</span>
                        </div>

                        <div class="flex justify-between text-slate-600 items-start">
                            <span>Estimated Delivery</span>
                            <span class="text-right text-[11px] font-medium text-slate-500 max-w-[160px]">
                                Calculated based on destination serviceability
                            </span>
                        </div>

                        @if($hasBulky)
                            <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px] flex items-start space-x-2">
                                <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Cart includes large appliances / bulky freight. Freight confirmed via WhatsApp.</span>
                            </div>
                        @endif

                        <div class="pt-3 border-t border-slate-200 flex justify-between items-baseline">
                            <span class="text-sm font-extrabold text-slate-900">Estimated Total</span>
                            <div class="text-right">
                                <span class="text-xl sm:text-2xl font-black text-rose-600">
                                    Rs {{ number_format($subtotal, 0) }}
                                </span>
                                <div class="text-[10px] text-slate-400">PKR (Taxes included where applicable)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Checkout & Order Booking Actions -->
                    <div class="space-y-2.5 pt-2">
                        <a href="{{ route('checkout.index') }}" 
                           class="w-full py-3.5 px-4 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs sm:text-sm flex items-center justify-center space-x-2 shadow-lg shadow-rose-600/25 transition-all hover:shadow-xl active:scale-[0.99]">
                            <svg class="w-5 h-5 fill-none stroke-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Proceed to Online Checkout</span>
                        </a>

                        <a href="{{ $whatsappOrderUrl }}" 
                           target="_blank" rel="noopener noreferrer"
                           class="w-full py-3 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm flex items-center justify-center space-x-2 shadow-md shadow-emerald-600/20 transition-all hover:shadow-lg">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                            <span>Proceed to WhatsApp Checkout</span>
                        </a>

                        <button type="button" 
                                @click="window.dispatchEvent(new CustomEvent('open-deen-chat', { detail: { query: 'Assalam o Alaikum, I have questions about my cart order total of Rs {{ number_format($subtotal, 0) }}.' } }))"
                                class="w-full py-2.5 px-3 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center justify-center space-x-1.5 transition-colors cursor-pointer">
                            <span>Ask DEEN AI Order Desk</span>
                        </button>
                    </div>

                    <!-- Trust Checklist -->
                    <div class="pt-4 border-t border-slate-100 space-y-2 text-[11px] text-slate-500">
                        <div class="flex items-center space-x-2 text-slate-700">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>100% Verified Brand Warranty</span>
                        </div>
                        <div class="flex items-center space-x-2 text-slate-700">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>COD, Bank Transfer, JazzCash &amp; Easypaisa</span>
                        </div>
                        <div class="flex items-center space-x-2 text-slate-700">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Showroom: Main GT Road, Manawan, Bata Pur, Lahore</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    @endif

</div>
@endsection
