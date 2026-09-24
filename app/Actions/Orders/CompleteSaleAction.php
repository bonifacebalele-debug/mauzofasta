<?php

namespace App\Actions\Orders;

use App\Actions\Invoices\GenerateInvoiceAction;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Inventory\StockService;
use App\Services\Payment\PaymentServiceInterface;
use Illuminate\Support\Facades\DB;

/**
 * The Fast Sale flow (spec §27): product + quantity + customer + payment in,
 * one completed order out. Every total is recalculated here from the
 * current product prices — a client-submitted subtotal/total is never
 * trusted (spec §28, rule 12).
 */
class CompleteSaleAction
{
    public function __construct(
        protected StockService $stock,
        protected PaymentServiceInterface $payments,
        protected GenerateInvoiceAction $generateInvoice,
    ) {}

    /**
     * @param  array<int, array{product_id: int, variant_id?: ?int, quantity: int}>  $items
     * @param  array{amount: int, method: string, reference?: ?string}|null  $paymentData
     * @param  array{discount_type?: ?string, discount_value?: int, delivery_fee?: int, notes?: ?string}  $orderOptions
     */
    public function execute(
        Business $business,
        Customer $customer,
        array $items,
        ?array $paymentData,
        array $orderOptions = [],
        bool $canApplyDiscount = false,
        ?int $userId = null,
    ): Order {
        return DB::transaction(function () use ($business, $customer, $items, $paymentData, $orderOptions, $canApplyDiscount, $userId) {
            $lines = $this->resolveLines($items);
            $subtotal = array_sum(array_column($lines, 'lineTotal'));

            $discountAmount = 0;
            $discountType = $orderOptions['discount_type'] ?? null;

            if ($canApplyDiscount && $discountType && ! empty($orderOptions['discount_value'])) {
                $discountAmount = $discountType === 'percentage'
                    ? (int) round($subtotal * min(100, (int) $orderOptions['discount_value']) / 100)
                    : min((int) $orderOptions['discount_value'], $subtotal);
            }

            $taxableAmount = $subtotal - $discountAmount;
            $taxAmount = 0;
            $profile = $business->profile;

            if ($profile?->tax_enabled && $profile->tax_rate) {
                $taxAmount = (int) round($taxableAmount * (float) $profile->tax_rate / 100);
            }

            $deliveryFee = (int) ($orderOptions['delivery_fee'] ?? 0);
            $total = $taxableAmount + $taxAmount + $deliveryFee;

            $order = Order::create([
                'business_id' => $business->id,
                'customer_id' => $customer->id,
                'order_number' => $this->nextOrderNumber($business),
                'source' => 'pos',
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_type' => $discountAmount > 0 ? $discountType : null,
                'tax_amount' => $taxAmount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'amount_paid' => 0,
                'status' => 'pending_payment',
                'notes' => $orderOptions['notes'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'variant_id' => $line['variant']?->id,
                    'product_name_snapshot' => $line['product']->name,
                    'sku_snapshot' => $line['variant']?->sku ?? $line['product']->sku,
                    'unit_price' => $line['unitPrice'],
                    'quantity' => $line['quantity'],
                    'line_total' => $line['lineTotal'],
                ]);

                $this->stock->recordMovement(
                    $line['product'],
                    'sale',
                    -$line['quantity'],
                    $line['variant'],
                    $order,
                    userId: $userId,
                );
            }

            $order->statusHistory()->create([
                'from_status' => null,
                'to_status' => 'pending_payment',
                'changed_by' => $userId,
                'note' => 'Uuzaji mpya',
            ]);

            $this->generateInvoice->execute($order->refresh()->load('items'));

            if ($paymentData && $paymentData['amount'] > 0) {
                $this->payments->recordPayment($order, [...$paymentData, 'recorded_by' => $userId]);
            }

            return $order->refresh();
        });
    }

    /**
     * @return array<int, array{product: Product, variant: ?ProductVariant, unitPrice: int, quantity: int, lineTotal: int}>
     */
    protected function resolveLines(array $items): array
    {
        return array_map(function (array $item) {
            $product = Product::findOrFail($item['product_id']);
            $variant = ! empty($item['variant_id'])
                ? $product->variants()->findOrFail($item['variant_id'])
                : null;

            $quantity = max(1, (int) $item['quantity']);
            $unitPrice = $variant ? $variant->sellingPrice() : $product->selling_price;

            return [
                'product' => $product,
                'variant' => $variant,
                'unitPrice' => $unitPrice,
                'quantity' => $quantity,
                'lineTotal' => $unitPrice * $quantity,
            ];
        }, $items);
    }

    protected function nextOrderNumber(Business $business): string
    {
        return DB::transaction(function () use ($business) {
            $settings = $business->settings()->lockForUpdate()->first();
            $number = $settings->next_order_number;

            $settings->increment('next_order_number');

            return sprintf('%s-%s-%06d', $settings->order_prefix, now()->format('Y'), $number);
        });
    }
}
