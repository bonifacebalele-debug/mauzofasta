<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function view(User $user, Business $business): bool
    {
        return $user->businessUsers()
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Business profile/settings updates are Owner-only per the permission
     * matrix (docs/03-permissions.md) — Manager has read access only.
     */
    public function update(User $user, Business $business): bool
    {
        return $user->businessUsers()
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->where('is_owner', true)
            ->exists();
    }

    public function delete(User $user, Business $business): bool
    {
        return $this->update($user, $business);
    }

    public function manageStaff(User $user, Business $business): bool
    {
        if ($this->update($user, $business)) {
            return true;
        }

        return $user->businessUsers()
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->whereHas('role', fn ($q) => $q->whereHas(
                'permissions',
                fn ($q) => $q->where('name', 'staff.manage')
            ))
            ->exists();
    }
}
