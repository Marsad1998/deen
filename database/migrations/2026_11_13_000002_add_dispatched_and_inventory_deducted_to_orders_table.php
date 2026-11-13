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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('dispatched_at')->nullable()->after('status');
            $table->timestamp('inventory_deducted_at')->nullable()->after('dispatched_at');

            $table->index('dispatched_at');
            $table->index('inventory_deducted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['dispatched_at']);
            $table->dropIndex(['inventory_deducted_at']);
            $table->dropColumn(['dispatched_at', 'inventory_deducted_at']);
        });
    }
};
