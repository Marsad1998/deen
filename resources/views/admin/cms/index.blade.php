@extends('layouts.admin')

@section('title', 'CMS Website Management Hub — DEEN Electronics')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    @include('admin.cms._nav')

    @if(session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center space-x-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- CMS Sections Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- 1. Hero Slider Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between hover:border-blue-300 transition-all">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-rose-50 text-rose-600 border border-rose-200">
                        Section 1 • Above Fold
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        4 Slides Active
                    </span>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-900">Cinematic Hero Slider</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Each slide has its own showroom pill badge, headline, subtitle, primary CTA button (product/catalog link), and background photography.
                    </p>
                </div>

                <!-- Snapshot of Slides -->
                <div class="grid grid-cols-2 gap-2 pt-2">
                    @foreach($heroConfig['slides'] as $s)
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                            <span class="text-[9px] font-bold text-rose-600 block truncate">{{ $s['badge_text'] }}</span>
                            <span class="text-xs font-bold text-slate-800 block truncate">{{ $s['title'] }}</span>
                            <span class="text-[10px] text-slate-400 block truncate">CTA: {{ $s['button_text'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">Typewriter &amp; Per-slide CTAs</span>
                <a href="{{ route('admin.cms.hero') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center space-x-1.5">
                    <span>Manage Slides &amp; CTAs</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- 2. Promotional Banner Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between hover:border-blue-300 transition-all">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
                        Section 2 • Mid-Page Deals
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $promoConfig['is_enabled'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $promoConfig['is_enabled'] ? 'Live on Storefront' : 'Disabled' }}
                    </span>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-900">Promotional Banner</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        High-impact promotional strip for seasonal campaigns (e.g. Inverter Cooling, Wedding Festive Specials) with direct WhatsApp &amp; catalog links.
                    </p>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-900 text-white space-y-1.5">
                    <span class="text-[10px] font-bold text-rose-400 uppercase tracking-wider">{{ $promoConfig['badge_text'] }}</span>
                    <h3 class="text-xs font-bold line-clamp-1">{{ $promoConfig['headline'] }}</h3>
                    <p class="text-[11px] text-slate-300 line-clamp-2">{{ $promoConfig['description'] }}</p>
                    <div class="pt-1 flex items-center space-x-2 text-[10px]">
                        <span class="px-2 py-0.5 rounded bg-rose-600 text-white font-bold">{{ $promoConfig['button_text'] }}</span>
                        <span class="text-slate-400">&bull; {{ $promoConfig['button_url'] }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">Custom Banner Photography</span>
                <a href="{{ route('admin.cms.promo') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center space-x-1.5">
                    <span>Configure Promo Banner</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- 3. Showroom Trust Highlights Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between hover:border-blue-300 transition-all">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Section 3 • Trust Bar
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        4 Value Badges Active
                    </span>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-900">Trust &amp; Value Highlights</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        The 4 commercial trust cards below the hero banner assuring visitors regarding Showroom Pickup, Nationwide Delivery, Official Warranty, and Flexible Payment.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2">
                    @foreach($trustConfig['items'] as $item)
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 space-y-0.5">
                            <span class="text-xs font-bold text-slate-900 block truncate">{{ $item['title'] }}</span>
                            <span class="text-[10px] text-slate-500 block line-clamp-2 leading-tight">{{ $item['description'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">Guarantees &amp; Policy Text</span>
                <a href="{{ route('admin.cms.trust') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center space-x-1.5">
                    <span>Edit Trust Badges</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- 4. Showroom About & Location Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between hover:border-blue-300 transition-all">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                        Section 4 • Footer Context
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                        Showroom Verified
                    </span>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-900">Showroom About &amp; Location</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Control the physical showroom narrative, daily opening hours (10:00 AM – 10:00 PM), representative name (Muhammad Aslam), and Google Maps/address information.
                    </p>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-1 text-xs">
                    <div class="flex justify-between py-0.5">
                        <span class="text-slate-400">Headline:</span>
                        <span class="font-bold text-slate-800">{{ $aboutConfig['headline'] }}</span>
                    </div>
                    <div class="flex justify-between py-0.5">
                        <span class="text-slate-400">Hours:</span>
                        <span class="font-semibold text-slate-700">{{ $aboutConfig['daily_hours'] }}</span>
                    </div>
                    <div class="flex justify-between py-0.5">
                        <span class="text-slate-400">Owner / Rep:</span>
                        <span class="font-bold text-slate-900">{{ $aboutConfig['business_owner'] }}</span>
                    </div>
                    <div class="text-[11px] text-slate-500 pt-1 line-clamp-2">
                        {{ $aboutConfig['narrative'] }}
                    </div>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">Showroom Identity &amp; Hours</span>
                <a href="{{ route('admin.cms.about') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center space-x-1.5">
                    <span>Manage Showroom Context</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- 5. Categories & Brands Display Manager -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between hover:border-blue-300 transition-all sm:col-span-2">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200">
                        Section 5 • Navigation &amp; Homepage Grids
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">
                        Full Curation
                    </span>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-900">Categories &amp; Brands Storefront Display</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Control which categories appear in the top desktop navigation bar (avoid cluttering the navbar), customize headings for homepage categories, and select which authorized brands (Haier, Dawlance, Gree, etc.) are highlighted in the homepage Brand Showcase.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[11px]">Navbar Categories:</span>
                        <span class="font-bold text-slate-900 text-sm mt-0.5 block">User-Controlled Links</span>
                        <span class="text-[10px] text-slate-500 mt-0.5 block">Select only primary categories for header</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[11px]">Homepage Categories:</span>
                        <span class="font-bold text-slate-900 text-sm mt-0.5 block">Custom Title &amp; Grid</span>
                        <span class="text-[10px] text-slate-500 mt-0.5 block">Enable/disable &amp; select featured items</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[11px]">Homepage Brands:</span>
                        <span class="font-bold text-slate-900 text-sm mt-0.5 block">Official Partner Logos</span>
                        <span class="text-[10px] text-slate-500 mt-0.5 block">Authorized dealer trust badges</span>
                    </div>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">Header Nav &amp; Homepage Showcases</span>
                <a href="{{ route('admin.cms.display') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center space-x-1.5">
                    <span>Manage Categories &amp; Brands</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
