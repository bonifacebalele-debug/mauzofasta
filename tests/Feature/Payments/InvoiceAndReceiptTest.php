<?php

namespace Tests\Feature\Payments;

use App\Actions\Orders\CompleteSaleAction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Receipt;
use App\Support\Tenancy\CurrentBusiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBusiness;
use Tests\TestCase;

class InvoiceAndReceiptTest extends TestCase
{
    use CreatesBusiness, RefreshDatabase;

    public function test_every_sale_generates_an_invoice_matching_its_totals(): void
    {
        [$business] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 15000]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $order = app(CompleteSaleAction::class)->execute(
            $business, $customer, [['product_id' => $product->id, 'quantity' => 2]], null
        );

        $invoice = $order->invoice;
        $this->assertNotNull($invoice);
        $this->assertSame(30000, $invoice->total);
        $this->assertSame('unpaid', $invoice->status);
        $this->assertSame(1, $invoice->items()->count());
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }

    public function test_invoice_numbers_are_sequential_per_business(): void
    {
        [$business] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);
        $action = app(CompleteSaleAction::class);

        $first = $action->execute($business, $customer, [['product_id' => $product->id, 'quantity' => 1]], null);
        $second = $action->execute($business, $customer, [['product_id' => $product->id, 'quantity' => 1]], null);

        $this->assertNotSame($first->invoice->invoice_number, $second->invoice->invoice_number);
    }

    public function test_a_confirmed_payment_generates_its_own_receipt_and_updates_invoice_status(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 10000]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $order = app(CompleteSaleAction::class)->execute(
            $business, $customer, [['product_id' => $product->id, 'quantity' => 1]], null
        );

        $this->actingAs($owner)->post(route('payments.store'), [
            'order_id' => $order->id,
            'amount' => 4000,
            'method' => 'cash',
        ])->assertRedirect();

        $this->assertSame(1, Receipt::count());
        $this->assertSame('partially_paid', $order->invoice->fresh()->status);

        $this->actingAs($owner)->post(route('payments.store'), [
            'order_id' => $order->id,
            'amount' => 6000,
            'method' => 'cash',
        ])->assertRedirect();

        $this->assertSame(2, Receipt::count());
        $this->assertSame('paid', $order->invoice->fresh()->status);
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_payment_amount_cannot_exceed_the_order_balance(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 10000]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $order = app(CompleteSaleAction::class)->execute(
            $business, $customer, [['product_id' => $product->id, 'quantity' => 1]], null
        );

        $this->actingAs($owner)->post(route('payments.store'), [
            'order_id' => $order->id,
            'amount' => 50000,
            'method' => 'cash',
        ])->assertSessionHasErrors('amount');

        $this->assertSame(0, $order->fresh()->amount_paid);
    }

    public function test_refunding_a_payment_keeps_the_receipt_as_historical_record(): void
    {
        [$business] = $this->createBusinessWithOwner();
        $accountant = $this->addStaff($business, 'accountant');
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 10000]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $order = app(CompleteSaleAction::class)->execute(
            $business, $customer, [['product_id' => $product->id, 'quantity' => 1]],
            ['amount' => 10000, 'method' => 'cash']
        );

        $payment = $order->payments()->first();
        $receipt = $payment->receipt;

        $this->actingAs($accountant)->post(route('payments.refund', $payment))->assertRedirect();

        $this->assertSame('refunded', $payment->fresh()->status);
        $this->assertDatabaseHas('receipts', ['id' => $receipt->id]);
        $this->assertSame(0, $order->fresh()->amount_paid);
    }

    public function test_sales_staff_cannot_refund_a_payment(): void
    {
        [$business] = $this->createBusinessWithOwner();
        $salesStaff = $this->addStaff($business, 'sales_staff');
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 10000]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $order = app(CompleteSaleAction::class)->execute(
            $business, $customer, [['product_id' => $product->id, 'quantity' => 1]],
            ['amount' => 10000, 'method' => 'cash']
        );

        $payment = $order->payments()->first();

        $this->actingAs($salesStaff)->post(route('payments.refund', $payment))->assertForbidden();
    }

    public function test_invoice_and_receipt_pdfs_download_successfully(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 10000]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $order = app(CompleteSaleAction::class)->execute(
            $business, $customer, [['product_id' => $product->id, 'quantity' => 1]],
            ['amount' => 10000, 'method' => 'cash']
        );

        $this->actingAs($owner)->get(route('invoices.pdf', $order->invoice))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $receipt = $order->payments()->first()->receipt;

        $this->actingAs($owner)->get(route('receipts.pdf', $receipt))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_a_business_cannot_view_another_businesss_invoice_or_receipt(): void
    {
        [$businessA, $ownerA] = $this->createBusinessWithOwner();
        [$businessB] = $this->createBusinessWithOwner();

        app(CurrentBusiness::class)->set($businessB);
        $product = Product::factory()->create(['business_id' => $businessB->id, 'selling_price' => 10000]);
        $customer = Customer::factory()->create(['business_id' => $businessB->id]);

        $orderB = app(CompleteSaleAction::class)->execute(
            $businessB, $customer, [['product_id' => $product->id, 'quantity' => 1]],
            ['amount' => 10000, 'method' => 'cash']
        );

        // Captured while still in businessB's tenant context — fetching
        // these after switching would itself be correctly scoped to null.
        $invoiceB = $orderB->invoice;
        $receiptB = $orderB->payments()->first()->receipt;

        app(CurrentBusiness::class)->set($businessA);

        $this->actingAs($ownerA)->get(route('invoices.show', $invoiceB))->assertNotFound();
        $this->actingAs($ownerA)->get(route('receipts.show', $receiptB))->assertNotFound();
    }
}
