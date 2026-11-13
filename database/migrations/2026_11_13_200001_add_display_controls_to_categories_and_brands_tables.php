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
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('show_in_nav')->default(false)->after('sort_order');
            $table->boolean('show_on_home')->default(true)->after('show_in_nav');

            $table->index('show_in_nav');
            $table->index('show_on_home');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->boolean('show_on_home')->default(true)->after('sort_order');

            $table->index('show_on_home');
        });

        // Initialize top categories for navigation
        $navSlugs = [
            'home-appliances',
            'air-conditioners',
            'refrigerators-freezers',
            'washing-machines',
            'led-tvs',
            'kitchen-appliances',
            'scooters',
        ];

        DB::table('categories')
            ->whereIn('slug', $navSlugs)
            ->update(['show_in_nav' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['show_in_nav']);
            $table->dropIndex(['show_on_home']);
            $table->dropColumn(['show_in_nav', 'show_on_home']);
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropIndex(['show_on_home']);
            $table->dropColumn(['show_on_home']);
        });
    }
};
