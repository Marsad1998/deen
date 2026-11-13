@extends('layouts.app')

@section('title', 'DEEN Electronics — Architecture Foundation')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-deen-900 to-slate-900 rounded-2xl p-8 sm:p-12 text-white shadow-xl mb-10 relative overflow-hidden">
        <div class="relative z-10 max-w-3xl">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-deen-500/20 text-deen-300 border border-deen-500/30 mb-4">
                Architecture Migration Complete
            </span>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight mb-4">
                DEEN Electronics
            </h1>
            <p class="text-lg text-slate-300 mb-8 leading-relaxed">
                {{ $tagline }}. Successfully transitioned from custom core PHP to <strong>Laravel 13</strong> with native Eloquent ORM, Blade templating, Tailwind CSS, and Alpine.js.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ url('/health') }}" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-deen-600 hover:bg-deen-500 text-white font-semibold transition shadow-md">
                    Check Health Endpoint
                </a>
                <a href="{{ url('/health/db') }}" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold transition">
                    Database Diagnostics
                </a>
            </div>
        </div>
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <svg class="w-96 h-96" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                    ✓
                </div>
                <h3 class="font-bold text-slate-900">Framework Infrastructure</h3>
            </div>
            <p class="text-sm text-slate-600 mb-4">
                Standard Laravel 13 framework configuration with environment routing, native logging, and exception handling.
            </p>
            <span class="text-xs font-semibold px-2 py-1 rounded bg-blue-50 text-blue-700">Laravel v{{ app()->version() }}</span>
        </div>

        <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold">
                    ⚡
                </div>
                <h3 class="font-bold text-slate-900">Frontend Technology</h3>
            </div>
            <p class="text-sm text-slate-600 mb-4">
                Modern Blade component system integrated with Tailwind CSS utility architecture and reactive Alpine.js.
            </p>
            <span class="text-xs font-semibold px-2 py-1 rounded bg-emerald-50 text-emerald-700">Tailwind + Alpine.js</span>
        </div>

        <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold">
                    ☁
                </div>
                <h3 class="font-bold text-slate-900">Shared Hosting Ready</h3>
            </div>
            <p class="text-sm text-slate-600 mb-4">
                Engineered for cPanel / standard shared hosting without Docker, continuous Redis, or complex daemon requirements.
            </p>
            <span class="text-xs font-semibold px-2 py-1 rounded bg-amber-50 text-amber-700">cPanel Compatible</span>
        </div>
    </div>

    <!-- Alpine.js Interactive Verification Component -->
    <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm" x-data="{
        tab: 'overview',
        modules: [
            { name: 'Admin RBAC', tables: 'roles, permissions, role_permissions, admins, audit_logs', status: 'Ready to migrate' },
            { name: 'Catalog Taxonomy', tables: 'categories, brands', status: 'Ready to migrate' },
            { name: 'Product Engine', tables: 'products, product_variants, product_media, product_specifications', status: 'Ready to migrate' },
            { name: 'Customer & Address', tables: 'customers, customer_addresses', status: 'Ready to migrate' },
            { name: 'Cart, Wishlist & Comparison', tables: 'carts, cart_items, wishlists, comparisons', status: 'Ready to migrate' },
            { name: 'Shipping & Coupons', tables: 'shipping_methods, shipping_rates, coupons', status: 'Ready to migrate' },
            { name: 'Order & Payment', tables: 'orders, order_items, order_status_history, payment_transactions', status: 'Ready to migrate' },
            { name: 'Store Configuration', tables: 'store_settings, email_settings', status: 'Ready to migrate' }
        ]
    }">
        <div class="flex justify-between items-center border-b border-slate-200 pb-4 mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Phase 2 Architecture Inventory</h2>
                <p class="text-xs text-slate-500">Planned Eloquent models and migration packages</p>
            </div>
            <div class="flex space-x-2">
                <button @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-deen-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                    Module Overview
                </button>
                <button @click="tab = 'tech'" :class="tab === 'tech' ? 'bg-deen-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                    Hosting Constraints
                </button>
            </div>
        </div>

        <div x-show="tab === 'overview'">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-slate-500 bg-slate-50">
                            <th class="px-4 py-3">Module</th>
                            <th class="px-4 py-3">Database Tables</th>
                            <th class="px-4 py-3">Migration Target</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="m in modules" :key="m.name">
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 font-semibold text-slate-900" x-text="m.name"></td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-600" x-text="m.tables"></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700" x-text="m.status"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="tab === 'tech'" x-cloak class="p-4 bg-slate-50 rounded-lg text-sm text-slate-700 space-y-2">
            <p><strong>Deployment Target:</strong> Standard cPanel Shared Hosting (Apache/LiteSpeed + PHP 8.3+ + MySQL).</p>
            <p><strong>Queue Strategy:</strong> <code>sync</code> for instant order notifications, or database queue scheduled via standard cPanel cron job (<code>* * * * * php /path/to/artisan schedule:run</code>).</p>
            <p><strong>Session / Cache Strategy:</strong> <code>file</code> or <code>database</code> driver (zero Redis dependency).</p>
            <p><strong>Media Storage:</strong> Standard local public symlink (<code>storage/app/public</code> $\to$ <code>public/storage</code>).</p>
        </div>
    </div>
</div>
@endsection
