@extends('layouts.storefront')

@section('title', 'Secure Checkout — DEEN Electronics')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 mb-6 sm:mb-8" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-rose-600 transition-colors">Home</a>
        <span>/</span>
        <a href="{{ route('cart.index') }}" class="hover:text-rose-600 transition-colors">Cart</a>
        <span>/</span>
        <span class="text-slate-900 font-semibold">Checkout</span>
    </nav>

    <!-- Header -->
    <div class="mb-8">
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span>DEEN Official Storefront Checkout</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
            Complete Your Order
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Please provide your delivery details below. Our Lahore showroom team verifies every order before dispatch.
        </p>
    </div>

    <!-- Error Summary if any -->
    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm">
            <div class="font-bold mb-1 flex items-center space-x-2">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Please correct the following fields to proceed:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 pl-6 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data" x-data="{
        paymentMethod: '{{ old('payment_method', ($paymentMethods->first()?->code ?? 'cod')) }}',
        recipientDifferent: {{ old('recipient_is_different') ? 'true' : 'false' }},
        city: '{{ old('shipping_city', 'Lahore') }}',
        province: '{{ old('shipping_province', 'Punjab') }}'
    }">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left: Checkout Information & Inputs (7 Columns) -->
            <div class="lg:col-span-7 space-y-8">
                
                <!-- 1. Customer Contact Details -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-5">
                    <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                        <span class="w-7 h-7 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-xs">1</span>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Customer Contact Details</h2>
                            <p class="text-[11px] text-slate-500">We'll use this phone number for order verification &amp; dispatch updates</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Full Name -->
                        <div class="sm:col-span-2">
                            <label for="customer_name" class="block text-xs font-bold text-slate-700 mb-1">
                                Full Name <span class="text-rose-600">*</span>
                            </label>
                            <input type="text" 
                                   id="customer_name" 
                                   name="customer_name" 
                                   value="{{ old('customer_name') }}" 
                                   required
                                   placeholder="e.g. Tariq Mehmood"
                                   class="w-full px-4 py-2.5 rounded-xl border @error('customer_name') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 transition-colors">
                            @error('customer_name')
                                <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="customer_phone" class="block text-xs font-bold text-slate-700 mb-1">
                                Mobile Number (WhatsApp) <span class="text-rose-600">*</span>
                            </label>
                            <input type="tel" 
                                   id="customer_phone" 
                                   name="customer_phone" 
                                   value="{{ old('customer_phone') }}" 
                                   required
                                   placeholder="e.g. 0300-1234567"
                                   class="w-full px-4 py-2.5 rounded-xl border @error('customer_phone') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 transition-colors font-mono">
                            @error('customer_phone')
                                <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label for="customer_email" class="block text-xs font-bold text-slate-700 mb-1">
                                Email Address <span class="text-slate-400 font-normal">(Optional)</span>
                            </label>
                            <input type="email" 
                                   id="customer_email" 
                                   name="customer_email" 
                                   value="{{ old('customer_email') }}" 
                                   placeholder="e.g. customer@example.com"
                                   class="w-full px-4 py-2.5 rounded-xl border @error('customer_email') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 transition-colors">
                            @error('customer_email')
                                <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 2. Shipping & Delivery Address -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-5">
                    <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                        <span class="w-7 h-7 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-xs">2</span>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Delivery Address</h2>
                            <p class="text-[11px] text-slate-500">Delivered across Lahore and all serviceable cities across Pakistan</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Street Address Line 1 -->
                        <div>
                            <label for="shipping_address_line1" class="block text-xs font-bold text-slate-700 mb-1">
                                House / Flat / Street / Sector <span class="text-rose-600">*</span>
                            </label>
                            <input type="text" 
                                   id="shipping_address_line1" 
                                   name="shipping_address_line1" 
                                   value="{{ old('shipping_address_line1') }}" 
                                   required
                                   placeholder="e.g. House #14, Street 8, Block B, DHA Phase 5"
                                   class="w-full px-4 py-2.5 rounded-xl border @error('shipping_address_line1') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 transition-colors">
                            @error('shipping_address_line1')
                                <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Address Line 2 (Landmark / Area) -->
                        <div>
                            <label for="shipping_address_line2" class="block text-xs font-bold text-slate-700 mb-1">
                                Area Landmark / Nearby Location <span class="text-slate-400 font-normal">(Optional)</span>
                            </label>
                            <input type="text" 
                                   id="shipping_address_line2" 
                                   name="shipping_address_line2" 
                                   value="{{ old('shipping_address_line2') }}" 
                                   placeholder="e.g. Near Jalal Sons or Shell Pump"
                                   class="w-full px-4 py-2.5 rounded-xl border @error('shipping_address_line2') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 transition-colors">
                            @error('shipping_address_line2')
                                <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- City, Province, Postal Code -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- City -->
                            <div>
                                <label for="shipping_city" class="block text-xs font-bold text-slate-700 mb-1">
                                    City <span class="text-rose-600">*</span>
                                </label>
                                <input type="text" 
                                       id="shipping_city" 
                                       name="shipping_city" 
                                       x-model="city"
                                       required
                                       placeholder="e.g. Lahore"
                                       class="w-full px-4 py-2.5 rounded-xl border @error('shipping_city') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 transition-colors">
                                @error('shipping_city')
                                    <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Province -->
                            <div>
                                <label for="shipping_province" class="block text-xs font-bold text-slate-700 mb-1">
                                    Province / Region <span class="text-rose-600">*</span>
                                </label>
                                <select id="shipping_province" 
                                        name="shipping_province" 
                                        x-model="province"
                                        class="w-full px-4 py-2.5 rounded-xl border @error('shipping_province') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror text-slate-900 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 transition-colors">
                                    @foreach($provinces as $p)
                                        <option value="{{ $p }}">{{ $p }}</option>
                                    @endforeach
                                </select>
                                @error('shipping_province')
                                    <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Postal Code -->
                            <div>
                                <label for="shipping_postal_code" class="block text-xs font-bold text-slate-700 mb-1">
                                    Postal Code <span class="text-slate-400 font-normal">(Optional)</span>
                                </label>
                                <input type="text" 
                                       id="shipping_postal_code" 
                                       name="shipping_postal_code" 
                                       value="{{ old('shipping_postal_code') }}" 
                                       placeholder="e.g. 54000"
                                       class="w-full px-4 py-2.5 rounded-xl border @error('shipping_postal_code') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 transition-colors font-mono">
                            </div>
                        </div>

                        <!-- Recipient Different Checkbox -->
                        <div class="pt-2">
                            <label class="inline-flex items-center space-x-2.5 cursor-pointer">
                                <input type="checkbox" 
                                       name="recipient_is_different" 
                                       value="1" 
                                       x-model="recipientDifferent"
                                       class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                <span class="text-xs font-semibold text-slate-700">Someone else will receive this delivery</span>
                            </label>

                            <!-- Collapsible Recipient Fields -->
                            <div x-show="recipientDifferent" x-collapse x-cloak class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Recipient's Full Name</label>
                                    <input type="text" 
                                           name="shipping_recipient_name" 
                                           value="{{ old('shipping_recipient_name') }}"
                                           placeholder="e.g. Brother / Manager"
                                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-rose-600 bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Recipient's Phone Number</label>
                                    <input type="tel" 
                                           name="shipping_recipient_phone" 
                                           value="{{ old('shipping_recipient_phone') }}"
                                           placeholder="e.g. 0321-7654321"
                                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-rose-600 bg-white font-mono">
                                </div>
                            </div>
                        </div>

                        <!-- Special Instructions -->
                        <div>
                            <label for="delivery_instructions" class="block text-xs font-bold text-slate-700 mb-1">
                                Delivery Instructions / Notes <span class="text-slate-400 font-normal">(Optional)</span>
                            </label>
                            <textarea id="delivery_instructions" 
                                      name="delivery_instructions" 
                                      rows="2"
                                      placeholder="e.g. Call before delivery, delivery required after 4:00 PM, elevator available for heavy appliance."
                                      class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 transition-colors">{{ old('delivery_instructions') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- 3. Payment Method Selection -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-5">
                    <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                        <span class="w-7 h-7 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-xs">3</span>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Payment Method</h2>
                            <p class="text-[11px] text-slate-500">Choose your preferred payment method</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @foreach($paymentMethods as $pm)
                            <div class="rounded-2xl border transition-all overflow-hidden"
                                 :class="paymentMethod === '{{ $pm->code }}' ? 'border-rose-600 bg-rose-50/20 ring-1 ring-rose-600' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                <label class="relative flex items-start p-4 cursor-pointer">
                                    <input type="radio" 
                                           name="payment_method" 
                                           value="{{ $pm->code }}" 
                                           x-model="paymentMethod" 
                                           class="mt-1 text-rose-600 focus:ring-rose-500">
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center justify-between gap-2 flex-wrap">
                                            <span class="font-bold text-slate-900 text-xs sm:text-sm">{{ $pm->name }}</span>
                                            @if($pm->requires_proof)
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Screenshot Proof Required</span>
                                            @elseif($pm->code === 'cod')
                                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold">Standard</span>
                                            @elseif($pm->code === 'showroom_pickup')
                                                <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold">Self-Collection</span>
                                            @endif
                                        </div>
                                        @if($pm->subtitle)
                                            <p class="text-[11px] text-slate-500 mt-1">{{ $pm->subtitle }}</p>
                                        @endif
                                    </div>
                                </label>

                                <!-- Details & Screenshot Upload Panel (shown when this method is selected) -->
                                <div x-show="paymentMethod === '{{ $pm->code }}'" x-cloak class="px-4 pb-4 pt-2 border-t border-slate-100 bg-slate-50/70 space-y-3">
                                    @if($pm->account_number || $pm->bank_name || $pm->account_title)
                                        <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-xs space-y-2.5">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-600 flex items-center space-x-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                    <span>Official Receiving Details</span>
                                                </span>
                                                <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">DEEN Verified</span>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                                @if($pm->account_title)
                                                    <div>
                                                        <span class="text-slate-400 block text-[10px]">Account Title</span>
                                                        <span class="font-bold text-slate-900">{{ $pm->account_title }}</span>
                                                    </div>
                                                @endif
                                                @if($pm->account_number)
                                                    <div>
                                                        <span class="text-slate-400 block text-[10px]">Account / Mobile Number</span>
                                                        <span class="font-mono font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 select-all inline-block">{{ $pm->account_number }}</span>
                                                    </div>
                                                @endif
                                                @if($pm->bank_name)
                                                    <div>
                                                        <span class="text-slate-400 block text-[10px]">Bank / Provider</span>
                                                        <span class="font-semibold text-slate-800">{{ $pm->bank_name }}</span>
                                                    </div>
                                                @endif
                                                @if($pm->branch_name)
                                                    <div>
                                                        <span class="text-slate-400 block text-[10px]">Branch</span>
                                                        <span class="text-slate-700">{{ $pm->branch_name }}</span>
                                                    </div>
                                                @endif
                                                @if($pm->iban)
                                                    <div class="sm:col-span-2">
                                                        <span class="text-slate-400 block text-[10px]">IBAN Number</span>
                                                        <span class="font-mono text-xs font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 select-all inline-block break-all">{{ $pm->iban }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if($pm->instructions)
                                        <p class="text-[11px] text-slate-600 leading-relaxed bg-white/70 p-3 rounded-xl border border-slate-100">
                                            {{ $pm->instructions }}
                                        </p>
                                    @endif

                                    @if($pm->requires_proof)
                                        <!-- Customer Screenshot Upload Section -->
                                        <div class="p-3.5 rounded-xl bg-amber-50/80 border border-amber-200/90 space-y-3" x-data="{ proofPreview: null }">
                                            <div class="flex items-start space-x-2">
                                                <svg class="w-4 h-4 text-amber-700 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <div class="text-[11px] text-amber-950 leading-relaxed">
                                                    <span class="font-bold">Instructions:</span> Transfer exactly <strong>Rs {{ number_format($subtotal, 0) }}</strong> to the details above. Then upload your transfer receipt / screenshot here before completing checkout.
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-800 mb-1">
                                                        Attach Payment Screenshot <span class="text-rose-600">*</span>
                                                    </label>
                                                    <input type="file" 
                                                           name="payment_proof" 
                                                           accept="image/png,image/jpeg,image/webp"
                                                           @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => proofPreview = e.target.result; reader.readAsDataURL(file); } else { proofPreview = null; }"
                                                           class="block w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-rose-600 file:text-white hover:file:bg-rose-700 cursor-pointer">
                                                    <p class="text-[10px] text-slate-500 mt-1">PNG, JPG or WEBP (Max 5MB)</p>
                                                    @error('payment_proof')
                                                        <p class="text-rose-600 text-[11px] mt-1 font-semibold">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-800 mb-1">
                                                        Transaction ID / Reference # <span class="text-slate-400 font-normal">(Optional)</span>
                                                    </label>
                                                    <input type="text" 
                                                           name="transaction_reference" 
                                                           value="{{ old('transaction_reference') }}"
                                                           placeholder="e.g. TID 987654321"
                                                           class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-rose-600 bg-white font-mono">
                                                    <p class="text-[10px] text-slate-500 mt-1">Found in your banking SMS / app receipt</p>
                                                </div>
                                            </div>

                                            <!-- Instant Image Preview -->
                                            <div x-show="proofPreview" x-cloak class="pt-2 border-t border-amber-200 flex items-center space-x-3">
                                                <img :src="proofPreview" alt="Payment Proof Preview" class="w-16 h-16 object-cover rounded-lg border border-amber-300 shadow-xs">
                                                <div class="text-[11px] text-emerald-800 font-bold flex items-center space-x-1.5">
                                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Screenshot attached &amp; ready to submit with order</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('payment_method')
                        <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Right: Order Review & Sticky Summary (5 Columns) -->
            <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-24">
                
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Order Review</h2>
                        <a href="{{ route('cart.index') }}" class="text-xs font-bold text-rose-600 hover:underline">Edit Cart</a>
                    </div>

                    <!-- Cart Items Preview -->
                    <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto pr-1 space-y-3">
                        @foreach($cart as $item)
                            <div class="pt-3 first:pt-0 flex items-center space-x-3 text-xs">
                                <img src="{{ $item['thumbnail_url'] }}" 
                                     alt="{{ $item['name'] }}" 
                                     class="w-12 h-12 object-contain rounded-xl bg-slate-50 border border-slate-100 flex-shrink-0 p-1">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-slate-900 truncate">{{ $item['name'] }}</h3>
                                    @if(!empty($item['variant_name']))
                                        <div class="text-[11px] text-slate-500">{{ $item['variant_name'] }}</div>
                                    @endif
                                    <div class="text-[11px] text-slate-400">Qty: {{ $item['quantity'] }} × Rs {{ number_format($item['price'], 0) }}</div>
                                </div>
                                <div class="text-right font-extrabold text-slate-900">
                                    Rs {{ number_format($item['price'] * $item['quantity'], 0) }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Financial Summary Breakdown -->
                    <div class="space-y-3 pt-4 border-t border-slate-200 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal ({{ $count }} {{ Str::plural('item', $count) }})</span>
                            <span class="font-bold text-slate-900">Rs {{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="flex justify-between text-slate-600">
                            <span>Estimated Shipping / Freight</span>
                            <span class="font-bold text-emerald-600">Free / Showroom Confirmed</span>
                        </div>

                        <div class="flex justify-between text-slate-600">
                            <span>Applicable Sales Tax</span>
                            <span class="font-medium text-slate-500">Included</span>
                        </div>

                        @if($hasBulky)
                            <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px] flex items-start space-x-2.5">
                                <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span class="leading-relaxed">
                                    <strong>Bulky Freight Item:</strong> Your cart includes large appliances/scooters. Delivery timeline and uncrating assistance will be confirmed directly by Muhammad Aslam.
                                </span>
                            </div>
                        @endif

                        <div class="pt-4 border-t border-slate-200 flex justify-between items-baseline">
                            <span class="text-sm font-black text-slate-900">Total Payable</span>
                            <div class="text-right">
                                <span class="text-2xl font-black text-rose-600">
                                    Rs {{ number_format($subtotal, 2) }}
                                </span>
                                <div class="text-[10px] text-slate-400">PKR Currency</div>
                            </div>
                        </div>
                    </div>

                    <!-- Place Order Button -->
                    <button type="submit" 
                            class="w-full py-4 px-6 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-sm flex items-center justify-center space-x-2 shadow-xl shadow-rose-600/25 transition-all hover:shadow-2xl active:scale-[0.99] cursor-pointer">
                        <svg class="w-5 h-5 fill-none stroke-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Confirm &amp; Place Order</span>
                    </button>

                    <!-- Trust & Guarantee Badges -->
                    <div class="pt-4 border-t border-slate-100 space-y-2.5 text-[11px] text-slate-500">
                        <div class="flex items-center space-x-2 text-slate-700">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>100% Verified Brand Warranty &amp; Invoice</span>
                        </div>
                        <div class="flex items-center space-x-2 text-slate-700">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Showroom: Main GT Road, Manawan, Bata Pur, Lahore</span>
                        </div>
                        <div class="flex items-center space-x-2 text-slate-700">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>Helpline &amp; Support: 0300-7769932 (Muhammad Aslam)</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </form>

</div>
@endsection
