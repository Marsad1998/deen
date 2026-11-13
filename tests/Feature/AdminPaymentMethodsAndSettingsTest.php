<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\StoreSetting;
use Database\Seeders\PaymentMethodsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StorefrontCategoriesSeeder;
use Database\Seeders\StorefrontDemoShowcaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPaymentMethodsAndSettingsTest extends TestCase
{
    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(StorefrontCategoriesSeeder::class);
        $this->seed(StorefrontDemoShowcaseSeeder::class);
        $this->seed(PaymentMethodsSeeder::class);

        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $this->admin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => 'settings_admin_' . uniqid(),
            'email'     => 'settings_admin_' . uniqid() . '@example.com',
            'password'  => Hash::make('SecretPass123!'),
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        if (isset($this->admin)) {
            AuditLog::where('admin_id', $this->admin->id)->delete();
            $this->admin->delete();
        }

        parent::tearDown();
    }

    public function test_admin_can_view_store_settings_page(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.settings.store'));

        $response->assertStatus(200);
        $response->assertSee('Store');
        $response->assertSee('WhatsApp');
        $response->assertSee('Store Logo');
        $response->assertSee('Store WhatsApp Number');
        $response->assertSee('Muhammad Aslam');
    }

    public function test_admin_can_update_store_settings(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.settings.store.update'), [
                'store_name'            => 'DEEN Electronics Showroom',
                'whatsapp_number'       => '0300-7769932',
                'contact_phone'         => '042-36581234',
                'sales_rep_name'        => 'Muhammad Aslam',
                'contact_email'         => 'showroom@deenelectronics.com',
                'store_address'         => 'Muhammad Amin Plaza, Main GT Road, Manawan, Bata Pur, Lahore',
                'customer_support_note' => 'Available 10 AM to 10 PM daily.',
            ]);

        $response->assertRedirect(route('admin.settings.store'));
        $response->assertSessionHas('success');

        $this->assertEquals('DEEN Electronics Showroom', StoreSetting::get('store_name'));
        $this->assertEquals('0300-7769932', StoreSetting::get('whatsapp_number'));
        $this->assertEquals('042-36581234', StoreSetting::get('contact_phone'));
        $this->assertEquals('Muhammad Aslam', StoreSetting::get('sales_rep_name'));
    }

    public function test_admin_can_upload_store_logo(): void
    {
        Storage::fake('public');

        $logoFile = UploadedFile::fake()->image('custom_store_logo.png', 400, 150);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.settings.store.update'), [
                'store_name'      => 'DEEN Electronics',
                'whatsapp_number' => '0300-7769932',
                'contact_phone'   => '0300-7769932',
                'store_logo_file' => $logoFile,
            ]);

        $response->assertRedirect(route('admin.settings.store'));
        $response->assertSessionHas('success');

        $savedLogo = StoreSetting::get('store_logo');
        $this->assertNotNull($savedLogo);
    }

    public function test_admin_can_view_payment_methods_index(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.payment-methods.index'));

        $response->assertStatus(200);
        $response->assertSee('Payment Methods');
        $response->assertSee('Cash on Delivery (COD)');
        $response->assertSee('JazzCash Mobile Account');
        $response->assertSee('Easypaisa Mobile Account');
        $response->assertSee('Direct Bank Transfer');
        $response->assertSee('PayFast Debit');
    }

    public function test_admin_can_toggle_payment_method_status(): void
    {
        $jazzcash = PaymentMethod::where('code', 'jazzcash')->firstOrFail();
        $this->assertTrue((bool) $jazzcash->is_enabled);

        // Toggle off
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.payment-methods.toggle', $jazzcash->id));

        $response->assertRedirect(route('admin.payment-methods.index'));
        $this->assertFalse((bool) $jazzcash->fresh()->is_enabled);

        // Toggle back on
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.payment-methods.toggle', $jazzcash->id));

        $response->assertRedirect(route('admin.payment-methods.index'));
        $this->assertTrue((bool) $jazzcash->fresh()->is_enabled);
    }

    public function test_admin_can_update_payment_method_details(): void
    {
        $bank = PaymentMethod::where('code', 'bank_transfer')->firstOrFail();

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.payment-methods.update', $bank->id), [
                'name'           => 'Meezan Bank Showroom Account',
                'subtitle'       => 'Instant IBFT & Counter Deposit',
                'is_enabled'     => 1,
                'requires_proof' => 1,
                'account_title'  => 'DEEN ELECTRONICS LAHORE',
                'account_number' => '0102-0999888777',
                'bank_name'      => 'Meezan Bank Limited',
                'branch_name'    => 'Manawan Branch, Lahore',
                'iban'           => 'PK76MEZN0001020999888777',
                'instructions'   => 'Transfer and upload receipt screenshot.',
                'display_order'  => 2,
            ]);

        $response->assertRedirect(route('admin.payment-methods.index'));
        $response->assertSessionHas('success');

        $updated = $bank->fresh();
        $this->assertEquals('Meezan Bank Showroom Account', $updated->name);
        $this->assertEquals('DEEN ELECTRONICS LAHORE', $updated->account_title);
        $this->assertEquals('PK76MEZN0001020999888777', $updated->iban);
        $this->assertTrue((bool) $updated->requires_proof);
    }

    public function test_checkout_requires_proof_when_payment_method_requires_proof(): void
    {
        $variant = ProductVariant::where('stock_quantity', '>', 5)->firstOrFail();

        // Place item in cart session
        $cart = [
            'item_' . $variant->id => [
                'key'           => 'item_' . $variant->id,
                'variant_id'    => $variant->id,
                'product_id'    => $variant->product_id,
                'name'          => 'Test Washing Machine',
                'sku'           => $variant->sku,
                'price'         => 80000,
                'quantity'      => 1,
                'is_bulky'      => false,
                'category_name' => 'Washing Machines',
                'thumbnail_url' => '/images/placeholder.svg',
            ],
        ];

        // Attempt checkout with JazzCash (which requires_proof = true) WITHOUT proof file
        $response = $this->withSession(['deen_storefront_cart' => $cart])
            ->post(route('checkout.store'), [
                'customer_name'          => 'Ahmad Raza',
                'customer_phone'         => '0300-1234567',
                'customer_email'         => 'ahmad@example.com',
                'shipping_address_line1' => 'House 12, Street 4, Manawan',
                'shipping_city'          => 'Lahore',
                'shipping_province'      => 'Punjab',
                'payment_method'         => 'jazzcash',
                // No payment_proof file attached!
            ]);

        $response->assertSessionHasErrors('payment_proof');
        $this->assertDatabaseMissing('orders', [
            'customer_name'  => 'Ahmad Raza',
            'payment_method' => 'jazzcash',
        ]);
    }

    public function test_checkout_succeeds_with_payment_proof_upload_and_stores_on_order(): void
    {
        Storage::fake('public');

        $variant = ProductVariant::where('stock_quantity', '>', 5)->firstOrFail();

        $cart = [
            'item_' . $variant->id => [
                'key'           => 'item_' . $variant->id,
                'variant_id'    => $variant->id,
                'product_id'    => $variant->product_id,
                'name'          => 'Test Washing Machine',
                'sku'           => $variant->sku,
                'price'         => 80000,
                'quantity'      => 1,
                'is_bulky'      => false,
                'category_name' => 'Washing Machines',
                'thumbnail_url' => '/images/placeholder.svg',
            ],
        ];

        $proofScreenshot = UploadedFile::fake()->image('jazzcash_receipt.jpg', 600, 1000);

        $response = $this->withSession(['deen_storefront_cart' => $cart])
            ->post(route('checkout.store'), [
                'customer_name'          => 'Bilal Hassan',
                'customer_phone'         => '0321-9876543',
                'customer_email'         => 'bilal@example.com',
                'shipping_address_line1' => 'House 45, Sector B, DHA Phase 5',
                'shipping_city'          => 'Lahore',
                'shipping_province'      => 'Punjab',
                'payment_method'         => 'jazzcash',
                'payment_proof'          => $proofScreenshot,
                'transaction_reference'  => 'TID-883719402',
            ]);

        $order = Order::where('customer_name', 'Bilal Hassan')->latest('id')->first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('checkout.success', $order->order_number));

        $this->assertNotNull($order->payment_proof_path);
        $this->assertEquals('TID-883719402', $order->transaction_reference);
        Storage::disk('public')->assertExists($order->payment_proof_path);

        // Test admin can view order show and see payment proof and TID
        $showResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.orders.show', $order->id));

        $showResponse->assertStatus(200);
        $showResponse->assertSee('Customer Payment Screenshot');
        $showResponse->assertSee('TID-883719402');
        $showResponse->assertSee($order->payment_proof_url);
    }
}
