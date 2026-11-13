<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StorefrontCategoriesSeeder;
use Database\Seeders\StorefrontDemoShowcaseSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductSeoTest extends TestCase
{
    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(StorefrontCategoriesSeeder::class);
        $this->seed(StorefrontDemoShowcaseSeeder::class);

        $this->cleanTestProducts();

        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $this->admin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => 'seo_admin_' . uniqid(),
            'email'     => 'seo_admin_' . uniqid() . '@example.com',
            'password'  => Hash::make('SecretPass123!'),
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        $this->cleanTestProducts();

        parent::tearDown();
    }

    protected function cleanTestProducts(): void
    {
        Admin::where('username', 'LIKE', 'seo_admin_%')->delete();
        $testProductIds = Product::where('slug', 'LIKE', 'test-seo-%')->pluck('id');
        if ($testProductIds->isNotEmpty()) {
            $variantIds = ProductVariant::whereIn('product_id', $testProductIds)->pluck('id');
            \Illuminate\Support\Facades\DB::table('inventory_histories')->whereIn('product_variant_id', $variantIds)->delete();
            \Illuminate\Support\Facades\DB::table('product_price_history')->whereIn('product_variant_id', $variantIds)->delete();
            ProductVariant::whereIn('id', $variantIds)->delete();
            Product::whereIn('id', $testProductIds)->delete();
        }
    }

    public function test_admin_can_save_product_with_custom_seo_fields(): void
    {
        $category = Category::where('is_active', true)->firstOrFail();
        $brand = Brand::where('is_active', true)->firstOrFail();

        $payload = [
            'name'              => 'Test SEO Inverter AC 1.5 Ton',
            'slug'              => 'test-seo-inverter-ac',
            'sku'               => 'SEO-AC-001',
            'category_id'       => $category->id,
            'brand_id'          => $brand->id,
            'short_description' => 'Energy saving DC inverter with T3 tropical compressor.',
            'full_description'  => 'Detailed inverter cooling performance test.',
            'warranty_text'     => '10 Years Compressor Warranty',
            'meta_title'        => 'Haier 1.5 Ton Inverter AC Price in Lahore — DEEN Electronics',
            'meta_description'  => 'Buy genuine 1.5 Ton DC Inverter Air Conditioner in Lahore with 10 years compressor warranty and free delivery from DEEN Electronics.',
            'meta_keywords'     => 'inverter ac lahore, 1.5 ton ac price, haier ac deen electronics',
            'status'            => 'active',
            'price'             => 145000,
            'original_price'    => 160000,
            'stock_quantity'    => 5,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post('/admin/products', $payload);

        $response->assertStatus(302);

        $product = Product::where('slug', 'test-seo-inverter-ac')->first();
        $this->assertNotNull($product);
        $this->assertEquals('Haier 1.5 Ton Inverter AC Price in Lahore — DEEN Electronics', $product->meta_title);
        $this->assertEquals('Buy genuine 1.5 Ton DC Inverter Air Conditioner in Lahore with 10 years compressor warranty and free delivery from DEEN Electronics.', $product->meta_description);
        $this->assertEquals('inverter ac lahore, 1.5 ton ac price, haier ac deen electronics', $product->meta_keywords);
    }

    public function test_admin_can_update_product_seo_fields(): void
    {
        $product = Product::active()->firstOrFail();

        $payload = [
            'name'             => $product->name,
            'slug'             => $product->slug,
            'status'           => 'active',
            'meta_title'       => 'Updated Meta Title for ' . $product->name,
            'meta_description' => 'Updated meta description summary for high ranking on Google Search.',
            'meta_keywords'    => 'updated, keywords, test',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->put("/admin/products/{$product->id}", $payload);

        $response->assertStatus(302);

        $product->refresh();
        $this->assertEquals('Updated Meta Title for ' . $product->name, $product->meta_title);
        $this->assertEquals('Updated meta description summary for high ranking on Google Search.', $product->meta_description);
    }

    public function test_storefront_product_detail_page_renders_rich_seo_meta_tags(): void
    {
        $category = Category::where('is_active', true)->firstOrFail();
        $brand = Brand::where('is_active', true)->firstOrFail();

        $product = Product::create([
            'category_id'      => $category->id,
            'brand_id'         => $brand->id,
            'name'             => 'Test SEO Flagship TV 65 Inch',
            'slug'             => 'test-seo-flagship-tv-65',
            'sku'              => 'SEO-TV-65',
            'meta_title'       => '65 Inch 4K UHD Smart Google TV Lahore — DEEN Electronics',
            'meta_description' => 'Order 65 Inch 4K UHD Smart TV with official panel warranty and cash on delivery in Lahore.',
            'meta_keywords'    => '65 inch led tv, 4k smart tv lahore, google tv',
            'is_active'        => true,
        ]);

        ProductVariant::create([
            'product_id'     => $product->id,
            'sku'            => 'SEO-TV-65-DEFAULT',
            'name'           => 'Default',
            'price'          => 189000,
            'stock_quantity' => 4,
            'is_default'     => true,
            'is_active'      => true,
        ]);

        $response = $this->get('/product/' . $product->slug);

        $response->assertStatus(200);

        // Title Tag & Meta Description
        $response->assertSee('<title>65 Inch 4K UHD Smart Google TV Lahore — DEEN Electronics</title>', false);
        $response->assertSee('content="Order 65 Inch 4K UHD Smart TV with official panel warranty and cash on delivery in Lahore."', false);
        $response->assertSee('content="65 inch led tv, 4k smart tv lahore, google tv"', false);

        // Canonical & Robots Indexing
        $response->assertSee('<link rel="canonical" href="' . route('storefront.product', $product->slug) . '">', false);
        $response->assertSee('<meta name="robots" content="index, follow">', false);

        // OpenGraph & WhatsApp Unfurling
        $response->assertSee('<meta property="og:type" content="product">', false);
        $response->assertSee('<meta property="og:site_name" content="DEEN Electronics">', false);
        $response->assertSee('<meta property="og:title" content="65 Inch 4K UHD Smart Google TV Lahore — DEEN Electronics">', false);
        $response->assertSee('<meta property="product:price:currency" content="PKR">', false);
        $response->assertSee('<meta property="product:price:amount" content="189000.00">', false);

        // Twitter Card
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);

        // Schema.org JSON-LD Structured Data
        $response->assertSee('"@context": "https://schema.org"', false);
        $response->assertSee('"@type": "Product"', false);
        $response->assertSee('"name": "Test SEO Flagship TV 65 Inch"', false);
        $response->assertSee('"priceCurrency": "PKR"', false);
        $response->assertSee('"price": 189000', false);
        $response->assertSee('"https://schema.org/InStock"', false);
    }

    public function test_product_without_custom_seo_fields_falls_back_cleanly_to_defaults(): void
    {
        $product = Product::create([
            'name'              => 'Test SEO Default Model',
            'slug'              => 'test-seo-default-model',
            'sku'               => 'SEO-DEF-01',
            'short_description' => 'Reliable home electrical unit with solid build quality.',
            'is_active'         => true,
        ]);

        $this->assertEquals('Test SEO Default Model — DEEN Electronics Lahore', $product->seo_title);
        $this->assertEquals('Reliable home electrical unit with solid build quality.', $product->seo_description);

        $jsonLd = $product->json_ld;
        $this->assertEquals('https://schema.org', $jsonLd['@context']);
        $this->assertEquals('Product', $jsonLd['@type']);
        $this->assertEquals('Test SEO Default Model', $jsonLd['name']);
        $this->assertEquals('PKR', $jsonLd['offers']['priceCurrency']);
    }
}
