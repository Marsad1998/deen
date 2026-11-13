<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSchemaIntegrityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.connections.mysql.database') === ':memory:') {
            config(['database.connections.mysql.database' => 'deen_electronics_dev']);
            DB::purge('mysql');
        }

        // Skip if MySQL connection is not configured or unavailable
        try {
            DB::connection('mysql')->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('MySQL connection not available: ' . $e->getMessage());
        }
    }

    public function test_all_expected_tables_exist_in_mysql(): void
    {
        $expectedDomainTables = [
            'roles', 'permissions', 'role_permissions', 'admins', 'audit_logs',
            'categories', 'brands',
            'products', 'product_variants', 'product_media', 'product_specifications',
            'product_price_history', 'inventory_histories',
            'customers', 'customer_addresses',
            'carts', 'cart_items', 'wishlists', 'wishlist_items', 'comparisons', 'comparison_items',
            'shipping_methods', 'shipping_rates', 'coupons',
            'orders', 'order_items', 'order_status_history', 'payment_transactions',
            'store_settings', 'email_settings',
        ];

        $tables = DB::connection('mysql')->select('SHOW TABLES');
        $existing = array_map(function ($row) {
            return current((array) $row);
        }, $tables);

        foreach ($expectedDomainTables as $table) {
            $this->assertContains($table, $existing, "Table {$table} does not exist in deen_electronics_dev");
        }
    }

    public function test_virtual_generated_columns_exist(): void
    {
        $vcols = DB::connection('mysql')->select("
            SELECT TABLE_NAME, COLUMN_NAME, GENERATION_EXPRESSION 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = 'deen_electronics_dev' AND EXTRA LIKE '%VIRTUAL%'
        ");

        $cols = [];
        foreach ($vcols as $row) {
            $cols[$row->TABLE_NAME . '.' . $row->COLUMN_NAME] = $row->GENERATION_EXPRESSION;
        }

        $this->assertArrayHasKey('product_variants.default_product_id', $cols);
        $this->assertArrayHasKey('customer_addresses.default_customer_id', $cols);
    }

    public function test_at_most_one_default_variant_enforced_by_virtual_unique(): void
    {
        $conn = DB::connection('mysql');

        $conn->transaction(function () use ($conn) {
            $productId = $conn->table('products')->insertGetId([
                'name'       => 'Schema Test Product',
                'slug'       => 'schema-test-product-' . uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // First default variant succeeds
            $v1 = $conn->table('product_variants')->insertGetId([
                'product_id' => $productId,
                'sku'        => 'TEST-DEF-1-' . uniqid(),
                'name'       => 'Default Variant',
                'price'      => 1500.00,
                'is_default' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->assertGreaterThan(0, $v1);

            // Unlimited non-default variants succeed
            $v2 = $conn->table('product_variants')->insertGetId([
                'product_id' => $productId,
                'sku'        => 'TEST-NONDEF-1-' . uniqid(),
                'name'       => 'Non Default 1',
                'price'      => 1600.00,
                'is_default' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->assertGreaterThan(0, $v2);

            $v3 = $conn->table('product_variants')->insertGetId([
                'product_id' => $productId,
                'sku'        => 'TEST-NONDEF-2-' . uniqid(),
                'name'       => 'Non Default 2',
                'price'      => 1700.00,
                'is_default' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->assertGreaterThan(0, $v3);

            // Second default variant MUST fail with duplicate key violation
            $this->expectException(QueryException::class);
            $conn->table('product_variants')->insert([
                'product_id' => $productId,
                'sku'        => 'TEST-DEF-2-' . uniqid(),
                'name'       => 'Second Default Variant',
                'price'      => 1800.00,
                'is_default' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function test_exclusive_ownership_on_product_media(): void
    {
        $conn = DB::connection('mysql');

        $conn->transaction(function () use ($conn) {
            $productId = $conn->table('products')->insertGetId([
                'name'       => 'Media Test Product',
                'slug'       => 'media-test-prod-' . uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $variantId = $conn->table('product_variants')->insertGetId([
                'product_id' => $productId,
                'sku'        => 'MEDIA-VAR-' . uniqid(),
                'name'       => 'Media Variant',
                'price'      => 1000.00,
                'is_default' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 1. Setting both product_id AND product_variant_id MUST fail
            $failedBoth = false;
            try {
                $conn->table('product_media')->insert([
                    'product_id'         => $productId,
                    'product_variant_id' => $variantId,
                    'url'                => 'https://example.com/test.jpg',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            } catch (QueryException $e) {
                $failedBoth = true;
            }
            $this->assertTrue($failedBoth, 'CHECK constraint must reject having both product_id and product_variant_id');

            // 2. Setting neither (both null) MUST fail
            $failedNeither = false;
            try {
                $conn->table('product_media')->insert([
                    'product_id'         => null,
                    'product_variant_id' => null,
                    'url'                => 'https://example.com/test.jpg',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            } catch (QueryException $e) {
                $failedNeither = true;
            }
            $this->assertTrue($failedNeither, 'CHECK constraint must reject having both null');

            // 3. Setting exactly product_id MUST succeed
            $mediaProd = $conn->table('product_media')->insertGetId([
                'product_id'         => $productId,
                'product_variant_id' => null,
                'url'                => 'https://example.com/prod.jpg',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
            $this->assertGreaterThan(0, $mediaProd);

            // 4. Setting exactly product_variant_id MUST succeed
            $mediaVar = $conn->table('product_media')->insertGetId([
                'product_id'         => null,
                'product_variant_id' => $variantId,
                'url'                => 'https://example.com/var.jpg',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
            $this->assertGreaterThan(0, $mediaVar);
        });
    }

    public function test_negative_numeric_check_constraint_rejects_invalid_values(): void
    {
        $conn = DB::connection('mysql');

        $this->expectException(QueryException::class);
        $conn->table('coupons')->insert([
            'code'           => 'INVALID-TEST-' . uniqid(),
            'discount_type'  => 'percentage',
            'discount_value' => -10.00, // CHECK constraint: discount_value > 0
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    protected function tearDown(): void
    {
        try {
            $conn = DB::connection('mysql');
            $conn->table('product_media')->delete();
            $conn->table('product_variants')->delete();
            $conn->table('products')->delete();
        } catch (\Throwable $e) {
            // Ignore if connection not available
        }

        parent::tearDown();
    }
}
