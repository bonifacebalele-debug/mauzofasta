<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class ManualPaymentService implements PaymentServiceInterface
{
    public function recordPayment(Order $order, array $data): Payment
    {
        return DB::transaction(function () use ($order, $data) {
            $payment = Payment::create([
                'business_id' => $order->business_id,
                'order_id' => $order->id,
                'amount' => $data['amount'],
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'status' => 'confirmed',
                'paid_at' => now(),
                'recorded_by' => $data['recorded_by'] ?? null,
            ]);

            $order->increment('amount_paid', $data['amount']);

            return $payment;
        });
    }

    public function refund(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'refunded']);
            $payment->order->decrement('amount_paid', $payment->amount);

            return $payment;
        });
    }
}
