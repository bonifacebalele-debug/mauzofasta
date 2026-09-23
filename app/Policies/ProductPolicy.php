<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasBusinessPermission('products.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasBusinessPermission('products.view');
    }

    public function create(User $user): bool
    {
        return $user->hasBusinessPermission('products.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasBusinessPermission('products.update');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasBusinessPermission('products.delete');
    }

    public function adjustStock(User $user, Product $product): bool
    {
        return $user->hasBusinessPermission('stock.adjust');
    }
}
