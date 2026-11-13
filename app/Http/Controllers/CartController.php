<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Display the shopping cart page.
     */
    public function index(): View
    {
        $items = $this->cartService->getCart();
        $subtotal = $this->cartService->subtotal();
        $count = $this->cartService->count();
        $hasBulky = $this->cartService->hasBulkyItems();
        $whatsappOrderUrl = $this->cartService->generateWhatsAppOrderUrl();

        // Also suggest a few popular products if cart is empty
        $suggestedProducts = [];
        if (empty($items)) {
            $suggestedProducts = Product::with(['category', 'defaultVariant', 'primaryMedia'])
                ->active()
                ->where('is_featured', true)
                ->limit(4)
                ->get();
        }

        return view('storefront.cart', compact(
            'items',
            'subtotal',
            'count',
            'hasBulky',
            'whatsappOrderUrl',
            'suggestedProducts'
        ));
    }

    /**
     * Add a product/variant to the cart.
     */
    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity'   => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::with(['category', 'defaultVariant'])
            ->active()
            ->findOrFail($validated['product_id']);

        $variant = null;
        if (! empty($validated['variant_id'])) {
            $variant = ProductVariant::where('product_id', $product->id)
                ->where('is_active', true)
                ->findOrFail($validated['variant_id']);
        }

        $quantity = (int) ($validated['quantity'] ?? 1);

        $this->cartService->add($product, $variant, $quantity);

        $message = "{$product->name} added to your cart!";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success'       => true,
                'message'       => $message,
                'count'         => $this->cartService->count(),
                'subtotal'      => $this->cartService->subtotal(),
                'subtotal_formatted' => 'Rs ' . number_format($this->cartService->subtotal(), 2),
                'cart_url'      => route('cart.index'),
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update quantity of an item in the cart.
     */
    public function update(Request $request, string $key): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->cartService->update($key, (int) $validated['quantity']);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success'            => true,
                'message'            => 'Cart updated successfully.',
                'count'              => $this->cartService->count(),
                'subtotal'           => $this->cartService->subtotal(),
                'subtotal_formatted' => 'Rs ' . number_format($this->cartService->subtotal(), 2),
                'items'              => $this->cartService->getCart(),
            ]);
        }

        return redirect()->back()->with('success', 'Cart updated successfully.');
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(Request $request, string $key): JsonResponse|RedirectResponse
    {
        $this->cartService->remove($key);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success'            => true,
                'message'            => 'Item removed from cart.',
                'count'              => $this->cartService->count(),
                'subtotal'           => $this->cartService->subtotal(),
                'subtotal_formatted' => 'Rs ' . number_format($this->cartService->subtotal(), 2),
            ]);
        }

        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    /**
     * Clear the entire cart.
     */
    public function clear(): RedirectResponse
    {
        $this->cartService->clear();

        return redirect()->route('cart.index')->with('success', 'Cart has been cleared.');
    }
}
