<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasBusinessPermission('payments.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasBusinessPermission('payments.view');
    }

    public function create(User $user): bool
    {
        return $user->hasBusinessPermission('payments.create');
    }

    public function refund(User $user, Payment $payment): bool
    {
        return $user->hasBusinessPermission('payments.delete');
    }
}
