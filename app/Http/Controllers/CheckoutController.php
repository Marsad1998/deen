<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Show the checkout page.
     */
    public function index(CartService $cartService): View|RedirectResponse
    {
        if ($cartService->count() === 0) {
            return redirect()->route('cart.index')
                ->with('warning', 'Your shopping cart is currently empty. Please add products before checking out.');
        }

        $cart = $cartService->getCart();
        $subtotal = $cartService->subtotal();
        $count = $cartService->count();
        $hasBulky = $cartService->hasBulkyItems();

        $provinces = [
            'Punjab',
            'Sindh',
            'Khyber Pakhtunkhwa',
            'Balochistan',
            'Islamabad Capital Territory',
            'Gilgit-Baltistan',
            'Azad Jammu & Kashmir',
        ];

        $paymentMethods = PaymentMethod::active()->get();

        return view('storefront.checkout', compact('cart', 'subtotal', 'count', 'hasBulky', 'provinces', 'paymentMethods'));
    }

    /**
     * Process checkout form and store order.
     */
    public function store(Request $request, CartService $cartService): RedirectResponse
    {
        if ($cartService->count() === 0) {
            return redirect()->route('cart.index')
                ->with('warning', 'Your shopping cart is empty.');
        }

        $validated = $request->validate([
            'customer_name'           => ['required', 'string', 'min:2', 'max:150'],
            'customer_phone'          => ['required', 'string', 'min:10', 'max:25'],
            'customer_email'          => ['nullable', 'email', 'max:150'],
            'shipping_address_line1'  => ['required', 'string', 'min:5', 'max:255'],
            'shipping_address_line2'  => ['nullable', 'string', 'max:255'],
            'shipping_city'           => ['required', 'string', 'min:2', 'max:100'],
            'shipping_province'       => ['required', 'string', 'in:Punjab,Sindh,Khyber Pakhtunkhwa,Balochistan,Islamabad Capital Territory,Gilgit-Baltistan,Azad Jammu & Kashmir'],
            'shipping_postal_code'    => ['nullable', 'string', 'max:20'],
            'payment_method'          => ['required', 'string'],
            'delivery_instructions'   => ['nullable', 'string', 'max:1000'],
            'recipient_is_different'  => ['nullable', 'boolean'],
            'shipping_recipient_name' => ['nullable', 'string', 'max:150'],
            'shipping_recipient_phone'=> ['nullable', 'string', 'max:25'],
            'payment_proof'           => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'transaction_reference'   => ['nullable', 'string', 'max:100'],
        ]);

        // Enforce payment proof upload if selected method requires it
        $selectedMethod = PaymentMethod::where('code', $validated['payment_method'])->first();
        if ($selectedMethod && $selectedMethod->requires_proof && ! $request->hasFile('payment_proof')) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['payment_proof' => "Please attach your payment screenshot or transfer receipt for {$selectedMethod->name} before placing your order."]);
        }

        // Store payment proof screenshot if uploaded
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $filename = 'proof_' . date('Ymd_His') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $paymentProofPath = $file->storeAs('payment_proofs', $filename, 'public');
        }

        $recipientName = ! empty($validated['recipient_is_different']) && ! empty($validated['shipping_recipient_name'])
            ? $validated['shipping_recipient_name']
            : $validated['customer_name'];

        $recipientPhone = ! empty($validated['recipient_is_different']) && ! empty($validated['shipping_recipient_phone'])
            ? $validated['shipping_recipient_phone']
            : $validated['customer_phone'];

        $cart = $cartService->getCart();
        $subtotal = $cartService->subtotal();
        $shippingCost = 0.00; // Free / calculated per showroom advisory
        $discountAmount = 0.00;
        $taxAmount = 0.00;
        $totalAmount = $subtotal + $shippingCost - $discountAmount + $taxAmount;

        // Generate guaranteed unique order number: DEEN-YYYYMMDD-XXXX
        do {
            $orderNumber = 'DEEN-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Order::where('order_number', $orderNumber)->exists());

        $order = DB::transaction(function () use (
            $validated,
            $recipientName,
            $recipientPhone,
            $orderNumber,
            $subtotal,
            $shippingCost,
            $discountAmount,
            $taxAmount,
            $totalAmount,
            $paymentProofPath,
            $cart,
            $cartService
        ) {
            $order = Order::create([
                'order_number'             => $orderNumber,
                'customer_id'              => null,
                'customer_name'            => $validated['customer_name'],
                'customer_email'           => $validated['customer_email'] ?? null,
                'customer_phone'           => $validated['customer_phone'],
                'shipping_recipient_name'  => $recipientName,
                'shipping_recipient_phone' => $recipientPhone,
                'shipping_address_line1'   => $validated['shipping_address_line1'],
                'shipping_address_line2'   => $validated['shipping_address_line2'] ?? null,
                'shipping_city'            => $validated['shipping_city'],
                'shipping_province'        => $validated['shipping_province'],
                'shipping_postal_code'     => $validated['shipping_postal_code'] ?? null,
                'shipping_country'         => 'Pakistan',
                'delivery_instructions'    => $validated['delivery_instructions'] ?? null,
                'status'                   => 'pending',
                'payment_method'           => $validated['payment_method'],
                'payment_status'           => 'pending',
                'payment_proof_path'       => $paymentProofPath,
                'transaction_reference'    => $validated['transaction_reference'] ?? null,
                'currency'                 => 'PKR',
                'subtotal'                 => $subtotal,
                'discount_amount'          => $discountAmount,
                'shipping_cost'            => $shippingCost,
                'tax_amount'               => $taxAmount,
                'total_amount'             => $totalAmount,
                'notes'                    => null,
            ]);

            foreach ($cart as $item) {
                $variantId = $item['variant_id'] ?? null;
                $productId = $item['product_id'] ?? null;

                if (! $variantId && ! empty($item['sku'])) {
                    $foundVariant = ProductVariant::where('sku', $item['sku'])->first();
                    if ($foundVariant) {
                        $variantId = $foundVariant->id;
                        $productId = $productId ?? $foundVariant->product_id;
                    }
                }

                OrderItem::create([
                    'order_id'            => $order->id,
                    'product_id'          => $productId,
                    'product_variant_id'  => $variantId,
                    'product_name'        => $item['name'],
                    'variant_name'        => $item['variant_name'] ?? null,
                    'sku'                 => $item['sku'] ?? 'DEEN-SKU',
                    'unit_price'          => $item['price'],
                    'quantity'            => $item['quantity'],
                    'total_price'         => $item['price'] * $item['quantity'],
                    'attributes_snapshot' => [
                        'category_name'  => $item['category_name'] ?? null,
                        'category_slug'  => $item['category_slug'] ?? null,
                        'is_bulky'       => $item['is_bulky'] ?? false,
                        'warranty_text'  => $item['warranty_text'] ?? null,
                        'thumbnail_url'  => $item['thumbnail_url'] ?? null,
                    ],
                ]);
            }

            $cartService->clear();

            return $order;
        });

        return redirect()->route('checkout.success', $order->order_number)
            ->with('success', 'Your order has been placed successfully! Our showroom team will contact you shortly to confirm delivery.');
    }

    /**
     * Show order confirmation and invoice page.
     */
    public function success(string $orderNumber): View
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();

        $lines = [];
        $lines[] = "Assalam o Alaikum DEEN Electronics,";
        $lines[] = "I placed Order #{$order->order_number} on your website.";
        $lines[] = "Customer: {$order->customer_name} ({$order->customer_phone})";
        $lines[] = "Delivery City: {$order->shipping_city}, {$order->shipping_province}";
        $lines[] = "Payment Method: {$order->payment_method_label}";
        $lines[] = "Total Amount: Rs " . number_format($order->total_amount, 0);
        $lines[] = "";
        $lines[] = "Items Ordered:";

        foreach ($order->items as $idx => $item) {
            $num = $idx + 1;
            $var = $item->variant_name ? " ({$item->variant_name})" : "";
            $lines[] = "{$num}. {$item->product_name}{$var} - Qty: {$item->quantity} - Rs " . number_format($item->total_price, 0);
        }

        $lines[] = "";
        $lines[] = "Please confirm order receipt, stock allocation, and dispatch schedule.";

        $whatsappConfirmationUrl = 'https://wa.me/923007769932?text=' . urlencode(implode("\n", $lines));

        return view('storefront.order-success', compact('order', 'whatsappConfirmationUrl'));
    }
}
