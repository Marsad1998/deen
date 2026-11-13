<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Product;

class ProductPolicy
{
    /**
     * Determine whether the admin can view any products.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission('products.view');
    }

    /**
     * Determine whether the admin can view the product.
     */
    public function view(Admin $admin, Product $product): bool
    {
        return $admin->hasPermission('products.view');
    }

    /**
     * Determine whether the admin can create products.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission('products.create');
    }

    /**
     * Determine whether the admin can update the product.
     */
    public function update(Admin $admin, Product $product): bool
    {
        return $admin->hasPermission('products.edit');
    }

    /**
     * Determine whether the admin can archive/delete the product.
     */
    public function delete(Admin $admin, Product $product): bool
    {
        return $admin->hasPermission('products.delete');
    }
}
