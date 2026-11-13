@extends('layouts.admin')

@section('title', 'Payment Methods Management')

@section('content')
<div class="space-y-6">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
        <span>/</span>
        <span class="text-slate-800 font-bold">Payment Methods</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Payment Methods Management</h1>
            <p class="text-xs text-slate-500 mt-1">
                Configure Cash on Delivery, JazzCash, Easypaisa, Bank Transfer, and card gateways. Manage receiving numbers, instructions, and payment screenshot proof requirements.
            </p>
        </div>
        <a href="{{ route('admin.settings.store') }}" 
           class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors">
            <span>Store &amp; WhatsApp Settings</span>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    @if(session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center space-x-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Payment Methods Grid / Cards -->
    <div class="grid grid-cols-1 gap-4">
        @foreach($methods as $method)
            <div class="bg-white border {{ $method->is_enabled ? 'border-slate-200' : 'border-slate-200/60 bg-slate-50/50' }} rounded-3xl p-6 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-5 transition-all">
                
                <!-- Left: Method Identity & Details -->
                <div class="space-y-2 min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Icon representation -->
                        @if($method->code === 'jazzcash')
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-rose-600 text-white tracking-wider">JazzCash</span>
                        @elseif($method->code === 'easypaisa')
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-600 text-white tracking-wider">easypaisa</span>
                        @elseif($method->code === 'bank_transfer')
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-blue-700 text-white tracking-wider">IBFT Bank</span>
                        @elseif($method->code === 'payfast')
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-indigo-600 text-white tracking-wider">PAYFAST</span>
                        @else
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-slate-800 text-white tracking-wider">COD</span>
                        @endif

                        <h2 class="text-base font-bold text-slate-900">{{ $method->name }}</h2>

                        @if($method->is_enabled)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                Active on Checkout
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">
                                Disabled
                            </span>
                        @endif

                        @if($method->requires_proof)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800" title="Buyer must upload payment screenshot before order placement">
                                📸 Screenshot Proof Required
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500">{{ $method->subtitle }}</p>

                    <!-- Account / Receiving Information Snapshot -->
                    @if(!empty($method->account_number) || !empty($method->bank_name))
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                            @if(!empty($method->bank_name))
                                <div class="text-slate-700">
                                    <span class="text-slate-400">Bank:</span> <strong>{{ $method->bank_name }}</strong> ({{ $method->branch_name }})
                                </div>
                            @endif

                            @if(!empty($method->account_title))
                                <div class="text-slate-700">
                                    <span class="text-slate-400">Account Title:</span> <strong class="text-slate-900">{{ $method->account_title }}</strong>
                                </div>
                            @endif

                            @if(!empty($method->account_number))
                                <div class="text-slate-700 font-mono">
                                    <span class="text-slate-400 font-sans">Account / Mobile #:</span> <strong class="text-blue-700">{{ $method->account_number }}</strong>
                                </div>
                            @endif

                            @if(!empty($method->iban))
                                <div class="text-[11px] text-slate-600 font-mono">
                                    <span class="text-slate-400 font-sans">IBAN:</span> {{ $method->iban }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center space-x-2 shrink-0 pt-3 md:pt-0 border-t md:border-t-0 border-slate-100">
                    <!-- Toggle Status -->
                    <form method="POST" action="{{ route('admin.payment-methods.toggle', $method->id) }}">
                        @csrf
                        <button type="submit" 
                                class="px-3 py-2 rounded-xl text-xs font-bold transition-colors cursor-pointer {{ $method->is_enabled ? 'bg-slate-100 hover:bg-slate-200 text-slate-700' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200' }}">
                            {{ $method->is_enabled ? 'Disable' : 'Enable' }}
                        </button>
                    </form>

                    <!-- Edit Configuration -->
                    <a href="{{ route('admin.payment-methods.edit', $method->id) }}" 
                       class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center space-x-1.5 shadow-sm transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Configure Details</span>
                    </a>
                </div>

            </div>
        @endforeach
    </div>

</div>
@endsection
