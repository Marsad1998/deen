@extends('layouts.admin')

@section('title', 'Store & WhatsApp Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
        <span>/</span>
        <span class="text-slate-800 font-bold">Store &amp; WhatsApp Settings</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Store &amp; WhatsApp Settings</h1>
            <p class="text-xs text-slate-500 mt-1">Configure your official storefront logo, showroom WhatsApp number, customer contact channels, and address.</p>
        </div>
        <a href="{{ route('admin.payment-methods.index') }}" 
           class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors">
            <span>Payment Methods</span>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    @if(session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center space-x-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.store.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xs space-y-6">
            
            <!-- 1. Store Identity & Logo -->
            <div class="space-y-4">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">1. Brand Identity &amp; Logo</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="store_name" class="block text-xs font-bold text-slate-800 mb-1.5">
                            Showroom / Store Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="store_name" id="store_name" value="{{ old('store_name', $settings['store_name']) }}" required
                               class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="store_tagline" class="block text-xs font-bold text-slate-800 mb-1.5">
                            Tagline / Subtitle
                        </label>
                        <input type="text" name="store_tagline" id="store_tagline" value="{{ old('store_tagline', $settings['store_tagline']) }}"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <!-- Logo Section -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3">
                    <label class="block text-xs font-bold text-slate-800">
                        Official Store Logo
                    </label>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <!-- Current Logo Preview -->
                        <div class="w-32 h-16 rounded-xl bg-white border border-slate-200 p-2 flex items-center justify-center shrink-0">
                            @if(!empty($settings['store_logo']))
                                <img src="{{ $settings['store_logo'] }}" alt="Store Logo" class="max-h-full max-w-full object-contain">
                            @else
                                <div class="flex items-center space-x-1.5">
                                    <span class="w-6 h-6 rounded-lg bg-rose-600 flex items-center justify-center font-black text-xs text-white">D</span>
                                    <span class="font-extrabold text-xs text-slate-900 tracking-tight">DEEN</span>
                                </div>
                            @endif
                        </div>

                        <!-- Upload New Logo -->
                        <div class="flex-1 space-y-1">
                            <input type="file" name="store_logo_file" id="store_logo_file" accept="image/*"
                                   class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400">PNG, JPG, SVG or WEBP (Max 2MB). Transparent background recommended.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. WhatsApp & Phone Contact Configuration -->
            <div class="pt-6 border-t border-slate-100 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">2. WhatsApp &amp; Showroom Desk</h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        WhatsApp Active
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- WhatsApp Number -->
                    <div>
                        <label for="whatsapp_number" class="block text-xs font-bold text-slate-800 mb-1.5">
                            Store WhatsApp Number <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="whatsapp_number" id="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number']) }}" required
                                   placeholder="0300-7769932"
                                   class="w-full pl-9 pr-3.5 py-2.5 text-xs font-mono font-bold text-emerald-700 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <span class="absolute left-3 top-2.5 text-emerald-600">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Used for floating AI/static chat desk and customer inquiry redirects.</p>
                    </div>

                    <!-- Direct Showroom Phone -->
                    <div>
                        <label for="contact_phone" class="block text-xs font-bold text-slate-800 mb-1.5">
                            Showroom Contact Phone <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}" required
                               placeholder="0300-7769932"
                               class="w-full px-3.5 py-2.5 text-xs font-mono rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Displayed on storefront footer and official invoices.</p>
                    </div>

                    <!-- Sales Rep / Manager Name -->
                    <div>
                        <label for="sales_rep_name" class="block text-xs font-bold text-slate-800 mb-1.5">
                            Lead Sales Representative / Manager
                        </label>
                        <input type="text" name="sales_rep_name" id="sales_rep_name" value="{{ old('sales_rep_name', $settings['sales_rep_name']) }}"
                               placeholder="e.g. Muhammad Aslam"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Contact Email -->
                    <div>
                        <label for="contact_email" class="block text-xs font-bold text-slate-800 mb-1.5">
                            Customer Support Email
                        </label>
                        <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}"
                               placeholder="info@deenelectronics.com"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Physical Showroom Address -->
                    <div class="sm:col-span-2">
                        <label for="store_address" class="block text-xs font-bold text-slate-800 mb-1.5">
                            Showroom Physical Address
                        </label>
                        <textarea name="store_address" id="store_address" rows="2"
                                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">{{ old('store_address', $settings['store_address']) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition-all flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save Store &amp; WhatsApp Settings</span>
                </button>
            </div>

        </div>
    </form>

</div>
@endsection
