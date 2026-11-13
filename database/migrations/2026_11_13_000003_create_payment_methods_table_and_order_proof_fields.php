<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Payment Methods Table
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('subtitle', 255)->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('requires_proof')->default(false);
            $table->string('account_title', 255)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('branch_name', 150)->nullable();
            $table->string('iban', 100)->nullable();
            $table->text('instructions')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('is_enabled');
            $table->index('display_order');
        });

        // 2. Add payment proof screenshot & transaction reference to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_proof_path', 255)->nullable()->after('payment_status');
            $table->string('transaction_reference', 100)->nullable()->after('payment_proof_path');

            $table->index('transaction_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['transaction_reference']);
            $table->dropColumn(['payment_proof_path', 'transaction_reference']);
        });

        Schema::dropIfExists('payment_methods');
    }
};
