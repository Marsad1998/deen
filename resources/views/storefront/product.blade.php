@extends('layouts.storefront')

@section('title', $product->seo_title)
@section('meta_description', $product->seo_description)

@section('seo_meta')
    <!-- Canonical & Search Indexing -->
    <link rel="canonical" href="{{ route('storefront.product', $product->slug) }}">
    <meta name="robots" content="index, follow">
    @if($product->meta_keywords)
        <meta name="keywords" content="{{ $product->meta_keywords }}">
    @endif

    <!-- Open Graph for Social & WhatsApp Rich Link Previews -->
    <meta property="og:type" content="product">
    <meta property="og:site_name" content="DEEN Electronics">
    <meta property="og:title" content="{{ $product->seo_title }}">
    <meta property="og:description" content="{{ $product->seo_description }}">
    <meta property="og:url" content="{{ route('storefront.product', $product->slug) }}">
    <meta property="og:image" content="{{ asset($product->thumbnail_url) }}">
    @php
        $defaultVariant = $product->defaultVariant ?: $product->variants->first();
    @endphp
    <meta property="product:price:amount" content="{{ $defaultVariant?->price ?? '0' }}">
    <meta property="product:price:currency" content="PKR">

    <!-- Twitter Summary Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $product->seo_title }}">
    <meta name="twitter:description" content="{{ $product->seo_description }}">
    <meta name="twitter:image" content="{{ asset($product->thumbnail_url) }}">

    <!-- Schema.org JSON-LD Structured Data for Search Engines -->
    <script type="application/ld+json">
        {!! json_encode($product->json_ld, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
@php
    $defaultVariant = $product->defaultVariant ?: $product->variants->first();
    $skuText = $defaultVariant?->sku ?: ($product->sku ?: 'DEEN-' . $product->id);
    $allMedia = $product->media;
    $initialImg = $product->thumbnail_url;
    $hasMultipleVariants = $product->variants->count() > 1;
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12" 
     x-data="productDetailState({
        initialImage: '{{ $initialImg }}',
        variants: {{ json_encode($product->variants->map(fn($v) => [
            'id' => $v->id,
            'name' => $v->name,
            'sku' => $v->sku ?: $product->sku,
            'price' => (float) $v->price,
            'price_formatted' => 'Rs ' . number_format($v->price, 2),
            'original_price' => (float) $v->original_price,
            'original_price_formatted' => $v->original_price ? 'Rs ' . number_format($v->original_price, 2) : null,
            'savings' => ($v->original_price && $v->original_price > $v->price) ? 'Save Rs ' . number_format($v->original_price - $v->price, 0) : null,
            'stock_quantity' => (int) $v->stock_quantity,
            'is_default' => (bool) $v->is_default,
        ])) }},
        defaultVariantId: {{ $defaultVariant ? $defaultVariant->id : 'null' }}
     })">

    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 overflow-x-auto whitespace-nowrap scrollbar-none">
        <a href="{{ route('home') }}" class="hover:text-rose-600 transition-colors">Home</a>
        <span>/</span>
        <a href="{{ route('storefront.catalog') }}" class="hover:text-rose-600 transition-colors">Catalogue</a>
        <span>/</span>
        @if($product->category)
            <a href="{{ route('storefront.catalog', ['category' => $product->category->slug]) }}" class="hover:text-rose-600 transition-colors">
                {{ $product->category->name }}
            </a>
            <span>/</span>
        @endif
        <span class="text-slate-800 font-semibold truncate max-w-xs">{{ $product->name }}</span>
    </nav>

    <!-- Main Product Showcase Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
        
        <!-- Left Column: Gallery Area (6 Cols) -->
        <div class="lg:col-span-6 space-y-4">
            <!-- Active Image Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 flex items-center justify-center min-h-[340px] sm:min-h-[460px] shadow-sm relative overflow-hidden group">
                <img :src="currentImage" alt="{{ $product->name }}" 
                     width="1200" height="1200"
                     class="max-h-[360px] sm:max-h-[420px] max-w-full object-contain transition-all duration-300 group-hover:scale-105"
                     onerror="this.src='/images/placeholder.svg';">

                @if($product->badge_text)
                    <div class="absolute top-4 left-4">
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-600 text-white shadow-sm uppercase tracking-wide">
                            {{ $product->badge_text }}
                        </span>
                    </div>
                @endif

                <!-- Lahore Showroom Guarantee Badge -->
                <div class="absolute bottom-4 right-4 hidden sm:flex items-center space-x-1.5 px-3 py-1 rounded-full bg-slate-900/80 text-white text-[10px] font-bold backdrop-blur-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>Showroom Stock</span>
                </div>
            </div>

            <!-- Thumbnail Selector Row -->
            @if($allMedia->count() > 1)
                <div class="flex items-center space-x-3 overflow-x-auto pb-2 scrollbar-none">
                    @foreach($allMedia as $item)
                        <button type="button" @click="currentImage = '{{ $item->url }}'"
                                :class="currentImage === '{{ $item->url }}' ? 'border-rose-600 ring-2 ring-rose-200' : 'border-slate-200 hover:border-slate-300'"
                                class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white border p-1.5 flex-shrink-0 flex items-center justify-center transition-all cursor-pointer">
                            <img src="{{ $item->url }}" alt="{{ $item->alt_text ?: $product->name }}" width="150" height="150" loading="lazy" class="max-h-full max-w-full object-contain">
                        </button>
                    @endforeach
                </div>
            @endif

            <!-- Confidence Badges Grid -->
            <div class="grid grid-cols-2 gap-3 pt-2 text-[11px] text-slate-600">
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <div>
                        <div class="font-bold text-slate-900">Showroom Collection</div>
                        <div class="text-[10px] text-slate-500">Main GT Road, Bata Pur</div>
                    </div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div>
                        <div class="font-bold text-slate-900">Official Brand Warranty</div>
                        <div class="text-[10px] text-slate-500">Genuine manufacturer claim</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Commercial & Detail Column (6 Cols) -->
        <div class="lg:col-span-6 space-y-6">
            
            <!-- Brand, Category, Title & Stock Header -->
            <div class="space-y-2 border-b border-slate-200 pb-5">
                <div class="flex items-center space-x-2 text-xs font-bold text-rose-600 uppercase tracking-wider">
                    <span>{{ $product->category?->name ?? 'Electronics' }}</span>
                    @if($product->brand)
                        <span class="text-slate-300">&bull;</span>
                        <span class="text-slate-600">{{ $product->brand->name }}</span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    {{ $product->name }}
                </h1>

                <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-xs text-slate-500 font-mono">
                    <span>SKU: <strong class="text-slate-800" x-text="activeSku"></strong></span>
                    <span class="text-slate-300">&bull;</span>
                    
                    <template x-if="stockQuantity > 0">
                        <span class="text-emerald-700 font-semibold flex items-center">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                            In Stock (<span x-text="stockQuantity"></span> units ready)
                        </span>
                    </template>
                    <template x-if="stockQuantity <= 0">
                        <span class="text-amber-700 font-semibold flex items-center">
                            <span class="w-2 h-2 rounded-full bg-amber-500 mr-1.5"></span>
                            Contact for Stock Booking
                        </span>
                    </template>
                </div>
            </div>

            <!-- Price & Savings Box -->
            <div class="bg-slate-50 rounded-3xl p-5 border border-slate-200 space-y-1.5">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Retail Price (PKR)</div>
                <div class="flex flex-wrap items-baseline gap-3">
                    <span class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight" x-text="activePrice"></span>
                    
                    <template x-if="activeOriginalPrice">
                        <span class="text-base sm:text-lg text-slate-400 line-through" x-text="activeOriginalPrice"></span>
                    </template>
                    
                    <template x-if="activeSavings">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700" x-text="activeSavings"></span>
                    </template>
                </div>
                <p class="text-[11px] text-slate-500 pt-0.5">
                    *Showroom prices subject to current availability and currency adjustments.
                </p>
            </div>

            <!-- Variant Selector (if multiple variants exist) -->
            @if($hasMultipleVariants)
                <div class="space-y-2.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Select Model / Capacity:
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($product->variants as $variant)
                            <button type="button" 
                                    @click="selectVariant({{ $variant->id }})"
                                    :class="selectedVariantId === {{ $variant->id }} ? 'bg-rose-600 text-white border-rose-600 shadow-sm' : 'bg-white text-slate-800 border-slate-300 hover:border-slate-400'"
                                    class="px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer">
                                {{ $variant->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Short Summary -->
            @if($product->short_description)
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    {{ $product->short_description }}
                </p>
            @endif

            <!-- Warranty & Terms Box -->
            @if($product->warranty_text)
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-start space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div>
                        <div class="font-bold text-emerald-950">Official Warranty Terms</div>
                        <div class="text-[11px] text-emerald-800 mt-0.5">{{ $product->warranty_text }}</div>
                    </div>
                </div>
            @endif

            <!-- Delivery Terms Notice -->
            <div class="p-3.5 rounded-2xl bg-slate-100 border border-slate-200 text-xs text-slate-600 space-y-1">
                <div class="font-bold text-slate-800">Nationwide Delivery Terms</div>
                <p class="text-[11px] leading-relaxed">
                    Delivery across Pakistan is subject to destination serviceability. Scooters, furniture, and large appliances require separate delivery arrangements and freight handling.
                </p>
            </div>

            <!-- E-Commerce Add To Cart & Order Actions Form -->
            <form method="POST" action="{{ route('cart.add') }}" class="space-y-4 pt-2">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variant_id" :value="selectedVariantId">

                <div class="flex items-center space-x-3">
                    <!-- Quantity Stepper -->
                    <div class="flex items-center border border-slate-300 rounded-2xl bg-white shadow-xs p-1">
                        <button type="button" @click="decrementQuantity()" 
                                class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 hover:bg-slate-100 font-bold text-base transition-colors"
                                aria-label="Decrease quantity">
                            -
                        </button>
                        <input type="number" name="quantity" x-model.number="quantity" min="1" :max="stockQuantity > 0 ? stockQuantity : 99"
                               class="w-12 text-center text-xs font-bold text-slate-900 border-none focus:outline-none focus:ring-0">
                        <button type="button" @click="incrementQuantity()" 
                                class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 hover:bg-rose-50 hover:text-rose-600 font-bold text-base transition-colors"
                                aria-label="Increase quantity">
                            +
                        </button>
                    </div>

                    <!-- Prominent Add To Cart Button (DEEN Red) -->
                    <button type="submit" 
                            class="flex-1 py-3.5 px-6 rounded-2xl text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-600/30 transition-all flex items-center justify-center space-x-2 cursor-pointer hover:scale-[1.01]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>Add to Cart</span>
                    </button>
                </div>
            </form>

            <!-- Direct Contact & WhatsApp Actions -->
            <div class="space-y-2.5 pt-1">
                <!-- Enquire & Order on WhatsApp Button -->
                <a href="{{ $whatsappUrl }}" 
                   @click.prevent="window.dispatchEvent(new CustomEvent('open-deen-chat', { detail: { query: 'Assalam o Alaikum, I am inquiring about {{ addslashes($product->name) }} (SKU: ' + activeSku + ').' } }))"
                   target="_blank" rel="noopener noreferrer"
                   class="w-full py-3.5 px-6 rounded-2xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-lg shadow-emerald-600/20 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                    <span>Enquire & Order on WhatsApp</span>
                </a>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <a href="tel:03007769932" 
                       class="py-2.5 px-3 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-center transition-colors">
                        Call Store: 0300-7769932
                    </a>
                    <a href="mailto:deenselectronics1@gmail.com?subject={{ urlencode('Product Enquiry: ' . $product->name) }}" 
                       class="py-2.5 px-3 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-center transition-colors">
                        Email Enquiry
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- Technical Specifications Table -->
    @if($product->specifications->isNotEmpty())
        <section class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
            <h2 class="text-lg font-extrabold text-slate-900 tracking-tight border-b border-slate-100 pb-3">
                Technical Specifications
            </h2>

            <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                <table class="w-full text-left text-xs">
                    <tbody class="divide-y divide-slate-100">
                        @foreach($product->specifications as $spec)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 font-semibold text-slate-500 w-1/3 bg-slate-50/50">
                                    {{ $spec->spec_key }}
                                </td>
                                <td class="py-3 px-4 text-slate-800 font-medium">
                                    {{ $spec->spec_value }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <!-- Full Description Section -->
    @if($product->full_description)
        <section class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-3">
            <h2 class="text-lg font-extrabold text-slate-900 tracking-tight border-b border-slate-100 pb-3">
                Product Description &amp; Details
            </h2>
            <div class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                {{ $product->full_description }}
            </div>
        </section>
    @endif

    <!-- Related Products -->
    @if($relatedProducts->isNotEmpty())
        <section class="space-y-6 pt-4">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Similar Products in {{ $product->category?->name }}</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
                @foreach($relatedProducts as $rel)
                    @php
                        $relDefault = $rel->defaultVariant;
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-200 hover:border-slate-300 shadow-xs hover:shadow-md transition-all flex flex-col justify-between overflow-hidden group">
                        <div class="bg-slate-50 p-4 h-40 flex items-center justify-center border-b border-slate-100">
                            <img src="{{ $rel->thumbnail_url }}" alt="{{ $rel->name }}" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform" onerror="this.src='/images/placeholder.svg';">
                        </div>
                        <div class="p-4 space-y-2 flex-1">
                            <h3 class="font-bold text-xs text-slate-900 line-clamp-2">
                                <a href="{{ route('storefront.product', $rel->slug) }}">{{ $rel->name }}</a>
                            </h3>
                            <div class="font-extrabold text-sm text-slate-900">
                                @if($relDefault)
                                    Rs {{ number_format($relDefault->price, 0) }}
                                @else
                                    <span class="text-xs text-slate-400 font-normal italic">On enquiry</span>
                                @endif
                            </div>
                        </div>
                        <div class="p-4 pt-0 space-y-2">
                            <form method="POST" action="{{ route('cart.add') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $rel->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-colors flex items-center justify-center space-x-1 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span>Add to Cart</span>
                                </button>
                            </form>
                            <a href="{{ route('storefront.product', $rel->slug) }}" class="block text-center py-1.5 px-3 rounded-xl text-[11px] font-semibold text-slate-600 hover:bg-slate-100">
                                View Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</div>

<script>
    function productDetailState(config) {
        return {
            currentImage: config.initialImage,
            variants: config.variants || [],
            selectedVariantId: config.defaultVariantId,
            quantity: 1,

            get activeVariant() {
                if (!this.selectedVariantId && this.variants.length > 0) {
                    return this.variants[0];
                }
                return this.variants.find(v => v.id === this.selectedVariantId) || this.variants[0] || null;
            },

            get activeSku() {
                return this.activeVariant ? this.activeVariant.sku : 'DEEN-ITEM';
            },

            get activePrice() {
                return this.activeVariant ? this.activeVariant.price_formatted : 'Price on Enquiry';
            },

            get activeOriginalPrice() {
                return this.activeVariant ? this.activeVariant.original_price_formatted : null;
            },

            get activeSavings() {
                return this.activeVariant ? this.activeVariant.savings : null;
            },

            get stockQuantity() {
                return this.activeVariant ? this.activeVariant.stock_quantity : 99;
            },

            selectVariant(variantId) {
                this.selectedVariantId = variantId;
                if (this.stockQuantity > 0 && this.quantity > this.stockQuantity) {
                    this.quantity = this.stockQuantity;
                }
            },

            incrementQuantity() {
                const max = this.stockQuantity > 0 ? this.stockQuantity : 99;
                if (this.quantity < max) {
                    this.quantity++;
                }
            },

            decrementQuantity() {
                if (this.quantity > 1) {
                    this.quantity--;
                }
            }
        };
    }
</script>
@endsection
