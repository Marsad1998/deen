<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Database\Seeders\StorefrontCategoriesSeeder;
use Database\Seeders\StorefrontDemoShowcaseSeeder;
use Tests\TestCase;

class CartTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StorefrontCategoriesSeeder::class);
        $this->seed(StorefrontDemoShowcaseSeeder::class);
    }

    public function test_empty_cart_page_renders_with_friendly_guidance(): void
    {
        $response = $this->get('/cart');

        $response->assertStatus(200);
        $response->assertSee('Shopping Cart');
        $response->assertSee('Your Cart is Currently Empty');
        $response->assertSee('Browse Product Catalogue');
        $response->assertSee('Popular Categories');
    }

    public function test_product_can_be_added_to_cart_and_redirects_with_success_flash(): void
    {
        $product = Product::active()->firstOrFail();

        $response = $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Check Cart contents
        $cartService = app(CartService::class);
        $this->assertEquals(2, $cartService->count());
        $this->assertGreaterThan(0, $cartService->subtotal());

        // Check Cart page rendering with populated items
        $cartResponse = $this->get('/cart');
        $cartResponse->assertStatus(200);
        $cartResponse->assertSee($product->name);
        $cartResponse->assertSee('Proceed to WhatsApp Checkout');
        $cartResponse->assertSee('Order Summary');
        $cartResponse->assertSee('Estimated Total');
    }

    public function test_product_add_via_ajax_returns_json_response(): void
    {
        $product = Product::active()->firstOrFail();

        $response = $this->postJson('/cart/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'count',
            'subtotal',
            'subtotal_formatted',
            'cart_url',
        ]);
    }

    public function test_cart_item_quantity_can_be_updated(): void
    {
        $product = Product::active()->firstOrFail();
        $cartService = app(CartService::class);
        $cartService->add($product, null, 1);

        $cart = $cartService->getCart();
        $itemKey = array_key_first($cart);

        // Update quantity to 4
        $response = $this->post("/cart/update/{$itemKey}", [
            'quantity' => 4,
        ]);

        $response->assertStatus(302);
        $this->assertEquals(4, $cartService->count());
    }

    public function test_cart_item_can_be_removed(): void
    {
        $product = Product::active()->firstOrFail();
        $cartService = app(CartService::class);
        $cartService->add($product, null, 2);

        $cart = $cartService->getCart();
        $itemKey = array_key_first($cart);

        $response = $this->delete("/cart/remove/{$itemKey}");

        $response->assertStatus(302);
        $this->assertEquals(0, $cartService->count());
    }

    public function test_cart_can_be_cleared_completely(): void
    {
        $products = Product::active()->limit(2)->get();
        $cartService = app(CartService::class);

        foreach ($products as $p) {
            $cartService->add($p, null, 1);
        }

        $this->assertGreaterThanOrEqual(2, $cartService->count());

        $response = $this->post('/cart/clear');
        $response->assertRedirect('/cart');
        $this->assertEquals(0, $cartService->count());
    }

    public function test_bulky_items_trigger_freight_advisory_in_cart(): void
    {
        // Scooter is a bulky product
        $scooterCat = Category::where('slug', 'scooters')->firstOrFail();
        $scooterProduct = Product::where('category_id', $scooterCat->id)->active()->firstOrFail();

        $cartService = app(CartService::class);
        $cartService->clear();
        $cartService->add($scooterProduct, null, 1);

        $this->assertTrue($cartService->hasBulkyItems());

        $response = $this->get('/cart');
        $response->assertStatus(200);
        $response->assertSee('bulky freight', false);
        $response->assertSee('Freight confirmed via WhatsApp');
    }

    public function test_product_detail_page_has_add_to_cart_form_and_quantity_stepper(): void
    {
        $product = Product::active()->firstOrFail();

        $response = $this->get('/product/' . $product->slug);

        $response->assertStatus(200);
        $response->assertSee('Add to Cart');
        $response->assertSee('action="' . route('cart.add') . '"', false);
        $response->assertSee('name="quantity"', false);
        $response->assertSee('Enquire & Order on WhatsApp', false);
        $response->assertSee('Technical Specifications');
    }
}
