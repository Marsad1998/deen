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
        // 1. Add admin & RBAC columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->change();

            if (! Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username', 100)->nullable()->unique()->after('role_id');
            }
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('password')->index();
            }
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }
        });

        // 2. Migrate existing admins into users table
        if (Schema::hasTable('admins')) {
            $admins = DB::table('admins')->get();
            foreach ($admins as $admin) {
                DB::table('users')->updateOrInsert(
                    ['email' => $admin->email],
                    [
                        'id'             => $admin->id,
                        'role_id'        => $admin->role_id,
                        'username'       => $admin->username,
                        'name'           => $admin->full_name ?: $admin->username,
                        'password'       => $admin->password,
                        'is_active'      => $admin->is_active,
                        'last_login_at'  => $admin->last_login_at,
                        'remember_token' => $admin->remember_token,
                        'created_at'     => $admin->created_at,
                        'updated_at'     => $admin->updated_at,
                    ]
                );
            }
        }

        // 3. Update foreign keys in audit_logs and inventory_histories to reference users
        if (DB::getDriverName() === 'mysql') {
            try {
                Schema::table('audit_logs', function (Blueprint $table) {
                    $table->dropForeign(['admin_id']);
                    $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
                });
            } catch (\Throwable $e) {
                // Ignore if foreign key was already pointing to users
            }

            try {
                Schema::table('inventory_histories', function (Blueprint $table) {
                    $table->dropForeign(['admin_id']);
                    $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
                });
            } catch (\Throwable $e) {
                // Ignore
            }

            try {
                Schema::table('product_price_history', function (Blueprint $table) {
                    $table->dropForeign(['changed_by_admin_id']);
                    $table->foreign('changed_by_admin_id')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
                });
            } catch (\Throwable $e) {
                // Ignore
            }

            try {
                Schema::table('order_status_history', function (Blueprint $table) {
                    $table->dropForeign(['changed_by_admin_id']);
                    $table->foreign('changed_by_admin_id')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
                });
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropConstrainedForeignId('role_id');
            }
            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }
        });
    }
};
