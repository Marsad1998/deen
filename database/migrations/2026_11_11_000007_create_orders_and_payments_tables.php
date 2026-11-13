<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Orders table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete()->cascadeOnUpdate();

            // Contact snapshot (email nullable to support phone-only guest COD)
            $table->string('customer_name', 255);
            $table->string('customer_email', 150)->nullable();
            $table->string('customer_phone', 50);

            // Immutable shipping address snapshot
            $table->string('shipping_recipient_name', 255);
            $table->string('shipping_recipient_phone', 50);
            $table->string('shipping_address_line1', 255);
            $table->string('shipping_address_line2', 255)->nullable();
            $table->string('shipping_city', 100);
            $table->string('shipping_province', 100);
            $table->string('shipping_postal_code', 20)->nullable();
            $table->string('shipping_country', 100)->default('Pakistan');
            $table->text('delivery_instructions')->nullable();
            $table->foreignId('shipping_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete()->cascadeOnUpdate();

            // Order Status & Payment
            $table->string('status', 50)->default('pending');
            $table->string('payment_method', 50)->default('COD');
            $table->string('payment_status', 50)->default('pending');
            $table->string('currency', 3)->default('PKR');

            // Financial breakdown
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete()->cascadeOnUpdate();
            $table->string('coupon_code', 50)->nullable();
            $table->decimal('shipping_cost', 12, 2)->default(0.00);
            $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods')->nullOnDelete()->cascadeOnUpdate();
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2);

            $table->text('notes')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('customer_phone');
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
            $table->index('archived_at');
        });

        // 2. Order Items table
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete()->cascadeOnUpdate();
            $table->string('product_name', 255);
            $table->string('variant_name', 255)->nullable();
            $table->string('sku', 100);
            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity');
            $table->decimal('total_price', 12, 2);
            $table->json('attributes_snapshot')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
            $table->index('product_variant_id');
            $table->index('sku');
        });

        // 3. Order Status History table
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('status', 50);
            $table->text('comment')->nullable();
            $table->foreignId('changed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index('order_id');
            $table->index('created_at');
        });

        // 4. Payment Transactions table
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('gateway', 50);
            $table->string('transaction_id', 150)->nullable();
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->string('status', 50)->default('pending');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('PKR');
            $table->json('gateway_response')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('transaction_id');
            $table->index('status');
            $table->index('created_at');
        });

        // Apply native database CHECK constraints on MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE orders ADD CONSTRAINT chk_orders_subtotal CHECK (subtotal >= 0.00)');
            DB::statement('ALTER TABLE orders ADD CONSTRAINT chk_orders_discount CHECK (discount_amount >= 0.00)');
            DB::statement('ALTER TABLE orders ADD CONSTRAINT chk_orders_shipping CHECK (shipping_cost >= 0.00)');
            DB::statement('ALTER TABLE orders ADD CONSTRAINT chk_orders_tax CHECK (tax_amount >= 0.00)');
            DB::statement('ALTER TABLE orders ADD CONSTRAINT chk_orders_total CHECK (total_amount >= 0.00)');
            DB::statement('ALTER TABLE orders ADD CONSTRAINT chk_orders_phone_not_empty CHECK (customer_phone IS NOT NULL AND LENGTH(TRIM(customer_phone)) > 0)');

            DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_order_items_unit_price CHECK (unit_price >= 0.00)');
            DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_order_items_total_price CHECK (total_price >= 0.00)');
            DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_order_items_qty CHECK (quantity > 0)');

            DB::statement('ALTER TABLE payment_transactions ADD CONSTRAINT chk_payment_trans_amt CHECK (amount >= 0.00)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
