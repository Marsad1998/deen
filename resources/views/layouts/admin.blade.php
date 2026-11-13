<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Console') — DEEN Electronics</title>

    <!-- Tailwind CDN for development -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full bg-slate-50 text-slate-800 antialiased" x-data="{ mobileSidebarOpen: false }">

    @php
        $currentAdmin = Auth::guard('admin')->user();
        $pendingOrdersBadge = \App\Models\Order::where('status', 'pending')->count();
    @endphp

    <!-- Mobile Off-Canvas Sidebar Drawer (Small screens only: md:hidden) -->
    <div x-cloak x-show="mobileSidebarOpen" class="relative z-50 md:hidden" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="mobileSidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs" 
             @click="mobileSidebarOpen = false"></div>

        <!-- Sidebar Drawer Container -->
        <div class="fixed inset-0 flex z-50">
            <div x-show="mobileSidebarOpen" 
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="relative mr-16 flex-1 w-full max-w-xs bg-slate-900 text-white flex flex-col justify-between py-5 px-4 shadow-2xl">
                
                <!-- Close Button -->
                <div class="absolute top-4 right-4">
                    <button type="button" @click="mobileSidebarOpen = false" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Drawer Content -->
                <div class="space-y-6">
                    <!-- Brand -->
                    <div class="flex items-center space-x-2.5 pb-4 border-b border-slate-800">
                        <span class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center font-black text-sm text-white shadow-sm">D</span>
                        <div class="flex flex-col">
                            <span class="font-extrabold text-sm tracking-tight text-white leading-tight">DEEN <span class="text-blue-400">ADMIN</span></span>
                            <span class="text-[9px] text-slate-400 uppercase tracking-widest leading-none">Management Console</span>
                        </div>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="space-y-1 text-xs font-semibold">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Dashboard</span>
                        </a>

                        @if($currentAdmin?->hasPermission('orders.view'))
                            <a href="{{ route('admin.orders.index') }}" 
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.orders.*') ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span>Orders</span>
                                </div>
                                @if($pendingOrdersBadge > 0)
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white shadow-xs">
                                        {{ $pendingOrdersBadge }}
                                    </span>
                                @endif
                            </a>
                        @endif

                        @if($currentAdmin?->hasPermission('products.view'))
                            <a href="{{ route('admin.products.index') }}" 
                               class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.products.*') ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span>Products</span>
                            </a>
                        @endif

                        <a href="{{ route('admin.categories.index') }}" 
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            <span>Categories</span>
                        </a>

                        <a href="{{ route('admin.brands.index') }}" 
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.brands.*') ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Brands</span>
                        </a>

                        @if($currentAdmin?->hasPermission('inventory.view'))
                            <a href="{{ route('admin.inventory.index') }}" 
                               class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.inventory.*') ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>Inventory</span>
                            </a>
                        @endif

                        <a href="{{ route('admin.cms.index') }}" 
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.cms.*') ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                            <span>CMS &amp; Website Content</span>
                        </a>

                        @if($currentAdmin?->hasPermission('settings.view'))
                            <a href="{{ route('admin.settings.store') }}" 
                               class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Store &amp; WhatsApp</span>
                            </a>
                            <a href="{{ route('admin.payment-methods.index') }}" 
                               class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.payment-methods.*') ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                <span>Payment Methods</span>
                            </a>
                        @endif

                        <a href="{{ route('home') }}" target="_blank"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>View Storefront &nearr;</span>
                        </a>
                    </nav>
                </div>

                <!-- Bottom User Card in Mobile Drawer -->
                <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-xs text-slate-200">{{ $currentAdmin?->full_name ?: $currentAdmin?->username }}</div>
                        <div class="text-[10px] text-slate-400">{{ $currentAdmin?->role?->name ?? 'Administrator' }}</div>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Fixed Left Sidebar (md:flex) -->
    <aside class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 z-30 bg-slate-900 border-r border-slate-800 text-white">
        
        <!-- Sidebar Brand Header -->
        <div class="h-16 flex items-center px-6 border-b border-slate-800 shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2.5">
                <span class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center font-black text-sm text-white shadow-sm">D</span>
                <div class="flex flex-col">
                    <span class="font-extrabold text-sm tracking-tight text-white leading-tight">DEEN <span class="text-blue-400">ADMIN</span></span>
                    <span class="text-[9px] text-slate-400 uppercase tracking-widest leading-none">Management Console</span>
                </div>
            </a>
        </div>

        <!-- Sidebar Navigation Menu (Scrollable) -->
        <div class="flex-1 flex flex-col justify-between overflow-y-auto px-4 py-5 space-y-6">
            
            <div class="space-y-5">
                
                <!-- Main Operations Section -->
                <div>
                    <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Operations</span>
                    <div class="space-y-1 text-xs font-semibold">
                        <!-- Dashboard -->
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Dashboard</span>
                        </a>

                        <!-- Orders -->
                        @if($currentAdmin?->hasPermission('orders.view'))
                            <a href="{{ route('admin.orders.index') }}" 
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.orders.*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span>Orders</span>
                                </div>
                                @if($pendingOrdersBadge > 0)
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white shadow-xs">
                                        {{ $pendingOrdersBadge }}
                                    </span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Catalog Management Section -->
                <div>
                    <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Catalog &amp; Inventory</span>
                    <div class="space-y-1 text-xs font-semibold">
                        <!-- Products -->
                        @if($currentAdmin?->hasPermission('products.view'))
                            <a href="{{ route('admin.products.index') }}" 
                               class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.products.*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span>Products</span>
                            </a>
                        @endif

                        <!-- Categories -->
                        <a href="{{ route('admin.categories.index') }}" 
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.categories.*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            <span>Categories</span>
                        </a>

                        <!-- Brands -->
                        <a href="{{ route('admin.brands.index') }}" 
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.brands.*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Brands</span>
                        </a>

                        <!-- Inventory -->
                        @if($currentAdmin?->hasPermission('inventory.view'))
                            <a href="{{ route('admin.inventory.index') }}" 
                               class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.inventory.*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>Inventory</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Storefront CMS Section -->
                <div>
                    <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Storefront CMS</span>
                    <div class="space-y-1 text-xs font-semibold">
                        <!-- CMS Website Management Hub -->
                        <a href="{{ route('admin.cms.index') }}" 
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.cms.*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                            <span>CMS &amp; Website Content</span>
                        </a>

                        @if($currentAdmin?->hasPermission('settings.view'))
                            <!-- Store & WhatsApp -->
                            <a href="{{ route('admin.settings.store') }}" 
                               class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Store &amp; WhatsApp</span>
                            </a>

                            <!-- Payment Methods -->
                            <a href="{{ route('admin.payment-methods.index') }}" 
                               class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.payment-methods.*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                <span>Payment Methods</span>
                            </a>
                        @endif

                        <!-- View Live Storefront -->
                        <a href="{{ route('home') }}" target="_blank"
                           class="flex items-center justify-between px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/80 hover:text-white transition-all group">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                <span>Live Storefront</span>
                            </div>
                            <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Bottom User Profile & Logout -->
            <div class="pt-4 border-t border-slate-800/80">
                <div class="bg-slate-800/60 rounded-2xl p-3 border border-slate-800 flex items-center justify-between">
                    <div class="min-w-0 flex-1 pr-2">
                        <div class="font-bold text-xs text-white truncate">
                            {{ $currentAdmin?->full_name ?: $currentAdmin?->username }}
                        </div>
                        <div class="text-[10px] text-slate-400 flex items-center space-x-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span class="truncate">{{ $currentAdmin?->role?->name ?? 'Admin' }}</span>
                        </div>
                    </div>

                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors cursor-pointer"
                                title="Sign out of console">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </aside>

    <!-- Main Workspace Area (Offset by sidebar width: md:pl-64) -->
    <div class="md:pl-64 flex flex-col min-h-screen">

        <!-- Top Header Bar -->
        <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-20 shadow-2xs">
            
            <!-- Left: Mobile Hamburger Trigger + Breadcrumb/Title -->
            <div class="flex items-center space-x-3">
                <button type="button" 
                        @click="mobileSidebarOpen = true"
                        class="p-2 rounded-xl text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 border border-slate-200 md:hidden focus:outline-none focus:ring-2 focus:ring-blue-500"
                        aria-label="Open mobile navigation sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <div class="flex items-center space-x-2 text-xs font-semibold text-slate-600">
                    <span class="hidden sm:inline font-bold text-slate-900">DEEN Electronics Lahore</span>
                    <span class="hidden sm:inline text-slate-300">•</span>
                    <span class="text-slate-500">Management Console</span>
                </div>
            </div>

            <!-- Right: Quick Storefront Link & Session Status -->
            <div class="flex items-center space-x-3">
                <a href="{{ route('home') }}" target="_blank"
                   class="hidden sm:inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors shadow-2xs">
                    <span>View Storefront</span>
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>

                <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

                <div class="flex items-center space-x-2 text-xs">
                    <div class="w-8 h-8 rounded-xl bg-blue-600/10 border border-blue-200 text-blue-700 font-bold flex items-center justify-center">
                        {{ strtoupper(substr($currentAdmin?->username ?? 'A', 0, 1)) }}
                    </div>
                    <span class="font-bold text-slate-800 hidden sm:inline">{{ $currentAdmin?->username }}</span>
                </div>
            </div>

        </header>

        <!-- Main Workspace Body -->
        <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">
            
            <!-- Global Flash Status Messages -->
            @if (session('status'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <div class="font-bold mb-1">Please review the following issues:</div>
                    <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

        </main>

        <!-- Admin Workspace Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 px-6 text-center text-xs text-slate-400">
            DEEN Electronics Enterprise Console &bull; Showroom Management System &bull; Phase 3 Architecture
        </footer>

    </div>

</body>
</html>
