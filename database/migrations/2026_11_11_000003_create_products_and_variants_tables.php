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
        // 1. Products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete()->cascadeOnUpdate();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('sku', 100)->nullable();
            $table->string('badge_text', 150)->nullable();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('main_image', 500)->nullable();
            $table->json('gallery_images')->nullable();
            $table->string('warranty_text', 255)->nullable();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('reviews_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index('category_id');
            $table->index('brand_id');
            $table->index('is_featured');
            $table->index('is_active');
            $table->index('archived_at');
        });

        // 2. Product Variants table
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('sku', 100)->unique();
            $table->string('name', 255);
            $table->decimal('price', 12, 2);
            $table->decimal('original_price', 12, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('stock_status', 50)->default('in_stock');
            $table->json('attributes')->nullable();
            $table->boolean('is_default')->default(false);
            // Virtual column guaranteeing at most one default variant per product in MySQL
            $table->unsignedBigInteger('default_product_id')->nullable()
                  ->virtualAs('CASE WHEN is_default = 1 THEN product_id ELSE NULL END');
            $table->boolean('is_active')->default(true);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique('default_product_id', 'uq_default_variant_per_product');
            $table->index('product_id');
            $table->index('is_active');
            $table->index('archived_at');
        });

        // 3. Product Media table (Exclusive Ownership Pattern)
        Schema::create('product_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->restrictOnDelete()->restrictOnUpdate();
            $table->string('media_type', 50)->default('image');
            $table->string('url', 500);
            $table->string('alt_text', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('product_id');
            $table->index('product_variant_id');
            $table->index('sort_order');
        });

        // 4. Product Specifications table (Exclusive Ownership Pattern)
        Schema::create('product_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->restrictOnDelete()->restrictOnUpdate();
            $table->string('spec_group', 100)->nullable();
            $table->string('spec_key', 150);
            $table->text('spec_value');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id');
            $table->index('product_variant_id');
            $table->index('spec_key');
        });

        // 5. Product Price History table
        Schema::create('product_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete()->cascadeOnUpdate();
            $table->decimal('old_price', 12, 2);
            $table->decimal('new_price', 12, 2);
            $table->string('currency', 3)->default('PKR');
            $table->timestamp('effective_date')->useCurrent();
            $table->foreignId('changed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->index('product_variant_id');
            $table->index('effective_date');
        });

        // 6. Inventory Histories table
        Schema::create('inventory_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete()->cascadeOnUpdate();
            $table->integer('change_qty');
            $table->integer('previous_qty')->nullable();
            $table->integer('new_qty')->nullable();
            $table->string('reason', 100);
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index('product_variant_id');
            $table->index('created_at');
        });

        // Apply native database CHECK constraints on MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_rating CHECK (rating >= 0.00 AND rating <= 5.00)');
            DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_reviews CHECK (reviews_count >= 0)');
            DB::statement('ALTER TABLE product_variants ADD CONSTRAINT chk_variants_price CHECK (price >= 0.00)');
            DB::statement('ALTER TABLE product_variants ADD CONSTRAINT chk_variants_orig_price CHECK (original_price IS NULL OR original_price >= 0.00)');
            DB::statement('ALTER TABLE product_variants ADD CONSTRAINT chk_variants_stock CHECK (stock_quantity >= 0)');
            DB::statement('ALTER TABLE product_media ADD CONSTRAINT chk_media_exclusive_owner CHECK ((product_id IS NOT NULL AND product_variant_id IS NULL) OR (product_id IS NULL AND product_variant_id IS NOT NULL))');
            DB::statement('ALTER TABLE product_specifications ADD CONSTRAINT chk_specs_exclusive_owner CHECK ((product_id IS NOT NULL AND product_variant_id IS NULL) OR (product_id IS NULL AND product_variant_id IS NOT NULL))');
            DB::statement('ALTER TABLE product_price_history ADD CONSTRAINT chk_price_hist_old CHECK (old_price >= 0.00)');
            DB::statement('ALTER TABLE product_price_history ADD CONSTRAINT chk_price_hist_new CHECK (new_price >= 0.00)');
            DB::statement('ALTER TABLE inventory_histories ADD CONSTRAINT chk_inv_prev_qty CHECK (previous_qty IS NULL OR previous_qty >= 0)');
            DB::statement('ALTER TABLE inventory_histories ADD CONSTRAINT chk_inv_new_qty CHECK (new_qty IS NULL OR new_qty >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_histories');
        Schema::dropIfExists('product_price_history');
        Schema::dropIfExists('product_specifications');
        Schema::dropIfExists('product_media');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
