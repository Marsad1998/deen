@extends('layouts.admin')

@section('title', 'Manage Hero Slider & Per-Slide CTAs — DEEN Electronics')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="{
    activeTab: 1
}">

    @include('admin.cms._nav')

    @if(session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center space-x-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Top Banner Card -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-rose-600 mb-1">
                <span>Visual Merchandising</span>
                <span>&bull;</span>
                <span>Hero Section CMS</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Homepage Hero Slider Management</h1>
            <p class="text-xs text-slate-500 mt-1">
                Each of the 4 rotating slides has its own independent <strong>Showroom Pill Badge</strong>, <strong>Headline</strong>, <strong>Subtitle</strong>, and <strong>CTA Buttons</strong> (with direct product / category / custom linking).
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('home') }}" target="_blank" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 bg-slate-50 hover:bg-slate-100 transition-colors flex items-center space-x-1.5 shadow-xs">
                <span>View Live Slider</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    <!-- Main CMS Management Form -->
    <form method="POST" action="{{ route('admin.cms.hero.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- SECTION 1: Default / Global Typewriter Headline Section -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-xs space-y-5">
            <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                <span class="w-7 h-7 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xs">1</span>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Global Header Brand Identity &amp; Typewriter Phrases</h2>
                    <p class="text-[11px] text-slate-500">Configure global fallback badge text, intro headline, and the animated typewriter phrases</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Global Badge Text -->
                <div>
                    <label for="badge_text" class="block text-xs font-bold text-slate-800 mb-1">
                        Global Showroom Pill Badge <span class="text-slate-400 font-normal">(Default Fallback)</span>
                    </label>
                    <input type="text" id="badge_text" name="badge_text" value="{{ old('badge_text', $heroConfig['badge_text']) }}"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">e.g. Lahore Showroom &amp; Online Store</p>
                </div>

                <!-- Global Headline -->
                <div>
                    <label for="headline" class="block text-xs font-bold text-slate-800 mb-1">
                        Intro Headline Prefix
                    </label>
                    <input type="text" id="headline" name="headline" value="{{ old('headline', $heroConfig['headline']) }}"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">e.g. Upgrade Your Home With</p>
                </div>

                <!-- Typewriter Phrases -->
                <div class="sm:col-span-2">
                    <label for="animated_phrases" class="block text-xs font-bold text-slate-800 mb-1">
                        Rotating Animated Typewriter Phrases <span class="text-slate-400 font-normal">(One phrase per line)</span>
                    </label>
                    <textarea id="animated_phrases" name="animated_phrases" rows="4"
                              class="w-full px-3.5 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">{{ old('animated_phrases', $heroConfig['animated_phrases_lines']) }}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Displays sequentially in vivid rose red text with a blinking cursor.</p>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Slide-by-Slide Detailed Content, Badges & CTAs -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-xs space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                <div class="flex items-center space-x-3">
                    <span class="w-7 h-7 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-xs">2</span>
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Per-Slide Content, Showroom Pill Badges &amp; CTA Buttons</h2>
                        <p class="text-[11px] text-slate-500">Each slide updates the headline, subtitle, pill badge, and button destination on rotation</p>
                    </div>
                </div>

                <!-- Slide Switcher Tabs -->
                <div class="flex space-x-1.5 p-1 bg-slate-100 rounded-xl">
                    @foreach($heroConfig['slides'] as $index => $slide)
                        @php $n = $index + 1; @endphp
                        <button type="button" 
                                @click="activeTab = {{ $n }}" 
                                :class="activeTab === {{ $n }} ? 'bg-white text-rose-600 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                                class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
                            Slide {{ $n }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Slides Configuration Panels -->
            @foreach($heroConfig['slides'] as $index => $slide)
                @php $num = $index + 1; @endphp
                <div x-show="activeTab === {{ $num }}" x-cloak class="space-y-6">
                    
                    <!-- Slide Overview Card -->
                    <div class="p-4 rounded-2xl bg-rose-50/40 border border-rose-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-xl bg-rose-600 text-white font-black text-xs flex items-center justify-center">#{{ $num }}</span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Slide {{ $num }} Configuration</h3>
                                <p class="text-[11px] text-slate-500">Preset: <span class="font-mono">{{ $slide['image'] }}</span></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-white text-rose-600 border border-rose-200 shadow-xs">
                                Active in 5.5s Rotation
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        
                        <!-- Left: Slide Content & CTA Setup (7 Cols) -->
                        <div class="lg:col-span-7 space-y-4">
                            
                            <!-- 1. Showroom Pill Badge for this slide -->
                            <div>
                                <label for="slide_{{ $num }}_badge" class="block text-xs font-bold text-slate-800 mb-1">
                                    Showroom Pill Badge Text <span class="text-rose-600">*</span>
                                </label>
                                <input type="text" 
                                       id="slide_{{ $num }}_badge" 
                                       name="slide_{{ $num }}_badge" 
                                       value="{{ old("slide_{$num}_badge", $slide['badge_text']) }}"
                                       placeholder="e.g. Lahore Showroom Exclusive, Energy Saver 2026, Wedding Package"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none font-semibold text-rose-600">
                                <p class="text-[10px] text-slate-400 mt-1">Appears inside the glowing pill badge above the headline for this slide.</p>
                            </div>

                            <!-- 2. Headline for this slide -->
                            <div>
                                <label for="slide_{{ $num }}_title" class="block text-xs font-bold text-slate-800 mb-1">
                                    Slide Title / Main Headline <span class="text-rose-600">*</span>
                                </label>
                                <input type="text" 
                                       id="slide_{{ $num }}_title" 
                                       name="slide_{{ $num }}_title" 
                                       value="{{ old("slide_{$num}_title", $slide['title']) }}"
                                       placeholder="e.g. DC Inverter Air Conditioners & Deep Freezers"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none font-bold text-slate-900">
                                <p class="text-[10px] text-slate-400 mt-1">Bold headline displayed prominently on this slide.</p>
                            </div>

                            <!-- 3. Subtitle / Description for this slide -->
                            <div>
                                <label for="slide_{{ $num }}_subtitle" class="block text-xs font-bold text-slate-800 mb-1">
                                    Slide Subtitle / Descriptive Narrative
                                </label>
                                <textarea id="slide_{{ $num }}_subtitle" 
                                          name="slide_{{ $num }}_subtitle" 
                                          rows="3"
                                          placeholder="e.g. T3 tropical compressors, energy saving up to 75%, and same-day Lahore dispatch."
                                          class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none text-slate-700">{{ old("slide_{$num}_subtitle", $slide['subtitle']) }}</textarea>
                            </div>

                            <!-- 4. Primary CTA Button Settings -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3" x-data="{
                                btnType: '{{ old("slide_{$num}_button_type", $slide['button_type']) }}'
                            }">
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-rose-600 block">Primary CTA Button Configuration</span>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label for="slide_{{ $num }}_button_text" class="block text-xs font-bold text-slate-700 mb-1">
                                            Button Label
                                        </label>
                                        <input type="text" 
                                               id="slide_{{ $num }}_button_text" 
                                               name="slide_{{ $num }}_button_text" 
                                               value="{{ old("slide_{$num}_button_text", $slide['button_text']) }}"
                                               placeholder="e.g. Shop Inverters"
                                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white font-bold text-slate-900 focus:outline-none focus:border-rose-600">
                                    </div>

                                    <div>
                                        <label for="slide_{{ $num }}_button_type" class="block text-xs font-bold text-slate-700 mb-1">
                                            Link Destination Mode
                                        </label>
                                        <select id="slide_{{ $num }}_button_type" 
                                                name="slide_{{ $num }}_button_type" 
                                                x-model="btnType"
                                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white font-semibold text-slate-800 focus:outline-none focus:border-rose-600">
                                            <option value="catalog">Browse Catalogue (All Products)</option>
                                            <option value="product">Link Specific Product</option>
                                            <option value="custom">Custom URL or Category Path</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Product selector (if button_type === 'product') -->
                                <div x-show="btnType === 'product'" x-cloak class="pt-2">
                                    <label for="slide_{{ $num }}_button_product_id" class="block text-xs font-bold text-slate-700 mb-1">
                                        Select Target Product
                                    </label>
                                    <select id="slide_{{ $num }}_button_product_id" 
                                            name="slide_{{ $num }}_button_product_id" 
                                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-rose-600">
                                        <option value="">-- Choose Product --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" {{ ($slide['button_product_id'] == $p->id) ? 'selected' : '' }}>
                                                {{ $p->name }} (SKU: {{ $p->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Custom URL input (if button_type === 'custom') -->
                                <div x-show="btnType === 'custom'" x-cloak class="pt-2">
                                    <label for="slide_{{ $num }}_button_custom_url" class="block text-xs font-bold text-slate-700 mb-1">
                                        Target URL / Category Path
                                    </label>
                                    <input type="text" 
                                           id="slide_{{ $num }}_button_custom_url" 
                                           name="slide_{{ $num }}_button_custom_url" 
                                           value="{{ old("slide_{$num}_button_custom_url", $slide['button_custom_url']) }}"
                                           placeholder="e.g. /catalog?category=air-conditioners"
                                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white font-mono text-slate-800 focus:outline-none focus:border-rose-600">
                                </div>
                            </div>

                            <!-- 5. Secondary WhatsApp Button -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <label for="slide_{{ $num }}_secondary_button_text" class="block text-xs font-bold text-slate-700 mb-1">
                                        Secondary Button Label
                                    </label>
                                    <input type="text" 
                                           id="slide_{{ $num }}_secondary_button_text" 
                                           name="slide_{{ $num }}_secondary_button_text" 
                                           value="{{ old("slide_{$num}_secondary_button_text", $slide['secondary_button_text']) }}"
                                           placeholder="Enquire on WhatsApp"
                                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-emerald-600">
                                </div>
                                <div>
                                    <label for="slide_{{ $num }}_secondary_button_url" class="block text-xs font-bold text-slate-700 mb-1">
                                        Secondary URL / WhatsApp Link
                                    </label>
                                    <input type="text" 
                                           id="slide_{{ $num }}_secondary_button_url" 
                                           name="slide_{{ $num }}_secondary_button_url" 
                                           value="{{ old("slide_{$num}_secondary_button_url", $slide['secondary_button_url']) }}"
                                           placeholder="https://wa.me/923007769932"
                                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white font-mono focus:outline-none focus:border-emerald-600">
                                </div>
                            </div>

                        </div>

                        <!-- Right: Slide Photograph & Live Card Preview (5 Cols) -->
                        <div class="lg:col-span-5 space-y-4">
                            
                            <!-- Mockup Card Container -->
                            <div class="rounded-2xl border border-slate-200 overflow-hidden bg-slate-950 text-white shadow-md relative">
                                <div class="aspect-video relative">
                                    <img src="{{ $slide['image_url'] }}" alt="Slide {{ $num }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
                                    
                                    <div class="absolute bottom-3 left-3 right-3 space-y-1">
                                        <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold bg-white/20 text-white border border-white/20">
                                            {{ $slide['badge_text'] }}
                                        </span>
                                        <h4 class="text-xs font-bold text-white line-clamp-1">{{ $slide['title'] }}</h4>
                                        <p class="text-[10px] text-slate-300 line-clamp-1">{{ $slide['subtitle'] }}</p>
                                        <div class="pt-1 flex items-center space-x-1.5">
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-rose-600 text-white">{{ $slide['button_text'] }}</span>
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-600 text-white">{{ $slide['secondary_button_text'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Choose from Library Preset -->
                            <div>
                                <label for="slide_{{ $num }}_image_preset" class="block text-xs font-bold text-slate-700 mb-1">
                                    Select Image Preset
                                </label>
                                <select id="slide_{{ $num }}_image_preset" 
                                        name="slide_{{ $num }}_image_preset" 
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-xs text-slate-900 focus:outline-none focus:border-rose-600">
                                    @foreach($presetImages as $path => $label)
                                        <option value="{{ $path }}" {{ ($slide['image'] === $path) ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Upload Custom Slide Photograph -->
                            <div>
                                <label for="slide_{{ $num }}_file" class="block text-xs font-bold text-slate-700 mb-1">
                                    Or Upload Custom Slide Photo
                                </label>
                                <input type="file" 
                                       id="slide_{{ $num }}_file" 
                                       name="slide_{{ $num }}_file" 
                                       accept="image/png,image/jpeg,image/webp"
                                       class="w-full text-xs text-slate-500 file:mr-2.5 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300 cursor-pointer">
                                <span class="text-[10px] text-slate-400 mt-1 block">Recommended: 1920&times;850px, WebP or JPEG</span>
                            </div>

                        </div>

                    </div>

                </div>
            @endforeach
        </div>

        <!-- Sticky Save Action Bar -->
        <div class="sticky bottom-4 z-30 bg-white/95 backdrop-blur-md border border-slate-200 rounded-2xl p-4 shadow-xl flex items-center justify-between gap-4">
            <div class="text-xs text-slate-500 hidden sm:block">
                Each slide's title, subtitle, badge, and CTA buttons publish immediately to the live slider.
            </div>
            <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
                <a href="{{ route('admin.cms.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Back to CMS Hub
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-rose-600/25 transition-all flex items-center space-x-2 cursor-pointer">
                    <svg class="w-4 h-4 fill-none stroke-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save &amp; Publish Hero Changes</span>
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
