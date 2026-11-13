@extends('layouts.admin')

@section('title', 'Add Warehouse Inventory')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" 
     x-data="{
        selectedVariantId: '{{ old('product_variant_id', $preselectedVariantId ?: '') }}',
        quantity: {{ old('quantity', 1) }},
        variants: {{ Js::from($variants) }},
        get selectedVariant() {
            return this.variants.find(v => v.id == this.selectedVariantId) || null;
        },
        get currentStock() {
            return this.selectedVariant ? this.selectedVariant.stock_quantity : 0;
        },
        get newStock() {
            const qty = parseInt(this.quantity) || 0;
            return this.currentStock + qty;
        }
     }">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.inventory.index') }}" class="hover:text-blue-600 transition-colors">Inventory</a>
        <span>/</span>
        <span class="text-slate-800 font-bold">Add Inventory</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Add Warehouse Inventory</h1>
            <p class="text-xs text-slate-500 mt-1">Record supplier inward shipments, showroom stock receiving, or warehouse restocks.</p>
        </div>
        <a href="{{ route('admin.inventory.index') }}" 
           class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Inventory</span>
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
            <div class="flex items-center space-x-2 font-bold">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span>Please correct the errors below:</span>
            </div>
            <ul class="list-disc list-inside pl-1 text-[11px] font-normal space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.inventory.store') }}" class="space-y-6">
        @csrf

        <!-- Main Form Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xs space-y-6">
            
            <!-- Section 1: Item & Quantity -->
            <div class="space-y-4">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">1. Product Variant &amp; Receiving Units</h2>

                <!-- Select Product Variant -->
                <div>
                    <label for="product_variant_id" class="block text-xs font-bold text-slate-800 mb-1.5">
                        Select Product / Variant <span class="text-rose-500">*</span>
                    </label>
                    <select name="product_variant_id" id="product_variant_id" required x-model="selectedVariantId"
                            class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Choose Product &amp; Variant from Warehouse Catalog --</option>
                        @foreach($variants as $variant)
                            <option value="{{ $variant->id }}">
                                {{ $variant->product?->name }} — {{ $variant->name }} (SKU: {{ $variant->sku }} | Stock: {{ $variant->stock_quantity }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Listed with live warehouse stock quantities and SKUs.</p>
                </div>

                <!-- Quantity to Add -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="quantity" class="block text-xs font-bold text-slate-800 mb-1.5">
                            Quantity Received (Units) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="quantity" id="quantity" min="1" max="100000" required x-model.number="quantity"
                                   placeholder="e.g. 25"
                                   class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            <span class="absolute right-3.5 top-2.5 text-xs font-bold text-slate-400">units</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Enter positive integer of items delivered.</p>
                    </div>

                    <!-- Live Calculation Box -->
                    <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Projected Stock Level</span>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="text-xs text-slate-500" x-text="currentStock + ' in stock'">0 in stock</span>
                                <span class="text-xs font-bold text-emerald-600" x-show="quantity > 0" x-text="'+ ' + quantity">+ 0</span>
                                <span class="text-xs text-slate-400">→</span>
                                <span class="text-sm font-black text-slate-900" x-text="newStock + ' total'">0 total</span>
                            </div>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                            +
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Inward Shipment Details -->
            <div class="pt-6 border-t border-slate-100 space-y-4">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">2. Inward Source &amp; Shipment Metadata</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Inward Type -->
                    <div>
                        <label for="inward_type" class="block text-xs font-bold text-slate-800 mb-1.5">
                            Transaction / Inward Type <span class="text-rose-500">*</span>
                        </label>
                        <select name="inward_type" id="inward_type" required
                                class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            <option value="Supplier Restock Shipment" {{ old('inward_type') === 'Supplier Restock Shipment' ? 'selected' : '' }}>🏭 Supplier Restock Shipment (Factory Delivery)</option>
                            <option value="Direct Purchase / Local Procurement" {{ old('inward_type') === 'Direct Purchase / Local Procurement' ? 'selected' : '' }}>🛒 Direct Showroom Purchase / Procurement</option>
                            <option value="Customer Return Restock" {{ old('inward_type') === 'Customer Return Restock' ? 'selected' : '' }}>↩️ Customer Return / Delivery Refusal Restock</option>
                            <option value="Warehouse Physical Count Audit" {{ old('inward_type') === 'Warehouse Physical Count Audit' ? 'selected' : '' }}>📋 Physical Inventory Count Correction</option>
                            <option value="Showroom Display Allocation" {{ old('inward_type') === 'Showroom Display Allocation' ? 'selected' : '' }}>🏢 Showroom Inter-Branch Allocation</option>
                            <option value="Other Inward Receiving" {{ old('inward_type') === 'Other Inward Receiving' ? 'selected' : '' }}>📦 Other Inward Restock</option>
                        </select>
                    </div>

                    <!-- Supplier / Vendor Name -->
                    <div>
                        <label for="supplier_name" class="block text-xs font-bold text-slate-800 mb-1.5">
                            Supplier / Manufacturer Name (Optional)
                        </label>
                        <input type="text" name="supplier_name" id="supplier_name" value="{{ old('supplier_name') }}"
                               placeholder="e.g. Haier Pakistan, Dawlance, Waves, Diamond Foam"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Delivery Challan / PO Reference # -->
                    <div class="sm:col-span-2">
                        <label for="reference_number" class="block text-xs font-bold text-slate-800 mb-1.5">
                            Shipment Reference / Bilty / PO / Challan # (Optional)
                        </label>
                        <input type="text" name="reference_number" id="reference_number" value="{{ old('reference_number') }}"
                               placeholder="e.g. PO-2026-8819 / Bilty #129381 / Challan #4092"
                               class="w-full px-3.5 py-2.5 text-xs font-mono rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Logged in immutable inventory history for audit tracking.</p>
                    </div>
                </div>
            </div>

            <!-- Section 3: Warehouse Notes -->
            <div class="pt-6 border-t border-slate-100 space-y-4">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">3. Internal Notes &amp; Location</h2>

                <div>
                    <label for="notes" class="block text-xs font-bold text-slate-800 mb-1.5">
                        Internal Warehouse Notes (Optional)
                    </label>
                    <textarea name="notes" id="notes" rows="3"
                              placeholder="e.g. Unloaded at Hall A Bay 2. Inspected by Showroom Manager. Serial numbers logged."
                              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                <a href="{{ route('admin.inventory.index') }}" 
                   class="px-5 py-2.5 text-xs font-bold rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-center transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all flex items-center justify-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Confirm &amp; Add to Inventory</span>
                </button>
            </div>

        </div>
    </form>

</div>
@endsection
