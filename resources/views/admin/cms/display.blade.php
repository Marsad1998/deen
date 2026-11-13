@extends('layouts.admin')

@section('title', 'Categories & Brands Display - Storefront CMS')

@section('content')
<div class="space-y-6">
    <!-- Header & Subnav -->
    @include('admin.cms._nav')

    @if(session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center space-x-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.cms.display.update') }}" method="POST" class="space-y-8">
        @csrf

        <!-- 1. Desktop Navbar Categories Manager -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200">
                            Header Navigation
                        </span>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900">Desktop Navbar Categories</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Select which categories appear on the top navigation bar. For optimal desktop layout, we recommend <strong>6 to 8 primary categories</strong> so menu items don't overflow or wrap awkwardly.
                    </p>
                </div>
                <div class="text-xs font-bold text-slate-600 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200 self-start sm:self-auto">
                    Total Categories: {{ $categories->count() }}
                </div>
            </div>

            <!-- Categories Checkboxes Grid for Navbar -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($categories as $category)
                    <label class="relative flex items-center p-3 rounded-2xl border transition-all cursor-pointer {{ $category->show_in_nav ? 'bg-indigo-50/50 border-indigo-300 ring-1 ring-indigo-200' : 'bg-slate-50/50 border-slate-200 hover:border-slate-300' }}">
                        <input type="checkbox" 
                               name="nav_category_ids[]" 
                               value="{{ $category->id }}" 
                               {{ $category->show_in_nav ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                        <div class="ml-3 flex-1 min-w-0">
                            <span class="text-xs font-bold text-slate-900 block truncate">{{ $category->name }}</span>
                            <span class="text-[10px] text-slate-400 block truncate">/{{ $category->slug }}</span>
                        </div>
                        @if($category->show_in_nav)
                            <span class="ml-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-indigo-100 text-indigo-700 flex-shrink-0">
                                In Nav
                            </span>
                        @endif
                    </label>
                @endforeach
            </div>
        </div>

        <!-- 2. Homepage Categories Section Configuration -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200">
                            Storefront Section 3
                        </span>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900">Homepage Categories Grid</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Control title, subtitle, enable toggle, and curate which category cards are featured on the homepage.
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="categories_enabled" value="0">
                        <input type="checkbox" name="categories_enabled" value="1" 
                               {{ ($displayConfig['categories_enabled'] ?? true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                        <span class="ml-2 text-xs font-bold text-slate-700">Section Enabled</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Section Title</label>
                    <input type="text" name="categories_title" 
                           value="{{ old('categories_title', $displayConfig['categories_title'] ?? 'Shop by Category') }}"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500"
                           placeholder="Shop by Category">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Section Subtitle</label>
                    <input type="text" name="categories_subtitle" 
                           value="{{ old('categories_subtitle', $displayConfig['categories_subtitle'] ?? 'Confirmed catalogue categories across home appliances, cooling, TVs, and personal transit.') }}"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500"
                           placeholder="Subtitle text">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Curate Categories on Homepage</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($categories as $category)
                        <label class="relative flex items-center p-3 rounded-2xl border transition-all cursor-pointer {{ $category->show_on_home ? 'bg-rose-50/50 border-rose-300 ring-1 ring-rose-200' : 'bg-slate-50/50 border-slate-200 hover:border-slate-300' }}">
                            <input type="checkbox" 
                                   name="home_category_ids[]" 
                                   value="{{ $category->id }}" 
                                   {{ $category->show_on_home ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-rose-600 border-slate-300 focus:ring-rose-500">
                            <div class="ml-3 flex-1 min-w-0">
                                <span class="text-xs font-bold text-slate-900 block truncate">{{ $category->name }}</span>
                                <span class="text-[10px] text-slate-400 block truncate">{{ $category->products_count ?? 0 }} products</span>
                            </div>
                            @if($category->show_on_home)
                                <span class="ml-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-rose-100 text-rose-700 flex-shrink-0">
                                    Featured
                                </span>
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 3. Homepage Brands Showcase Configuration -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Storefront Section 3.1
                        </span>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900">Official Brand Partners Showcase</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Highlight authorized manufacturer brands (Haier, Dawlance, Gree, TCL, Super Asia, etc.) to establish trust and direct customers to brand-specific catalogues.
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="brands_enabled" value="0">
                        <input type="checkbox" name="brands_enabled" value="1" 
                               {{ ($displayConfig['brands_enabled'] ?? true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        <span class="ml-2 text-xs font-bold text-slate-700">Section Enabled</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Section Title</label>
                    <input type="text" name="brands_title" 
                           value="{{ old('brands_title', $displayConfig['brands_title'] ?? 'Authorized Brand Partners') }}"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                           placeholder="Authorized Brand Partners">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Section Subtitle</label>
                    <input type="text" name="brands_subtitle" 
                           value="{{ old('brands_subtitle', $displayConfig['brands_subtitle'] ?? 'Direct distributor warranties and official service support across Pakistan\'s top electronics manufacturers.') }}"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                           placeholder="Subtitle text">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Curate Brands on Homepage</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($brands as $brand)
                        <label class="relative flex items-center p-3 rounded-2xl border transition-all cursor-pointer {{ $brand->show_on_home ? 'bg-emerald-50/50 border-emerald-300 ring-1 ring-emerald-200' : 'bg-slate-50/50 border-slate-200 hover:border-slate-300' }}">
                            <input type="checkbox" 
                                   name="home_brand_ids[]" 
                                   value="{{ $brand->id }}" 
                                   {{ $brand->show_on_home ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500">
                            <div class="ml-3 flex-1 min-w-0">
                                <span class="text-xs font-bold text-slate-900 block truncate">{{ $brand->name }}</span>
                                <span class="text-[10px] text-slate-400 block truncate">{{ $brand->products_count ?? 0 }} products</span>
                            </div>
                            @if($brand->show_on_home)
                                <span class="ml-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-emerald-100 text-emerald-700 flex-shrink-0">
                                    Showcase
                                </span>
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-900 block">Save Storefront Display Layout</span>
                <span class="text-[11px] text-slate-500">Applies immediately to the public header navigation and homepage.</span>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save Display Settings</span>
            </button>
        </div>
    </form>
</div>
@endsection
