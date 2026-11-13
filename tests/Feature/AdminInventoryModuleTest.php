<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\InventoryHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StorefrontCategoriesSeeder;
use Database\Seeders\StorefrontDemoShowcaseSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminInventoryModuleTest extends TestCase
{
    protected Admin $superAdmin;
    protected Admin $editor;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(StorefrontCategoriesSeeder::class);
        $this->seed(StorefrontDemoShowcaseSeeder::class);

        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();
        $editorRole = Role::where('name', 'Editor')->firstOrFail();

        Admin::where('username', 'LIKE', 'inv_admin_%')->delete();

        $this->superAdmin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => 'inv_admin_' . uniqid(),
            'email'     => 'inv_admin_' . uniqid() . '@deen.test',
            'password'  => Hash::make('AdminPass123!'),
            'is_active' => true,
        ]);

        $this->editor = Admin::create([
            'role_id'   => $editorRole->id,
            'username'  => 'inv_admin_editor_' . uniqid(),
            'email'     => 'inv_admin_editor_' . uniqid() . '@deen.test',
            'password'  => Hash::make('AdminPass123!'),
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::firstOrFail();
        $this->variant->update(['stock_quantity' => 10, 'stock_status' => 'in_stock']);
    }

    protected function tearDown(): void
    {
        Admin::where('username', 'LIKE', 'inv_admin_%')->delete();

        parent::tearDown();
    }

    public function test_unauthenticated_user_cannot_access_add_inventory(): void
    {
        $this->get(route('admin.inventory.create'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.inventory.store'), [])->assertRedirect(route('admin.login'));
    }

    public function test_editor_without_inventory_adjust_permission_is_forbidden(): void
    {
        $this->actingAs($this->editor, 'admin')
            ->get(route('admin.inventory.create'))
            ->assertStatus(403);

        $this->actingAs($this->editor, 'admin')
            ->post(route('admin.inventory.store'), [
                'product_variant_id' => $this->variant->id,
                'quantity'           => 10,
                'inward_type'        => 'Supplier Restock Shipment',
            ])
            ->assertStatus(403);
    }

    public function test_admin_can_view_add_inventory_page_with_preselection(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->get(route('admin.inventory.create', ['variant_id' => $this->variant->id]));

        $response->assertStatus(200);
        $response->assertSee('Add Warehouse Inventory');
        $response->assertSee('Product Variant &amp; Receiving Units', false);
        $response->assertSee($this->variant->sku);
        $response->assertSee('Supplier Restock Shipment');
        $response->assertSee('Projected Stock Level');
    }

    public function test_admin_can_successfully_add_inventory_and_logs_history(): void
    {
        $initialStock = $this->variant->stock_quantity; // 10

        $payload = [
            'product_variant_id' => $this->variant->id,
            'quantity'           => 15,
            'inward_type'        => 'Supplier Restock Shipment',
            'supplier_name'      => 'Haier Pakistan Factory',
            'reference_number'   => 'PO-2026-9041',
            'notes'              => 'Received at Lahore showroom bay 2.',
        ];

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.inventory.store'), $payload);

        $response->assertRedirect(route('admin.inventory.index'));
        $response->assertSessionHas('status');

        $this->variant->refresh();
        $this->assertEquals($initialStock + 15, $this->variant->stock_quantity, 'Stock should increase by 15 units');

        // Check InventoryHistory
        $history = InventoryHistory::where('product_variant_id', $this->variant->id)->latest('id')->first();
        $this->assertNotNull($history);
        $this->assertEquals(15, $history->change_qty);
        $this->assertEquals($initialStock, $history->previous_qty);
        $this->assertEquals($initialStock + 15, $history->new_qty);
        $this->assertStringContainsString('PO-2026-9041', $history->reason);
        $this->assertStringContainsString('Haier Pakistan', $history->reason);

        // Check AuditLog
        $auditLog = AuditLog::where('action', 'inventory.received')
            ->where('entity_id', $this->variant->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($auditLog);
        $this->assertEquals(15, $auditLog->changes['quantity_added']);
    }

    public function test_add_inventory_validation_rules(): void
    {
        // 1. Missing required fields
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.inventory.store'), []);

        $response->assertSessionHasErrors(['product_variant_id', 'quantity', 'inward_type']);

        // 2. Quantity zero or negative
        $responseZero = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.inventory.store'), [
                'product_variant_id' => $this->variant->id,
                'quantity'           => 0,
                'inward_type'        => 'Supplier Restock Shipment',
            ]);

        $responseZero->assertSessionHasErrors(['quantity']);

        // 3. Invalid variant id
        $responseInvalid = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.inventory.store'), [
                'product_variant_id' => 9999999,
                'quantity'           => 10,
                'inward_type'        => 'Supplier Restock Shipment',
            ]);

        $responseInvalid->assertSessionHasErrors(['product_variant_id']);
    }

    public function test_inventory_index_displays_add_inventory_buttons_and_movements(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->get(route('admin.inventory.index'));

        $response->assertStatus(200);
        $response->assertSee('Add / Receive Inventory');
        $response->assertSee('+ Add Stock');
        $response->assertSee('Recent Stock Movements');
    }
}
