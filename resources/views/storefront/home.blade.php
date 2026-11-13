@extends('layouts.storefront')

@section('title', 'DEEN Electronics — Home Appliances, Electronics & Scooters | Lahore')
@section('meta_description', 'DEEN Electronics Lahore. Quality home appliances, refrigerators, air conditioners, LED TVs, kitchen appliances, and scooters. Nationwide delivery subject to serviceability.')

@section('content')

    <!-- 1. ChargeHub-Inspired Cinematic Hero Section with 4-Image Slider & Per-Slide Content -->
    <section class="relative w-full overflow-hidden bg-slate-950 min-h-[550px] lg:min-h-[620px] flex items-center border-b border-slate-800"
             @mouseenter="clearInterval(slideTimer)"
             @mouseleave="resetTimer()"
             x-data="{
                 currentSlide: 0,
                 slides: {{ json_encode($heroConfig['slides']) }},
                 slideTimer: null,
                 activeSlide() {
                     return this.slides[this.currentSlide] || this.slides[0];
                 },
                 nextSlide() {
                     this.currentSlide = (this.currentSlide + 1) % this.slides.length;
                 },
                 prevSlide() {
                     this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
                 },
                 goToSlide(index) {
                     this.currentSlide = index;
                     this.resetTimer();
                 },
                 resetTimer() {
                     clearInterval(this.slideTimer);
                     this.slideTimer = setInterval(() => {
                         this.nextSlide();
                     }, 5500);
                 },
                 init() {
                     this.resetTimer();
                 }
             }">

        <!-- 4-Image Background Slider (ChargeHub-Inspired Presentation with Smooth Crossfade) -->
        <div class="absolute inset-0 w-full h-full overflow-hidden select-none pointer-events-none">
            @foreach($heroConfig['slides'] as $index => $slide)
                <div class="absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out"
                     :class="currentSlide === {{ $index }} ? 'opacity-100' : 'opacity-0'">
                    <picture>
                        <source srcset="{{ $slide['image_url'] }}" type="image/webp">
                        @if($index === 0)
                            <source srcset="{{ asset('images/hero/hero-cinematic.webp') }}" type="image/webp">
                            <img src="{{ asset('/images/hero-cinematic.jpg') }}" 
                                 alt="{{ $slide['title'] }} — DEEN Electronics" 
                                 class="w-full h-full object-cover object-right sm:object-center lg:object-right"
                                 loading="eager" fetchpriority="high"
                                 width="1376" height="768">
                        @else
                            <img src="{{ $slide['image_url'] }}" 
                                 alt="{{ $slide['title'] }} — DEEN Electronics" 
                                 class="w-full h-full object-cover object-right sm:object-center lg:object-right"
                                 loading="lazy"
                                 width="1376" height="768">
                        @endif
                    </picture>
                </div>
            @endforeach
        </div>

        <!-- High-Contrast Cinematic Gradient Overlays (Strongest on the Left behind text) -->
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/85 via-50% to-slate-950/40 lg:from-slate-950 lg:via-slate-950/80 lg:via-45% lg:to-transparent z-10"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-slate-950/30 z-10"></div>

        <!-- Left-Aligned Content Container (Vertically Centered) -->
        <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24 w-full">
            <div class="max-w-2xl text-left space-y-6">
                
                <!-- Per-Slide Showroom Pill Badge -->
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-white backdrop-blur-xs border border-white/15">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    <span x-text="activeSlide().badge_text">{{ $heroConfig['slides'][0]['badge_text'] }}</span>
                </div>

                <!-- Headline with Intro + Active Slide Title -->
                <div class="space-y-1.5">
                    <span class="block text-xs font-bold uppercase tracking-widest text-slate-400">
                        {{ $heroConfig['headline'] }}
                    </span>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight min-h-[2.4em] sm:min-h-[1.5em] transition-all"
                        x-text="activeSlide().title">
                        {{ $heroConfig['slides'][0]['title'] }}
                    </h1>
                    <!-- SEO & Accessibility Categorical Highlights -->
                    <div class="sr-only">
                        @foreach($heroConfig['animated_phrases'] as $phrase)
                            <span>{{ $phrase }}</span>
                        @endforeach
                    </div>
                </div>

                <!-- Description Paragraph (Active Slide Subtitle / Description) -->
                <p class="text-sm sm:text-base text-slate-200 leading-relaxed max-w-xl min-h-[3em] transition-all"
                   x-text="activeSlide().subtitle || activeSlide().description">
                    {{ $heroConfig['slides'][0]['subtitle'] ?: $heroConfig['description'] }}
                </p>
                <p class="sr-only">{{ $heroConfig['description'] }}</p>

                <!-- Action Buttons: Active Slide Primary CTA Button + Secondary WhatsApp Button -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                    <!-- Primary Button — DEEN red (Configured per slide, links to Catalog, Category, or Specific Product) -->
                    <a :href="activeSlide().button_url" 
                       href="{{ $heroConfig['slides'][0]['button_url'] }}" 
                       class="px-7 py-3.5 rounded-xl text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-600/30 transition-all flex items-center justify-center space-x-2 group">
                        <span x-text="activeSlide().button_text">{{ $heroConfig['slides'][0]['button_text'] }}</span>
                        <span class="sr-only">Browse Products</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>

                    <!-- Enquire on WhatsApp — WhatsApp green (Configured per slide) -->
                    <a :href="activeSlide().secondary_button_url || 'https://wa.me/923007769932'" 
                       href="{{ $heroConfig['slides'][0]['secondary_button_url'] ?? 'https://wa.me/923007769932' }}" 
                       target="_blank" rel="noopener noreferrer"
                       class="px-6 py-3.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                        <span x-text="activeSlide().secondary_button_text || 'Enquire on WhatsApp'">{{ $heroConfig['slides'][0]['secondary_button_text'] ?? 'Enquire on WhatsApp' }}</span>
                    </a>
                </div>

            </div>
        </div>

        <!-- Slider Controls & Indicator Pills (ChargeHub-Inspired) -->
        <div class="absolute bottom-6 right-4 sm:right-8 lg:right-12 z-20 flex items-center space-x-2.5">
            <!-- Prev Button -->
            <button type="button" 
                    @click="prevSlide(); resetTimer();" 
                    aria-label="Previous Slide"
                    class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-slate-900/70 hover:bg-rose-600 text-white border border-white/20 backdrop-blur-xs flex items-center justify-center transition-all hover:scale-105 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Slide Indicator Pills with slide titles -->
            <div class="flex items-center space-x-1.5 p-1 bg-slate-900/70 rounded-full border border-white/20 backdrop-blur-xs">
                @foreach($heroConfig['slides'] as $index => $slide)
                    <button type="button" 
                            @click="goToSlide({{ $index }})" 
                            :class="currentSlide === {{ $index }} ? 'bg-rose-600 w-7 sm:w-8' : 'bg-white/40 hover:bg-white/70 w-2.5 sm:w-3'"
                            class="h-2.5 sm:h-3 rounded-full transition-all duration-300 focus:outline-none"
                            title="Slide {{ $index + 1 }}: {{ $slide['title'] }}"
                            aria-label="Go to slide {{ $index + 1 }}"></button>
                @endforeach
            </div>

            <!-- Next Button -->
            <button type="button" 
                    @click="nextSlide(); resetTimer();" 
                    aria-label="Next Slide"
                    class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-slate-900/70 hover:bg-rose-600 text-white border border-white/20 backdrop-blur-xs flex items-center justify-center transition-all hover:scale-105 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </section>

    <!-- 2. Compact Trust Indicators Section Below Hero (Clean Light Retail) -->
    <section class="bg-slate-50 border-b border-slate-200 py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                
                @php
                    $trustIcons = [
                        1 => ['bg' => 'bg-rose-50 text-rose-600', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'],
                        2 => ['bg' => 'bg-slate-100 text-slate-700', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"/>'],
                        3 => ['bg' => 'bg-emerald-50 text-emerald-600', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>'],
                        4 => ['bg' => 'bg-amber-50 text-amber-600', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>'],
                    ];
                @endphp

                @foreach($trustConfig['items'] as $num => $item)
                    <div class="bg-white rounded-xl p-4 border border-slate-200 flex items-start space-x-3.5 shadow-xs">
                        <div class="w-10 h-10 rounded-lg {{ $trustIcons[$num]['bg'] ?? 'bg-slate-100 text-slate-700' }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $trustIcons[$num]['svg'] ?? '' !!}
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">{{ $item['title'] }}</h2>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                                {{ $item['description'] }}
                            </p>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>

    <!-- 3. Shop by Category (Clean Retail Cards) -->
    @if(!isset($cmsSections['categories_enabled']) || $cmsSections['categories_enabled'])
    <section class="py-12 sm:py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-3">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $cmsSections['categories_title'] ?? 'Shop by Category' }}</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">{{ $cmsSections['categories_subtitle'] ?? 'Confirmed catalogue categories across home appliances, cooling, TVs, and personal transit.' }}</p>
                </div>
                <a href="{{ route('storefront.catalog') }}" class="text-xs font-bold text-rose-600 hover:text-rose-700 flex items-center space-x-1 transition-colors">
                    <span>View Full Catalogue</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                @php
                    $categoryIcons = [
                        'home-appliances'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
                        'refrigerators-freezers'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2zm0 8h14M8 6h.01M8 15h.01"/>',
                        'washing-machines'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M5 9h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2zm7 9a3 3 0 100-6 3 3 0 000 6z"/>',
                        'air-conditioners'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                        'led-tvs'                   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20h6l-.75-3M3 5h18a2 2 0 012 2v8a2 2 0 01-2 2H3a2 2 0 01-2-2V7a2 2 0 012-2z"/>',
                        'kitchen-appliances'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>',
                        'fans'                      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M8.5 10.5l-3-3m13 0l-3 3M12 2a10 10 0 100 20 10 10 0 000-20z"/>',
                        'geysers-water-dispensers'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>',
                        'other-electronics'         => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
                        'scooters'                  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
                        'molded-foam-multiform'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
                        'furniture'                 => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',
                    ];
                @endphp

                @foreach($categories as $category)
                    <a href="{{ route('storefront.catalog', ['category' => $category->slug]) }}" 
                       class="group bg-white rounded-2xl p-3 sm:p-4 border border-slate-200 hover:border-rose-400 hover:shadow-lg transition-all text-center flex flex-col justify-between overflow-hidden">
                        
                        <!-- Category Image Container (Clean WebP presentation) -->
                        <div class="relative w-full aspect-4/3 mb-3 bg-slate-50 rounded-xl overflow-hidden border border-slate-100 flex items-center justify-center">
                            @if($category->image_url)
                                <picture>
                                    <source srcset="{{ asset($category->image_url) }}" type="image/webp">
                                    <img src="{{ asset(str_replace('.webp', '.jpg', $category->image_url)) }}" 
                                         alt="{{ $category->name }}" 
                                         width="600" height="450" loading="lazy"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </picture>
                            @else
                                <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        {!! $categoryIcons[$category->slug] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>' !!}
                                    </svg>
                                </div>
                            @endif

                            <div class="absolute bottom-1.5 right-1.5 w-6 h-6 rounded-md bg-white/90 backdrop-blur-xs text-rose-600 flex items-center justify-center shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $categoryIcons[$category->slug] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>' !!}
                                </svg>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-rose-600 transition-colors leading-snug">
                                {{ $category->name }}
                            </h3>
                            <span class="text-[10px] text-slate-400 mt-1 block">
                                Browse Category &rarr;
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- 3.1. Official Brand Partners & Authorized Distributors -->
    @if((!isset($cmsSections['brands_enabled']) || $cmsSections['brands_enabled']) && isset($brands) && $brands->isNotEmpty())
    <section class="py-10 sm:py-12 bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-6 sm:mb-8 gap-3">
                <div>
                    <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-200 text-slate-800 mb-1.5">
                        <svg class="w-3.5 h-3.5 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>Official Brand Partners</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $cmsSections['brands_title'] ?? 'Authorized Brand Partners' }}</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">{{ $cmsSections['brands_subtitle'] ?? 'Direct distributor warranties and official service support across Pakistan\'s top electronics manufacturers.' }}</p>
                </div>
                <a href="{{ route('storefront.catalog') }}" class="text-xs font-bold text-slate-700 hover:text-rose-600 flex items-center space-x-1 transition-colors">
                    <span>Browse All Brands</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                @foreach($brands as $brand)
                    <a href="{{ route('storefront.catalog', ['brand' => $brand->slug]) }}" 
                       class="group bg-white rounded-2xl p-4 border border-slate-200 hover:border-rose-400 hover:shadow-md transition-all flex flex-col items-center justify-center text-center min-h-[110px]">
                        @if($brand->logo_url && file_exists(public_path($brand->logo_url)))
                            <img src="{{ asset($brand->logo_url) }}" alt="{{ $brand->name }}" class="h-10 max-w-[120px] object-contain filter grayscale group-hover:grayscale-0 transition-all">
                        @else
                            <div class="w-12 h-12 rounded-xl bg-slate-100 group-hover:bg-rose-50 text-slate-700 group-hover:text-rose-600 font-black text-base flex items-center justify-center transition-colors mb-2">
                                {{ strtoupper(substr($brand->name, 0, 2)) }}
                            </div>
                        @endif
                        <span class="text-xs font-bold text-slate-800 group-hover:text-rose-600 transition-colors mt-1">
                            {{ $brand->name }}
                        </span>
                        <span class="text-[10px] text-slate-400">
                            Authorized Partner
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- 3.5. Retail Promotional Banner (Configurable in CMS) -->
    @if(!empty($promoConfig['is_enabled']))
    <section class="py-4 sm:py-6 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative rounded-3xl overflow-hidden shadow-xs border border-slate-200 bg-slate-900">
                <img src="{{ $promoConfig['image_url'] }}" 
                     alt="{{ $promoConfig['headline'] }}" 
                     width="1600" height="600" loading="lazy"
                     class="w-full h-auto object-cover min-h-[180px] max-h-[300px] select-none">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-950/60 to-transparent flex items-center p-6 sm:p-10">
                    <div class="max-w-xl space-y-2.5 sm:space-y-3">
                        <span class="inline-block px-3 py-1 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider bg-rose-600 text-white shadow-xs">
                            {{ $promoConfig['badge_text'] }}
                        </span>
                        <h3 class="text-lg sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-tight">
                            {{ $promoConfig['headline'] }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-200 line-clamp-2 max-w-lg">
                            {{ $promoConfig['description'] }}
                        </p>
                        <div class="pt-1 flex flex-wrap items-center gap-3">
                            <a href="{{ $promoConfig['button_url'] }}" 
                               class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition-colors shadow-xs">
                                {{ $promoConfig['button_text'] }}
                            </a>
                            @if(!empty($promoConfig['secondary_button_text']))
                                <a href="{{ $promoConfig['secondary_button_url'] }}" 
                                   target="_blank" rel="noopener noreferrer"
                                   class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition-colors flex items-center space-x-1.5 shadow-xs">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                                    <span>{{ $promoConfig['secondary_button_text'] }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- 4. Featured Deals & Showcase Products (Clean Retail Cards) -->
    <section class="py-12 sm:py-16 bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-3">
                <div>
                    <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 mb-2">
                        <span>Showcase Deals</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Featured Products</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Verified commercial availability with direct WhatsApp booking.</p>
                </div>
                <a href="{{ route('storefront.catalog') }}" class="text-xs font-bold text-rose-600 hover:text-rose-700 flex items-center space-x-1 transition-colors">
                    <span>Explore All Products</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <!-- Product Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
                @forelse($featuredProducts as $product)
                    @php
                        $default = $product->defaultVariant;
                        $waText = urlencode("Assalam o Alaikum DEEN Electronics, I want to enquire about {$product->name} (SKU: {$product->sku}).");
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-200 hover:border-slate-300 hover:shadow-lg transition-all flex flex-col justify-between overflow-hidden group">
                        
                        <div>
                            <!-- Product Image / Banner Container -->
                            <div class="relative bg-slate-50 p-4 h-52 flex items-center justify-center border-b border-slate-100">
                                @if($product->primaryMedia)
                                    <picture>
                                        <source srcset="{{ asset($product->primaryMedia->url) }}" type="image/webp">
                                        <img src="{{ asset(str_replace('.webp', '.jpg', $product->primaryMedia->url)) }}" 
                                             alt="{{ $product->name }}" 
                                             width="600" height="600" loading="lazy"
                                             class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300">
                                    </picture>
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 bg-slate-100 rounded-xl">
                                        <svg class="w-10 h-10 text-slate-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-[11px] font-medium text-slate-500">DEEN Showcase</span>
                                    </div>
                                @endif

                                @if($product->badge_text)
                                    <span class="absolute top-3 left-3 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white shadow-xs">
                                        {{ $product->badge_text }}
                                    </span>
                                @endif

                                @if($default && $default->stock_quantity > 0)
                                    <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        In Stock ({{ $default->stock_quantity }} units)
                                    </span>
                                @endif
                            </div>

                            <!-- Product Info -->
                            <div class="p-4 space-y-2.5">
                                <div class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">
                                    {{ $product->category?->name ?? 'Electronics' }}
                                </div>

                                <h3 class="text-sm font-bold text-slate-900 group-hover:text-rose-600 transition-colors line-clamp-2 leading-snug">
                                    <a href="{{ route('storefront.product', $product->slug) }}">
                                        {{ $product->name }}
                                    </a>
                                </h3>

                                <div class="text-xs text-slate-400 font-mono">
                                    SKU: {{ $product->sku }}
                                </div>

                                <!-- Price Display -->
                                <div class="flex items-baseline space-x-2 pt-1">
                                    @if($default)
                                        <span class="text-lg font-black text-slate-900">Rs {{ number_format($default->price, 0) }}</span>
                                        @if($default->original_price && $default->original_price > $default->price)
                                            <span class="text-xs text-slate-400 line-through">Rs {{ number_format($default->original_price, 0) }}</span>
                                            <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded">
                                                Save Rs {{ number_format($default->original_price - $default->price, 0) }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-500 italic">Price on enquiry</span>
                                    @endif
                                </div>

                                <!-- Warranty indicator if available -->
                                @if($product->warranty_text)
                                    <div class="text-[11px] text-emerald-800 bg-emerald-50/70 border border-emerald-100 rounded-lg p-1.5 flex items-center space-x-1.5">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="truncate">{{ $product->warranty_text }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Controls (Green WhatsApp Button) -->
                        <div class="p-4 pt-0 space-y-2">
                            <a href="https://wa.me/923007769932?text={{ $waText }}" target="_blank" rel="noopener noreferrer"
                               class="w-full py-2.5 px-3 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-xs transition-all flex items-center justify-center space-x-1.5">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                                <span>Enquire on WhatsApp</span>
                            </a>

                            <a href="{{ route('storefront.product', $product->slug) }}" 
                               class="w-full py-2 px-3 rounded-xl text-xs font-semibold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors text-center block">
                                View Details
                            </a>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500">
                        Products are being updated. Please visit our <a href="{{ route('storefront.catalog') }}" class="text-rose-600 underline font-semibold">catalogue</a>.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- 5. Showroom Business Context & Location Card -->
    <section class="py-12 sm:py-16 bg-white border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-slate-50 border border-slate-200 rounded-3xl p-6 sm:p-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    <div class="lg:col-span-7 space-y-4">
                        <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                            <span>{{ $aboutConfig['badge_text'] ?? 'Physical Showroom' }}</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            {{ $aboutConfig['headline'] ?? 'Visit DEEN Electronics in Lahore' }}
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            {!! $aboutConfig['narrative'] ?? "Located at Muhammad Amin's Plaza on Main Grand Trunk Road in Manawan (near Shell Petrol Pump, Bata Pur), DEEN Electronics is committed to offering a trusted retail destination for appliances, cooling systems, 4K LED televisions, kitchen gear, and electric commuter scooters." !!}
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-slate-700 pt-2">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span><strong>Daily Hours:</strong> {{ $aboutConfig['daily_hours'] ?? '10:00 AM – 10:00 PM' }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span><strong>Phone / WhatsApp:</strong> {{ $aboutConfig['phone'] ?? '0300-7769932' }}</span>
                                <span class="sr-only">03007769932</span>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl p-6 space-y-4 shadow-xs">
                        <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Business Information</h3>
                        <dl class="space-y-2 text-xs">
                            <div class="flex justify-between py-1 border-b border-slate-100">
                                <dt class="text-slate-500">Business Owner</dt>
                                <dd class="font-bold text-slate-900">{{ $aboutConfig['business_owner'] ?? 'Muhammad Aslam' }}</dd>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-slate-100 gap-0.5">
                                <dt class="text-slate-500 flex-shrink-0">Address</dt>
                                <dd class="sm:text-right font-medium text-slate-800">{!! $aboutConfig['address'] ?? "Muhammad Amin's Plaza, Main GT Road, Manawan, Bata Pur, Lahore" !!}</dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-100">
                                <dt class="text-slate-500">Official Email</dt>
                                <dd class="font-medium text-slate-800">{{ $aboutConfig['email'] ?? 'deenselectronics1@gmail.com' }}</dd>
                            </div>
                            <div class="flex justify-between py-1">
                                <dt class="text-slate-500">Domain</dt>
                                <dd class="font-mono text-slate-800">deenelectronics.com</dd>
                            </div>
                        </dl>
                        <a href="{{ $aboutConfig['whatsapp_url'] ?? 'https://wa.me/923007769932?text=Assalam%20o%20Alaikum%20DEEN%20Electronics,%20I%20want%20to%20visit%20your%20Lahore%20showroom.' }}" 
                           target="_blank" rel="noopener noreferrer"
                           class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 flex items-center justify-center space-x-2 transition-colors">
                            <span>Get Directions &amp; Enquire on WhatsApp</span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </section>

@endsection
