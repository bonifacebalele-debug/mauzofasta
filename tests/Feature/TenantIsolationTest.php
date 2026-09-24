<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Product;
use App\Support\Tenancy\CurrentBusiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBusiness;
use Tests\TestCase;

/**
 * Release blocker per spec §73: Business A must never be able to read or
 * write Business B's data, however the request tries to get at it.
 */
class TenantIsolationTest extends TestCase
{
    use CreatesBusiness, RefreshDatabase;

    public function test_switching_current_business_scopes_queries_to_it(): void
    {
        [$businessA] = $this->createBusinessWithOwner();
        [$businessB] = $this->createBusinessWithOwner();

        Branch::forceCreate(['business_id' => $businessA->id, 'name' => 'A Branch']);
        Branch::forceCreate(['business_id' => $businessB->id, 'name' => 'B Branch']);

        app(CurrentBusiness::class)->set($businessA);
        $this->assertSame(['A Branch'], Branch::pluck('name')->all());

        app(CurrentBusiness::class)->set($businessB);
        $this->assertSame(['B Branch'], Branch::pluck('name')->all());
    }

    public function test_creating_a_record_auto_stamps_the_current_business(): void
    {
        [$business] = $this->createBusinessWithOwner();

        app(CurrentBusiness::class)->set($business);

        $branch = Branch::create(['name' => 'Auto-stamped']);

        $this->assertSame($business->id, $branch->business_id);
    }

    public function test_a_user_cannot_switch_into_a_business_they_do_not_belong_to(): void
    {
        [$businessA, $ownerA] = $this->createBusinessWithOwner();
        [$businessB] = $this->createBusinessWithOwner();

        $this->actingAs($ownerA)
            ->post(route('business.switch', $businessB))
            ->assertForbidden();
    }

    public function test_a_user_cannot_view_a_business_they_do_not_belong_to(): void
    {
        [, $ownerA] = $this->createBusinessWithOwner();
        [$businessB] = $this->createBusinessWithOwner();

        $this->assertFalse($ownerA->can('view', $businessB));
        $this->assertFalse($ownerA->can('update', $businessB));
    }

    public function test_resolving_current_business_ignores_a_tampered_session_value_for_a_foreign_business(): void
    {
        [$businessA, $ownerA] = $this->createBusinessWithOwner();
        [$businessB] = $this->createBusinessWithOwner();

        $this->withSession(['current_business_id' => $businessB->id])
            ->actingAs($ownerA)
            ->get(route('dashboard.index'))
            ->assertOk();

        $this->assertSame($businessA->id, session('current_business_id'));
    }

    public function test_manager_permission_check_is_scoped_to_the_currently_resolved_business(): void
    {
        [$businessA] = $this->createBusinessWithOwner();
        [$businessB] = $this->createBusinessWithOwner();

        $manager = $this->addStaff($businessA, 'manager');
        $this->addExistingUserAsStaff($businessB, $manager, 'sales_staff');

        app(CurrentBusiness::class)->set($businessA);
        $this->assertTrue($manager->hasBusinessPermission('products.create'));

        app(CurrentBusiness::class)->set($businessB);
        $this->assertFalse($manager->hasBusinessPermission('products.create'));
    }

    public function test_a_business_cannot_edit_or_delete_another_businesss_product(): void
    {
        [$businessA, $ownerA] = $this->createBusinessWithOwner();
        [$businessB] = $this->createBusinessWithOwner();

        app(CurrentBusiness::class)->set($businessB);
        $productB = Product::factory()->create(['business_id' => $businessB->id]);

        app(CurrentBusiness::class)->set($businessA);

        $this->actingAs($ownerA)->get(route('products.edit', $productB))->assertNotFound();
        $this->actingAs($ownerA)->put(route('products.update', $productB), [
            'name' => 'Hijacked',
            'selling_price' => 1,
            'status' => 'active',
        ])->assertNotFound();
        $this->actingAs($ownerA)->delete(route('products.destroy', $productB))->assertNotFound();

        $this->assertSame($businessB->id, $productB->fresh()->business_id);
        $this->assertNotSame('Hijacked', $productB->fresh()->name);
    }

    public function test_a_business_cannot_see_another_businesss_products_in_its_listing(): void
    {
        [$businessA, $ownerA] = $this->createBusinessWithOwner();
        [$businessB] = $this->createBusinessWithOwner();

        app(CurrentBusiness::class)->set($businessA);
        Product::factory()->create(['business_id' => $businessA->id, 'name' => 'Perfume A']);

        app(CurrentBusiness::class)->set($businessB);
        Product::factory()->create(['business_id' => $businessB->id, 'name' => 'Perfume B']);

        app(CurrentBusiness::class)->set($businessA);
        $response = $this->actingAs($ownerA)->get(route('products.index'));

        $response->assertSee('Perfume A');
        $response->assertDontSee('Perfume B');
    }

    public function test_a_business_cannot_view_or_edit_another_businesss_customer(): void
    {
        [$businessA, $ownerA] = $this->createBusinessWithOwner();
        [$businessB] = $this->createBusinessWithOwner();

        app(CurrentBusiness::class)->set($businessB);
        $customerB = Customer::factory()->create(['business_id' => $businessB->id]);

        app(CurrentBusiness::class)->set($businessA);

        $this->actingAs($ownerA)->get(route('customers.show', $customerB))->assertNotFound();
        $this->actingAs($ownerA)->put(route('customers.update', $customerB), [
            'name' => 'Hijacked',
            'phone' => $customerB->phone,
        ])->assertNotFound();

        $this->assertNotSame('Hijacked', $customerB->fresh()->name);
    }
}
