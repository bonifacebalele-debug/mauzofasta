<?php

use App\Models\Business;
use App\Support\Tenancy\CurrentBusiness;

if (! function_exists('current_business')) {
    function current_business(): ?Business
    {
        return app(CurrentBusiness::class)->get();
    }
}

if (! function_exists('money')) {
    /**
     * Format whole-shilling amounts as "Tsh 35,000" (spec §61 — TZS has no
     * subunit in everyday use, so amounts are stored/passed as integers).
     */
    function money(int $amount, string $currency = 'Tsh'): string
    {
        return $currency.' '.number_format($amount);
    }
}
