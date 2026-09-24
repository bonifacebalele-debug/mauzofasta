<?php

namespace App\Services\Payment;

use App\Actions\Receipts\GenerateReceiptAction;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class ManualPaymentService implements PaymentServiceInterface
{
    public function __construct(protected GenerateReceiptAction $generateReceipt) {}

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
            $order->refresh();

            $userId = $data['recorded_by'] ?? null;

            if ($order->isFullyPaid() && $order->status === 'pending_payment') {
                $order->transitionTo('paid', $userId, 'Malipo kamili');
            }

            $order->invoice?->syncFromOrder($order);

            $this->generateReceipt->execute($payment);

            return $payment;
        });
    }

    public function refund(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'refunded']);
            $payment->order->decrement('amount_paid', $payment->amount);
            $payment->order->refresh();
            $payment->order->invoice?->syncFromOrder($payment->order);

            // The original receipt is left exactly as issued — it's an
            // accurate historical record of what was received at the time
            // (spec rule 83: never delete historical transaction records).
            // The refunded payment's own status is the source of truth for
            // "this money came back."

            return $payment;
        });
    }
}
