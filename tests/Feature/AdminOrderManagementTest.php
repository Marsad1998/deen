<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\InventoryHistory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StorefrontCategoriesSeeder;
use Database\Seeders\StorefrontDemoShowcaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(StorefrontCategoriesSeeder::class);
        $this->seed(StorefrontDemoShowcaseSeeder::class);

        $this->cleanTestData();

        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $this->admin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => 'order_admin_' . uniqid(),
            'email'     => 'order_admin_' . uniqid() . '@example.com',
            'password'  => Hash::make('SecretPass123!'),
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        $this->cleanTestData();

        parent::tearDown();
    }

    protected function cleanTestData(): void
    {
        Admin::where('username', 'LIKE', 'order_admin_%')->delete();

        $testOrderIds = Order::where('order_number', 'LIKE', 'TEST-ORD-%')->pluck('id');
        if ($testOrderIds->isNotEmpty()) {
            DB::table('order_status_history')->whereIn('order_id', $testOrderIds)->delete();
            OrderItem::whereIn('order_id', $testOrderIds)->delete();
            Order::whereIn('id', $testOrderIds)->delete();
        }
    }

    protected function createTestOrder(array $attributes = [], ?ProductVariant $variant = null): Order
    {
        if (! $variant) {
            $product = Product::active()->first();
            $variant = $product?->variants()->first();
        } else {
            $product = $variant->product;
        }

        $order = Order::create(array_merge([
            'order_number'            => 'TEST-ORD-' . strtoupper(uniqid()),
            'customer_name'           => 'Tariq Mehmood',
            'customer_email'          => 'tariq.mehmood@example.com',
            'customer_phone'          => '03001234567',
            'shipping_recipient_name' => 'Tariq Mehmood',
            'shipping_recipient_phone'=> '03001234567',
            'shipping_address_line1'  => 'House # 14, Street 3, Cavalry Ground',
            'shipping_city'           => 'Lahore',
            'shipping_province'       => 'Punjab',
            'shipping_postal_code'    => '54000',
            'shipping_country'        => 'Pakistan',
            'delivery_instructions'   => 'Please deliver after 2 PM.',
            'status'                  => 'pending',
            'payment_method'          => 'COD',
            'payment_status'          => 'pending',
            'currency'                => 'PKR',
            'subtotal'                => 85000,
            'shipping_cost'           => 0,
            'discount_amount'         => 0,
            'total_amount'            => 85000,
            'notes'                   => 'New order placed via website checkout.',
        ], $attributes));

        OrderItem::create([
            'order_id'           => $order->id,
            'product_id'         => $product?->id,
            'product_variant_id' => $variant?->id,
            'product_name'       => $product?->name ?? 'Haier Inverter Refrigerator',
            'sku'                => $variant?->sku ?? 'TEST-SKU-01',
            'unit_price'         => 85000,
            'quantity'           => 1,
            'total_price'        => 85000,
        ]);

        return $order;
    }

    public function test_unauthenticated_user_cannot_access_admin_orders(): void
    {
        $response = $this->get('/admin/orders');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_orders_index_with_status_counters(): void
    {
        $order = $this->createTestOrder(['status' => 'pending']);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertSee('Customer Orders');
        $response->assertSee($order->order_number);
        $response->assertSee('Tariq Mehmood');
        $response->assertSee('03001234567');
        $response->assertSee('Pending Verification');
        $response->assertSee('Rs 85,000');
    }

    public function test_admin_can_filter_orders_by_search_query_and_status(): void
    {
        $order1 = $this->createTestOrder([
            'customer_name' => 'Usman Mukhtar',
            'customer_phone'=> '03219876543',
            'status'        => 'pending',
        ]);

        $order2 = $this->createTestOrder([
            'customer_name' => 'Bilal Khan',
            'customer_phone'=> '03451122334',
            'status'        => 'delivered',
        ]);

        // Search by phone
        $searchResponse = $this->actingAs($this->admin, 'admin')->get('/admin/orders?q=03219876543');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee($order1->order_number);
        $searchResponse->assertDontSee($order2->order_number);

        // Filter by status = delivered
        $statusResponse = $this->actingAs($this->admin, 'admin')->get('/admin/orders?status=delivered');
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee($order2->order_number);
        $statusResponse->assertDontSee($order1->order_number);
    }

    public function test_admin_can_view_comprehensive_order_details(): void
    {
        $order = $this->createTestOrder();

        $response = $this->actingAs($this->admin, 'admin')->get("/admin/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee('Tariq Mehmood');
        $response->assertSee('House # 14, Street 3, Cavalry Ground');
        $response->assertSee('Please deliver after 2 PM.');
        $response->assertSee($order->items->first()->sku);
        $response->assertSee('Rs 85,000');
        $response->assertSee('Update Fulfillment');
        $response->assertSee('WhatsApp Customer');
        $response->assertSee('Print Invoice');
    }

    public function test_admin_can_update_order_status_and_audit_trail_is_recorded(): void
    {
        $order = $this->createTestOrder(['status' => 'pending', 'payment_status' => 'pending']);

        $payload = [
            'status'         => 'confirmed',
            'payment_status' => 'paid',
            'notes'          => 'Showroom phone verification complete. Stock reserved in warehouse.',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post("/admin/orders/{$order->id}/status", $payload);

        $response->assertRedirect(route('admin.orders.show', $order->id));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('Showroom phone verification complete. Stock reserved in warehouse.', $order->notes);

        // Verify Audit Log
        $auditLog = AuditLog::where('entity', 'Order')
            ->where('entity_id', $order->id)
            ->where('action', 'order.status_updated')
            ->first();

        $this->assertNotNull($auditLog, 'Audit log entry should be created on order status update');
        $this->assertEquals('pending', $auditLog->changes['previous_status']);
        $this->assertEquals('confirmed', $auditLog->changes['new_status']);
    }

    public function test_admin_can_render_official_printable_invoice(): void
    {
        $order = $this->createTestOrder();

        $response = $this->actingAs($this->admin, 'admin')->get("/admin/orders/{$order->id}/invoice");

        $response->assertStatus(200);
        $response->assertSee('DEEN');
        $response->assertSee('ELECTRONICS');
        $response->assertSee('Official Retail Invoice');
        $response->assertSee($order->order_number);
        $response->assertSee($order->customer_name);
        $response->assertSee($order->items->first()->sku);
        $response->assertSee('Rs 85,000');
        $response->assertSee('0300-7769932');
    }

    public function test_dashboard_displays_real_order_statistics_and_recent_order(): void
    {
        $order = $this->createTestOrder(['status' => 'pending', 'total_amount' => 85000]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Total Orders');
        $response->assertSee('Pending Verification');
        $response->assertSee('Gross Sales Volume');
        $response->assertSee('Recent Customer Orders');
        $response->assertSee($order->order_number);
        $response->assertSee($order->customer_name);
    }

    public function test_dispatching_order_deducts_inventory_and_logs_history(): void
    {
        $variant = ProductVariant::firstOrFail();
        $variant->update(['stock_quantity' => 25, 'stock_status' => 'in_stock']);

        $order = $this->createTestOrder(['status' => 'pending'], $variant);

        $payload = [
            'status'         => 'shipped',
            'payment_status' => 'pending',
            'notes'          => 'Dispatched via Trax Logistics tracking #TRAX-99881.',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post("/admin/orders/{$order->id}/status", $payload);

        $response->assertRedirect(route('admin.orders.show', $order->id));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('shipped', $order->status);
        $this->assertNotNull($order->dispatched_at, 'dispatched_at should be recorded');
        $this->assertNotNull($order->inventory_deducted_at, 'inventory_deducted_at should be recorded');

        $variant->refresh();
        $this->assertEquals(24, $variant->stock_quantity, 'Stock should be decremented by ordered item quantity (1)');

        $history = InventoryHistory::where('product_variant_id', $variant->id)->latest('id')->first();
        $this->assertNotNull($history);
        $this->assertEquals(-1, $history->change_qty);
        $this->assertEquals(25, $history->previous_qty);
        $this->assertEquals(24, $history->new_qty);
        $this->assertStringContainsString($order->order_number, $history->reason);
    }

    public function test_subsequent_status_update_does_not_deduct_inventory_again(): void
    {
        $variant = ProductVariant::firstOrFail();
        $variant->update(['stock_quantity' => 20, 'stock_status' => 'in_stock']);

        $order = $this->createTestOrder(['status' => 'pending'], $variant);

        // Step 1: Dispatch order
        $this->actingAs($this->admin, 'admin')
            ->post("/admin/orders/{$order->id}/status", [
                'status'         => 'shipped',
                'payment_status' => 'pending',
            ]);

        $variant->refresh();
        $this->assertEquals(19, $variant->stock_quantity);

        // Step 2: Mark as Delivered
        $this->actingAs($this->admin, 'admin')
            ->post("/admin/orders/{$order->id}/status", [
                'status'         => 'delivered',
                'payment_status' => 'paid',
                'notes'          => 'Customer confirmed receipt.',
            ]);

        $variant->refresh();
        $this->assertEquals(19, $variant->stock_quantity, 'Stock should NOT be deducted a second time');
    }

    public function test_cancelling_dispatched_order_restores_inventory(): void
    {
        $variant = ProductVariant::firstOrFail();
        $variant->update(['stock_quantity' => 15, 'stock_status' => 'in_stock']);

        $order = $this->createTestOrder(['status' => 'pending'], $variant);

        // Dispatch order
        $this->actingAs($this->admin, 'admin')
            ->post("/admin/orders/{$order->id}/status", [
                'status'         => 'shipped',
                'payment_status' => 'pending',
            ]);

        $variant->refresh();
        $this->assertEquals(14, $variant->stock_quantity);
        $order->refresh();
        $this->assertNotNull($order->inventory_deducted_at);

        // Cancel order
        $this->actingAs($this->admin, 'admin')
            ->post("/admin/orders/{$order->id}/status", [
                'status'         => 'cancelled',
                'payment_status' => 'failed',
                'notes'          => 'Customer rejected delivery at doorstep; returned to warehouse.',
            ]);

        $variant->refresh();
        $this->assertEquals(15, $variant->stock_quantity, 'Stock should be restored after cancellation of dispatched order');

        $order->refresh();
        $this->assertNull($order->inventory_deducted_at, 'inventory_deducted_at should be cleared upon restoration');

        $history = InventoryHistory::where('product_variant_id', $variant->id)->latest('id')->first();
        $this->assertNotNull($history);
        $this->assertEquals(1, $history->change_qty);
        $this->assertStringContainsString('cancelled', $history->reason);
    }

    public function test_dispatching_order_fails_gracefully_when_stock_is_insufficient(): void
    {
        $variant = ProductVariant::firstOrFail();
        $variant->update(['stock_quantity' => 0, 'stock_status' => 'out_of_stock']);

        $order = $this->createTestOrder(['status' => 'pending'], $variant);

        $response = $this->actingAs($this->admin, 'admin')
            ->post("/admin/orders/{$order->id}/status", [
                'status'         => 'shipped',
                'payment_status' => 'pending',
            ]);

        $response->assertRedirect(route('admin.orders.show', $order->id));
        $response->assertSessionHasErrors(['inventory']);

        $order->refresh();
        $this->assertEquals('pending', $order->status, 'Order status should NOT change when inventory is insufficient');
        $this->assertNull($order->inventory_deducted_at);

        $variant->refresh();
        $this->assertEquals(0, $variant->stock_quantity);
    }
}
