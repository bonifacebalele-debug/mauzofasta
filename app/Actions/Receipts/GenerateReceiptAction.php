<?php

namespace App\Actions\Receipts;

use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;

/**
 * Every confirmed payment gets its own receipt (spec §33) — a partial
 * payment is still a real transaction and gets proof of it, not just the
 * final payment that completes the order.
 */
class GenerateReceiptAction
{
    public function execute(Payment $payment): Receipt
    {
        return DB::transaction(function () use ($payment) {
            $order = $payment->order;

            return Receipt::create([
                'business_id' => $payment->business_id,
                'invoice_id' => $order->invoice?->id,
                'payment_id' => $payment->id,
                'customer_id' => $order->customer_id,
                'receipt_number' => $this->nextReceiptNumber($payment),
                'amount' => $payment->amount,
                'method' => $payment->method,
                'transaction_reference' => $payment->reference,
                'issued_at' => $payment->paid_at,
            ]);
        });
    }

    protected function nextReceiptNumber(Payment $payment): string
    {
        return DB::transaction(function () use ($payment) {
            $settings = $payment->business->settings()->lockForUpdate()->first();
            $number = $settings->next_receipt_number;

            $settings->increment('next_receipt_number');

            return sprintf('%s-%s-%06d', $settings->receipt_prefix, now()->format('Y'), $number);
        });
    }
}
