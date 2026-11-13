<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #{{ $order->order_number }} — DEEN Electronics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 12px; }
            .print-border { border: 1px solid #e2e8f0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 antialiased">

    <!-- Actions Bar (Hidden on print) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('admin.orders.show', $order->id) }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 flex items-center space-x-1">
            <span>&larr; Back to Order Details</span>
        </a>

        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md transition-all flex items-center space-x-2 cursor-pointer">
                <svg class="w-4 h-4 fill-none stroke-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Invoice</span>
            </button>
        </div>
    </div>

    <!-- Printable Invoice Sheet -->
    <div class="max-w-4xl mx-auto bg-white rounded-3xl p-8 sm:p-12 shadow-sm border border-slate-200 space-y-8 print-border">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-8 gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <span class="w-10 h-10 rounded-xl bg-rose-600 flex items-center justify-center font-black text-xl text-white">D</span>
                    <div>
                        <div class="text-2xl font-black text-slate-900 tracking-tight leading-none">
                            DEEN <span class="text-rose-600">ELECTRONICS</span>
                        </div>
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest mt-1">
                            Appliances &amp; Electric Scooters
                        </div>
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-3 space-y-0.5 leading-relaxed">
                    <div>Muhammad Amin's Plaza, Main GT Road, near Shell Pump</div>
                    <div>Manawan, Bata Pur, Lahore, Pakistan</div>
                    <div>Sales Desk: <strong>0300-7769932</strong> · deenselectronics1@gmail.com</div>
                </div>
            </div>

            <div class="text-left sm:text-right space-y-1">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Official Retail Invoice</div>
                <div class="text-xl font-black font-mono text-slate-900">#{{ $order->order_number }}</div>
                <div class="text-xs text-slate-500">Date: <strong>{{ $order->created_at->format('F d, Y') }}</strong></div>
                <div class="text-xs text-slate-500">Status: <span class="font-bold text-slate-800 uppercase">{{ $order->status }}</span></div>
            </div>
        </div>

        <!-- Billing & Shipping Information -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 text-xs">
            <div>
                <div class="font-bold uppercase tracking-wider text-slate-400 text-[10px] mb-2">Customer &amp; Recipient</div>
                <div class="font-bold text-slate-900 text-sm">{{ $order->customer_name }}</div>
                <div class="text-slate-600 mt-1 space-y-0.5 font-mono">
                    <div>Phone: {{ $order->customer_phone }}</div>
                    @if($order->customer_email)
                        <div>Email: {{ $order->customer_email }}</div>
                    @endif
                </div>
            </div>

            <div>
                <div class="font-bold uppercase tracking-wider text-slate-400 text-[10px] mb-2">Delivery Address</div>
                <div class="font-semibold text-slate-800 leading-snug">
                    {{ $order->shipping_address_line1 }}
                    @if($order->shipping_address_line2)
                        , {{ $order->shipping_address_line2 }}
                    @endif
                </div>
                <div class="text-slate-600 text-[11px] mt-0.5">
                    {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code ?? '' }}
                </div>
                <div class="text-slate-500 text-[11px] mt-1">
                    Payment Method: <strong class="text-slate-800">{{ $order->payment_method_label }}</strong>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div>
            <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-200">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4">SKU</th>
                        <th class="py-3 px-4 text-right">Unit Price</th>
                        <th class="py-3 px-4 text-center">Qty</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($order->items as $idx => $item)
                        <tr>
                            <td class="py-3.5 px-4 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $item->product_name }}
                                @if($item->variant_name)
                                    <span class="text-xs text-slate-500 font-normal block">Variant: {{ $item->variant_name }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-500 text-[11px]">{{ $item->sku }}</td>
                            <td class="py-3.5 px-4 text-right font-medium">Rs {{ number_format($item->unit_price, 0) }}</td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-900">{{ $item->quantity }}</td>
                            <td class="py-3.5 px-4 text-right font-black text-slate-900">Rs {{ number_format($item->total_price, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals & Warranty Notice -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 pt-4 border-t border-slate-200 items-start">
            <div class="space-y-2 text-[11px] text-slate-500">
                <div class="font-bold text-slate-700">Official Warranty &amp; Terms:</div>
                <p class="leading-relaxed">
                    • All electrical appliances carry original manufacturer warranty (Haier, Dawlance, Super Asia, Kenwood, etc.).<br>
                    • Keep this invoice along with the warranty card for official service claims.<br>
                    • Queries &amp; Support WhatsApp: <strong>0300-7769932</strong>.
                </p>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100 text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-bold text-slate-900">Rs {{ number_format($order->subtotal, 0) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 text-slate-600">
                    <span>Delivery / Freight</span>
                    <span class="font-bold text-slate-900">
                        {{ $order->shipping_cost > 0 ? 'Rs ' . number_format($order->shipping_cost, 0) : 'Free Delivery' }}
                    </span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between py-1 border-b border-slate-100 text-rose-600">
                        <span>Discount ({{ $order->coupon_code }})</span>
                        <span class="font-bold">- Rs {{ number_format($order->discount_amount, 0) }}</span>
                    </div>
                @endif
                <div class="flex justify-between pt-2 items-baseline">
                    <span class="text-sm font-black text-slate-900">Total Payable</span>
                    <span class="text-xl font-black text-rose-600">Rs {{ number_format($order->total_amount, 0) }} PKR</span>
                </div>
            </div>
        </div>

        <!-- Footer Sign-off -->
        <div class="border-t border-slate-100 pt-6 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-400">
            <div>Authorized DEEN Electronics Lahore Sales Representative</div>
            <div class="font-mono mt-2 sm:mt-0">Generated: {{ now()->format('Y-m-d H:i:s') }}</div>
        </div>

    </div>

</body>
</html>
