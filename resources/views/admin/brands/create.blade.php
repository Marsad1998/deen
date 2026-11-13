@extends('layouts.admin')

@section('title', 'Add New Brand')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-rose-600 mb-1">
                <a href="{{ route('admin.brands.index') }}" class="hover:underline">Brands</a>
                <span>&bull;</span>
                <span>Create</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Add New Brand</h1>
            <p class="text-xs text-slate-500 mt-1">Configure brand name, slug, official website, and brand logo.</p>
        </div>
        <a href="{{ route('admin.brands.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
            Back to List
        </a>
    </div>

    <!-- Create Form -->
    <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

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
                           value="{{ old('name') }}" 
                           required
                           placeholder="e.g. Haier, Dawlance, PEL"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600">
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-xs font-bold text-slate-700 mb-1">
                        URL Slug <span class="text-slate-400 font-normal">(Auto-generated if empty)</span>
                    </label>
                    <input type="text" 
                           id="slug" 
                           name="slug" 
                           value="{{ old('slug') }}" 
                           placeholder="e.g. haier"
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
                           value="{{ old('website') }}" 
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
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-rose-600">{{ old('description') }}</textarea>
                </div>

                <!-- Logo Upload -->
                <div>
                    <label for="logo" class="block text-xs font-bold text-slate-700 mb-1">
                        Brand Logo <span class="text-slate-400 font-normal">(Upload SVG/PNG/WEBP/JPG)</span>
                    </label>
                    <input type="file" 
                           id="logo" 
                           name="logo" 
                           accept="image/png,image/jpeg,image/webp,image/svg+xml"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300">
                </div>

                <!-- Or Logo Path -->
                <div>
                    <label for="logo_url" class="block text-xs font-bold text-slate-700 mb-1">
                        Or Existing Asset Path
                    </label>
                    <input type="text" 
                           id="logo_url" 
                           name="logo_url" 
                           value="{{ old('logo_url') }}" 
                           placeholder="images/brands/haier.png"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm font-mono focus:outline-none focus:border-rose-600">
                </div>

                <!-- Sort Order -->
                <div>
                    <label for="sort_order" class="block text-xs font-bold text-slate-700 mb-1">
                        Sort Order <span class="text-slate-400 font-normal">(Lower numbers display first)</span>
                    </label>
                    <input type="number" 
                           id="sort_order" 
                           name="sort_order" 
                           value="{{ old('sort_order', 0) }}" 
                           min="0"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-rose-600">
                </div>

                <!-- Active Status -->
                <div class="flex items-center pt-6">
                    <label class="inline-flex items-center space-x-2.5 cursor-pointer">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                               class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        <span class="text-xs font-bold text-slate-800">Show brand in catalogue filters</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('admin.brands.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-rose-600/25 transition-all">
                Save Brand
            </button>
        </div>

    </form>

</div>
@endsection
