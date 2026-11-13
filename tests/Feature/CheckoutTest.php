<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use Database\Seeders\StorefrontCategoriesSeeder;
use Database\Seeders\StorefrontDemoShowcaseSeeder;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StorefrontCategoriesSeeder::class);
        $this->seed(StorefrontDemoShowcaseSeeder::class);
    }

    public function test_empty_cart_redirects_to_cart_index_when_accessing_checkout(): void
    {
        $response = $this->get('/checkout');

        $response->assertStatus(302);
        $response->assertRedirect('/cart');
        $response->assertSessionHas('warning');
    }

    public function test_checkout_page_renders_with_items_and_form_when_cart_has_products(): void
    {
        $product = Product::active()->firstOrFail();
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $response = $this->get('/checkout');

        $response->assertStatus(200);
        $response->assertSee('Complete Your Order');
        $response->assertSee('Customer Contact Details');
        $response->assertSee('Delivery Address');
        $response->assertSee('Payment Method');
        $response->assertSee('Order Review');
        $response->assertSee($product->name);
        $response->assertSee('Confirm');
        $response->assertSee('Place Order');
    }

    public function test_checkout_validation_rules_require_necessary_fields(): void
    {
        $product = Product::active()->firstOrFail();
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $response = $this->post('/checkout', []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'customer_name',
            'customer_phone',
            'shipping_address_line1',
            'shipping_city',
            'shipping_province',
            'payment_method',
        ]);
    }

    public function test_valid_checkout_creates_order_and_order_items_and_clears_cart(): void
    {
        $product = Product::active()->firstOrFail();
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $cartService = app(CartService::class);
        $this->assertEquals(2, $cartService->count());
        $subtotal = $cartService->subtotal();

        $checkoutData = [
            'customer_name'          => 'Ahmed Khan',
            'customer_phone'         => '0300-1234567',
            'customer_email'         => 'ahmed@example.com',
            'shipping_address_line1' => 'House 42, Street 5, Model Town',
            'shipping_address_line2' => 'Near Central Park',
            'shipping_city'          => 'Lahore',
            'shipping_province'      => 'Punjab',
            'shipping_postal_code'   => '54700',
            'payment_method'         => 'COD',
            'delivery_instructions'  => 'Please call 1 hour before arrival.',
        ];

        $response = $this->post('/checkout', $checkoutData);

        $order = Order::where('customer_phone', '0300-1234567')->latest()->first();
        $this->assertNotNull($order);
        $this->assertStringStartsWith('DEEN-', $order->order_number);
        $this->assertEquals('Ahmed Khan', $order->customer_name);
        $this->assertEquals('Lahore', $order->shipping_city);
        $this->assertEquals('COD', $order->payment_method);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals($subtotal, (float) $order->subtotal);

        // Verify order items
        $this->assertCount(1, $order->items);
        $item = $order->items->first();
        $this->assertEquals($product->id, $item->product_id);
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals($subtotal, (float) $item->total_price);

        // Verify cart cleared
        $this->assertEquals(0, $cartService->count());

        // Verify redirect to success page
        $response->assertStatus(302);
        $response->assertRedirect('/checkout/success/' . $order->order_number);
    }

    public function test_checkout_supports_different_recipient_details(): void
    {
        $product = Product::active()->firstOrFail();
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $checkoutData = [
            'customer_name'           => 'Buyer Name',
            'customer_phone'          => '0311-1111111',
            'customer_email'          => 'buyer@example.com',
            'shipping_address_line1'  => 'Plot 99, Gulberg III',
            'shipping_city'           => 'Lahore',
            'shipping_province'       => 'Punjab',
            'payment_method'          => 'COD',
            'recipient_is_different'  => '1',
            'shipping_recipient_name' => 'Receiver Brother',
            'shipping_recipient_phone'=> '0322-2222222',
        ];

        $response = $this->post('/checkout', $checkoutData);

        $order = Order::where('customer_phone', '0311-1111111')->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals('Receiver Brother', $order->shipping_recipient_name);
        $this->assertEquals('0322-2222222', $order->shipping_recipient_phone);

        $response->assertRedirect('/checkout/success/' . $order->order_number);
    }

    public function test_order_success_page_renders_with_invoice_and_whatsapp_confirmation(): void
    {
        $product = Product::active()->firstOrFail();
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->post('/checkout', [
            'customer_name'          => 'Zainab Bibi',
            'customer_phone'         => '0333-3333333',
            'shipping_address_line1' => 'Flat 3B, Askari 11',
            'shipping_city'          => 'Lahore',
            'shipping_province'      => 'Punjab',
            'payment_method'         => 'COD',
        ]);

        $order = Order::where('customer_phone', '0333-3333333')->latest('id')->first();
        $response = $this->get('/checkout/success/' . $order->order_number);

        $response->assertStatus(200);
        $response->assertSee('Thank You, Zainab Bibi!');
        $response->assertSee($order->order_number);
        $response->assertSee('Instant WhatsApp Confirmation (0300-7769932)');
        $response->assertSee('Order Invoice Summary');
        $response->assertSee($product->name);
        $response->assertSee('Print Invoice');
    }

    public function test_order_success_page_renders_bank_transfer_advice_when_selected(): void
    {
        $product = Product::active()->firstOrFail();
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->post('/checkout', [
            'customer_name'          => 'Farhan Ali',
            'customer_phone'         => '0344-4444444',
            'shipping_address_line1' => 'Defence View, Phase 2',
            'shipping_city'          => 'Karachi',
            'shipping_province'      => 'Sindh',
            'payment_method'         => 'bank_transfer',
        ]);

        $order = Order::where('customer_phone', '0344-4444444')->latest('id')->first();
        $response = $this->get('/checkout/success/' . $order->order_number);

        $response->assertStatus(200);
        $response->assertSee('Showroom Bank Account Transfer Details');
        $response->assertSee('Meezan Bank Limited');
        $response->assertSee('Habib Bank Limited (HBL)');
    }

    public function test_order_success_page_renders_showroom_pickup_advice_when_selected(): void
    {
        $product = Product::active()->firstOrFail();
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->post('/checkout', [
            'customer_name'          => 'Usman Tariq',
            'customer_phone'         => '0300-5555555',
            'shipping_address_line1' => 'Manawan GT Road',
            'shipping_city'          => 'Lahore',
            'shipping_province'      => 'Punjab',
            'payment_method'         => 'showroom_pickup',
        ]);

        $order = Order::where('customer_phone', '0300-5555555')->latest('id')->first();
        $response = $this->get('/checkout/success/' . $order->order_number);

        $response->assertStatus(200);
        $response->assertSee('Showroom Counter Self-Collection');
        $response->assertSee('Main GT Road, Manawan, Bata Pur, Lahore');
    }
}
