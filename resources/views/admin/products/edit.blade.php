@extends('layouts.admin')

@section('title', 'Edit ' . $product->name)

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'details', showAddVariantModal: false, showEditVariantModal: false, editingVariant: {} }">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.products.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-800 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $product->name }}</h1>
                    @if($product->is_archived)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-300">Archived</span>
                    @elseif($product->is_active)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">Active</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-300">Draft</span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 font-mono mt-0.5">Slug: {{ $product->slug }} &bull; ID #{{ $product->id }}</p>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            @if(Auth::guard('admin')->user()->hasPermission('products.delete'))
                @if($product->is_archived)
                    <form method="POST" action="{{ route('admin.products.unarchive', $product->id) }}" onsubmit="return confirm('Restore product to draft?');">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
                            Restore to Draft
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.products.archive', $product->id) }}" onsubmit="return confirm('Archive product [{{ $product->name }}]?');">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-50 border border-amber-200 text-amber-800 hover:bg-amber-100 transition-colors">
                            Archive Product
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 bg-white rounded-t-2xl px-4 pt-3 flex space-x-2 shadow-sm">
        <button type="button" @click="activeTab = 'details'" 
                :class="activeTab === 'details' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="px-4 py-2 text-xs border-b-2 transition-all">
            Core Details
        </button>

        <button type="button" @click="activeTab = 'variants'" 
                :class="activeTab === 'variants' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="px-4 py-2 text-xs border-b-2 transition-all flex items-center space-x-1.5">
            <span>Variants</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-600">{{ $product->variants->count() }}</span>
        </button>

        <button type="button" @click="activeTab = 'media'" 
                :class="activeTab === 'media' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="px-4 py-2 text-xs border-b-2 transition-all flex items-center space-x-1.5">
            <span>Media Gallery</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-600">{{ $product->media->count() }}</span>
        </button>

        <button type="button" @click="activeTab = 'specs'" 
                :class="activeTab === 'specs' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="px-4 py-2 text-xs border-b-2 transition-all flex items-center space-x-1.5">
            <span>Specifications</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-600">{{ $product->specifications->count() }}</span>
        </button>

        <button type="button" @click="activeTab = 'history'" 
                :class="activeTab === 'history' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="px-4 py-2 text-xs border-b-2 transition-all">
            Price & Stock History
        </button>
    </div>

    <!-- TAB 1: CORE DETAILS -->
    <div x-show="activeTab === 'details'" 
         x-data="{ 
             metaTitle: '{{ addslashes(old('meta_title', $product->meta_title ?? '')) }}', 
             metaDescription: '{{ addslashes(old('meta_description', $product->meta_description ?? '')) }}', 
             prodName: '{{ addslashes($product->name) }}', 
             prodSlug: '{{ addslashes($product->slug) }}' 
         }" 
         class="bg-white rounded-b-2xl border border-slate-200 border-t-0 p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.products.update', $product->id) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
                           class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-xs font-semibold text-slate-700 mb-1">URL Slug <span class="text-red-500">*</span></label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $product->slug) }}" required
                           class="w-full px-3.5 py-2 text-xs font-mono rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>

                <!-- Master SKU -->
                <div>
                    <label for="sku" class="block text-xs font-semibold text-slate-700 mb-1">Master Product SKU</label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}"
                           class="w-full px-3.5 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-xs font-semibold text-slate-700 mb-1">Category</label>
                    <select id="category_id" name="category_id" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500">
                        <option value="">Uncategorized</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Brand -->
                <div>
                    <label for="brand_id" class="block text-xs font-semibold text-slate-700 mb-1">Brand</label>
                    <select id="brand_id" name="brand_id" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500">
                        <option value="">Generic / In-house</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Badge Text -->
                <div>
                    <label for="badge_text" class="block text-xs font-semibold text-slate-700 mb-1">Badge Text</label>
                    <input type="text" id="badge_text" name="badge_text" value="{{ old('badge_text', $product->badge_text) }}"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Warranty Text -->
                <div>
                    <label for="warranty_text" class="block text-xs font-semibold text-slate-700 mb-1">Warranty Details</label>
                    <input type="text" id="warranty_text" name="warranty_text" value="{{ old('warranty_text', $product->warranty_text) }}"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Short Description -->
                <div class="md:col-span-2">
                    <label for="short_description" class="block text-xs font-semibold text-slate-700 mb-1">Short Summary</label>
                    <textarea id="short_description" name="short_description" rows="2"
                              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500">{{ old('short_description', $product->short_description) }}</textarea>
                </div>

                <!-- Full Description -->
                <div class="md:col-span-2">
                    <label for="full_description" class="block text-xs font-semibold text-slate-700 mb-1">Detailed Description</label>
                    <textarea id="full_description" name="full_description" rows="5"
                              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500">{{ old('full_description', $product->full_description) }}</textarea>
                </div>

                <!-- Search Engine Optimization (SEO) & Social Sharing -->
                <div class="md:col-span-2 p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Search Engine Optimization (SEO)</h3>
                        </div>
                        <a href="{{ route('storefront.product', $product->slug) }}" target="_blank" class="text-[11px] text-blue-600 font-semibold hover:underline flex items-center space-x-1">
                            <span>Test Live Product URL</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>

                    <!-- Google SERP Snippet Preview -->
                    <div class="p-3.5 rounded-xl bg-white border border-slate-200 space-y-1 shadow-2xs">
                        <div class="text-[11px] text-slate-500 font-mono truncate">
                            https://deenelectronics.com &rsaquo; product &rsaquo; <span>{{ $product->slug }}</span>
                        </div>
                        <div class="text-sm font-semibold text-blue-700 hover:underline truncate"
                             x-text="metaTitle || (prodName + ' — DEEN Electronics Lahore')"></div>
                        <div class="text-xs text-slate-600 line-clamp-2 leading-relaxed"
                             x-text="metaDescription || 'Detailed product specifications, official warranty, and cash on delivery across Pakistan from DEEN Electronics.'"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <div class="flex justify-between items-center mb-1">
                                <label for="meta_title" class="block text-xs font-semibold text-slate-700">Meta Title Tag</label>
                                <span class="text-[10px] text-slate-400" :class="metaTitle.length > 60 ? 'text-amber-600 font-bold' : ''">
                                    <span x-text="metaTitle.length"></span>/60 characters
                                </span>
                            </div>
                            <input type="text" id="meta_title" name="meta_title" x-model="metaTitle"
                                   placeholder="{{ $product->name }} — DEEN Electronics Lahore"
                                   class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-[10px] text-slate-400 mt-0.5">Leave blank to use default: [Product Name] — DEEN Electronics Lahore.</p>
                        </div>

                        <div class="md:col-span-2">
                            <div class="flex justify-between items-center mb-1">
                                <label for="meta_description" class="block text-xs font-semibold text-slate-700">Meta Description</label>
                                <span class="text-[10px] text-slate-400" :class="metaDescription.length > 160 ? 'text-amber-600 font-bold' : ''">
                                    <span x-text="metaDescription.length"></span>/160 characters
                                </span>
                            </div>
                            <textarea id="meta_description" name="meta_description" rows="2" x-model="metaDescription"
                                      class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="meta_keywords" class="block text-xs font-semibold text-slate-700 mb-1">Meta Keywords / Search Tags</label>
                            <input type="text" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $product->meta_keywords) }}"
                                   placeholder="e.g. inverter, air conditioner, haier, lahore"
                                   class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Publication Status & Featured -->
                <div class="flex items-center space-x-6 md:col-span-2 pt-2 border-t border-slate-100">
                    <div>
                        <label for="status" class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                        <select id="status" name="status" class="px-3 py-1.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500">
                            <option value="active" {{ old('status', $product->is_archived ? 'archived' : ($product->is_active ? 'active' : 'draft')) === 'active' ? 'selected' : '' }}>Active (Sellable Online)</option>
                            <option value="draft" {{ old('status', $product->is_archived ? 'archived' : ($product->is_active ? 'active' : 'draft')) === 'draft' ? 'selected' : '' }}>Draft (Hidden)</option>
                            <option value="archived" {{ old('status', $product->is_archived ? 'archived' : ($product->is_active ? 'active' : 'draft')) === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2 pt-5">
                        <input id="is_featured" name="is_featured" type="checkbox" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_featured" class="text-xs font-semibold text-slate-700 cursor-pointer">Featured on Storefront</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-500 text-white shadow-sm transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 2: PRODUCT VARIANTS -->
    <div x-show="activeTab === 'variants'" class="bg-white rounded-b-2xl border border-slate-200 border-t-0 p-6 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Product Variants</h2>
                <p class="text-xs text-slate-400 mt-0.5">Manage sellable SKUs, options, prices, and default variant assignment.</p>
            </div>
            <button type="button" @click="showAddVariantModal = true"
                    class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-sm transition-all">
                + Add Variant
            </button>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[10px]">
                    <tr>
                        <th class="py-2.5 px-3">Default</th>
                        <th class="py-2.5 px-3">SKU</th>
                        <th class="py-2.5 px-3">Variant Title</th>
                        <th class="py-2.5 px-3">Price (PKR)</th>
                        <th class="py-2.5 px-3">Stock</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($product->variants as $variant)
                        <tr class="hover:bg-slate-50/60 {{ $variant->is_default ? 'bg-blue-50/30' : '' }}">
                            <td class="py-2.5 px-3">
                                @if($variant->is_default)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-600 text-white shadow-xs">
                                        ★ Default
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('admin.products.variants.set-default', [$product->id, $variant->id]) }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-slate-400 hover:text-blue-600 transition-colors">
                                            Set Default
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 font-mono font-medium text-slate-800">{{ $variant->sku }}</td>
                            <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $variant->name }}</td>
                            <td class="py-2.5 px-3">
                                <span class="font-bold">Rs {{ number_format($variant->price, 2) }}</span>
                                @if($variant->original_price && $variant->original_price > $variant->price)
                                    <span class="text-[10px] text-slate-400 line-through ml-1">Rs {{ number_format($variant->original_price, 2) }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="font-bold {{ $variant->stock_quantity > 0 ? 'text-slate-800' : 'text-red-600' }}">{{ $variant->stock_quantity }}</span>
                            </td>
                            <td class="py-2.5 px-3">
                                @if($variant->is_active)
                                    <span class="text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded text-[10px] font-semibold">Active</span>
                                @else
                                    <span class="text-slate-500 bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded text-[10px] font-semibold">Disabled</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                <div class="inline-flex items-center space-x-1">
                                    <button type="button" 
                                            @click="editingVariant = {{ json_encode($variant) }}; showEditVariantModal = true"
                                            class="px-2 py-1 rounded-md text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">
                                        Edit
                                    </button>

                                    @if(! $variant->is_default)
                                        <form method="POST" action="{{ route('admin.products.variants.destroy', [$product->id, $variant->id]) }}" onsubmit="return confirm('Delete variant [{{ $variant->sku }}]?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 rounded-md text-[11px] font-semibold bg-slate-100 hover:bg-red-50 text-red-600">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">No variants defined.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: ADD VARIANT -->
    <div x-show="showAddVariantModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="showAddVariantModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Add New Variant</h3>
                <button type="button" @click="showAddVariantModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.products.variants.store', $product->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Variant Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Midnight Black / 24,000mAh"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">SKU <span class="text-red-500">*</span></label>
                        <input type="text" name="sku" required placeholder="ANK-737-BLK"
                               class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Stock Quantity <span class="text-red-500">*</span></label>
                        <input type="number" min="0" name="stock_quantity" value="10" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Price (PKR) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="price" value="0.00" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Original Price (PKR)</label>
                        <input type="number" step="0.01" min="0" name="original_price"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                    </div>
                </div>

                <div class="flex items-center space-x-2 pt-2">
                    <input id="modal_is_default" name="is_default" type="checkbox" value="1"
                           class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="modal_is_default" class="text-xs font-semibold text-slate-700 cursor-pointer">Set as default sellable variant</label>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showAddVariantModal = false" class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-xl bg-blue-600 text-white">Create Variant</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT VARIANT -->
    <div x-show="showEditVariantModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="showEditVariantModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Edit Variant: <span x-text="editingVariant.sku"></span></h3>
                <button type="button" @click="showEditVariantModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form method="POST" :action="`{{ url('/admin/products/' . $product->id . '/variants') }}/${editingVariant.id}`" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Variant Name</label>
                    <input type="text" name="name" x-model="editingVariant.name" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">SKU</label>
                        <input type="text" name="sku" x-model="editingVariant.sku" required
                               class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Stock Quantity</label>
                        <input type="number" min="0" name="stock_quantity" x-model="editingVariant.stock_quantity" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Price (PKR)</label>
                        <input type="number" step="0.01" min="0" name="price" x-model="editingVariant.price" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Original Price (PKR)</label>
                        <input type="number" step="0.01" min="0" name="original_price" x-model="editingVariant.original_price"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reason for price change (if updating price)</label>
                    <input type="text" name="price_change_reason" placeholder="e.g. Promotional sale, Supplier cost change"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showEditVariantModal = false" class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-xl bg-blue-600 text-white">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 3: MEDIA GALLERY -->
    <div x-show="activeTab === 'media'" class="bg-white rounded-b-2xl border border-slate-200 border-t-0 p-6 shadow-sm space-y-6">
        
        <!-- Upload Form -->
        <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 bg-slate-50/50 hover:bg-slate-50 transition-colors">
            <form method="POST" action="{{ route('admin.products.media.upload', $product->id) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-slate-800 mb-1">Upload Product Image</label>
                        <input type="file" name="file" required accept="image/jpeg,image/png,image/webp"
                               class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
                        <p class="text-[10px] text-slate-400 mt-1">Accepted formats: JPG, PNG, WEBP (Max 5MB). File will be verified and given a collision-resistant UUID filename.</p>
                    </div>

                    <div class="w-full sm:w-auto flex items-center space-x-3">
                        <div>
                            <input type="text" name="alt_text" placeholder="Alt text / caption" 
                                   class="px-3 py-2 text-xs rounded-xl border border-slate-300">
                        </div>

                        <div class="flex items-center space-x-1.5 whitespace-nowrap">
                            <input id="media_is_primary" name="is_primary" type="checkbox" value="1"
                                   class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <label for="media_is_primary" class="text-xs font-semibold text-slate-700 cursor-pointer">Make Primary</label>
                        </div>

                        <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-900 text-white hover:bg-slate-800 transition-colors">
                            Upload
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Gallery Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @forelse($product->media as $item)
                <div class="group relative rounded-2xl border border-slate-200 overflow-hidden bg-slate-50 p-2 flex flex-col justify-between shadow-xs hover:shadow-sm transition-all">
                    <div class="w-full h-36 rounded-xl bg-white flex items-center justify-center overflow-hidden mb-2">
                        <img src="{{ $item->url }}" alt="{{ $item->alt_text }}" class="w-full h-full object-contain">
                    </div>

                    <div class="space-y-1">
                        @if($item->is_primary)
                            <div class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-600 text-white shadow-xs">
                                ★ Primary Thumbnail
                            </div>
                        @else
                            <form method="POST" action="{{ route('admin.products.media.set-primary', [$product->id, $item->id]) }}">
                                @csrf
                                <button type="submit" class="text-[10px] text-blue-600 hover:underline">
                                    Set as Primary
                                </button>
                            </form>
                        @endif

                        <div class="text-[10px] text-slate-400 truncate">{{ $item->alt_text ?: 'No alt text' }}</div>
                    </div>

                    <div class="mt-2 pt-2 border-t border-slate-200 flex justify-end">
                        <form method="POST" action="{{ route('admin.products.media.destroy', [$product->id, $item->id]) }}" onsubmit="return confirm('Delete this image?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 font-semibold">
                                Remove
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-10 text-center text-slate-400">
                    No images uploaded yet. Upload images above to populate the product gallery.
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 4: SPECIFICATIONS -->
    <div x-show="activeTab === 'specs'" class="bg-white rounded-b-2xl border border-slate-200 border-t-0 p-6 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Dynamic Specifications</h2>
                <p class="text-xs text-slate-400 mt-0.5">Flexible key/value specifications (e.g. Charging Power: 140W, Capacity: 24,000mAh).</p>
            </div>
        </div>

        <!-- Add Specification Form -->
        <form method="POST" action="{{ route('admin.products.specifications.store', $product->id) }}" class="bg-slate-50 p-4 rounded-xl border border-slate-200 grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Group (Optional)</label>
                <input type="text" name="spec_group" placeholder="e.g. Battery, Physical, Connectivity"
                       class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Key / Attribute <span class="text-red-500">*</span></label>
                <input type="text" name="spec_key" required placeholder="e.g. Output Power"
                       class="w-full px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Value <span class="text-red-500">*</span></label>
                <input type="text" name="spec_value" required placeholder="e.g. 140W"
                       class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300">
            </div>
            <div>
                <button type="submit" class="w-full py-2 text-xs font-semibold rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition-colors">
                    Add Specification
                </button>
            </div>
        </form>

        <!-- Specifications Table -->
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[10px]">
                    <tr>
                        <th class="py-2.5 px-3">Group</th>
                        <th class="py-2.5 px-3">Attribute Name</th>
                        <th class="py-2.5 px-3">Value</th>
                        <th class="py-2.5 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($product->specifications as $spec)
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-2.5 px-3 font-semibold text-slate-500">{{ $spec->spec_group ?: 'General' }}</td>
                            <td class="py-2.5 px-3 font-bold text-slate-800">{{ $spec->spec_key }}</td>
                            <td class="py-2.5 px-3 text-slate-700">{{ $spec->spec_value }}</td>
                            <td class="py-2.5 px-3 text-right">
                                <form method="POST" action="{{ route('admin.products.specifications.destroy', [$product->id, $spec->id]) }}" onsubmit="return confirm('Remove specification [{{ $spec->spec_key }}]?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[11px] text-red-500 hover:text-red-700 font-semibold">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">No specifications defined.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 5: PRICE & STOCK HISTORY -->
    <div x-show="activeTab === 'history'" class="bg-white rounded-b-2xl border border-slate-200 border-t-0 p-6 shadow-sm space-y-8">
        
        <!-- Price History Section -->
        <div>
            <h2 class="text-sm font-bold text-slate-900 mb-2">Price Change Audit Trail</h2>
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[10px]">
                        <tr>
                            <th class="py-2 px-3">Date</th>
                            <th class="py-2 px-3">Variant SKU</th>
                            <th class="py-2 px-3">Previous Price</th>
                            <th class="py-2 px-3">New Price</th>
                            <th class="py-2 px-3">Reason</th>
                            <th class="py-2 px-3">Changed By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $allPriceHistories = $product->variants->flatMap->priceHistories->sortByDesc('effective_date');
                        @endphp
                        @forelse($allPriceHistories as $ph)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-2 px-3 font-mono text-slate-500">{{ $ph->effective_date->format('Y-m-d H:i') }}</td>
                                <td class="py-2 px-3 font-mono font-semibold text-slate-800">{{ $ph->variant?->sku }}</td>
                                <td class="py-2 px-3 text-slate-500 line-through">Rs {{ number_format($ph->old_price, 2) }}</td>
                                <td class="py-2 px-3 font-bold text-emerald-700">Rs {{ number_format($ph->new_price, 2) }}</td>
                                <td class="py-2 px-3 text-slate-600">{{ $ph->reason }}</td>
                                <td class="py-2 px-3 text-slate-500">{{ $ph->admin?->username ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-center text-slate-400">No price adjustments recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inventory History Section -->
        <div>
            <h2 class="text-sm font-bold text-slate-900 mb-2">Inventory Movement Audit Trail</h2>
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[10px]">
                        <tr>
                            <th class="py-2 px-3">Date</th>
                            <th class="py-2 px-3">Variant SKU</th>
                            <th class="py-2 px-3">Stock Change</th>
                            <th class="py-2 px-3">Resulting Stock</th>
                            <th class="py-2 px-3">Reason</th>
                            <th class="py-2 px-3">Logged By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $allInvHistories = $product->variants->flatMap->inventoryHistories->sortByDesc('created_at');
                        @endphp
                        @forelse($allInvHistories as $ih)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-2 px-3 font-mono text-slate-500">{{ $ih->created_at->format('Y-m-d H:i') }}</td>
                                <td class="py-2 px-3 font-mono font-semibold text-slate-800">{{ $ih->variant?->sku }}</td>
                                <td class="py-2 px-3 font-bold {{ $ih->change_qty >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                    {{ $ih->change_qty >= 0 ? '+' : '' }}{{ $ih->change_qty }}
                                </td>
                                <td class="py-2 px-3 font-semibold text-slate-900">{{ $ih->new_qty }}</td>
                                <td class="py-2 px-3 text-slate-600">{{ $ih->reason }}</td>
                                <td class="py-2 px-3 text-slate-500">{{ $ih->admin?->username ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-center text-slate-400">No inventory movements recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
