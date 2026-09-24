<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Every order gets exactly one invoice (spec §27 point 4) — generated right
 * after the order and its items exist, mirroring the order's own totals
 * rather than recalculating anything independently.
 */
class GenerateInvoiceAction
{
    public function execute(Order $order): Invoice
    {
        return DB::transaction(function () use ($order) {
            $status = match (true) {
                $order->amount_paid <= 0 => 'unpaid',
                $order->amount_paid < $order->total => 'partially_paid',
                default => 'paid',
            };

            $invoice = Invoice::create([
                'business_id' => $order->business_id,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'invoice_number' => $this->nextInvoiceNumber($order),
                'issue_date' => now()->toDateString(),
                'subtotal' => $order->subtotal,
                'discount_amount' => $order->discount_amount,
                'tax_amount' => $order->tax_amount,
                'delivery_fee' => $order->delivery_fee,
                'total' => $order->total,
                'amount_paid' => $order->amount_paid,
                'status' => $status,
            ]);

            foreach ($order->items as $item) {
                $invoice->items()->create([
                    'description' => $item->product_name_snapshot,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ]);
            }

            return $invoice;
        });
    }

    protected function nextInvoiceNumber(Order $order): string
    {
        return DB::transaction(function () use ($order) {
            $settings = $order->business->settings()->lockForUpdate()->first();
            $number = $settings->next_invoice_number;

            $settings->increment('next_invoice_number');

            return sprintf('%s-%s-%06d', $settings->invoice_prefix, now()->format('Y'), $number);
        });
    }
}
