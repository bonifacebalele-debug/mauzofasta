<?php

namespace Tests\Concerns;

use App\Models\Business;
use App\Models\BusinessProfile;
use App\Models\BusinessSettings;
use App\Models\BusinessUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait CreatesBusiness
{
    /**
     * @return array{0: Business, 1: User}
     */
    protected function createBusinessWithOwner(array $businessOverrides = [], array $userOverrides = []): array
    {
        // RefreshDatabase resets rows without firing model events, so a
        // previous test's cached role/permission pivot data could otherwise
        // leak into this one.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seed(RolesAndPermissionsSeeder::class);

        $owner = User::factory()->create($userOverrides);
        $business = Business::factory()->create(array_merge([
            'owner_user_id' => $owner->id,
        ], $businessOverrides));

        BusinessProfile::create(['business_id' => $business->id]);
        BusinessSettings::create(['business_id' => $business->id]);

        BusinessUser::create([
            'business_id' => $business->id,
            'user_id' => $owner->id,
            'role_id' => Role::findOrCreate('owner', 'web')->id,
            'is_owner' => true,
            'status' => 'active',
        ]);

        return [$business, $owner];
    }

    protected function addStaff(Business $business, string $role, array $userOverrides = []): User
    {
        $user = User::factory()->create($userOverrides);

        $this->addExistingUserAsStaff($business, $user, $role);

        return $user;
    }

    protected function addExistingUserAsStaff(Business $business, User $user, string $role): BusinessUser
    {
        return BusinessUser::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'role_id' => Role::findOrCreate($role, 'web')->id,
            'is_owner' => false,
            'status' => 'active',
        ]);
    }
}
