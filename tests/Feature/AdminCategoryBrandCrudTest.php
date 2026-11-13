<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StorefrontCategoriesSeeder;
use Database\Seeders\StorefrontDemoShowcaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCategoryBrandCrudTest extends TestCase
{
    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(StorefrontCategoriesSeeder::class);
        $this->seed(StorefrontDemoShowcaseSeeder::class);

        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $this->admin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => 'cat_brand_admin_' . uniqid(),
            'email'     => 'cat_brand_admin_' . uniqid() . '@example.com',
            'password'  => Hash::make('SecretPass123!'),
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Admin::where('username', 'LIKE', 'cat_brand_admin_%')->delete();
        Category::where('slug', 'LIKE', 'test-crud-%')->orWhere('slug', 'solar-inverters')->delete();
        Brand::where('slug', 'LIKE', 'test-brand-%')->orWhere('slug', 'samsung-electronics')->orWhere('slug', 'samsung-gulf-pakistan')->delete();

        parent::tearDown();
    }

    public function test_guest_is_redirected_to_login_when_accessing_categories_or_brands(): void
    {
        $this->get('/admin/categories')->assertRedirect('/admin/login');
        $this->get('/admin/brands')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_categories_list(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/categories');

        $response->assertStatus(200);
        $response->assertSee('Categories');
        $response->assertSee('Add New Category');
        $response->assertSee('Home Appliances');
        $response->assertSee('Refrigerators &amp; Freezers', false);
    }

    public function test_admin_can_create_new_category_and_see_it_on_webfront(): void
    {
        $payload = [
            'name'              => 'Solar Inverters & Batteries',
            'slug'              => 'solar-inverters',
            'badge_text'        => 'Eco Energy',
            'models_count_text' => '10+ Systems',
            'description'       => 'High-efficiency hybrid inverters and lithium battery storage.',
            'sort_order'        => 1,
            'is_active'         => '1',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post('/admin/categories', $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('status');

        // Verify stored in DB
        $category = Category::where('slug', 'solar-inverters')->first();
        $this->assertNotNull($category);
        $this->assertEquals('Solar Inverters & Batteries', $category->name);
        $this->assertEquals('Eco Energy', $category->badge_text);
        $this->assertTrue($category->is_active);

        // Verify audit log
        $log = AuditLog::where('action', 'category.created')
            ->where('entity_id', $category->id)
            ->first();
        $this->assertNotNull($log);

        // Verify effect on webfront catalog sidebar filters
        $catalogResponse = $this->get('/catalog');
        $catalogResponse->assertStatus(200);
        $catalogResponse->assertSee('Solar Inverters &amp; Batteries', false);

        // Verify effect on webfront homepage
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Solar Inverters &amp; Batteries', false);
    }

    public function test_admin_can_update_category_and_reflects_on_webfront(): void
    {
        $category = Category::create([
            'name'      => 'Test CRUD Initial',
            'slug'      => 'test-crud-initial',
            'is_active' => true,
        ]);

        $payload = [
            'name'        => 'Test CRUD Updated Category',
            'slug'        => 'test-crud-updated',
            'description' => 'Updated description test.',
            'sort_order'  => 5,
            'is_active'   => '1',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->put("/admin/categories/{$category->id}", $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/categories');

        $category->refresh();
        $this->assertEquals('Test CRUD Updated Category', $category->name);
        $this->assertEquals('test-crud-updated', $category->slug);

        // Verify webfront reflection
        $catalogResponse = $this->get('/catalog');
        $catalogResponse->assertSee('Test CRUD Updated Category');
    }

    public function test_admin_can_toggle_category_active_status_and_deactivated_category_disappears_from_webfront(): void
    {
        $category = Category::create([
            'name'      => 'Test CRUD To Deactivate',
            'slug'      => 'test-crud-deactivate',
            'is_active' => true,
        ]);

        // Prior: visible on webfront
        $this->get('/catalog')->assertSee('Test CRUD To Deactivate');

        // Toggle active status to false
        $response = $this->actingAs($this->admin, 'admin')
            ->post("/admin/categories/{$category->id}/toggle-active");

        $response->assertStatus(302);

        $category->refresh();
        $this->assertFalse($category->is_active);

        // Posterior: disappears from webfront active filters
        $catalogResponse = $this->get('/catalog');
        $catalogResponse->assertDontSee('Test CRUD To Deactivate');
    }

    public function test_admin_cannot_delete_category_with_active_products(): void
    {
        $category = Category::create([
            'name'      => 'Test CRUD Non Empty',
            'slug'      => 'test-crud-non-empty',
            'is_active' => true,
        ]);

        // Attach product
        $product = Product::active()->first();
        $product->category_id = $category->id;
        $product->save();

        $response = $this->actingAs($this->admin, 'admin')
            ->delete("/admin/categories/{$category->id}");

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['error']);

        // Assert category still exists
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_delete_empty_category(): void
    {
        $category = Category::create([
            'name'      => 'Test CRUD Empty To Delete',
            'slug'      => 'test-crud-empty-delete',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete("/admin/categories/{$category->id}");

        $response->assertStatus(302);
        $response->assertSessionHas('status');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    // --- BRANDS CRUD TESTS ---

    public function test_admin_can_view_brands_list(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/brands');

        $response->assertStatus(200);
        $response->assertSee('Brands &amp; Manufacturers', false);
        $response->assertSee('Add New Brand');
    }

    public function test_admin_can_create_new_brand_and_see_it_in_catalog_filters(): void
    {
        $payload = [
            'name'        => 'Samsung Electronics',
            'slug'        => 'samsung-electronics',
            'website'     => 'https://www.samsung.com',
            'description' => 'World leader in digital appliances and visual displays.',
            'sort_order'  => 1,
            'is_active'   => '1',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post('/admin/brands', $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/brands');
        $response->assertSessionHas('status');

        $brand = Brand::where('slug', 'samsung-electronics')->first();
        $this->assertNotNull($brand);
        $this->assertEquals('Samsung Electronics', $brand->name);
        $this->assertEquals('https://www.samsung.com', $brand->website);
        $this->assertTrue($brand->is_active);

        // Verify audit log
        $log = AuditLog::where('action', 'brand.created')
            ->where('entity_id', $brand->id)
            ->first();
        $this->assertNotNull($log);

        // Attach a product so brand has count > 0 or appears in catalog filters
        $product = Product::active()->first();
        $product->brand_id = $brand->id;
        $product->save();

        // Verify storefront catalog official brands filter
        $catalogResponse = $this->get('/catalog');
        $catalogResponse->assertStatus(200);
        $catalogResponse->assertSee('Samsung Electronics');
    }

    public function test_admin_can_update_brand_and_reflects_on_webfront(): void
    {
        $brand = Brand::create([
            'name'      => 'Test Brand Alpha',
            'slug'      => 'test-brand-alpha',
            'is_active' => true,
        ]);

        $payload = [
            'name'        => 'Test Brand Beta International',
            'slug'        => 'test-brand-beta',
            'website'     => 'https://example.com/beta',
            'description' => 'Updated brand profile.',
            'sort_order'  => 2,
            'is_active'   => '1',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->put("/admin/brands/{$brand->id}", $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/brands');

        $brand->refresh();
        $this->assertEquals('Test Brand Beta International', $brand->name);
        $this->assertEquals('test-brand-beta', $brand->slug);
    }

    public function test_admin_can_toggle_brand_active_status(): void
    {
        $brand = Brand::create([
            'name'      => 'Test Brand Active Toggle',
            'slug'      => 'test-brand-toggle',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post("/admin/brands/{$brand->id}/toggle-active");

        $response->assertStatus(302);

        $brand->refresh();
        $this->assertFalse($brand->is_active);
    }

    public function test_admin_can_delete_empty_brand(): void
    {
        $brand = Brand::create([
            'name'      => 'Test Brand Empty Delete',
            'slug'      => 'test-brand-empty-delete',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete("/admin/brands/{$brand->id}");

        $response->assertStatus(302);
        $response->assertSessionHas('status');

        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }

    public function test_storefront_catalog_filters_products_by_brand_slug(): void
    {
        $brand = Brand::create([
            'name'      => 'Test Brand Filter Target',
            'slug'      => 'test-brand-filter-target',
            'is_active' => true,
        ]);

        $product = Product::active()->first();
        $product->brand_id = $brand->id;
        $product->save();

        $response = $this->get('/catalog?brand=' . $brand->slug);
        $response->assertStatus(200);
        $response->assertSee('Test Brand Filter Target');
        $response->assertSee($product->name);
    }
}
