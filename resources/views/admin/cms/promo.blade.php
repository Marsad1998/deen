@extends('layouts.admin')

@section('title', 'Manage Promotional Banner — DEEN Electronics CMS')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="{
    isEnabled: {{ $promoConfig['is_enabled'] ? 'true' : 'false' }},
    badgeText: '{{ addslashes($promoConfig['badge_text']) }}',
    headline: '{{ addslashes($promoConfig['headline']) }}',
    description: `{{ addslashes($promoConfig['description']) }}`,
    buttonText: '{{ addslashes($promoConfig['button_text']) }}',
    buttonUrl: '{{ addslashes($promoConfig['button_url']) }}',
    secondaryButtonText: '{{ addslashes($promoConfig['secondary_button_text']) }}',
    secondaryButtonUrl: '{{ addslashes($promoConfig['secondary_button_url']) }}'
}">

    @include('admin.cms._nav')

    @if(session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center space-x-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Live Preview Banner Box -->
    <div class="rounded-3xl border border-slate-800 bg-slate-900 overflow-hidden shadow-xl text-white relative">
        <div class="relative min-h-[200px] flex items-center p-6 sm:p-10">
            <!-- Background Image -->
            <img src="{{ $promoConfig['image_url'] }}" alt="Promo Background" class="absolute inset-0 w-full h-full object-cover opacity-50 select-none pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-950/70 to-transparent"></div>

            <div class="relative z-10 max-w-xl space-y-3">
                <span class="inline-block px-3 py-1 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider bg-rose-600 text-white shadow-xs"
                      x-text="badgeText || 'Showroom & Online Specials'">
                    {{ $promoConfig['badge_text'] }}
                </span>
                <h3 class="text-lg sm:text-2xl font-black text-white tracking-tight leading-tight"
                    x-text="headline || 'Beat the Heat with Inverter Air Conditioners'">
                    {{ $promoConfig['headline'] }}
                </h3>
                <p class="text-xs sm:text-sm text-slate-200 line-clamp-2 max-w-lg"
                   x-text="description">
                    {{ $promoConfig['description'] }}
                </p>
                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <span class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 shadow-xs"
                          x-text="buttonText || 'Explore Cooling'">
                        {{ $promoConfig['button_text'] }}
                    </span>
                    <span class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 shadow-xs flex items-center space-x-1.5"
                          x-text="secondaryButtonText || 'WhatsApp Specials Desk'">
                        {{ $promoConfig['secondary_button_text'] }}
                    </span>
                </div>
            </div>

            <div class="absolute top-4 right-4 px-3 py-1 rounded-full bg-black/60 border border-white/20 text-white text-[10px] font-mono">
                Live Storefront Mockup
            </div>
        </div>
    </div>

    <!-- Management Form -->
    <form method="POST" action="{{ route('admin.cms.promo.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-xs space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Promotional Banner Content &amp; Buttons</h2>
                    <p class="text-[11px] text-slate-500">Positioned between Categories and Featured Deals on the homepage</p>
                </div>
                <!-- Enable/Disable Switch -->
                <label class="flex items-center space-x-2 cursor-pointer bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl">
                    <input type="checkbox" name="is_enabled" value="1" x-model="isEnabled" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                    <span class="text-xs font-bold text-slate-800" x-text="isEnabled ? 'Banner Enabled' : 'Banner Hidden'"></span>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Badge Text -->
                <div>
                    <label for="badge_text" class="block text-xs font-bold text-slate-800 mb-1">
                        Pill Badge Text <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="badge_text" name="badge_text" x-model="badgeText" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none">
                </div>

                <!-- Headline -->
                <div>
                    <label for="headline" class="block text-xs font-bold text-slate-800 mb-1">
                        Banner Headline <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="headline" name="headline" x-model="headline" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none font-bold">
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label for="description" class="block text-xs font-bold text-slate-800 mb-1">
                        Description / Campaign Copy <span class="text-rose-600">*</span>
                    </label>
                    <textarea id="description" name="description" rows="3" x-model="description" required
                              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none"></textarea>
                </div>

                <!-- Primary Button -->
                <div>
                    <label for="button_text" class="block text-xs font-bold text-slate-800 mb-1">
                        Primary CTA Button Text <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="button_text" name="button_text" x-model="buttonText" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none">
                </div>
                <div>
                    <label for="button_url" class="block text-xs font-bold text-slate-800 mb-1">
                        Primary Button URL / Path <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="button_url" name="button_url" x-model="buttonUrl" required
                           class="w-full px-3.5 py-2.5 text-xs font-mono rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none">
                </div>

                <!-- Secondary Button -->
                <div>
                    <label for="secondary_button_text" class="block text-xs font-bold text-slate-800 mb-1">
                        Secondary WhatsApp Button Text
                    </label>
                    <input type="text" id="secondary_button_text" name="secondary_button_text" x-model="secondaryButtonText"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label for="secondary_button_url" class="block text-xs font-bold text-slate-800 mb-1">
                        Secondary WhatsApp URL
                    </label>
                    <input type="text" id="secondary_button_url" name="secondary_button_url" x-model="secondaryButtonUrl"
                           class="w-full px-3.5 py-2.5 text-xs font-mono rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Background Image Configuration -->
                <div class="sm:col-span-2 pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="promo_image_preset" class="block text-xs font-bold text-slate-800 mb-1">
                            Choose From Library Presets
                        </label>
                        <select id="promo_image_preset" name="promo_image_preset"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 bg-white">
                            @foreach($presetImages as $path => $label)
                                <option value="{{ $path }}" {{ ($promoConfig['image'] === $path) ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="promo_image_file" class="block text-xs font-bold text-slate-800 mb-1">
                            Or Upload Custom Banner Photograph
                        </label>
                        <input type="file" id="promo_image_file" name="promo_image_file" accept="image/png,image/jpeg,image/webp"
                               class="w-full text-xs text-slate-500 file:mr-2.5 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300 cursor-pointer">
                        <span class="text-[10px] text-slate-400 mt-1 block">Recommended: 1600&times;600px, WebP or JPEG</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Sticky Save Action Bar -->
        <div class="sticky bottom-4 z-30 bg-white/95 backdrop-blur-md border border-slate-200 rounded-2xl p-4 shadow-xl flex items-center justify-between gap-4">
            <div class="text-xs text-slate-500 hidden sm:block">
                Updates appear immediately on the storefront homepage.
            </div>
            <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
                <a href="{{ route('admin.cms.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Back to CMS Hub
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm shadow-md transition-all flex items-center space-x-2 cursor-pointer">
                    <svg class="w-4 h-4 fill-none stroke-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save Promotional Banner</span>
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
