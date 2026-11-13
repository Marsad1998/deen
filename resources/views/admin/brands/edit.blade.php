@extends('layouts.admin')

@section('title', 'Edit Brand — ' . $brand->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-rose-600 mb-1">
                <a href="{{ route('admin.brands.index') }}" class="hover:underline">Brands</a>
                <span>&bull;</span>
                <span>Edit</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Brand: {{ $brand->name }}</h1>
            <p class="text-xs text-slate-500 mt-1">Updates immediately reflect in the webfront brand filter and product details.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('storefront.catalog', ['brand' => $brand->slug]) }}" 
               target="_blank"
               class="px-3.5 py-2 rounded-xl text-xs font-bold border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors flex items-center space-x-1.5">
                <span>View Webfront</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
            <a href="{{ route('admin.brands.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
                Back
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-xs space-y-5">
            <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-400 pb-2 border-b border-slate-100">
                Brand Details
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1">
                        Brand Name <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $brand->name) }}" 
                           required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600">
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-xs font-bold text-slate-700 mb-1">
                        URL Slug <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" 
                           id="slug" 
                           name="slug" 
                           value="{{ old('slug', $brand->slug) }}" 
                           required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm font-mono focus:outline-none focus:border-rose-600">
                </div>

                <!-- Website -->
                <div class="sm:col-span-2">
                    <label for="website" class="block text-xs font-bold text-slate-700 mb-1">
                        Official Website URL <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <input type="url" 
                           id="website" 
                           name="website" 
                           value="{{ old('website', $brand->website) }}" 
                           placeholder="https://www.haier.com"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-rose-600">
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label for="description" class="block text-xs font-bold text-slate-700 mb-1">
                        Description / Brand Profile <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-rose-600">{{ old('description', $brand->description) }}</textarea>
                </div>

                <!-- Logo Upload & Preview -->
                <div class="sm:col-span-2 p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-center gap-4">
                    @if($brand->logo_url)
                        <img src="{{ asset($brand->logo_url) }}" 
                             alt="{{ $brand->name }}" 
                             class="w-16 h-16 rounded-xl object-contain bg-white border border-slate-200 p-1 flex-shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-700 font-black text-xl flex-shrink-0">
                            {{ substr($brand->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="flex-1 w-full space-y-2">
                        <label for="logo" class="block text-xs font-bold text-slate-700">
                            Change Brand Logo
                        </label>
                        <input type="file" 
                               id="logo" 
                               name="logo" 
                               accept="image/png,image/jpeg,image/webp,image/svg+xml"
                               class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300">
                        <input type="text" 
                               id="logo_url" 
                               name="logo_url" 
                               value="{{ old('logo_url', $brand->logo_url) }}" 
                               placeholder="Or image path..."
                               class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 bg-white">
                    </div>
                </div>

                <!-- Sort Order -->
                <div>
                    <label for="sort_order" class="block text-xs font-bold text-slate-700 mb-1">
                        Sort Order <span class="text-slate-400 font-normal">(Lower numbers display first)</span>
                    </label>
                    <input type="number" 
                           id="sort_order" 
                           name="sort_order" 
                           value="{{ old('sort_order', $brand->sort_order) }}" 
                           min="0"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-rose-600">
                </div>

                <!-- Active Status & Homepage Showcase -->
                <div class="sm:col-span-2 pt-4 border-t border-slate-100 space-y-3">
                    <span class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Storefront Placements &amp; Visibility</span>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-center p-3 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition-colors">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1" 
                                   {{ old('is_active', $brand->is_active) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                            <div class="ml-2.5">
                                <span class="text-xs font-bold text-slate-800 block">Catalogue Filter Active</span>
                                <span class="text-[10px] text-slate-400 block">Available in store search and filters</span>
                            </div>
                        </label>

                        <label class="flex items-center p-3 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition-colors">
                            <input type="checkbox" 
                                   name="show_on_home" 
                                   value="1" 
                                   {{ old('show_on_home', $brand->show_on_home) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <div class="ml-2.5">
                                <span class="text-xs font-bold text-slate-800 block">Homepage Brand Partner</span>
                                <span class="text-[10px] text-slate-400 block">Show in Authorized Partners showcase</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('admin.brands.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-rose-600/25 transition-all">
                Update Brand
            </button>
        </div>

    </form>

</div>
@endsection
