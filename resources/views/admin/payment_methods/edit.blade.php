@extends('layouts.admin')

@section('title', 'Configure ' . $paymentMethod->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.payment-methods.index') }}" class="hover:text-blue-600 transition-colors">Payment Methods</a>
        <span>/</span>
        <span class="text-slate-800 font-bold">{{ $paymentMethod->name }}</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Configure {{ $paymentMethod->name }}</h1>
            <p class="text-xs text-slate-500 mt-1">Manage payment details, receiving account numbers, customer transfer instructions, and screenshot proof rules.</p>
        </div>
        <a href="{{ route('admin.payment-methods.index') }}" 
           class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Payment Methods</span>
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.payment-methods.update', $paymentMethod->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xs space-y-6">
            
            <!-- Section 1: Method Status & Toggles -->
            <div class="space-y-4">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">1. Status &amp; Verification Rules</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Method Name -->
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-800 mb-1.5">Method Display Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $paymentMethod->name) }}" required
                               class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Subtitle / Short Description -->
                    <div>
                        <label for="subtitle" class="block text-xs font-bold text-slate-800 mb-1.5">Subtitle / Helper text</label>
                        <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle', $paymentMethod->subtitle) }}"
                               placeholder="e.g. Instant mobile transfer to our official JazzCash account"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Display Order -->
                    <div>
                        <label for="display_order" class="block text-xs font-bold text-slate-800 mb-1.5">Display Order</label>
                        <input type="number" name="display_order" id="display_order" value="{{ old('display_order', $paymentMethod->display_order) }}" min="0" max="999" required
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none font-mono">
                    </div>

                    <!-- Toggles -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <!-- Is Enabled Toggle -->
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $paymentMethod->is_enabled) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Enable on Storefront Checkout</span>
                                <span class="text-[11px] text-slate-500 block">Allow customers to choose this payment option</span>
                            </div>
                        </label>

                        <!-- Requires Proof Toggle -->
                        <label class="flex items-center space-x-3 cursor-pointer pt-2 border-t border-slate-200">
                            <input type="checkbox" name="requires_proof" value="1" {{ old('requires_proof', $paymentMethod->requires_proof) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-slate-300">
                            <div>
                                <span class="text-xs font-bold text-amber-800 block">Require Payment Screenshot Proof</span>
                                <span class="text-[11px] text-slate-500 block">Customer must attach a transfer receipt screenshot before placing order</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Section 2: Account Details (If Applicable) -->
            <div class="pt-6 border-t border-slate-100 space-y-4">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">2. Receiving Account Details (Displayed to Customer)</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Account Title -->
                    <div>
                        <label for="account_title" class="block text-xs font-bold text-slate-800 mb-1.5">Beneficiary / Account Title</label>
                        <input type="text" name="account_title" id="account_title" value="{{ old('account_title', $paymentMethod->account_title) }}"
                               placeholder="e.g. Muhammad Aslam (DEEN Electronics)"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Account / Mobile Number -->
                    <div>
                        <label for="account_number" class="block text-xs font-bold text-slate-800 mb-1.5">Account / Mobile Number</label>
                        <input type="text" name="account_number" id="account_number" value="{{ old('account_number', $paymentMethod->account_number) }}"
                               placeholder="e.g. 0300-7769932 or Bank Account #"
                               class="w-full px-3.5 py-2.5 text-xs font-mono font-bold text-blue-700 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Bank Name -->
                    <div>
                        <label for="bank_name" class="block text-xs font-bold text-slate-800 mb-1.5">Bank Name (For Bank Transfer)</label>
                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $paymentMethod->bank_name) }}"
                               placeholder="e.g. Meezan Bank Limited / HBL"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Branch Name -->
                    <div>
                        <label for="branch_name" class="block text-xs font-bold text-slate-800 mb-1.5">Branch Name</label>
                        <input type="text" name="branch_name" id="branch_name" value="{{ old('branch_name', $paymentMethod->branch_name) }}"
                               placeholder="e.g. Gulberg III Main Branch, Lahore"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- IBAN -->
                    <div class="sm:col-span-2">
                        <label for="iban" class="block text-xs font-bold text-slate-800 mb-1.5">IBAN (International Bank Account Number)</label>
                        <input type="text" name="iban" id="iban" value="{{ old('iban', $paymentMethod->iban) }}"
                               placeholder="e.g. PK12MEZN0001020103984712"
                               class="w-full px-3.5 py-2.5 text-xs font-mono rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Section 3: Customer Instructions -->
            <div class="pt-6 border-t border-slate-100 space-y-4">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">3. Checkout Payment Instructions</h2>

                <div>
                    <label for="instructions" class="block text-xs font-bold text-slate-800 mb-1.5">
                        Instructions displayed to customer on checkout
                    </label>
                    <textarea name="instructions" id="instructions" rows="4"
                              placeholder="e.g. Transfer the total amount to JazzCash account 0300-7769932 (Muhammad Aslam) and attach your payment receipt screenshot before completing checkout."
                              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">{{ old('instructions', $paymentMethod->instructions) }}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1">This text appears directly under this payment option on the customer checkout page.</p>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.payment-methods.index') }}" 
                   class="px-5 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition-all flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save Configuration</span>
                </button>
            </div>

        </div>
    </form>

</div>
@endsection
