@extends('layouts.admin')

@section('title', 'Manage Showroom About & Timings — DEEN Electronics CMS')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    @include('admin.cms._nav')

    @if(session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center space-x-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Top Info Card -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-blue-600 mb-1">
                <span>Showroom Identity</span>
                <span>&bull;</span>
                <span>Location &amp; Operating Hours</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Showroom About, Location &amp; Timings</h1>
            <p class="text-xs text-slate-500 mt-1">
                Manage the physical retail showroom information card, daily timings, sales lead contact, and official business details shown on the homepage.
            </p>
        </div>
    </div>

    <!-- Management Form -->
    <form method="POST" action="{{ route('admin.cms.about.update') }}" class="space-y-6">
        @csrf

        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Homepage Showroom Showcase Card</h2>
                <p class="text-[11px] text-slate-500">Provides physical retail transparency and customer visit directions</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Badge Text -->
                <div>
                    <label for="badge_text" class="block text-xs font-bold text-slate-800 mb-1">
                        Pill Badge Text <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="badge_text" name="badge_text" value="{{ old('badge_text', $aboutConfig['badge_text']) }}" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none font-bold text-rose-600">
                </div>

                <!-- Headline -->
                <div>
                    <label for="headline" class="block text-xs font-bold text-slate-800 mb-1">
                        Section Headline <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="headline" name="headline" value="{{ old('headline', $aboutConfig['headline']) }}" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none font-bold text-slate-900">
                </div>

                <!-- Narrative -->
                <div class="sm:col-span-2">
                    <label for="narrative" class="block text-xs font-bold text-slate-800 mb-1">
                        Showroom Story &amp; Narrative <span class="text-rose-600">*</span>
                    </label>
                    <textarea id="narrative" name="narrative" rows="4" required
                              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none leading-relaxed text-slate-700">{{ old('narrative', $aboutConfig['narrative']) }}</textarea>
                </div>

                <!-- Daily Hours -->
                <div>
                    <label for="daily_hours" class="block text-xs font-bold text-slate-800 mb-1">
                        Daily Opening Hours <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="daily_hours" name="daily_hours" value="{{ old('daily_hours', $aboutConfig['daily_hours']) }}" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[10px] text-slate-400 mt-1">e.g. Daily 10:00 AM – 10:00 PM</p>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-800 mb-1">
                        Showroom Phone / WhatsApp <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $aboutConfig['phone']) }}" required
                           class="w-full px-3.5 py-2.5 text-xs font-mono rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                </div>

                <!-- Business Owner / Lead Rep -->
                <div>
                    <label for="business_owner" class="block text-xs font-bold text-slate-800 mb-1">
                        Showroom Representative / Business Owner <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="business_owner" name="business_owner" value="{{ old('business_owner', $aboutConfig['business_owner']) }}" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none font-bold">
                    <p class="text-[10px] text-slate-400 mt-1">e.g. Muhammad Aslam</p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-800 mb-1">
                        Official Showroom Email <span class="text-rose-600">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $aboutConfig['email']) }}" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                </div>

                <!-- Address -->
                <div class="sm:col-span-2">
                    <label for="address" class="block text-xs font-bold text-slate-800 mb-1">
                        Physical Showroom Address <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="address" name="address" value="{{ old('address', $aboutConfig['address']) }}" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                </div>

                <!-- WhatsApp Link -->
                <div class="sm:col-span-2">
                    <label for="whatsapp_url" class="block text-xs font-bold text-slate-800 mb-1">
                        Get Directions &amp; Enquire WhatsApp Target URL
                    </label>
                    <input type="text" id="whatsapp_url" name="whatsapp_url" value="{{ old('whatsapp_url', $aboutConfig['whatsapp_url']) }}"
                           class="w-full px-3.5 py-2.5 text-xs font-mono rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
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
                    <span>Save Showroom Details</span>
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
