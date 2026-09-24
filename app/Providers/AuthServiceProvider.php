<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Policies\BusinessPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Business::class => BusinessPolicy::class,
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,
        Customer::class => CustomerPolicy::class,
        Order::class => OrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Super Admin abilities are checked explicitly (e.g. "admin.manage-businesses")
        // and never inherited by ordinary tenant permissions — a super admin has no
        // business role and must go through the Super Admin console for that data.
        Gate::before(function ($user, string $ability) {
            if ($user->is_super_admin && str_starts_with($ability, 'admin.')) {
                return true;
            }

            return null;
        });
    }
}
