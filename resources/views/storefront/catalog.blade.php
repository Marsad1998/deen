@extends('layouts.storefront')

@section('title', 'Product Catalogue — DEEN Electronics')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" x-data="{ mobileFiltersOpen: false }">

    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500">
        <a href="{{ route('home') }}" class="hover:text-rose-600 transition-colors">Home</a>
        <span>/</span>
        <span class="text-slate-800 font-semibold">{{ $currentCategory ? $currentCategory->name : 'All Products' }}</span>
        @if($currentBrand)
            <span>/</span>
            <span class="text-rose-600 font-semibold">{{ $currentBrand->name }}</span>
        @endif
    </nav>

    <!-- Page Title & Search Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                {{ $currentCategory ? $currentCategory->name : ($currentBrand ? $currentBrand->name . ' Products' : 'All Products') }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Showing {{ $products->total() }} {{ Str::plural('item', $products->total()) }}
                @if($search) matching &ldquo;<span class="font-semibold text-slate-800">{{ $search }}</span>&rdquo; @endif
            </p>
        </div>

        <!-- Sort Control -->
        <div class="flex items-center space-x-3">
            <form method="GET" action="{{ route('storefront.catalog') }}" class="flex items-center space-x-2 text-xs">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                @if(request('brand'))
                    <input type="hidden" name="brand" value="{{ request('brand') }}">
                @endif
                @if(request('min_price'))
                    <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                @endif
                @if(request('max_price'))
                    <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                @endif
                @if(request('in_stock'))
                    <input type="hidden" name="in_stock" value="{{ request('in_stock') }}">
                @endif
                @if(request('has_warranty'))
                    <input type="hidden" name="has_warranty" value="{{ request('has_warranty') }}">
                @endif
                @if(request('featured'))
                    <input type="hidden" name="featured" value="{{ request('featured') }}">
                @endif
                @if(request('delivery_type'))
                    <input type="hidden" name="delivery_type" value="{{ request('delivery_type') }}">
                @endif
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif

                <label for="sort" class="text-slate-500 whitespace-nowrap font-medium">Sort by:</label>
                <select id="sort" name="sort" onchange="this.form.submit()"
                        class="px-3 py-1.5 rounded-xl border border-slate-300 bg-white text-xs text-slate-800 focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    <option value="newest" {{ $sortBy === 'newest' ? 'selected' : '' }}>Newest Arrivals</option>
                    <option value="price_low" {{ $sortBy === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_high" {{ $sortBy === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Name: A to Z</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Mobile Filter Toggle Bar (< lg) -->
    <div class="flex items-center justify-between lg:hidden pb-2">
        <button type="button" @click="mobileFiltersOpen = true" 
                class="inline-flex items-center space-x-2 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-800 bg-white border border-slate-300 shadow-xs hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            <span>Website Filters</span>
            @if($activeFiltersCount > 0)
                <span class="w-5 h-5 rounded-full bg-rose-600 text-white text-[10px] flex items-center justify-center font-bold">
                    {{ $activeFiltersCount }}
                </span>
            @endif
        </button>
        <span class="text-xs text-slate-500 font-medium">
            {{ $products->total() }} {{ Str::plural('product', $products->total()) }}
        </span>
    </div>

    <!-- Mobile Slide-over Drawer -->
    <div x-show="mobileFiltersOpen" x-cloak class="relative z-50 lg:hidden" aria-labelledby="mobile-filters-title" role="dialog" aria-modal="true">
        <div x-show="mobileFiltersOpen" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs" @click="mobileFiltersOpen = false"></div>

        <div class="fixed inset-0 z-50 flex">
            <div x-show="mobileFiltersOpen" 
                 x-transition:enter="ease-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="ease-in duration-200 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                 class="relative mr-auto flex h-full w-full max-w-xs flex-col overflow-y-auto bg-slate-50 py-4 pb-12 shadow-2xl px-4 space-y-4">
                
                <div class="flex items-center justify-between border-b border-slate-200 pb-3 bg-white -mx-4 -mt-4 p-4 shadow-xs">
                    <h2 id="mobile-filters-title" class="text-sm font-black text-slate-900 flex items-center space-x-2">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Website Filters</span>
                    </h2>
                    <button type="button" @click="mobileFiltersOpen = false" class="text-slate-400 hover:text-slate-800 text-xl font-bold p-1">
                        &times;
                    </button>
                </div>

                <!-- Include Shared Filter Component -->
                @include('storefront.partials.filters-sidebar')
            </div>
        </div>
    </div>

    <!-- Main Layout Grid (Desktop 4 Columns: 1 Col Left Filters + 3 Col Products) -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
        
        <!-- Desktop Left Sidebar Filters -->
        <aside class="hidden lg:block space-y-6">
            @include('storefront.partials.filters-sidebar')
        </aside>

        <!-- Product Grid Column -->
        <div class="lg:col-span-3 space-y-6">
            
            @if($products->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400 space-y-3">
                    <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <p class="font-bold text-slate-700 text-sm">No products match your filter criteria</p>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Try clearing your filters or resetting the search keywords to explore all items.</p>
                    <a href="{{ route('storefront.catalog') }}" class="inline-block mt-2 px-5 py-2.5 text-xs font-bold rounded-xl bg-rose-600 text-white hover:bg-rose-700 transition-colors shadow-xs">
                        Reset All Filters
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($products as $product)
                        @php
                            $default = $product->defaultVariant;
                            $skuVal = $product->sku ?: ($default?->sku ?: 'DEEN-ITEM');
                            $waText = urlencode("Assalam o Alaikum DEEN Electronics, I am interested in: " . $product->name . " (SKU: " . $skuVal . "). Please share availability and delivery charges.");
                        @endphp
                        <div class="bg-white rounded-2xl border border-slate-200 hover:border-slate-300 shadow-xs hover:shadow-lg transition-all flex flex-col justify-between overflow-hidden group">
                            <div>
                                <!-- Image Box -->
                                <div class="relative bg-slate-50 p-6 flex items-center justify-center h-48 border-b border-slate-100 overflow-hidden">
                                    <picture>
                                        <source srcset="{{ asset($product->thumbnail_url) }}" type="image/webp">
                                        <img src="{{ asset(str_replace('.webp', '.jpg', $product->thumbnail_url)) }}" 
                                             alt="{{ $product->name }}" 
                                             width="600" height="600" loading="lazy"
                                             class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300"
                                             onerror="this.src='/images/placeholder.svg';">
                                    </picture>

                                    @if($product->badge_text)
                                        <div class="absolute top-3 left-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white shadow-xs">
                                                {{ $product->badge_text }}
                                            </span>
                                        </div>
                                    @endif

                                    @if($default && $default->stock_quantity > 0)
                                        <div class="absolute bottom-2.5 right-2.5">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                In Stock
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Body -->
                                <div class="p-4 space-y-2">
                                    <div class="flex items-center space-x-1.5 text-[10px] font-bold uppercase tracking-wider">
                                        <span class="text-rose-600">{{ $product->category?->name ?? 'Electronics' }}</span>
                                        @if($product->brand)
                                            <span class="text-slate-300">&bull;</span>
                                            <span class="text-slate-500">{{ $product->brand->name }}</span>
                                        @endif
                                    </div>

                                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-rose-600 transition-colors line-clamp-2 leading-snug">
                                        <a href="{{ route('storefront.product', $product->slug) }}">{{ $product->name }}</a>
                                    </h3>

                                    <div class="pt-1 flex items-baseline space-x-2">
                                        @if($default)
                                            <span class="text-base font-extrabold text-slate-900">Rs {{ number_format($default->price, 0) }}</span>
                                            @if($default->original_price && $default->original_price > $default->price)
                                                <span class="text-xs text-slate-400 line-through">Rs {{ number_format($default->original_price, 0) }}</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400 italic">Price on enquiry</span>
                                        @endif
                                    </div>

                                    @if($product->warranty_text)
                                        <div class="text-[10px] text-slate-500 flex items-center space-x-1">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="truncate">{{ $product->warranty_text }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="p-4 pt-0 space-y-2">
                                <a href="https://wa.me/923007769932?text={{ $waText }}" target="_blank" rel="noopener noreferrer"
                                   class="w-full py-2 px-3 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-xs transition-all flex items-center justify-center space-x-1.5">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                                    <span>Enquire on WhatsApp</span>
                                </a>

                                <div class="grid grid-cols-2 gap-2">
                                    <a href="{{ route('storefront.product', $product->slug) }}" 
                                       class="py-2 px-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors text-center block">
                                        Details
                                    </a>
                                    <form method="POST" action="{{ route('cart.add') }}" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="w-full py-2 px-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition-colors flex items-center justify-center space-x-1 cursor-pointer shadow-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                            <span>Cart</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="pt-6 border-t border-slate-200">
                        {{ $products->links() }}
                    </div>
                @endif
            @endif

        </div>

    </div>

</div>
@endsection
