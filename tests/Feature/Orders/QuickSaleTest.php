<?php

namespace Tests\Feature\Orders;

use App\Actions\Orders\CompleteSaleAction;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Support\Tenancy\CurrentBusiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBusiness;
use Tests\TestCase;

/**
 * Fast Sale (spec §27): product + quantity + customer + payment in, one
 * completed order out, with every total recalculated server-side (§28).
 */
class QuickSaleTest extends TestCase
{
    use CreatesBusiness, RefreshDatabase;

    public function test_completing_a_sale_creates_order_decrements_stock_and_marks_it_paid(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 35000, 'stock_quantity' => 10]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $response = $this->actingAs($owner)->post(route('orders.quickSale.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'payment_method' => 'cash',
            'payment_amount' => 70000,
        ]);

        $order = Order::first();
        $response->assertRedirect(route('orders.show', $order));

        $this->assertSame(70000, $order->subtotal);
        $this->assertSame(70000, $order->total);
        $this->assertSame(70000, $order->amount_paid);
        $this->assertSame('paid', $order->status);
        $this->assertSame(8, $product->refresh()->stock_quantity);
        $this->assertSame(1, $order->payments()->count());
        $this->assertSame(2, $order->statusHistory()->count());
    }

    public function test_partial_payment_leaves_the_order_pending(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 10000, 'stock_quantity' => 5]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $this->actingAs($owner)->post(route('orders.quickSale.store'), [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'payment_amount' => 4000,
        ]);

        $order = Order::first();
        $this->assertSame('pending_payment', $order->status);
        $this->assertSame(6000, $order->balance());
    }

    public function test_sale_with_no_payment_creates_an_unpaid_order(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 10000]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $this->actingAs($owner)->post(route('orders.quickSale.store'), [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame(0, $order->amount_paid);
        $this->assertSame('pending_payment', $order->status);
        $this->assertSame(0, $order->payments()->count());
    }

    public function test_a_new_customer_is_created_inline_during_the_sale(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 5000]);

        $this->actingAs($owner)->post(route('orders.quickSale.store'), [
            'new_customer_name' => 'Neema John',
            'new_customer_phone' => '0755123456',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertRedirect();

        $this->assertDatabaseHas('customers', [
            'business_id' => $business->id,
            'name' => 'Neema John',
            'phone' => '0755123456',
        ]);
    }

    public function test_client_submitted_totals_are_ignored_and_recalculated_server_side(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 20000]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $this->actingAs($owner)->post(route('orders.quickSale.store'), [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
            // These fields do not exist on QuickSaleRequest and must have zero effect.
            'total' => 1,
            'subtotal' => 1,
            'amount_paid' => 999999,
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame(60000, $order->subtotal);
        $this->assertSame(60000, $order->total);
        $this->assertSame(0, $order->amount_paid);
    }

    public function test_sales_staff_without_permission_cannot_apply_a_discount(): void
    {
        [$business] = $this->createBusinessWithOwner();
        $salesStaff = $this->addStaff($business, 'sales_staff');
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 10000]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $this->actingAs($salesStaff)->post(route('orders.quickSale.store'), [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'discount_type' => 'fixed',
            'discount_value' => 5000,
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame(0, $order->discount_amount);
        $this->assertSame(10000, $order->total);
    }

    public function test_manager_with_permission_can_apply_a_percentage_discount(): void
    {
        [$business] = $this->createBusinessWithOwner();
        $manager = $this->addStaff($business, 'manager');
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 10000]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $this->actingAs($manager)->post(route('orders.quickSale.store'), [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame(1000, $order->discount_amount);
        $this->assertSame(9000, $order->total);
    }

    public function test_tax_is_applied_when_the_business_has_tax_enabled(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        BusinessProfile::where('business_id', $business->id)->update([
            'tax_enabled' => true,
            'tax_rate' => 18,
        ]);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 10000]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $this->actingAs($owner)->post(route('orders.quickSale.store'), [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame(1800, $order->tax_amount);
        $this->assertSame(11800, $order->total);
    }

    public function test_cancelling_an_order_restocks_items_and_logs_history(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'selling_price' => 10000, 'stock_quantity' => 10]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $this->actingAs($owner)->post(route('orders.quickSale.store'), [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ]);

        $order = Order::first();
        $this->assertSame(7, $product->refresh()->stock_quantity);

        $this->actingAs($owner)->post(route('orders.cancel', $order), [
            'reason' => 'Mteja alibadili mawazo',
        ])->assertRedirect();

        $this->assertSame('cancelled', $order->refresh()->status);
        $this->assertSame(10, $product->refresh()->stock_quantity);
        $this->assertSame(2, $order->statusHistory()->count());
    }

    public function test_sales_staff_cannot_cancel_an_order(): void
    {
        [$business] = $this->createBusinessWithOwner();
        $salesStaff = $this->addStaff($business, 'sales_staff');
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $this->actingAs($salesStaff)->post(route('orders.quickSale.store'), [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $order = Order::first();

        $this->actingAs($salesStaff)->post(route('orders.cancel', $order), [
            'reason' => 'test',
        ])->assertForbidden();
    }

    public function test_a_business_cannot_view_or_cancel_another_businesss_order(): void
    {
        [$businessA, $ownerA] = $this->createBusinessWithOwner();
        [$businessB] = $this->createBusinessWithOwner();

        app(CurrentBusiness::class)->set($businessB);
        $productB = Product::factory()->create(['business_id' => $businessB->id]);
        $customerB = Customer::factory()->create(['business_id' => $businessB->id]);
        $orderB = app(CompleteSaleAction::class)->execute(
            $businessB, $customerB, [['product_id' => $productB->id, 'quantity' => 1]], null
        );

        app(CurrentBusiness::class)->set($businessA);

        $this->actingAs($ownerA)->get(route('orders.show', $orderB))->assertNotFound();
        $this->actingAs($ownerA)->post(route('orders.cancel', $orderB), ['reason' => 'x'])->assertNotFound();
    }
}
