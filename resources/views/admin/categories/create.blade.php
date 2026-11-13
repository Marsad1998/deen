@extends('layouts.admin')

@section('title', 'Add New Category')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-rose-600 mb-1">
                <a href="{{ route('admin.categories.index') }}" class="hover:underline">Categories</a>
                <span>&bull;</span>
                <span>Create</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Add New Category</h1>
            <p class="text-xs text-slate-500 mt-1">Configure name, slug, image, visual badge, and storefront hierarchy.</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
            Back to List
        </a>
    </div>

    <!-- Create Form -->
    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-xs space-y-5">
            <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-400 pb-2 border-b border-slate-100">
                Category Identity
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Name -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1">
                        Category Name <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required
                           placeholder="e.g. Smart LED TVs"
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
                           placeholder="e.g. smart-led-tvs"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm font-mono focus:outline-none focus:border-rose-600">
                </div>

                <!-- Parent Category -->
                <div>
                    <label for="parent_id" class="block text-xs font-bold text-slate-700 mb-1">
                        Parent Category <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <select id="parent_id" 
                            name="parent_id" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-rose-600">
                        <option value="">None (Top-Level Category)</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Badge Text -->
                <div>
                    <label for="badge_text" class="block text-xs font-bold text-slate-700 mb-1">
                        Visual Badge / Tag <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <input type="text" 
                           id="badge_text" 
                           name="badge_text" 
                           value="{{ old('badge_text') }}" 
                           placeholder="e.g. Inverter Cooling or Best Seller"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-rose-600">
                </div>

                <!-- Models Count Text -->
                <div>
                    <label for="models_count_text" class="block text-xs font-bold text-slate-700 mb-1">
                        Models Count Note <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <input type="text" 
                           id="models_count_text" 
                           name="models_count_text" 
                           value="{{ old('models_count_text') }}" 
                           placeholder="e.g. 20+ Models Available"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-rose-600">
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label for="description" class="block text-xs font-bold text-slate-700 mb-1">
                        Description <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-rose-600">{{ old('description') }}</textarea>
                </div>

                <!-- Image Upload -->
                <div>
                    <label for="image" class="block text-xs font-bold text-slate-700 mb-1">
                        Category Image <span class="text-slate-400 font-normal">(Upload WebP/JPEG/PNG)</span>
                    </label>
                    <input type="file" 
                           id="image" 
                           name="image" 
                           accept="image/png,image/jpeg,image/webp,image/svg+xml"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300">
                </div>

                <!-- Or Image Path -->
                <div>
                    <label for="image_url" class="block text-xs font-bold text-slate-700 mb-1">
                        Or Existing Asset Path
                    </label>
                    <input type="text" 
                           id="image_url" 
                           name="image_url" 
                           value="{{ old('image_url') }}" 
                           placeholder="images/categories/cat_led-tvs.webp"
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
                        <span class="text-xs font-bold text-slate-800">Publish immediately on storefront</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('admin.categories.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-rose-600/25 transition-all">
                Save Category
            </button>
        </div>

    </form>

</div>
@endsection
