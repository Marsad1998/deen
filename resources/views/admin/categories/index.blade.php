@extends('layouts.admin')

@section('title', 'Categories Management')

@section('content')
<div class="space-y-6">

    <!-- Header Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-rose-600 mb-1">
                <span>Catalogue Architecture</span>
                <span>&bull;</span>
                <span>Category Management</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Categories</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Manage product categories, visual badges, sort orders, and storefront navigation hierarchies.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.categories.create') }}" 
               class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/25 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add New Category</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Search category by name, slug or description..."
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="sm:w-44">
                <select name="status" 
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 text-slate-800 bg-white focus:outline-none focus:border-rose-600">
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-white transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.categories.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-rose-600 border border-slate-200 hover:bg-slate-50 transition-colors flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Categories Table -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/70 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Slug</th>
                        <th class="py-3.5 px-4 text-center">Products</th>
                        <th class="py-3.5 px-4 text-center">Sort Order</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    @if($category->image_url)
                                        <img src="{{ asset($category->image_url) }}" 
                                             alt="{{ $category->name }}" 
                                             class="w-10 h-10 rounded-xl object-contain bg-slate-50 border border-slate-100 p-1 flex-shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 font-bold flex-shrink-0">
                                            {{ substr($category->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $category->name }}</div>
                                        @if($category->parent)
                                            <div class="text-[10px] text-slate-400">Parent: {{ $category->parent->name }}</div>
                                        @endif
                                        <div class="flex items-center space-x-1.5 mt-0.5">
                                            @if($category->badge_text)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    {{ $category->badge_text }}
                                                </span>
                                            @endif
                                            @if($category->show_in_nav)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                    In Nav
                                                </span>
                                            @endif
                                            @if($category->show_on_home)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Home
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 font-mono text-slate-500">
                                {{ $category->slug }}
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold text-[10px] {{ $category->products_count > 0 ? 'bg-slate-100 text-slate-800' : 'bg-slate-50 text-slate-400' }}">
                                    {{ $category->products_count }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-center font-mono text-slate-600">
                                {{ $category->sort_order }}
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <form method="POST" action="{{ route('admin.categories.toggle-active', $category) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold transition-colors cursor-pointer {{ $category->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 border border-slate-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $category->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center space-x-2">
                                    <a href="{{ route('storefront.catalog', ['category' => $category->slug]) }}" 
                                       target="_blank"
                                       title="View on Webfront"
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>

                                    <a href="{{ route('admin.categories.edit', $category) }}" 
                                       class="px-2.5 py-1 rounded-lg text-slate-700 font-bold bg-slate-100 hover:bg-slate-200 transition-colors">
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" 
                                          onsubmit="return confirm('Are you sure you want to delete category \'{{ addslashes($category->name) }}\'?');" 
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-2.5 py-1 rounded-lg text-rose-600 font-bold hover:bg-rose-50 transition-colors cursor-pointer"
                                                {{ $category->products_count > 0 ? 'title=Reassign products first' : '' }}>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                No categories found matching your query.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
