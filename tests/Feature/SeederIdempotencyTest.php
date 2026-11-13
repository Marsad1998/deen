<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\StoreSetting;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreSettingsSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeederIdempotencyTest extends TestCase
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

    public function test_seeders_are_idempotent(): void
    {
        // Execute seeders
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(StoreSettingsSeeder::class);

        $rolesCount1 = Role::count();
        $permsCount1 = Permission::count();
        $pivotCount1 = DB::table('role_permissions')->count();
        $settingsCount1 = StoreSetting::count();

        // Run second time
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(StoreSettingsSeeder::class);

        $this->assertEquals($rolesCount1, Role::count(), 'Role count must not change on subsequent seed runs');
        $this->assertEquals($permsCount1, Permission::count(), 'Permission count must not change on subsequent seed runs');
        $this->assertEquals($pivotCount1, DB::table('role_permissions')->count(), 'Role-permission assignments must not duplicate');
        $this->assertEquals($settingsCount1, StoreSetting::count(), 'Store setting count must not change on subsequent seed runs');
    }

    public function test_roles_and_permissions_structure(): void
    {
        $superAdmin = Role::where('name', 'Super Admin')->first();
        $manager    = Role::where('name', 'Manager')->first();
        $editor     = Role::where('name', 'Editor')->first();

        $this->assertNotNull($superAdmin);
        $this->assertNotNull($manager);
        $this->assertNotNull($editor);

        $totalPerms = Permission::count();
        $this->assertEquals($totalPerms, $superAdmin->permissions()->count(), 'Super Admin must have all permissions');
        $this->assertGreaterThan(0, $manager->permissions()->count());
        $this->assertGreaterThan(0, $editor->permissions()->count());
        $this->assertLessThan($totalPerms, $editor->permissions()->count());
    }

    public function test_no_hardcoded_admin_created(): void
    {
        $adminsBefore = DB::table('admins')->count();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->assertEquals($adminsBefore, DB::table('admins')->count(), 'No admin account should be created during automated seeding');
    }

    public function test_store_settings_seeded(): void
    {
        $this->assertEquals('DEEN Electronics', StoreSetting::get('store_name'));
        $this->assertEquals('PKR', StoreSetting::get('store_currency'));
        $this->assertEquals('Rs.', StoreSetting::get('store_currency_symbol'));
        $this->assertEquals('Asia/Karachi', StoreSetting::get('store_timezone'));
    }
}
