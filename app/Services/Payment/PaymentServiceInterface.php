<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;

/**
 * Provider-independent (spec rules 6-7). ManualPaymentService is the only
 * implementation today (cash/mobile-money reference recorded by staff);
 * future mobile money/gateway integrations implement this same contract
 * without orders/invoices ever coupling to a specific provider.
 */
interface PaymentServiceInterface
{
    /**
     * @param  array{amount: int, method: string, reference?: ?string, recorded_by?: ?int}  $data
     */
    public function recordPayment(Order $order, array $data): Payment;

    public function refund(Payment $payment): Payment;
}
