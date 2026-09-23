<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasBusinessPermission('products.view');
    }

    public function create(User $user): bool
    {
        return $user->hasBusinessPermission('products.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasBusinessPermission('products.update');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasBusinessPermission('products.delete');
    }
}
