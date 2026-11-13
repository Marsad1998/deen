@extends('layouts.admin')

@section('title', 'New Product')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="productCreateForm()">

    <!-- Header & Back Navigation -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.products.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-800 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Create Product</h1>
                <p class="text-xs text-slate-500 mt-0.5">Define core product details, pricing, inventory, and specifications.</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.products.store') }}" class="space-y-6">
        @csrf

        <!-- Core Details Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center space-x-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>General Information</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Product Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" x-model="name" @input="updateSlug()" required
                           value="{{ old('name') }}"
                           placeholder="e.g. Anker 737 Power Bank (PowerCore 24K)"
                           class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-xs font-semibold text-slate-700 mb-1">URL Slug <span class="text-red-500">*</span></label>
                    <input type="text" id="slug" name="slug" x-model="slug" required
                           value="{{ old('slug') }}"
                           placeholder="anker-737-power-bank"
                           class="w-full px-3.5 py-2 text-xs font-mono rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>

                <!-- Master SKU -->
                <div>
                    <label for="sku" class="block text-xs font-semibold text-slate-700 mb-1">Master Product SKU</label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku') }}"
                           placeholder="ANK-737-24K"
                           class="w-full px-3.5 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-xs font-semibold text-slate-700 mb-1">Category</label>
                    <select id="category_id" name="category_id" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <option value="">Select Category...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Brand -->
                <div>
                    <label for="brand_id" class="block text-xs font-semibold text-slate-700 mb-1">Brand</label>
                    <select id="brand_id" name="brand_id" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <option value="">Select Brand (Optional)...</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Badge Text -->
                <div>
                    <label for="badge_text" class="block text-xs font-semibold text-slate-700 mb-1">Badge Text</label>
                    <input type="text" id="badge_text" name="badge_text" value="{{ old('badge_text') }}"
                           placeholder="e.g. Best Seller, New Arrival, 140W Fast Charging"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>

                <!-- Warranty Text -->
                <div>
                    <label for="warranty_text" class="block text-xs font-semibold text-slate-700 mb-1">Warranty Details</label>
                    <input type="text" id="warranty_text" name="warranty_text" value="{{ old('warranty_text') }}"
                           placeholder="e.g. 18 Months Official Warranty"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>

                <!-- Short Description -->
                <div class="md:col-span-2">
                    <label for="short_description" class="block text-xs font-semibold text-slate-700 mb-1">Short Summary</label>
                    <textarea id="short_description" name="short_description" rows="2"
                              placeholder="Brief highlight of power, ports, and key capabilities..."
                              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">{{ old('short_description') }}</textarea>
                </div>

                <!-- Full Description -->
                <div class="md:col-span-2">
                    <label for="full_description" class="block text-xs font-semibold text-slate-700 mb-1">Detailed Description</label>
                    <textarea id="full_description" name="full_description" rows="5"
                              placeholder="Full technical description, package contents, safety certifications..."
                              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">{{ old('full_description') }}</textarea>
                </div>

                <!-- Status & Featured -->
                <div class="flex items-center space-x-6 md:col-span-2 pt-2">
                    <div>
                        <label for="status" class="block text-xs font-semibold text-slate-700 mb-1">Publication Status <span class="text-red-500">*</span></label>
                        <select id="status" name="status" class="px-3 py-1.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active (Sellable Online)</option>
                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Hidden from Storefront)</option>
                            <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2 pt-5">
                        <input id="is_featured" name="is_featured" type="checkbox" value="1" {{ old('is_featured') ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_featured" class="text-xs font-semibold text-slate-700 cursor-pointer">Featured Product</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Initial Pricing & Inventory (Default Variant) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Pricing & Stock (Default Variant)</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Selling Price -->
                <div>
                    <label for="price" class="block text-xs font-semibold text-slate-700 mb-1">Selling Price (PKR) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs font-bold text-slate-400">Rs</span>
                        <input type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price', '0.00') }}" required
                               class="w-full pl-9 pr-3 py-2 text-sm font-semibold rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                </div>

                <!-- Compare-At / Original Price -->
                <div>
                    <label for="original_price" class="block text-xs font-semibold text-slate-700 mb-1">Original Price (PKR)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs font-bold text-slate-400">Rs</span>
                        <input type="number" step="0.01" min="0" id="original_price" name="original_price" value="{{ old('original_price') }}"
                               placeholder="Optional strike-through price"
                               class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                </div>

                <!-- Initial Stock Quantity -->
                <div>
                    <label for="stock_quantity" class="block text-xs font-semibold text-slate-700 mb-1">Initial Stock Quantity <span class="text-red-500">*</span></label>
                    <input type="number" min="0" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', '10') }}" required
                           class="w-full px-3.5 py-2 text-sm font-semibold rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>
            </div>
            <p class="text-[11px] text-slate-400 italic">Every sellable product initializes with a default variant. Additional variants (colors, capacities) can be added after saving.</p>
        </div>

        <!-- Dynamic Specifications (Flexible Key-Value) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Product Specifications</span>
                </h2>
                <button type="button" @click="addSpec()" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                    + Add Attribute
                </button>
            </div>

            <div class="space-y-2">
                <template x-for="(spec, index) in specifications" :key="index">
                    <div class="grid grid-cols-1 sm:grid-cols-7 gap-2 items-center bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <div class="sm:col-span-2">
                            <input type="text" :name="`specifications[${index}][spec_group]`" x-model="spec.spec_group"
                                   placeholder="Group (e.g. Battery, Ports)"
                                   class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 bg-white focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="sm:col-span-2">
                            <input type="text" :name="`specifications[${index}][spec_key]`" x-model="spec.spec_key"
                                   placeholder="Name (e.g. Charging Power)"
                                   class="w-full px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 bg-white focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="sm:col-span-2">
                            <input type="text" :name="`specifications[${index}][spec_value]`" x-model="spec.spec_value"
                                   placeholder="Value (e.g. 140W Max)"
                                   class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 bg-white focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="text-right sm:col-span-1">
                            <button type="button" @click="removeSpec(index)" class="p-1 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Search Engine Optimization (SEO) Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <h2 class="text-sm font-bold text-slate-900">Search Engine Optimization (SEO)</h2>
                </div>
                <span class="text-[11px] text-slate-400">Google &amp; Social Media Ready</span>
            </div>

            <!-- SERP Preview Box -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                <div class="text-[11px] text-slate-500 font-mono truncate">
                    https://deenelectronics.com &rsaquo; product &rsaquo; <span x-text="slug || 'product-slug'"></span>
                </div>
                <div class="text-sm font-semibold text-blue-700 hover:underline truncate"
                     x-text="metaTitle || (name ? name + ' — DEEN Electronics Lahore' : 'Product Title — DEEN Electronics Lahore')"></div>
                <div class="text-xs text-slate-600 line-clamp-2 leading-relaxed"
                     x-text="metaDescription || 'Detailed product specifications, official warranty, and cash on delivery across Pakistan from DEEN Electronics.'"></div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <!-- Meta Title -->
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label for="meta_title" class="block text-xs font-semibold text-slate-700">Meta Title Tag</label>
                        <span class="text-[10px] text-slate-400" :class="metaTitle.length > 60 ? 'text-amber-600 font-bold' : ''">
                            <span x-text="metaTitle.length"></span>/60 characters
                        </span>
                    </div>
                    <input type="text" id="meta_title" name="meta_title" x-model="metaTitle"
                           placeholder="Optional custom search engine title tag"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-[10px] text-slate-400 mt-1">If empty, defaults automatically to Product Name — DEEN Electronics Lahore.</p>
                </div>

                <!-- Meta Description -->
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label for="meta_description" class="block text-xs font-semibold text-slate-700">Meta Description</label>
                        <span class="text-[10px] text-slate-400" :class="metaDescription.length > 160 ? 'text-amber-600 font-bold' : ''">
                            <span x-text="metaDescription.length"></span>/160 characters
                        </span>
                    </div>
                    <textarea id="meta_description" name="meta_description" rows="2" x-model="metaDescription"
                              placeholder="Brief, persuasive summary for search results and social shares"
                              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <!-- Meta Keywords -->
                <div>
                    <label for="meta_keywords" class="block text-xs font-semibold text-slate-700 mb-1">Meta Keywords / Search Tags</label>
                    <input type="text" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}"
                           placeholder="e.g. inverter ac, split ac 1.5 ton, haier pakistan"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Form Submission Footer -->
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200">
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-500 text-white shadow-sm transition-all focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Save & Continue to Media
            </button>
        </div>
    </form>
</div>

<script>
function productCreateForm() {
    return {
        name: '{{ old('name', '') }}',
        slug: '{{ old('slug', '') }}',
        metaTitle: '{{ old('meta_title', '') }}',
        metaDescription: '{{ old('meta_description', '') }}',
        specifications: [
            { spec_group: 'Performance', spec_key: 'Output Power', spec_value: '' },
            { spec_group: 'Battery', spec_key: 'Capacity', spec_value: '' }
        ],
        updateSlug() {
            if (!this.slug || this.slug === this.slugify(this.name.slice(0, -1))) {
                this.slug = this.slugify(this.name);
            }
        },
        slugify(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
        },
        addSpec() {
            this.specifications.push({ spec_group: '', spec_key: '', spec_value: '' });
        },
        removeSpec(index) {
            this.specifications.splice(index, 1);
        }
    };
}
</script>
@endsection
