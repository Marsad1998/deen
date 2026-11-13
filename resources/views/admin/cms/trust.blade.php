@extends('layouts.admin')

@section('title', 'Manage Trust & Value Highlights — DEEN Electronics CMS')

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
            <div class="flex items-center space-x-2 text-xs font-bold text-emerald-600 mb-1">
                <span>Value Props</span>
                <span>&bull;</span>
                <span>Customer Confidence</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Showroom Trust &amp; Value Highlights</h1>
            <p class="text-xs text-slate-500 mt-1">
                Manage the 4 trust cards displayed directly beneath the hero slider assuring buyers on Showroom Pickup, Nationwide Delivery, Manufacturer Warranty, and Flexible Payment.
            </p>
        </div>
    </div>

    <!-- Management Form -->
    <form method="POST" action="{{ route('admin.cms.trust.update') }}" class="space-y-6">
        @csrf

        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-xs space-y-6">
            <div>
                <label for="section_title" class="block text-xs font-bold text-slate-800 mb-1">
                    Internal Section Title
                </label>
                <input type="text" id="section_title" name="section_title" value="{{ old('section_title', $trustConfig['section_title']) }}" required
                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                @php
                    $badges = [
                        1 => ['name' => 'Card 1: Physical Showroom', 'icon' => 'Storefront / Location'],
                        2 => ['name' => 'Card 2: Nationwide Delivery', 'icon' => 'Logistics / Courier'],
                        3 => ['name' => 'Card 3: Manufacturer Warranty', 'icon' => 'Authorized Dealer'],
                        4 => ['name' => 'Card 4: Payment Options', 'icon' => 'COD & Digital IBFT'],
                    ];
                @endphp

                @foreach($trustConfig['items'] as $num => $item)
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800">{{ $badges[$num]['name'] }}</span>
                            <span class="text-[10px] text-slate-400 font-semibold">{{ $badges[$num]['icon'] }}</span>
                        </div>

                        <div>
                            <label for="trust_{{ $num }}_title" class="block text-xs font-bold text-slate-700 mb-1">
                                Card Title <span class="text-rose-600">*</span>
                            </label>
                            <input type="text" 
                                   id="trust_{{ $num }}_title" 
                                   name="trust_{{ $num }}_title" 
                                   value="{{ old("trust_{$num}_title", $item['title']) }}" required
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white font-bold text-slate-900 focus:outline-none focus:border-emerald-600">
                        </div>

                        <div>
                            <label for="trust_{{ $num }}_description" class="block text-xs font-bold text-slate-700 mb-1">
                                Card Description Text <span class="text-rose-600">*</span>
                            </label>
                            <textarea id="trust_{{ $num }}_description" 
                                      name="trust_{{ $num }}_description" 
                                      rows="3" required
                                      class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-emerald-600">{{ old("trust_{$num}_description", $item['description']) }}</textarea>
                        </div>
                    </div>
                @endforeach
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
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs sm:text-sm shadow-md transition-all flex items-center space-x-2 cursor-pointer">
                    <svg class="w-4 h-4 fill-none stroke-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save Trust Highlights</span>
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
