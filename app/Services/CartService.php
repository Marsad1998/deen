<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected const SESSION_KEY = 'deen_storefront_cart';

    protected const BULKY_CATEGORIES = [
        'scooters',
        'furniture',
        'refrigerators-freezers',
        'air-conditioners',
        'washing-machines',
        'molded-foam-multiform',
    ];

    /**
     * Retrieve all items currently in the cart.
     *
     * @return array<string, array>
     */
    public function getCart(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    /**
     * Add a product/variant to the cart.
     */
    public function add(Product $product, ?ProductVariant $variant = null, int $quantity = 1): array
    {
        $quantity = max(1, $quantity);
        $cart = $this->getCart();

        // Determine active variant (passed, or default, or first active)
        if (! $variant) {
            $variant = $product->defaultVariant ?: $product->variants()->where('is_active', true)->first();
        }

        $variantId = $variant ? $variant->id : null;
        $itemKey = 'p_' . $product->id . ($variantId ? '_v_' . $variantId : '');

        $unitPrice = $variant ? (float) $variant->price : 0.0;
        $originalPrice = ($variant && $variant->original_price && $variant->original_price > $variant->price)
            ? (float) $variant->original_price
            : null;

        $sku = $variant?->sku ?: ($product->sku ?: 'DEEN-' . $product->id);
        $categorySlug = $product->category?->slug ?? '';
        $isBulky = in_array($categorySlug, self::BULKY_CATEGORIES, true);
        $maxStock = $variant ? (int) $variant->stock_quantity : 99;

        if (isset($cart[$itemKey])) {
            $newQuantity = $cart[$itemKey]['quantity'] + $quantity;
            if ($maxStock > 0) {
                $newQuantity = min($newQuantity, $maxStock);
            }
            $cart[$itemKey]['quantity'] = $newQuantity;
        } else {
            $initialQty = $maxStock > 0 ? min($quantity, $maxStock) : $quantity;
            $cart[$itemKey] = [
                'key'            => $itemKey,
                'product_id'     => $product->id,
                'variant_id'     => $variantId,
                'name'           => $product->name,
                'variant_name'   => $variant && $variant->name !== 'Standard' && $variant->name !== 'Default' ? $variant->name : null,
                'slug'           => $product->slug,
                'sku'            => $sku,
                'price'          => $unitPrice,
                'original_price' => $originalPrice,
                'quantity'       => $initialQty,
                'max_stock'      => $maxStock > 0 ? $maxStock : 99,
                'thumbnail_url'  => asset($product->thumbnail_url),
                'category_name'  => $product->category?->name ?? 'Electronics',
                'category_slug'  => $categorySlug,
                'is_bulky'       => $isBulky,
                'warranty_text'  => $product->warranty_text,
            ];
        }

        Session::put(self::SESSION_KEY, $cart);

        return $cart;
    }

    /**
     * Update quantity of a specific cart item.
     */
    public function update(string $key, int $quantity): array
    {
        $cart = $this->getCart();

        if (! isset($cart[$key])) {
            return $cart;
        }

        if ($quantity <= 0) {
            unset($cart[$key]);
        } else {
            $max = $cart[$key]['max_stock'] ?? 99;
            $cart[$key]['quantity'] = $max > 0 ? min($quantity, $max) : $quantity;
        }

        Session::put(self::SESSION_KEY, $cart);

        return $cart;
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(string $key): array
    {
        $cart = $this->getCart();

        if (isset($cart[$key])) {
            unset($cart[$key]);
            Session::put(self::SESSION_KEY, $cart);
        }

        return $cart;
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Get the total count of items in the cart.
     */
    public function count(): int
    {
        $cart = $this->getCart();

        return (int) array_sum(array_column($cart, 'quantity'));
    }

    /**
     * Calculate subtotal of all items.
     */
    public function subtotal(): float
    {
        $cart = $this->getCart();
        $total = 0.0;

        foreach ($cart as $item) {
            $total += ((float) $item['price']) * ((int) $item['quantity']);
        }

        return $total;
    }

    /**
     * Check if any item in the cart is classified as bulky freight.
     */
    public function hasBulkyItems(): bool
    {
        $cart = $this->getCart();

        foreach ($cart as $item) {
            if (! empty($item['is_bulky'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate an itemized pre-filled WhatsApp message URL for direct order booking.
     */
    public function generateWhatsAppOrderUrl(): string
    {
        $cart = $this->getCart();
        $subtotal = $this->subtotal();

        $lines = [];
        $lines[] = "Assalam o Alaikum DEEN Electronics,";
        $lines[] = "I would like to place an order from your website catalogue:";
        $lines[] = "";

        $idx = 1;
        foreach ($cart as $item) {
            $itemTotal = number_format($item['price'] * $item['quantity'], 0);
            $varText = ! empty($item['variant_name']) ? " ({$item['variant_name']})" : "";
            $lines[] = "{$idx}. {$item['name']}{$varText}";
            $lines[] = "   Qty: {$item['quantity']} × Rs " . number_format($item['price'], 0) . " = Rs {$itemTotal}";
            $lines[] = "   SKU: {$item['sku']}";
            $idx++;
        }

        $lines[] = "";
        $lines[] = "Estimated Total: Rs " . number_format($subtotal, 0);

        if ($this->hasBulkyItems()) {
            $lines[] = "*Note: Order includes large appliance / bulky item(s). Please confirm freight delivery serviceability.";
        }

        $lines[] = "";
        $lines[] = "Please confirm stock availability, delivery schedule, and issue invoice.";

        $message = implode("\n", $lines);

        return "https://wa.me/923007769932?text=" . urlencode($message);
    }
}
