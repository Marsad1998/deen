<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds idempotently.
     */
    public function run(): void
    {
        // 1. Roles: Super Admin, Manager, Editor
        $rolesData = [
            [
                'name'        => 'Super Admin',
                'description' => 'Full administrative access to all system modules, configurations, and user management.',
            ],
            [
                'name'        => 'Manager',
                'description' => 'Operational management access for catalog, inventory, orders, customers, and promotions.',
            ],
            [
                'name'        => 'Editor',
                'description' => 'Content and catalog management access for products, categories, brands, and media.',
            ],
        ];

        $roles = [];
        foreach ($rolesData as $roleItem) {
            $roles[$roleItem['name']] = Role::updateOrCreate(
                ['name' => $roleItem['name']],
                ['description' => $roleItem['description']]
            );
        }

        // 2. Permissions
        $permissionsData = [
            // Catalog Module
            ['name' => 'products.view', 'description' => 'View products and variants'],
            ['name' => 'products.create', 'description' => 'Create new products and variants'],
            ['name' => 'products.edit', 'description' => 'Edit existing products and variants'],
            ['name' => 'products.delete', 'description' => 'Archive or delete products and variants'],
            ['name' => 'categories.manage', 'description' => 'Create, edit, and delete categories'],
            ['name' => 'brands.manage', 'description' => 'Create, edit, and delete brands'],

            // Inventory Module
            ['name' => 'inventory.view', 'description' => 'View inventory stock levels and history'],
            ['name' => 'inventory.adjust', 'description' => 'Adjust inventory quantities and log stock movements'],

            // Orders Module
            ['name' => 'orders.view', 'description' => 'View orders and order details'],
            ['name' => 'orders.edit', 'description' => 'Update order status, shipping info, and admin notes'],
            ['name' => 'orders.cancel', 'description' => 'Cancel orders and restock inventory'],

            // Customers Module
            ['name' => 'customers.view', 'description' => 'View customer profiles and order histories'],
            ['name' => 'customers.edit', 'description' => 'Update customer status and manage address records'],

            // Promotions Module
            ['name' => 'coupons.manage', 'description' => 'Create, edit, and deactivate discount coupons'],

            // Settings Module
            ['name' => 'settings.view', 'description' => 'View store configuration and settings'],
            ['name' => 'settings.edit', 'description' => 'Modify store settings and configuration'],

            // Admins & RBAC Module
            ['name' => 'admins.manage', 'description' => 'Manage administrators, roles, and permission assignments'],

            // Audit Logs Module
            ['name' => 'audit.view', 'description' => 'View immutable administrative audit trail'],
        ];

        $permissionsByName = [];
        foreach ($permissionsData as $permItem) {
            $perm = Permission::updateOrCreate(
                ['name' => $permItem['name']],
                ['description' => $permItem['description']]
            );
            $permissionsByName[$permItem['name']] = $perm->id;
        }

        // 3. Assign Permissions to Roles (Idempotent Sync)

        // Super Admin: All permissions
        $roles['Super Admin']->permissions()->sync(array_values($permissionsByName));

        // Manager: Catalog, Inventory, Orders, Customers, Coupons, View Settings, View Audit
        $managerPermNames = [
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'categories.manage', 'brands.manage',
            'inventory.view', 'inventory.adjust',
            'orders.view', 'orders.edit', 'orders.cancel',
            'customers.view', 'customers.edit',
            'coupons.manage',
            'settings.view',
            'audit.view',
        ];
        $managerPermIds = array_map(fn ($name) => $permissionsByName[$name], $managerPermNames);
        $roles['Manager']->permissions()->sync($managerPermIds);

        // Editor: Products and categories/brands viewing & editing, view inventory
        $editorPermNames = [
            'products.view', 'products.create', 'products.edit',
            'categories.manage', 'brands.manage',
            'inventory.view',
        ];
        $editorPermIds = array_map(fn ($name) => $permissionsByName[$name], $editorPermNames);
        $roles['Editor']->permissions()->sync($editorPermIds);
    }
}
