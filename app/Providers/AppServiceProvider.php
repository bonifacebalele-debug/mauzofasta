<?php

namespace App\Providers;

use App\Services\Payment\ManualPaymentService;
use App\Services\Payment\PaymentServiceInterface;
use App\Support\Tenancy\CurrentBusiness;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrentBusiness::class);

        // All payment methods today (cash, mobile money reference numbers)
        // are recorded manually by staff — ManualPaymentService is the only
        // implementation until a real gateway/mobile-money API is
        // integrated. Never bind a specific provider directly into
        // controllers/actions (spec rules 6-7).
        $this->app->bind(PaymentServiceInterface::class, ManualPaymentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
