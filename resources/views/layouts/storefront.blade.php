<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'DEEN Electronics — Home Appliances, Electronics & Scooters')</title>
    <meta name="description" content="@yield('meta_description', 'DEEN Electronics Lahore. Quality home appliances, refrigerators, air conditioners, LED TVs, kitchen appliances, and scooters. Nationwide delivery subject to serviceability.')">

    @yield('seo_meta')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CDN for frontend styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        deen: {
                            red: '#E11D48',
                            darkred: '#BE123C',
                            black: '#0A0A0A',
                            dark: '#111827',
                            gray: '#1F2937',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full flex flex-col text-slate-800 antialiased selection:bg-rose-500 selection:text-white" 
      x-data="{ mobileMenuOpen: false }"
      x-init="$watch('mobileMenuOpen', value => document.body.classList.toggle('overflow-hidden', value))">

@php
    $storeName = \App\Models\StoreSetting::get('store_name', 'DEEN Electronics');
    $storeLogo = \App\Models\StoreSetting::get('store_logo');
    $storeLogoUrl = $storeLogo ? (str_starts_with($storeLogo, 'http') || str_starts_with($storeLogo, '/') ? $storeLogo : asset('storage/' . $storeLogo)) : asset('images/deen-logo.png');
    $storeWhatsapp = \App\Models\StoreSetting::get('whatsapp_number', '0300-7769932');
    $storeWhatsappDigits = preg_replace('/[^0-9]/', '', $storeWhatsapp);
    if (!str_starts_with($storeWhatsappDigits, '92') && str_starts_with($storeWhatsappDigits, '0')) {
        $storeWhatsappDigits = '92' . substr($storeWhatsappDigits, 1);
    }
    $storePhone = \App\Models\StoreSetting::get('contact_phone', \App\Models\StoreSetting::get('showroom_phone', '0300-7769932'));
    $storePhoneDigits = preg_replace('/[^0-9]/', '', $storePhone);
    $storeEmail = \App\Models\StoreSetting::get('contact_email', \App\Models\StoreSetting::get('store_email', 'deenselectronics1@gmail.com'));
    $storeAddress = \App\Models\StoreSetting::get('store_address', \App\Models\StoreSetting::get('showroom_address', "Muhammad Amin's Plaza, Main GT Road, Manawan, Bata Pur, Lahore"));
@endphp

    <!-- Top Announcement Strip: Black background with crisp white / muted text -->
    <aside aria-label="Announcement" class="bg-slate-950 text-slate-300 text-[11px] border-b border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-col sm:flex-row items-center justify-between gap-1.5">
            <div class="flex items-center space-x-3 text-center sm:text-left">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-600 text-white">
                    Lahore Showroom
                </span>
                <span>Main GT Road, Manawan, Bata Pur &bull; Hours: 10:00 AM – 10:00 PM</span>
            </div>
            <div class="flex items-center space-x-4 text-xs font-medium">
                <a href="tel:{{ $storePhoneDigits }}" class="hover:text-white flex items-center space-x-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>
                    <span>{{ $storePhone }}</span>
                </a>
                <span class="text-slate-700">|</span>
                <a href="https://wa.me/{{ $storeWhatsappDigits }}" 
                   @click.prevent="window.dispatchEvent(new CustomEvent('open-deen-chat'))"
                   target="_blank" rel="noopener noreferrer" 
                   class="hover:text-emerald-300 flex items-center space-x-1 transition-colors text-emerald-400 font-semibold cursor-pointer">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                    <span>WhatsApp Order Desk</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Navigation Header: Crisp White Background with Prominent Logo -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4 sm:gap-6">
                
                <!-- Brand Logo (High-contrast, prominent on white) -->
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center group py-1" aria-label="{{ $storeName }} Home">
                        <img src="{{ $storeLogoUrl }}" 
                             alt="{{ $storeName }}" 
                             class="h-14 sm:h-16 w-auto object-contain transition-transform group-hover:scale-[1.02]"
                             onerror="this.style.display='none'; document.getElementById('logo-fallback').style.display='flex';">
                        <div id="logo-fallback" style="display:none;" class="flex items-center space-x-2.5">
                            <span class="w-11 h-11 rounded-xl bg-rose-600 flex items-center justify-center font-extrabold text-2xl text-white shadow-sm">D</span>
                            <div class="flex flex-col">
                                <span class="font-extrabold text-xl tracking-tight text-slate-900 leading-tight">DEEN <span class="text-rose-600">ELECTRONICS</span></span>
                                <span class="text-[10px] text-slate-500 font-semibold tracking-widest uppercase">Appliances &amp; Scooters</span>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Clean Retail Search Bar with Autocomplete Dropdown -->
                <div class="flex-1 max-w-xl hidden md:block" x-data="searchAutocomplete('{{ addslashes(request('search')) }}')">
                    <form method="GET" action="{{ route('storefront.catalog') }}" class="relative flex items-center" @submit="close()">
                        <div class="relative w-full">
                            <input type="text" name="search" x-model="query"
                                   @input="onInput()"
                                   @keydown="onKeyDown($event)"
                                   @focus="if(query && query.trim().length >= 2 && results.length > 0) isOpen = true"
                                   placeholder="Search air conditioners, refrigerators, LED TVs, scooters..."
                                   autocomplete="off"
                                   class="w-full pl-10 pr-24 py-2.5 text-xs rounded-xl bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 focus:border-rose-500 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500/20 transition-all shadow-inner">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button type="submit" class="absolute right-1.5 top-1.5 px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition-colors shadow-xs">
                            Search
                        </button>
                        @include('storefront.partials.search-dropdown')
                    </form>
                </div>

                <!-- Cart Action (Desktop: md:flex) -->
                <div class="hidden md:flex items-center space-x-3">
                    @php $cartCount = app(\App\Services\CartService::class)->count(); @endphp
                    <!-- Shopping Cart Button -->
                    <a href="{{ route('cart.index') }}" 
                       class="relative px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 transition-colors flex items-center justify-center group"
                       aria-label="View Shopping Cart">
                        <svg class="w-5 h-5 text-slate-700 group-hover:text-rose-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span class="text-xs font-bold ml-1.5 text-slate-700 group-hover:text-slate-900">Cart</span>
                        @if($cartCount > 0)
                            <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 px-1.5 rounded-full bg-rose-600 text-white text-[10px] font-black flex items-center justify-center shadow-xs">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                </div>

                <!-- Mobile Navigation Actions (Cart & Hamburger only: md:hidden) -->
                <div class="flex items-center space-x-2 md:hidden">
                    @php $mobileCartCount = app(\App\Services\CartService::class)->count(); @endphp
                    <!-- Mobile Cart Button -->
                    <a href="{{ route('cart.index') }}" 
                       class="relative p-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors"
                       aria-label="View Shopping Cart">
                        <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        @if($mobileCartCount > 0)
                            <span class="absolute -top-1 -right-1 min-w-[18px] h-4.5 px-1 rounded-full bg-rose-600 text-white text-[9px] font-black flex items-center justify-center shadow-xs">
                                {{ $mobileCartCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Hamburger Button to Open Off-Canvas Navigation Sidebar (Mobile Only) -->
                    <button type="button" 
                            @click="mobileMenuOpen = true" 
                            class="p-2.5 rounded-xl text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500/30 flex items-center justify-center transition-colors"
                            aria-label="Open mobile navigation sidebar"
                            :aria-expanded="mobileMenuOpen.toString()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Search Bar (Phone Screens with Live Autocomplete) -->
            <div class="md:hidden pb-3 pt-1" x-data="searchAutocomplete('{{ addslashes(request('search')) }}')">
                <form method="GET" action="{{ route('storefront.catalog') }}" class="relative" @submit="close()">
                    <div class="relative w-full">
                        <input type="text" name="search" x-model="query"
                               @input="onInput()"
                               @keydown="onKeyDown($event)"
                               @focus="if(query && query.trim().length >= 2 && results.length > 0) isOpen = true"
                               placeholder="Search appliances, electronics, scooters..."
                               autocomplete="off"
                               class="w-full pl-9 pr-20 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-inner">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <button type="submit" class="absolute right-1.5 top-1.5 px-3 py-1 bg-rose-600 text-white text-[11px] font-bold rounded-lg hover:bg-rose-700 transition-colors shadow-xs">
                            Search
                        </button>
                    </div>
                    @include('storefront.partials.search-dropdown')
                </form>
            </div>
        </div>

        <!-- Clean Desktop Category Navigation Bar: Light background with red active indicators (md:block) -->
        <div class="bg-slate-50 border-t border-slate-200 overflow-x-auto scrollbar-none hidden md:block">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex items-center space-x-7 py-2.5 text-xs font-semibold tracking-wide whitespace-nowrap">
                    <a href="{{ route('storefront.catalog') }}" 
                       class="transition-colors pb-0.5 {{ !request('category') ? 'text-rose-600 font-bold border-b-2 border-rose-600' : 'text-slate-700 hover:text-rose-600' }}">
                        All Products
                    </a>
                    <a href="{{ route('storefront.catalog', ['category' => 'home-appliances']) }}" 
                       class="transition-colors pb-0.5 {{ request('category') === 'home-appliances' ? 'text-rose-600 font-bold border-b-2 border-rose-600' : 'text-slate-700 hover:text-rose-600' }}">
                        Home Appliances
                    </a>
                    <a href="{{ route('storefront.catalog', ['category' => 'air-conditioners']) }}" 
                       class="transition-colors pb-0.5 {{ request('category') === 'air-conditioners' ? 'text-rose-600 font-bold border-b-2 border-rose-600' : 'text-slate-700 hover:text-rose-600' }}">
                        Air Conditioners
                    </a>
                    <a href="{{ route('storefront.catalog', ['category' => 'refrigerators-freezers']) }}" 
                       class="transition-colors pb-0.5 {{ request('category') === 'refrigerators-freezers' ? 'text-rose-600 font-bold border-b-2 border-rose-600' : 'text-slate-700 hover:text-rose-600' }}">
                        Refrigerators
                    </a>
                    <a href="{{ route('storefront.catalog', ['category' => 'washing-machines']) }}" 
                       class="transition-colors pb-0.5 {{ request('category') === 'washing-machines' ? 'text-rose-600 font-bold border-b-2 border-rose-600' : 'text-slate-700 hover:text-rose-600' }}">
                        Washing Machines
                    </a>
                    <a href="{{ route('storefront.catalog', ['category' => 'led-tvs']) }}" 
                       class="transition-colors pb-0.5 {{ request('category') === 'led-tvs' ? 'text-rose-600 font-bold border-b-2 border-rose-600' : 'text-slate-700 hover:text-rose-600' }}">
                        LED TVs
                    </a>
                    <a href="{{ route('storefront.catalog', ['category' => 'kitchen-appliances']) }}" 
                       class="transition-colors pb-0.5 {{ request('category') === 'kitchen-appliances' ? 'text-rose-600 font-bold border-b-2 border-rose-600' : 'text-slate-700 hover:text-rose-600' }}">
                        Kitchen Appliances
                    </a>
                    <a href="{{ route('storefront.catalog', ['category' => 'fans']) }}" 
                       class="transition-colors pb-0.5 {{ request('category') === 'fans' ? 'text-rose-600 font-bold border-b-2 border-rose-600' : 'text-slate-700 hover:text-rose-600' }}">
                        Fans
                    </a>
                    <a href="{{ route('storefront.catalog', ['category' => 'geysers-water-dispensers']) }}" 
                       class="transition-colors pb-0.5 {{ request('category') === 'geysers-water-dispensers' ? 'text-rose-600 font-bold border-b-2 border-rose-600' : 'text-slate-700 hover:text-rose-600' }}">
                        Geysers & Dispensers
                    </a>
                    <a href="{{ route('storefront.catalog', ['category' => 'scooters']) }}" 
                       class="transition-colors pb-0.5 {{ request('category') === 'scooters' ? 'text-rose-600 font-bold border-b-2 border-rose-600' : 'text-slate-700 hover:text-rose-600' }}">
                        Scooters
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Mobile Off-Canvas Sidebar Navigation (Navbar becomes Sidebar on small devices/phones) -->
    <div x-show="mobileMenuOpen" 
         x-cloak 
         class="md:hidden fixed inset-0 z-50 flex" 
         role="dialog" 
         aria-modal="true" 
         aria-label="Mobile Navigation Sidebar"
         @keydown.window.escape="mobileMenuOpen = false">

        <!-- Smooth Backdrop Blur Overlay -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs" 
             @click="mobileMenuOpen = false" 
             aria-hidden="true"></div>

        <!-- Sliding Sidebar Panel from Left -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="relative w-[85vw] max-w-xs sm:max-w-sm bg-white h-full flex flex-col shadow-2xl z-10 border-r border-slate-200 overflow-hidden"
             @click.stop>

            <!-- Sidebar Header with Brand & Close Button -->
            <div class="p-4 bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                <a href="{{ route('home') }}" @click="mobileMenuOpen = false" class="flex items-center space-x-2.5">
                    <img src="{{ $storeLogoUrl }}" alt="{{ $storeName }}" class="h-9 w-auto object-contain brightness-105">
                </a>
                <button type="button" 
                        @click="mobileMenuOpen = false" 
                        class="w-8 h-8 rounded-full bg-slate-800 text-slate-300 hover:text-white hover:bg-rose-600 flex items-center justify-center transition-colors focus:outline-none"
                        aria-label="Close navigation sidebar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Showroom Badge & Search Bar Inside Sidebar -->
            <div class="p-4 border-b border-slate-100 bg-slate-50 space-y-3">
                <div class="flex items-center justify-between text-[11px]">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold bg-rose-100 text-rose-700">
                        Lahore Showroom
                    </span>
                    <span class="text-slate-500 font-medium">Daily: 10 AM – 10 PM</span>
                </div>

                <!-- Integrated Search within Sidebar with Autocomplete Dropdown -->
                <div x-data="searchAutocomplete('{{ addslashes(request('search')) }}')" class="relative">
                    <form method="GET" action="{{ route('storefront.catalog') }}" class="relative" @submit="close(); mobileMenuOpen = false;">
                        <input type="text" name="search" x-model="query"
                               @input="onInput()"
                               @keydown="onKeyDown($event)"
                               @focus="if(query && query.trim().length >= 2 && results.length > 0) isOpen = true"
                               placeholder="Search products..."
                               autocomplete="off"
                               class="w-full pl-8 pr-16 py-2 text-xs rounded-xl bg-white border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-xs">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <button type="submit" class="absolute right-1 top-1 px-2.5 py-1 bg-rose-600 text-white text-[10px] font-bold rounded-lg hover:bg-rose-700">
                            Go
                        </button>
                        @include('storefront.partials.search-dropdown')
                    </form>
                </div>
            </div>

            <!-- Scrollable Navigation Area (Main Links + All 12 Categories) -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 text-xs">
                
                <!-- Main Nav Links -->
                <div class="space-y-1">
                    <a href="{{ route('home') }}" @click="mobileMenuOpen = false" 
                       class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl font-bold transition-colors {{ request()->routeIs('home') ? 'bg-rose-50 text-rose-600' : 'text-slate-800 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Home</span>
                    </a>
                    <a href="{{ route('storefront.catalog') }}" @click="mobileMenuOpen = false" 
                       class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl font-bold transition-colors {{ request()->routeIs('storefront.catalog') && !request('category') ? 'bg-rose-50 text-rose-600' : 'text-slate-800 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>All Products (Catalogue)</span>
                    </a>
                    <a href="{{ route('cart.index') }}" @click="mobileMenuOpen = false" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-xl font-bold transition-colors {{ request()->routeIs('cart.index') ? 'bg-rose-50 text-rose-600' : 'text-slate-800 hover:bg-slate-100' }}">
                        <div class="flex items-center space-x-2.5">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <span>Shopping Cart</span>
                        </div>
                        @php $drawerCartCount = app(\App\Services\CartService::class)->count(); @endphp
                        @if($drawerCartCount > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white shadow-xs">
                                {{ $drawerCartCount }}
                            </span>
                        @endif
                    </a>
                </div>

                <!-- Confirmed Categories Section (All 12 Client Categories) -->
                <div>
                    <div class="flex items-center justify-between px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                        <span>Browse Categories</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    </div>

                    <div class="space-y-0.5 mt-1">
                        @php
                            try {
                                $mobileCategories = \App\Models\Category::where('is_active', true)->orderBy('sort_order')->get();
                            } catch (\Throwable $e) {
                                $mobileCategories = collect();
                            }

                            $displayLabelMap = [
                                'fans'                   => 'Fans & Inverter Fans',
                                'led-tvs'                => 'Smart LED TVs',
                                'scooters'               => 'Electric Scooters',
                                'furniture'              => 'Furniture (Home & Office)',
                                'molded-foam-multiform'  => 'Molded Foam Products',
                            ];
                        @endphp

                        @foreach($mobileCategories as $mc)
                            @php 
                                $isActive = request('category') === $mc->slug; 
                                $catLabel = $displayLabelMap[$mc->slug] ?? $mc->name;
                            @endphp
                            <a href="{{ route('storefront.catalog', ['category' => $mc->slug]) }}" 
                               @click="mobileMenuOpen = false"
                               class="flex items-center justify-between px-3 py-2 rounded-lg transition-colors {{ $isActive ? 'bg-rose-50 text-rose-600 font-bold border-l-2 border-rose-600' : 'text-slate-600 hover:text-rose-600 hover:bg-slate-50 font-medium' }}">
                                <span>{{ $catLabel }}</span>
                                <svg class="w-3.5 h-3.5 text-slate-300 {{ $isActive ? 'text-rose-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Sidebar Sticky Footer Actions (WhatsApp Desk & Showroom Call) -->
            <div class="p-4 border-t border-slate-200 bg-white space-y-2.5">
                <a href="https://wa.me/{{ $storeWhatsappDigits }}" 
                   @click.prevent="window.dispatchEvent(new CustomEvent('open-deen-chat')); mobileMenuOpen = false"
                   target="_blank" rel="noopener noreferrer" 
                   class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center justify-center space-x-2 shadow-sm transition-all cursor-pointer">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                    <span>WhatsApp Order Desk &amp; AI Assistant</span>
                </a>

                <a href="tel:{{ $storePhoneDigits }}" 
                   class="w-full py-2 px-3 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center justify-center space-x-2 transition-colors">
                    <svg class="w-3.5 h-3.5 text-rose-600" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>
                    <span>Call Showroom: {{ $storePhone }}</span>
                </a>

                <div class="text-[10px] text-slate-500 pt-1 leading-snug">
                    <p class="font-bold text-slate-800">{{ $storeName }}</p>
                    <p>{{ $storeAddress }}</p>
                </div>
            </div>

        </div>
    </div>

    <!-- Main Content Slot -->
    <main class="flex-grow">
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <div x-data="{ show: true }" x-show="show" x-transition class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-3 text-xs sm:text-sm font-semibold">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('cart.index') }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors shadow-xs">
                            View Cart
                        </a>
                        <button type="button" @click="show = false" class="text-emerald-700 hover:text-emerald-950 p-1 text-lg leading-none font-bold">&times;</button>
                    </div>
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <!-- Interactive DEEN AI Chat Popup & WhatsApp Order Desk Floating Widget -->
    @include('storefront.partials.ai-chat-modal')

    <!-- Deep Black Retail Footer with White Text & Red Accents -->
    <footer class="bg-slate-950 text-slate-300 border-t border-slate-900 mt-16 pt-14 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                
                <!-- Brand Context -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('images/deen-logo.png') }}" alt="DEEN Electronics" class="h-10 w-auto object-contain">
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        DEEN Electronics is a retail destination in Lahore offering quality home appliances, refrigerators, air conditioners, LED TVs, washing machines, and electric scooters.
                    </p>
                    <div class="text-xs text-slate-400 space-y-1">
                        <div><strong class="text-white">Business Owner:</strong> Muhammad Aslam</div>
                        <div><strong class="text-white">Operating Hours:</strong> 10:00 AM – 10:00 PM Daily</div>
                    </div>
                </div>

                <!-- Catalogue Quick Links -->
                <div>
                    <h3 class="text-white font-bold text-xs uppercase tracking-wider mb-4 border-l-2 border-rose-600 pl-2">Top Categories</h3>
                    <ul class="space-y-2 text-slate-400 text-xs">
                        <li><a href="{{ route('storefront.catalog', ['category' => 'air-conditioners']) }}" class="hover:text-rose-400 transition-colors">Air Conditioners</a></li>
                        <li><a href="{{ route('storefront.catalog', ['category' => 'refrigerators-freezers']) }}" class="hover:text-rose-400 transition-colors">Refrigerators & Freezers</a></li>
                        <li><a href="{{ route('storefront.catalog', ['category' => 'washing-machines']) }}" class="hover:text-rose-400 transition-colors">Washing Machines</a></li>
                        <li><a href="{{ route('storefront.catalog', ['category' => 'led-tvs']) }}" class="hover:text-rose-400 transition-colors">Smart LED TVs</a></li>
                        <li><a href="{{ route('storefront.catalog', ['category' => 'kitchen-appliances']) }}" class="hover:text-rose-400 transition-colors">Kitchen Appliances</a></li>
                        <li><a href="{{ route('storefront.catalog', ['category' => 'scooters']) }}" class="hover:text-rose-400 transition-colors">Electric Scooters</a></li>
                        <li><a href="{{ route('storefront.catalog', ['category' => 'molded-foam-multiform']) }}" class="hover:text-rose-400 transition-colors">Molded Foam Products</a></li>
                    </ul>
                </div>

                <!-- Policies & Ordering Guidelines -->
                <div>
                    <h3 class="text-white font-bold text-xs uppercase tracking-wider mb-4 border-l-2 border-rose-600 pl-2">Policies & Service Terms</h3>
                    <ul class="space-y-2.5 text-slate-400 text-xs">
                        <li>
                            <strong class="text-slate-200">Nationwide Delivery:</strong>
                            <p class="text-[11px] text-slate-400 mt-0.5">Subject to product type, destination serviceability, and applicable delivery charges.</p>
                        </li>
                        <li>
                            <strong class="text-slate-200">Bulky Items:</strong>
                            <p class="text-[11px] text-slate-400 mt-0.5">Separate delivery arrangements and freight charges apply to scooters, furniture, and large appliances.</p>
                        </li>
                        <li>
                            <strong class="text-slate-200">Warranty & Returns:</strong>
                            <p class="text-[11px] text-slate-400 mt-0.5">No returns. Faults and warranty claims are handled strictly according to manufacturer warranty terms.</p>
                        </li>
                        <li>
                            <strong class="text-slate-200">Payment Modes:</strong>
                            <p class="text-[11px] text-slate-400 mt-0.5">Cash on Delivery (subject to order confirmation & serviceability), Bank Transfer, JazzCash, and Easypaisa.</p>
                        </li>
                    </ul>
                </div>

                <!-- Store Contact & Showroom -->
                <div class="space-y-4">
                    <h3 class="text-white font-bold text-xs uppercase tracking-wider mb-4 border-l-2 border-rose-600 pl-2">Lahore Showroom</h3>
                    <div class="text-slate-400 text-xs space-y-3">
                        <div class="flex items-start space-x-2.5">
                            <svg class="w-4 h-4 text-rose-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $storeName }}, {{ $storeAddress }}.</span>
                        </div>
                        <div class="flex items-center space-x-2.5">
                            <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <a href="tel:{{ $storePhoneDigits }}" class="hover:text-white transition-colors font-medium">{{ $storePhone }}</a>
                        </div>
                        <div class="flex items-center space-x-2.5">
                            <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <a href="mailto:{{ $storeEmail }}" class="hover:text-white transition-colors">{{ $storeEmail }}</a>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-12 pt-6 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2">
                <div>&copy; 2026 DEEN Electronics. All rights reserved.</div>
                <div>Domain: <span class="text-slate-400 font-mono">deenelectronics.com</span></div>
            </div>
        </div>
    </footer>

    <!-- Universal Search Autocomplete Controller -->
    <script>
        function searchAutocomplete(initialQuery = '') {
            return {
                query: initialQuery || '',
                isOpen: false,
                isLoading: false,
                results: [],
                total: 0,
                selectedIndex: -1,
                debounceTimer: null,
                endpoint: '{{ route("storefront.search.autocomplete") }}',
                catalogUrl: '{{ route("storefront.catalog") }}',

                onInput() {
                    clearTimeout(this.debounceTimer);
                    const q = this.query ? this.query.trim() : '';
                    if (q.length < 2) {
                        this.results = [];
                        this.total = 0;
                        this.isOpen = false;
                        this.isLoading = false;
                        this.selectedIndex = -1;
                        return;
                    }
                    this.isLoading = true;
                    this.isOpen = true;
                    this.debounceTimer = setTimeout(() => {
                        this.fetchResults();
                    }, 250);
                },

                async fetchResults() {
                    const q = this.query ? this.query.trim() : '';
                    if (q.length < 2) return;
                    try {
                        const response = await fetch(`${this.endpoint}?q=${encodeURIComponent(q)}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (response.ok) {
                            const data = await response.json();
                            this.results = data.results || [];
                            this.total = data.total || 0;
                            this.selectedIndex = -1;
                            this.isOpen = true;
                        }
                    } catch (err) {
                        console.error('Autocomplete fetch error:', err);
                    } finally {
                        this.isLoading = false;
                    }
                },

                onKeyDown(event) {
                    if (!this.isOpen || this.results.length === 0) return;

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        this.selectedIndex = (this.selectedIndex + 1) % this.results.length;
                    } else if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        this.selectedIndex = (this.selectedIndex - 1 + this.results.length) % this.results.length;
                    } else if (event.key === 'Enter') {
                        if (this.selectedIndex >= 0 && this.results[this.selectedIndex]) {
                            event.preventDefault();
                            window.location.href = this.results[this.selectedIndex].url;
                        }
                    } else if (event.key === 'Escape') {
                        this.isOpen = false;
                        this.selectedIndex = -1;
                    }
                },

                close() {
                    this.isOpen = false;
                    this.selectedIndex = -1;
                }
            };
        }
    </script>

</body>
</html>
