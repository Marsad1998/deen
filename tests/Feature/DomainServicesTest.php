<?php

namespace Tests\Feature;

use App\Exceptions\ShippingRateCalculationException;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use App\Services\ProductVariantService;
use App\Services\ShippingRateService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class DomainServicesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.connections.mysql.database') === ':memory:') {
            config(['database.connections.mysql.database' => 'deen_electronics_dev']);
        }
        config(['database.default' => 'mysql']);
        DB::purge('mysql');

        try {
            DB::connection('mysql')->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('MySQL connection not available: ' . $e->getMessage());
        }
    }

    public function test_product_variant_service_lifecycle(): void
    {
        $conn = DB::connection('mysql');

        $conn->transaction(function () {
            $service = new ProductVariantService();

            // 1. Create product with default variant
            $res = $service->createProductWithDefaultVariant(
                [
                    'name'      => 'Service Test Product',
                    'slug'      => 'service-test-product-' . uniqid(),
                    'is_active' => false,
                ],
                [
                    'sku'   => 'SVC-VAR-1-' . uniqid(),
                    'name'  => 'Initial Default',
                    'price' => 2500.00,
                ]
            );

            $productId = $res['product_id'];
            $defaultVarId = $res['default_variant_id'];

            // 2. Add a second variant (non-default)
            $var2Id = DB::table('product_variants')->insertGetId([
                'product_id' => $productId,
                'sku'        => 'SVC-VAR-2-' . uniqid(),
                'name'       => 'Second Variant',
                'price'      => 3000.00,
                'is_default' => 0,
                'is_active'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Atomically swap default variant to variant 2
            $service->setDefaultVariant($productId, $var2Id);

            $v1 = DB::table('product_variants')->where('id', $defaultVarId)->first();
            $v2 = DB::table('product_variants')->where('id', $var2Id)->first();

            $this->assertEquals(0, $v1->is_default);
            $this->assertEquals(1, $v2->is_default);

            // 4. Activate product
            $service->activateProduct($productId);
            $product = DB::table('products')->where('id', $productId)->first();
            $this->assertEquals(1, $product->is_active);

            // 5. Guard: Cannot deactivate default variant while product is active
            $this->expectException(RuntimeException::class);
            $service->deactivateVariant($var2Id);
        });
    }

    public function test_shipping_rate_overlap_prevention_and_resolution(): void
    {
        $conn = DB::connection('mysql');

        $conn->transaction(function () use ($conn) {
            $service = new ShippingRateService();

            // 1. Create shipping method
            $methodId = $conn->table('shipping_methods')->insertGetId([
                'name'       => 'Standard TCS',
                'code'       => 'TCS_TEST_' . uniqid(),
                'is_active'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Save tier 1: 0 to 5000
            $rate1Id = $service->saveRate([
                'shipping_method_id' => $methodId,
                'region'             => 'Lahore',
                'min_order_amount'   => 0.00,
                'max_order_amount'   => 5000.00,
                'cost'               => 250.00,
                'is_active'          => 1,
            ]);
            $this->assertGreaterThan(0, $rate1Id);

            // 3. Attempt to save overlapping tier: 3000 to 8000 (must be rejected)
            $caught = false;
            try {
                $service->saveRate([
                    'shipping_method_id' => $methodId,
                    'region'             => 'Lahore',
                    'min_order_amount'   => 3000.00,
                    'max_order_amount'   => 8000.00,
                    'cost'               => 200.00,
                    'is_active'          => 1,
                ]);
            } catch (InvalidArgumentException $e) {
                $caught = true;
                $this->assertStringContainsString('overlaps with existing rate', $e->getMessage());
            }
            $this->assertTrue($caught, 'Overlapping shipping rate must throw InvalidArgumentException');

            // 4. Resolve rate for order amount 2500 -> matches rate 1
            $resolved = $service->resolveRate($methodId, 'Lahore', 2500.00);
            $this->assertNotNull($resolved);
            $this->assertEquals($rate1Id, $resolved->id);
            $this->assertEquals(250.00, (float) $resolved->cost);
        });
    }

    public function test_audit_log_append_only_immutability(): void
    {
        $conn = DB::connection('mysql');

        $conn->transaction(function () {
            $service = new AuditLogService();

            $log = $service->log(
                action: 'product.created',
                entity: 'Product',
                entityId: 999,
                changes: ['name' => 'Audit Test Product']
            );

            $this->assertNotNull($log->id);

            // Updating must throw RuntimeException
            $updateFailed = false;
            try {
                $log->action = 'product.modified';
                $log->save();
            } catch (RuntimeException $e) {
                $updateFailed = true;
                $this->assertStringContainsString('Audit logs are strictly append-only', $e->getMessage());
            }
            $this->assertTrue($updateFailed, 'Updating an audit log must throw RuntimeException');

            // Deleting must throw RuntimeException
            $deleteFailed = false;
            try {
                $log->delete();
            } catch (RuntimeException $e) {
                $deleteFailed = true;
                $this->assertStringContainsString('Audit logs are strictly append-only', $e->getMessage());
            }
            $this->assertTrue($deleteFailed, 'Deleting an audit log must throw RuntimeException');
        });
    }

    protected function tearDown(): void
    {
        try {
            $conn = DB::connection('mysql');
            $conn->table('product_media')->delete();
            $conn->table('product_variants')->delete();
            $conn->table('products')->delete();
            $conn->table('shipping_rates')->delete();
            $conn->table('shipping_methods')->delete();
        } catch (\Throwable $e) {
            // Ignore
        }

        parent::tearDown();
    }
}
