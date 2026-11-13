<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryHistory;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductPriceHistory;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;
use App\Models\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    protected Admin $superAdmin;
    protected Admin $manager;
    protected Admin $editor;
    protected Category $category;
    protected Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.database' => 'deen_electronics_dev',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => 'root',
        ]);
        DB::purge('mysql');

        try {
            DB::connection('mysql')->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('MySQL connection not available: ' . $e->getMessage());
        }

        // Seed roles & permissions
        $this->seed(RolesAndPermissionsSeeder::class);

        // Provision test roles
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();
        $managerRole    = Role::where('name', 'Manager')->firstOrFail();
        $editorRole     = Role::where('name', 'Editor')->firstOrFail();

        $this->superAdmin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => 'test_cat_super_' . uniqid(),
            'email'     => 'test_cat_super_' . uniqid() . '@example.com',
            'password'  => Hash::make('AdminPass123!'),
            'is_active' => true,
        ]);

        $this->manager = Admin::create([
            'role_id'   => $managerRole->id,
            'username'  => 'test_cat_mgr_' . uniqid(),
            'email'     => 'test_cat_mgr_' . uniqid() . '@example.com',
            'password'  => Hash::make('ManagerPass123!'),
            'is_active' => true,
        ]);

        $this->editor = Admin::create([
            'role_id'   => $editorRole->id,
            'username'  => 'test_cat_ed_' . uniqid(),
            'email'     => 'test_cat_ed_' . uniqid() . '@example.com',
            'password'  => Hash::make('EditorPass123!'),
            'is_active' => true,
        ]);

        // Create test category and brand
        $this->category = Category::create([
            'name'      => 'Test Chargers ' . uniqid(),
            'slug'      => 'test-chargers-' . uniqid(),
            'is_active' => true,
        ]);

        $this->brand = Brand::create([
            'name'      => 'Test Brand ' . uniqid(),
            'slug'      => 'test-brand-' . uniqid(),
            'is_active' => true,
        ]);

        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        try {
            $conn = DB::connection('mysql');
            // Clean up child-first order
            $conn->table('product_media')->delete();
            $conn->table('product_specifications')->delete();
            $conn->table('product_price_history')->delete();
            $conn->table('inventory_histories')->delete();
            $conn->table('product_variants')->delete();
            $conn->table('products')->delete();
            $conn->table('brands')->where('name', 'LIKE', 'Test Brand%')->delete();
            $conn->table('categories')->where('name', 'LIKE', 'Test Chargers%')->delete();
            $conn->table('admins')->where('username', 'LIKE', 'test_cat_%')->delete();
        } catch (\Throwable $e) {
            // Ignore
        }

        parent::tearDown();
    }

    public function test_unauthenticated_user_cannot_access_product_catalog_routes(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.products.create'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.products.store'), [])->assertRedirect(route('admin.login'));
        $this->get(route('admin.inventory.index'))->assertRedirect(route('admin.login'));
    }

    public function test_editor_can_view_create_and_edit_but_cannot_archive_or_adjust_inventory(): void
    {
        $this->actingAs($this->editor, 'admin');

        // View catalog -> 200
        $this->get(route('admin.products.index'))->assertStatus(200);

        // View create form -> 200
        $this->get(route('admin.products.create'))->assertStatus(200);

        // Create a product as editor
        $sku = 'ED-PROD-' . uniqid();
        $response = $this->post(route('admin.products.store'), [
            'name'           => 'Editor Product ' . uniqid(),
            'slug'           => 'editor-product-' . uniqid(),
            'sku'            => $sku,
            'category_id'    => $this->category->id,
            'brand_id'       => $this->brand->id,
            'status'         => 'active',
            'price'          => '9500.00',
            'stock_quantity' => 15,
        ]);
        $response->assertStatus(302);

        $product = Product::where('sku', $sku)->firstOrFail();

        // View edit form -> 200
        $this->get(route('admin.products.edit', $product->id))->assertStatus(200);

        // Edit product -> 302
        $this->put(route('admin.products.update', $product->id), [
            'name'           => 'Editor Product Updated',
            'slug'           => $product->slug,
            'category_id'    => $this->category->id,
            'status'         => 'active',
            'price'          => '9800.00',
            'stock_quantity' => 15,
        ])->assertStatus(302);

        // Archive product -> 403 Forbidden (Editor does NOT have products.delete)
        $this->post(route('admin.products.archive', $product->id))->assertStatus(403);

        // Adjust inventory -> 403 Forbidden (Editor does NOT have inventory.adjust)
        $variant = $product->defaultVariant;
        $this->post(route('admin.inventory.adjust', $variant->id), [
            'change_qty' => 5,
            'reason'     => 'Restock',
        ])->assertStatus(403);
    }

    public function test_product_creation_with_automatic_default_variant_and_specs(): void
    {
        $this->actingAs($this->manager, 'admin');

        $sku = 'ANK-737-' . uniqid();
        $slug = 'anker-737-power-bank-' . uniqid();

        $response = $this->post(route('admin.products.store'), [
            'name'               => 'Anker 737 Power Bank (PowerCore 24K)',
            'slug'               => $slug,
            'sku'                => $sku,
            'category_id'        => $this->category->id,
            'brand_id'           => $this->brand->id,
            'badge_text'         => '140W Fast Charging',
            'short_description'  => 'Equipped with Power Delivery 3.1 and bi-directional technology.',
            'status'             => 'active',
            'is_featured'        => true,
            'price'              => '28500.00',
            'original_price'     => '32000.00',
            'stock_quantity'     => 40,
            'specifications'     => [
                ['spec_group' => 'Performance', 'spec_key' => 'Output Power', 'spec_value' => '140W Max'],
                ['spec_group' => 'Battery', 'spec_key' => 'Capacity', 'spec_value' => '24,000mAh'],
            ],
        ]);

        $product = Product::where('slug', $slug)->first();
        $this->assertNotNull($product);
        $response->assertRedirect(route('admin.products.edit', $product->id));

        $this->assertEquals($sku, $product->sku);
        $this->assertTrue($product->is_active);
        $this->assertTrue($product->is_featured);
        $this->assertEquals($this->category->id, $product->category_id);
        $this->assertEquals($this->brand->id, $product->brand_id);

        // Verify default variant was created atomically
        $defaultVariant = $product->defaultVariant;
        $this->assertNotNull($defaultVariant);
        $this->assertEquals($sku, $defaultVariant->sku);
        $this->assertEquals('28500.00', $defaultVariant->price);
        $this->assertEquals('32000.00', $defaultVariant->original_price);
        $this->assertEquals(40, $defaultVariant->stock_quantity);
        $this->assertTrue($defaultVariant->is_default);

        // Verify initial inventory history record
        $invHistory = InventoryHistory::where('product_variant_id', $defaultVariant->id)->first();
        $this->assertNotNull($invHistory);
        $this->assertEquals(40, $invHistory->change_qty);
        $this->assertEquals('Initial product creation', $invHistory->reason);

        // Verify specifications were created
        $specs = ProductSpecification::where('product_id', $product->id)->get();
        $this->assertCount(2, $specs);
        $this->assertEquals('Output Power', $specs->first()->spec_key);
        $this->assertEquals('140W Max', $specs->first()->spec_value);

        // Verify audit log
        $audit = DB::table('audit_logs')
            ->where('action', 'product.created')
            ->where('entity_id', $product->id)
            ->first();
        $this->assertNotNull($audit);
    }

    public function test_product_validation_and_duplicate_sku_rejection(): void
    {
        $this->actingAs($this->manager, 'admin');

        $sku = 'DUP-SKU-' . uniqid();
        $slug = 'first-product-' . uniqid();

        // 1. Create first product
        $this->post(route('admin.products.store'), [
            'name'           => 'First Product',
            'slug'           => $slug,
            'sku'            => $sku,
            'status'         => 'active',
            'price'          => '5000.00',
            'stock_quantity' => 10,
        ])->assertStatus(302);

        // 2. Attempting duplicate SKU must fail validation
        $responseDupSku = $this->post(route('admin.products.store'), [
            'name'           => 'Second Product Different Name',
            'slug'           => 'second-product-' . uniqid(),
            'sku'            => $sku, // duplicate
            'status'         => 'active',
            'price'          => '6000.00',
            'stock_quantity' => 10,
        ]);
        $responseDupSku->assertSessionHasErrors('sku');

        // 3. Attempting duplicate slug must fail validation
        $responseDupSlug = $this->post(route('admin.products.store'), [
            'name'           => 'Third Product',
            'slug'           => $slug, // duplicate
            'sku'            => 'UNIQUE-SKU-' . uniqid(),
            'status'         => 'active',
            'price'          => '7000.00',
            'stock_quantity' => 10,
        ]);
        $responseDupSlug->assertSessionHasErrors('slug');
    }

    public function test_product_update_and_price_history_recording(): void
    {
        $this->actingAs($this->manager, 'admin');

        $sku = 'PRICE-TEST-' . uniqid();
        $this->post(route('admin.products.store'), [
            'name'           => 'Price Track Product',
            'slug'           => 'price-track-' . uniqid(),
            'sku'            => $sku,
            'status'         => 'active',
            'price'          => '10000.00',
            'stock_quantity' => 20,
        ]);

        $product = Product::where('sku', $sku)->firstOrFail();
        $defaultVariant = $product->defaultVariant;

        // Update price from 10000 to 12500
        $this->put(route('admin.products.update', $product->id), [
            'name'           => 'Price Track Product Updated',
            'slug'           => $product->slug,
            'sku'            => $product->sku,
            'status'         => 'active',
            'price'          => '12500.00',
            'stock_quantity' => 20,
        ])->assertStatus(302);

        $defaultVariant->refresh();
        $this->assertEquals('12500.00', $defaultVariant->price);

        // Verify price history record was written
        $priceHistory = ProductPriceHistory::where('product_variant_id', $defaultVariant->id)->first();
        $this->assertNotNull($priceHistory);
        $this->assertEquals('10000.00', $priceHistory->old_price);
        $this->assertEquals('12500.00', $priceHistory->new_price);
        $this->assertEquals($this->manager->id, $priceHistory->changed_by_admin_id);
    }

    public function test_product_archival_and_restoration(): void
    {
        $this->actingAs($this->superAdmin, 'admin');

        $sku = 'ARCHIVE-TEST-' . uniqid();
        $this->post(route('admin.products.store'), [
            'name'           => 'To Be Archived',
            'slug'           => 'to-be-archived-' . uniqid(),
            'sku'            => $sku,
            'status'         => 'active',
            'price'          => '1500.00',
            'stock_quantity' => 5,
        ]);

        $product = Product::where('sku', $sku)->firstOrFail();
        $this->assertTrue($product->is_active);
        $this->assertNull($product->archived_at);

        // Archive product
        $this->post(route('admin.products.archive', $product->id))->assertStatus(302);

        $product->refresh();
        $this->assertFalse($product->is_active);
        $this->assertNotNull($product->archived_at);
        $this->assertTrue($product->is_archived);

        // Variant must also be marked archived
        $variant = $product->defaultVariant;
        $variant->refresh();
        $this->assertFalse($variant->is_active);
        $this->assertNotNull($variant->archived_at);

        // Unarchive / Restore product
        $this->post(route('admin.products.unarchive', $product->id))->assertStatus(302);

        $product->refresh();
        $this->assertNull($product->archived_at);
        $this->assertFalse($product->is_active, 'Restored product defaults to draft for review');
    }

    public function test_variant_management_and_default_variant_lifecycle(): void
    {
        $this->actingAs($this->manager, 'admin');

        $masterSku = 'MULTI-VAR-' . uniqid();
        $this->post(route('admin.products.store'), [
            'name'           => 'Multi Variant Product',
            'slug'           => 'multi-variant-' . uniqid(),
            'sku'            => $masterSku,
            'status'         => 'active',
            'price'          => '8000.00',
            'stock_quantity' => 10,
        ]);

        $product = Product::where('sku', $masterSku)->firstOrFail();
        $initialDefault = $product->defaultVariant;

        // 1. Add a second variant
        $secondSku = 'VAR-SECOND-' . uniqid();
        $response = $this->post(route('admin.products.variants.store', $product->id), [
            'name'           => 'Silver Edition',
            'sku'            => $secondSku,
            'price'          => '8500.00',
            'stock_quantity' => 25,
            'is_default'     => true, // Request to switch default
        ]);
        $response->assertStatus(302);

        $secondVariant = ProductVariant::where('sku', $secondSku)->firstOrFail();
        $this->assertTrue($secondVariant->is_default);

        $initialDefault->refresh();
        $this->assertFalse($initialDefault->is_default, 'Initial variant must have is_default cleared');

        // 2. Setting initial back to default
        $this->post(route('admin.products.variants.set-default', [$product->id, $initialDefault->id]))->assertStatus(302);

        $initialDefault->refresh();
        $secondVariant->refresh();
        $this->assertTrue($initialDefault->is_default);
        $this->assertFalse($secondVariant->is_default);

        // 3. Attempting to delete the default variant of an active product must be blocked
        $this->delete(route('admin.products.variants.destroy', [$product->id, $initialDefault->id]));
        $this->assertNotNull(ProductVariant::find($initialDefault->id), 'Default variant of active product cannot be deleted');

        // 4. Deleting non-default variant archives it to preserve historical inventory logs
        $this->delete(route('admin.products.variants.destroy', [$product->id, $secondVariant->id]))->assertStatus(302);
        $secondVariant->refresh();
        $this->assertTrue($secondVariant->is_archived);
        $this->assertFalse($secondVariant->is_active);
    }

    public function test_media_upload_validation_safe_naming_and_primary_selection(): void
    {
        $this->actingAs($this->manager, 'admin');

        $sku = 'MEDIA-PROD-' . uniqid();
        $this->post(route('admin.products.store'), [
            'name'           => 'Media Product',
            'slug'           => 'media-prod-' . uniqid(),
            'sku'            => $sku,
            'status'         => 'active',
            'price'          => '12000.00',
            'stock_quantity' => 5,
        ]);

        $product = Product::where('sku', $sku)->firstOrFail();

        // 1. Attempt upload of executable / non-image file must be rejected
        $badFile = UploadedFile::fake()->create('exploit.php', 100, 'application/x-php');
        $this->post(route('admin.products.media.upload', $product->id), [
            'file' => $badFile,
        ])->assertSessionHasErrors('file');

        // 2. Upload valid image
        $validImage = UploadedFile::fake()->image('user_supplied_name.jpg', 800, 800);
        $response = $this->post(route('admin.products.media.upload', $product->id), [
            'file'       => $validImage,
            'alt_text'   => 'High quality charger front view',
            'is_primary' => true,
        ]);
        $response->assertStatus(302);

        $media = ProductMedia::where('product_id', $product->id)->first();
        $this->assertNotNull($media);
        $this->assertTrue($media->is_primary);
        $this->assertEquals('High quality charger front view', $media->alt_text);

        // Verify filename is NOT the user-supplied filename (UUID generated)
        $this->assertStringNotContainsString('user_supplied_name', $media->url);

        // Verify product main_image was synchronized
        $product->refresh();
        $this->assertEquals($media->url, $product->main_image);

        // 3. Delete media and ensure orphan cleanup
        $this->delete(route('admin.products.media.destroy', [$product->id, $media->id]))->assertStatus(302);
        $this->assertNull(ProductMedia::find($media->id));

        $product->refresh();
        $this->assertNull($product->main_image);
    }

    public function test_dynamic_specifications_management(): void
    {
        $this->actingAs($this->manager, 'admin');

        $sku = 'SPEC-PROD-' . uniqid();
        $this->post(route('admin.products.store'), [
            'name'           => 'Spec Product',
            'slug'           => 'spec-prod-' . uniqid(),
            'sku'            => $sku,
            'status'         => 'active',
            'price'          => '4500.00',
            'stock_quantity' => 5,
        ]);

        $product = Product::where('sku', $sku)->firstOrFail();

        // Add specification
        $this->post(route('admin.products.specifications.store', $product->id), [
            'spec_group' => 'Connectivity',
            'spec_key'   => 'Bluetooth Version',
            'spec_value' => '5.3 BLE',
        ])->assertStatus(302);

        $spec = ProductSpecification::where('product_id', $product->id)
            ->where('spec_key', 'Bluetooth Version')
            ->first();
        $this->assertNotNull($spec);
        $this->assertEquals('5.3 BLE', $spec->spec_value);

        // Delete specification
        $this->delete(route('admin.products.specifications.destroy', [$product->id, $spec->id]))->assertStatus(302);
        $this->assertNull(ProductSpecification::find($spec->id));
    }

    public function test_inventory_adjustment_and_insufficient_stock_prevention(): void
    {
        $this->actingAs($this->manager, 'admin');

        $sku = 'INV-PROD-' . uniqid();
        $this->post(route('admin.products.store'), [
            'name'           => 'Inventory Test Product',
            'slug'           => 'inv-prod-' . uniqid(),
            'sku'            => $sku,
            'status'         => 'active',
            'price'          => '3000.00',
            'stock_quantity' => 20,
        ]);

        $product = Product::where('sku', $sku)->firstOrFail();
        $variant = $product->defaultVariant;

        // 1. Positive adjustment (restock)
        $this->post(route('admin.inventory.adjust', $variant->id), [
            'change_qty' => 15,
            'reason'     => 'Supplier Restock',
        ])->assertStatus(302);

        $variant->refresh();
        $this->assertEquals(35, $variant->stock_quantity);

        $history = InventoryHistory::where('product_variant_id', $variant->id)
            ->where('reason', 'Supplier Restock')
            ->first();
        $this->assertNotNull($history);
        $this->assertEquals(15, $history->change_qty);
        $this->assertEquals(20, $history->previous_qty);
        $this->assertEquals(35, $history->new_qty);

        // 2. Excessive negative adjustment that would violate non-negative stock constraint
        $this->post(route('admin.inventory.adjust', $variant->id), [
            'change_qty' => -50,
            'reason'     => 'Warehouse Loss',
        ])->assertSessionHasErrors('inventory');

        $variant->refresh();
        $this->assertEquals(35, $variant->stock_quantity, 'Stock quantity must remain unchanged after rejected adjustment');
    }
}
