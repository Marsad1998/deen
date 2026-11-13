@extends('layouts.admin')

@section('title', 'Product Catalog')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Product Catalog</h1>
            <p class="text-xs text-slate-500 mt-1">Manage physical hardware products, variants, media, and pricing.</p>
        </div>

        @if(Auth::guard('admin')->user()->hasPermission('products.create'))
            <div>
                <a href="{{ route('admin.products.create') }}" 
                   class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Product
                </a>
            </div>
        @endif
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <div class="relative">
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                           placeholder="Search by name, SKU, or slug..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Category Filter -->
            <div>
                <label for="category_id" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Category</label>
                <select id="category_id" name="category_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Brand Filter -->
            <div>
                <label for="brand_id" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Brand</label>
                <select id="brand_id" name="brand_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    <option value="">All Brands</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label for="status" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                    <select id="status" name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-900 hover:bg-slate-800 text-white transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'category_id', 'brand_id', 'status']))
                    <a href="{{ route('admin.products.index') }}" class="px-3 py-2 text-xs rounded-xl text-slate-500 hover:text-slate-800 border border-slate-300 hover:bg-slate-100 transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4 w-14">Media</th>
                        <th class="py-3 px-4">Product Details</th>
                        <th class="py-3 px-4">Category & Brand</th>
                        <th class="py-3 px-4">Price (PKR)</th>
                        <th class="py-3 px-4">Stock</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        @php
                            $default = $product->defaultVariant;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors {{ $product->is_archived ? 'opacity-60 bg-slate-50/40' : '' }}">
                            
                            <!-- Thumbnail -->
                            <td class="py-3 px-4">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center p-0.5">
                                    <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" 
                                         class="w-full h-full object-contain"
                                         onerror="this.src='/images/placeholder.svg';">
                                </div>
                            </td>

                            <!-- Product Name & SKU -->
                            <td class="py-3 px-4 max-w-xs">
                                <div class="font-bold text-slate-900 hover:text-blue-600 transition-colors">
                                    <a href="{{ route('admin.products.edit', $product->id) }}">{{ $product->name }}</a>
                                </div>
                                <div class="flex items-center space-x-2 text-[11px] text-slate-500 mt-0.5">
                                    <span class="font-mono text-slate-600">{{ $product->sku ?: ($default?->sku ?: 'No SKU') }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $product->variants_count }} {{ Str::plural('variant', $product->variants_count) }}</span>
                                </div>
                                @if($product->badge_text)
                                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[9px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $product->badge_text }}
                                    </span>
                                @endif
                            </td>

                            <!-- Category & Brand -->
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-800">{{ $product->category?->name ?? 'Uncategorized' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $product->brand?->name ?? 'Generic / In-house' }}</div>
                            </td>

                            <!-- Price & Original Price -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($default)
                                    <div class="font-semibold text-slate-900">
                                        Rs {{ number_format($default->price, 2) }}
                                    </div>
                                    @if($default->original_price && $default->original_price > $default->price)
                                        <div class="text-[10px] text-slate-400 line-through">
                                            Rs {{ number_format($default->original_price, 2) }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">No price</span>
                                @endif
                            </td>

                            <!-- Stock Status & Quantity -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @php
                                    $totalStock = $product->variants->sum('stock_quantity');
                                @endphp
                                <div class="flex items-center space-x-1.5">
                                    <span class="w-2 h-2 rounded-full {{ $totalStock > 0 ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    <span class="font-semibold {{ $totalStock > 0 ? 'text-slate-800' : 'text-red-600' }}">{{ $totalStock }}</span>
                                </div>
                                <div class="text-[10px] text-slate-400 uppercase tracking-wider mt-0.5">
                                    {{ $default?->stock_status ?? ($totalStock > 0 ? 'in_stock' : 'out_of_stock') }}
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($product->is_archived)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-300">
                                        Archived
                                    </span>
                                @elseif($product->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                                        Draft
                                    </span>
                                @endif

                                @if($product->is_featured)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-semibold bg-purple-100 text-purple-700 border border-purple-200 ml-1">
                                        Featured
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center space-x-2">
                                    @if(Auth::guard('admin')->user()->hasPermission('products.edit'))
                                        <a href="{{ route('admin.products.edit', $product->id) }}" 
                                           class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200 transition-colors">
                                            Edit
                                        </a>
                                    @endif

                                    @if(Auth::guard('admin')->user()->hasPermission('products.delete'))
                                        @if($product->is_archived)
                                            <form method="POST" action="{{ route('admin.products.unarchive', $product->id) }}" class="inline" onsubmit="return confirm('Restore this product to draft?');">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 border border-slate-200 transition-colors">
                                                    Restore
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.products.archive', $product->id) }}" class="inline" onsubmit="return confirm('Archive product [{{ $product->name }}]?');">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-amber-50 text-slate-600 hover:text-amber-700 border border-slate-200 transition-colors">
                                                    Archive
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <p class="font-semibold text-slate-600 text-sm">No products found</p>
                                    <p class="text-xs text-slate-400 mt-1">Try adjusting your search criteria or add a new product to the catalog.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
