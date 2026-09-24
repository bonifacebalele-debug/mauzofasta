<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasBusinessPermission('orders.view');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasBusinessPermission('orders.view');
    }

    public function create(User $user): bool
    {
        return $user->hasBusinessPermission('orders.create');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasBusinessPermission('orders.update');
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->hasBusinessPermission('orders.cancel');
    }

    public function applyDiscount(User $user): bool
    {
        return $user->hasBusinessPermission('orders.apply_discount');
    }
}
