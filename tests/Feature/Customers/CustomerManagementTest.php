<?php

namespace Tests\Feature\Customers;

use App\Models\Customer;
use App\Models\Order;
use App\Support\Tenancy\CurrentBusiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBusiness;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use CreatesBusiness, RefreshDatabase;

    public function test_owner_can_create_a_customer(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $this->actingAs($owner)->post(route('customers.store'), [
            'name' => 'Asha Mohamed',
            'phone' => '0712345678',
        ])->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', [
            'business_id' => $business->id,
            'name' => 'Asha Mohamed',
            'phone' => '0712345678',
        ]);
    }

    public function test_duplicate_phone_within_the_same_business_is_rejected(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        Customer::factory()->create(['business_id' => $business->id, 'phone' => '0712345678']);

        $this->actingAs($owner)->post(route('customers.store'), [
            'name' => 'Another Person',
            'phone' => '0712345678',
        ])->assertSessionHasErrors('phone');
    }

    public function test_same_phone_is_allowed_across_different_businesses(): void
    {
        [$businessA, $ownerA] = $this->createBusinessWithOwner();
        [$businessB] = $this->createBusinessWithOwner();

        app(CurrentBusiness::class)->set($businessB);
        Customer::factory()->create(['business_id' => $businessB->id, 'phone' => '0712345678']);

        app(CurrentBusiness::class)->set($businessA);

        $this->actingAs($ownerA)->post(route('customers.store'), [
            'name' => 'Asha Mohamed',
            'phone' => '0712345678',
        ])->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', ['business_id' => $businessA->id, 'phone' => '0712345678']);
    }

    public function test_customer_profile_shows_totals_and_outstanding_balance(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $customer = Customer::factory()->create(['business_id' => $business->id]);

        Order::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-2026-000001',
            'subtotal' => 10000,
            'total' => 10000,
            'amount_paid' => 4000,
            'status' => 'pending_payment',
        ]);

        $this->assertSame(4000, $customer->totalSpent());
        $this->assertSame(6000, $customer->outstandingBalance());

        $this->actingAs($owner)->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('6,000');
    }

    public function test_staff_can_add_a_note_to_a_customer(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $customer = Customer::factory()->create(['business_id' => $business->id]);

        $this->actingAs($owner)->post(route('customers.notes.store', $customer), [
            'note' => 'Anapenda manukato ya Mikocheni.',
        ])->assertRedirect(route('customers.show', $customer));

        $this->assertDatabaseHas('customer_notes', [
            'customer_id' => $customer->id,
            'note' => 'Anapenda manukato ya Mikocheni.',
        ]);
    }
}
