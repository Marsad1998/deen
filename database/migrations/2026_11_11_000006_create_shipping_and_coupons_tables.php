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
        // 1. Shipping Methods table
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        // 2. Shipping Rates table
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained('shipping_methods')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('region', 100)->default('ALL');
            $table->decimal('min_order_amount', 12, 2)->default(0.00);
            $table->decimal('max_order_amount', 12, 2)->nullable();
            $table->decimal('cost', 12, 2)->default(0.00);
            $table->string('estimated_days', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shipping_method_id', 'region', 'min_order_amount'], 'uq_ship_rates_method_region_min');
            $table->index('shipping_method_id');
            $table->index('region');
            $table->index('is_active');
        });

        // 3. Coupons table
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 12, 2);
            $table->decimal('min_order_amount', 12, 2)->default(0.00);
            $table->decimal('max_discount_amount', 12, 2)->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('uses')->default(0);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('start_date');
            $table->index('end_date');
        });

        // Apply native database CHECK constraints on MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE shipping_rates ADD CONSTRAINT chk_ship_rates_order_amt CHECK (min_order_amount >= 0.00 AND (max_order_amount IS NULL OR max_order_amount > min_order_amount))');
            DB::statement('ALTER TABLE shipping_rates ADD CONSTRAINT chk_ship_rates_cost CHECK (cost >= 0.00)');
            DB::statement('ALTER TABLE coupons ADD CONSTRAINT chk_coupons_disc_val CHECK (discount_value > 0.00)');
            DB::statement('ALTER TABLE coupons ADD CONSTRAINT chk_coupons_pct_limit CHECK (discount_type != \'percentage\' OR (discount_value > 0.00 AND discount_value <= 100.00))');
            DB::statement('ALTER TABLE coupons ADD CONSTRAINT chk_coupons_min_amt CHECK (min_order_amount >= 0.00)');
            DB::statement('ALTER TABLE coupons ADD CONSTRAINT chk_coupons_uses CHECK (uses >= 0)');
            DB::statement('ALTER TABLE coupons ADD CONSTRAINT chk_coupons_max_uses CHECK (max_uses IS NULL OR max_uses >= 1)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_methods');
    }
};
