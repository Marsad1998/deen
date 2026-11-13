@php
    $buildFilterUrl = function(array $params) {
        $merged = array_merge(request()->query(), $params);
        $filtered = array_filter($merged, fn($v) => !is_null($v) && $v !== '');
        unset($filtered['page']);
        return route('storefront.catalog', $filtered);
    };

    $bulkySlugs = ['scooters', 'furniture', 'refrigerators-freezers', 'air-conditioners', 'washing-machines'];
@endphp

<div class="space-y-6">

    <!-- Active Filters Summary (if any filter is applied) -->
    @if($activeFiltersCount > 0)
        <div class="bg-rose-50/80 border border-rose-200/90 rounded-2xl p-4 space-y-3 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-950 flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Active Filters ({{ $activeFiltersCount }})</span>
                </span>
                <a href="{{ route('storefront.catalog', request()->only(['sort'])) }}" 
                   class="text-[11px] font-bold text-rose-600 hover:text-rose-800 underline transition-colors">
                    Clear All
                </a>
            </div>

            <div class="flex flex-wrap gap-1.5">
                @if($currentCategory)
                    <a href="{{ $buildFilterUrl(['category' => null]) }}" 
                       class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white text-slate-800 border border-rose-200 hover:bg-rose-100 hover:text-rose-900 transition-colors">
                        <span>Category: <strong>{{ $currentCategory->name }}</strong></span>
                        <span class="text-rose-500 font-bold ml-1">&times;</span>
                    </a>
                @endif

                @if($currentBrand)
                    <a href="{{ $buildFilterUrl(['brand' => null]) }}" 
                       class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white text-slate-800 border border-rose-200 hover:bg-rose-100 hover:text-rose-900 transition-colors">
                        <span>Brand: <strong>{{ $currentBrand->name }}</strong></span>
                        <span class="text-rose-500 font-bold ml-1">&times;</span>
                    </a>
                @endif

                @if($minPrice !== null || $maxPrice !== null)
                    <a href="{{ $buildFilterUrl(['min_price' => null, 'max_price' => null]) }}" 
                       class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white text-slate-800 border border-rose-200 hover:bg-rose-100 hover:text-rose-900 transition-colors">
                        <span>Price: <strong>Rs {{ number_format($minPrice ?: 0) }} – {{ $maxPrice ? 'Rs ' . number_format($maxPrice) : 'Above' }}</strong></span>
                        <span class="text-rose-500 font-bold ml-1">&times;</span>
                    </a>
                @endif

                @if($inStockOnly)
                    <a href="{{ $buildFilterUrl(['in_stock' => null]) }}" 
                       class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white text-emerald-800 border border-emerald-200 hover:bg-emerald-100 transition-colors">
                        <span><strong>In Stock Only</strong></span>
                        <span class="text-emerald-600 font-bold ml-1">&times;</span>
                    </a>
                @endif

                @if($hasWarranty)
                    <a href="{{ $buildFilterUrl(['has_warranty' => null]) }}" 
                       class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white text-slate-800 border border-rose-200 hover:bg-rose-100 transition-colors">
                        <span><strong>Official Warranty</strong></span>
                        <span class="text-rose-500 font-bold ml-1">&times;</span>
                    </a>
                @endif

                @if($featuredOnly)
                    <a href="{{ $buildFilterUrl(['featured' => null]) }}" 
                       class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white text-slate-800 border border-rose-200 hover:bg-rose-100 transition-colors">
                        <span><strong>Showcase Deals</strong></span>
                        <span class="text-rose-500 font-bold ml-1">&times;</span>
                    </a>
                @endif

                @if($deliveryType)
                    <a href="{{ $buildFilterUrl(['delivery_type' => null]) }}" 
                       class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white text-slate-800 border border-rose-200 hover:bg-rose-100 transition-colors">
                        <span>Delivery: <strong>{{ ucfirst($deliveryType) }}</strong></span>
                        <span class="text-rose-500 font-bold ml-1">&times;</span>
                    </a>
                @endif

                @if($search)
                    <a href="{{ $buildFilterUrl(['search' => null]) }}" 
                       class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white text-slate-800 border border-rose-200 hover:bg-rose-100 transition-colors">
                        <span>Search: &ldquo;<strong>{{ $search }}</strong>&rdquo;</span>
                        <span class="text-rose-500 font-bold ml-1">&times;</span>
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- 1. Categories Filter (All 12 Confirmed Categories) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3.5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900 flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <span>Product Categories</span>
            </h3>
            <span class="text-[10px] text-slate-400 font-bold">12 Total</span>
        </div>

        <ul class="space-y-1 text-xs">
            <li>
                <a href="{{ $buildFilterUrl(['category' => null]) }}" 
                   class="flex items-center justify-between py-1.5 px-2.5 rounded-lg transition-colors {{ !$currentCategory ? 'bg-rose-50 text-rose-700 font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span>All Categories</span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md {{ !$currentCategory ? 'bg-rose-200 text-rose-800' : 'bg-slate-100 text-slate-500' }}">
                        {{ $categories->sum('products_count') }}
                    </span>
                </a>
            </li>
            @foreach($categories as $cat)
                @php
                    $isCatActive = $currentCategory && $currentCategory->id === $cat->id;
                @endphp
                <li>
                    <a href="{{ $buildFilterUrl(['category' => $isCatActive ? null : $cat->slug]) }}" 
                       class="flex items-center justify-between py-1.5 px-2.5 rounded-lg transition-colors {{ $isCatActive ? 'bg-rose-50 text-rose-700 font-bold border-l-2 border-rose-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <span class="truncate pr-1">{{ $cat->name }}</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md flex-shrink-0 {{ $isCatActive ? 'bg-rose-200 text-rose-800' : 'bg-slate-100 text-slate-500' }}">
                            {{ $cat->products_count }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- 2. Official Brands Filter -->
    @if($brands->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3.5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900 flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Official Brands</span>
                </h3>
                @if($currentBrand)
                    <a href="{{ $buildFilterUrl(['brand' => null]) }}" class="text-[10px] font-bold text-rose-600 hover:underline">Reset</a>
                @endif
            </div>

            <div class="space-y-1 text-xs">
                <a href="{{ $buildFilterUrl(['brand' => null]) }}" 
                   class="flex items-center justify-between py-1.5 px-2.5 rounded-lg transition-colors {{ !$currentBrand ? 'bg-rose-50 text-rose-700 font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span>All Brands</span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md {{ !$currentBrand ? 'bg-rose-200 text-rose-800' : 'bg-slate-100 text-slate-500' }}">
                        {{ $brands->sum('products_count') }}
                    </span>
                </a>

                @foreach($brands as $brand)
                    @php
                        $isBrandActive = $currentBrand && $currentBrand->id === $brand->id;
                    @endphp
                    <a href="{{ $buildFilterUrl(['brand' => $isBrandActive ? null : $brand->slug]) }}" 
                       class="flex items-center justify-between py-1.5 px-2.5 rounded-lg transition-colors {{ $isBrandActive ? 'bg-rose-50 text-rose-700 font-bold border-l-2 border-rose-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <div class="flex items-center space-x-2 truncate">
                            <span class="w-4 h-4 rounded bg-slate-100 text-slate-700 flex items-center justify-center text-[9px] font-black uppercase flex-shrink-0">
                                {{ substr($brand->name, 0, 1) }}
                            </span>
                            <span class="truncate">{{ $brand->name }}</span>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md flex-shrink-0 {{ $isBrandActive ? 'bg-rose-200 text-rose-800' : 'bg-slate-100 text-slate-500' }}">
                            {{ $brand->products_count }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 3. Price Range Filter (PKR) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900 flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Price Range (PKR)</span>
            </h3>
            @if($minPrice !== null || $maxPrice !== null)
                <a href="{{ $buildFilterUrl(['min_price' => null, 'max_price' => null]) }}" class="text-[10px] font-bold text-rose-600 hover:underline">Reset</a>
            @endif
        </div>

        <!-- Quick Price Brackets -->
        <div class="grid grid-cols-2 gap-1.5 text-xs">
            @php
                $presets = [
                    ['label' => '< Rs 25,000',     'min' => null,   'max' => 25000],
                    ['label' => 'Rs 25K – 75K',     'min' => 25000,  'max' => 75000],
                    ['label' => 'Rs 75K – 150K',    'min' => 75000,  'max' => 150000],
                    ['label' => '> Rs 150,000',    'min' => 150000, 'max' => null],
                ];
            @endphp

            @foreach($presets as $p)
                @php
                    $isPresetActive = ($minPrice == $p['min']) && ($maxPrice == $p['max']);
                @endphp
                <a href="{{ $buildFilterUrl(['min_price' => $isPresetActive ? null : $p['min'], 'max_price' => $isPresetActive ? null : $p['max']]) }}" 
                   class="px-2 py-1.5 rounded-lg text-center text-[11px] font-medium border transition-colors {{ $isPresetActive ? 'bg-rose-600 text-white border-rose-600 font-bold' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 hover:text-slate-900' }}">
                    {{ $p['label'] }}
                </a>
            @endforeach
        </div>

        <!-- Custom Min/Max Inputs Form -->
        <form method="GET" action="{{ route('storefront.catalog') }}" class="space-y-2.5 pt-1">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            @if(request('brand'))
                <input type="hidden" name="brand" value="{{ request('brand') }}">
            @endif
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
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

            <div class="flex items-center space-x-2">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-[10px] text-slate-400 font-bold">Rs</span>
                    <input type="number" name="min_price" value="{{ $minPrice }}" placeholder="Min" 
                           class="w-full pl-6 pr-2 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-1 focus:ring-rose-500 focus:outline-none">
                </div>
                <span class="text-xs text-slate-400">&ndash;</span>
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-[10px] text-slate-400 font-bold">Rs</span>
                    <input type="number" name="max_price" value="{{ $maxPrice }}" placeholder="Max" 
                           class="w-full pl-6 pr-2 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-1 focus:ring-rose-500 focus:outline-none">
                </div>
                <button type="submit" 
                        class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition-colors shadow-xs">
                    Apply
                </button>
            </div>
        </form>
    </div>

    <!-- 4. Availability & Warranties Filter -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
        <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2 flex items-center space-x-1.5">
            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Assurance &amp; Availability</span>
        </h3>

        <div class="space-y-2 text-xs">
            <!-- In Stock Only -->
            <a href="{{ $buildFilterUrl(['in_stock' => $inStockOnly ? null : 1]) }}" 
               class="flex items-center justify-between p-2 rounded-xl transition-colors {{ $inStockOnly ? 'bg-emerald-50 text-emerald-900 border border-emerald-200 font-bold' : 'hover:bg-slate-50 text-slate-700' }}">
                <div class="flex items-center space-x-2">
                    <span class="w-4 h-4 rounded flex items-center justify-center border {{ $inStockOnly ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300 bg-white' }}">
                        @if($inStockOnly)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </span>
                    <span>In Stock Only</span>
                </div>
                <span class="text-[10px] text-emerald-700 bg-emerald-100/70 px-1.5 py-0.5 rounded font-bold">Ready</span>
            </a>

            <!-- Official Warranty -->
            <a href="{{ $buildFilterUrl(['has_warranty' => $hasWarranty ? null : 1]) }}" 
               class="flex items-center justify-between p-2 rounded-xl transition-colors {{ $hasWarranty ? 'bg-rose-50 text-rose-900 border border-rose-200 font-bold' : 'hover:bg-slate-50 text-slate-700' }}">
                <div class="flex items-center space-x-2">
                    <span class="w-4 h-4 rounded flex items-center justify-center border {{ $hasWarranty ? 'bg-rose-600 border-rose-600 text-white' : 'border-slate-300 bg-white' }}">
                        @if($hasWarranty)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </span>
                    <span>Official Brand Warranty</span>
                </div>
                <span class="text-[10px] text-rose-700 bg-rose-100 px-1.5 py-0.5 rounded font-bold">Guaranteed</span>
            </a>

            <!-- Showcase Deals -->
            <a href="{{ $buildFilterUrl(['featured' => $featuredOnly ? null : 1]) }}" 
               class="flex items-center justify-between p-2 rounded-xl transition-colors {{ $featuredOnly ? 'bg-amber-50 text-amber-900 border border-amber-200 font-bold' : 'hover:bg-slate-50 text-slate-700' }}">
                <div class="flex items-center space-x-2">
                    <span class="w-4 h-4 rounded flex items-center justify-center border {{ $featuredOnly ? 'bg-amber-600 border-amber-600 text-white' : 'border-slate-300 bg-white' }}">
                        @if($featuredOnly)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </span>
                    <span>Showcase Deals Only</span>
                </div>
                <span class="text-[10px] text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded font-bold">Featured</span>
            </a>
        </div>
    </div>

    <!-- 5. Delivery & Logistics Logistics Filter (DEEN Policy) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
        <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2 flex items-center space-x-1.5">
            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Delivery Type</span>
        </h3>

        <div class="space-y-1.5 text-xs">
            <a href="{{ $buildFilterUrl(['delivery_type' => null]) }}" 
               class="flex items-center justify-between py-1.5 px-2.5 rounded-lg transition-colors {{ !$deliveryType ? 'bg-rose-50 text-rose-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                <span>All Delivery Types</span>
                @if(!$deliveryType) <span class="text-rose-600 font-bold">&check;</span> @endif
            </a>

            <a href="{{ $buildFilterUrl(['delivery_type' => 'standard']) }}" 
               class="flex items-center justify-between py-1.5 px-2.5 rounded-lg transition-colors {{ $deliveryType === 'standard' ? 'bg-rose-50 text-rose-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                <div>
                    <span class="block">Standard Courier Parcel</span>
                    <span class="text-[10px] text-slate-400">Fans, kitchen, geysers</span>
                </div>
                @if($deliveryType === 'standard') <span class="text-rose-600 font-bold">&check;</span> @endif
            </a>

            <a href="{{ $buildFilterUrl(['delivery_type' => 'bulky']) }}" 
               class="flex items-center justify-between py-1.5 px-2.5 rounded-lg transition-colors {{ $deliveryType === 'bulky' ? 'bg-rose-50 text-rose-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                <div>
                    <span class="block">Bulky / Showroom Freight</span>
                    <span class="text-[10px] text-slate-400">ACs, Fridges, Scooters, Furniture</span>
                </div>
                @if($deliveryType === 'bulky') <span class="text-rose-600 font-bold">&check;</span> @endif
            </a>
        </div>
    </div>

    <!-- 6. Lahore Showroom & WhatsApp Assistance Desk -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 text-white rounded-2xl p-5 shadow-xs space-y-3">
        <div class="inline-flex items-center space-x-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white">
            <span>Showroom Assistance</span>
        </div>
        <h4 class="text-xs font-bold leading-snug">Can&rsquo;t Find a Model?</h4>
        <p class="text-[11px] text-slate-300 leading-relaxed">
            Visit Muhammad Amin&rsquo;s Plaza, Main GT Road, Manawan, Bata Pur Lahore (10 AM &ndash; 10 PM) or ask our sales desk on WhatsApp.
        </p>
        <a href="https://wa.me/923007769932?text=Assalam%20o%20Alaikum%20DEEN%20Electronics,%20I%20am%20looking%20for%20a%20specific%20product%20model." 
           target="_blank" rel="noopener noreferrer"
           class="w-full py-2 px-3 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition-colors flex items-center justify-center space-x-1.5 shadow-xs">
            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
            <span>Ask on WhatsApp</span>
        </a>
    </div>

</div>
