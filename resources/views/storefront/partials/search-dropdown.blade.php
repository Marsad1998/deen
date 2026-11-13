<!-- Autocomplete Search Dropdown with Product Images -->
<div x-show="isOpen" 
     x-cloak
     @click.outside="close()"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0 translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-1"
     class="absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-50 text-left">
    
    <!-- Header status bar -->
    <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-[11px]">
        <div class="flex items-center space-x-2 text-slate-500 font-medium">
            <template x-if="isLoading">
                <svg class="animate-spin w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            </template>
            <template x-if="!isLoading">
                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </template>
            <span>
                <span x-show="isLoading">Searching products...</span>
                <span x-show="!isLoading" x-text="results.length > 0 ? 'Matching Products (' + total + ')' : 'Search Results'"></span>
            </span>
        </div>
        <span class="text-[10px] text-slate-400 font-medium hidden sm:inline">&uarr;&darr; Navigate &bull; Esc to close</span>
    </div>

    <!-- Results List (Scrollable) -->
    <div class="max-h-96 overflow-y-auto divide-y divide-slate-100">
        <!-- Results template -->
        <template x-for="(item, index) in results" :key="item.id">
            <a :href="item.url" 
               class="flex items-center space-x-3.5 p-3 transition-colors group cursor-pointer"
               :class="selectedIndex === index ? 'bg-rose-50/80 text-rose-950' : 'hover:bg-slate-50 text-slate-900'">
                
                <!-- Product Thumbnail with Image -->
                <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200/80 p-1 flex items-center justify-center flex-shrink-0 overflow-hidden group-hover:scale-105 transition-transform">
                    <img :src="item.thumbnail_url" :alt="item.name" class="max-w-full max-h-full object-contain" onerror="this.src='/images/placeholder.svg';">
                </div>

                <!-- Product Details -->
                <div class="flex-1 min-w-0 space-y-0.5">
                    <div class="flex items-center space-x-1.5 text-[10px] font-bold uppercase tracking-wider">
                        <span class="text-rose-600 truncate" x-text="item.category"></span>
                        <template x-if="item.brand">
                            <span class="text-slate-300">&bull;</span>
                        </template>
                        <template x-if="item.brand">
                            <span class="text-slate-500 truncate" x-text="item.brand"></span>
                        </template>
                    </div>

                    <h4 class="text-xs font-bold truncate transition-colors group-hover:text-rose-600 leading-snug"
                        :class="selectedIndex === index ? 'text-rose-600' : 'text-slate-900'"
                        x-text="item.name"></h4>

                    <div class="flex items-center space-x-2 pt-0.5">
                        <template x-if="item.price">
                            <span class="text-xs font-black text-slate-900" x-text="'Rs ' + item.price"></span>
                        </template>
                        <template x-if="item.original_price">
                            <span class="text-[10px] text-slate-400 line-through" x-text="'Rs ' + item.original_price"></span>
                        </template>
                        <template x-if="item.in_stock">
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">In Stock</span>
                        </template>
                    </div>
                </div>

                <!-- Right Arrow Indicator -->
                <div class="text-slate-300 group-hover:text-rose-600 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
        </template>

        <!-- Empty state (no results) -->
        <div x-show="!isLoading && results.length === 0 && query && query.trim().length >= 2" class="p-6 text-center space-y-2">
            <div class="w-10 h-10 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-xs font-bold text-slate-700">No products match &ldquo;<span x-text="query"></span>&rdquo;</p>
            <p class="text-[11px] text-slate-400 max-w-xs mx-auto">Try checking spelling or search general categories like AC, Refrigerator, LED TV, Scooter, or Washing Machine.</p>
        </div>
    </div>

    <!-- Footer: View All Results Link in Catalogue -->
    <div x-show="results.length > 0" class="p-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
        <a :href="catalogUrl + '?search=' + encodeURIComponent(query)" 
           class="w-full py-2 px-3 rounded-xl text-center text-xs font-bold text-rose-600 hover:text-white hover:bg-rose-600 bg-white border border-rose-200 transition-all flex items-center justify-center space-x-1.5 shadow-xs">
            <span>View all</span>
            <span x-text="total"></span>
            <span>results in Catalogue &rarr;</span>
        </a>
    </div>

</div>
