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
        // 1. Carts table
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete()->cascadeOnUpdate();
            $table->string('session_id', 255)->nullable()->unique();
            $table->timestamps();

            $table->index('customer_id');
        });

        // 2. Cart Items table
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->unique(['cart_id', 'product_variant_id']);
            $table->index('cart_id');
            $table->index('product_variant_id');
        });

        // 3. Wishlists table
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index('customer_id');
        });

        // 4. Wishlist Items table
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wishlist_id')->constrained('wishlists')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->unique(['wishlist_id', 'product_variant_id']);
            $table->index('wishlist_id');
            $table->index('product_variant_id');
        });

        // 5. Comparisons table
        Schema::create('comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('session_id', 255)->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('session_id');
        });

        // 6. Comparison Items table
        Schema::create('comparison_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comparison_id')->constrained('comparisons')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->unique(['comparison_id', 'product_variant_id']);
            $table->index('comparison_id');
            $table->index('product_variant_id');
        });

        // Apply native database CHECK constraints on MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE cart_items ADD CONSTRAINT chk_cart_items_qty CHECK (quantity > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparison_items');
        Schema::dropIfExists('comparisons');
        Schema::dropIfExists('wishlist_items');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
