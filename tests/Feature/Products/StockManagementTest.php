<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Inventory\StockService;
use App\Support\Tenancy\CurrentBusiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBusiness;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use CreatesBusiness, RefreshDatabase;

    public function test_manual_adjustment_updates_quantity_and_writes_ledger_rows(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);

        $this->actingAs($owner)->put(route('products.stock.update', $product), [
            'new_quantity' => 25,
            'reason' => 'Hesabu ya kila mwezi',
        ])->assertRedirect(route('products.stock.edit', $product));

        $product->refresh();
        $this->assertSame(25, $product->stock_quantity);

        $adjustment = $product->fresh()->stockMovements()->where('type', 'adjustment')->first();
        $this->assertNotNull($adjustment);
        $this->assertSame(15, $adjustment->quantity);

        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $product->id,
            'before_quantity' => 10,
            'after_quantity' => 25,
        ]);
    }

    public function test_stock_service_never_updates_quantity_without_a_ledger_row(): void
    {
        [$business] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 5]);

        app(StockService::class)->recordMovement($product, 'purchase', 20);

        $product->refresh();
        $this->assertSame(25, $product->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'purchase',
            'quantity' => 20,
        ]);
    }

    public function test_variant_stock_can_be_adjusted_independently_of_the_parent_product(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'has_variants' => true, 'stock_quantity' => 0]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'attributes' => ['size' => 'M'],
            'stock_quantity' => 4,
        ]);

        $this->actingAs($owner)->put(route('products.stock.update', $product), [
            'variant_id' => $variant->id,
            'new_quantity' => 12,
            'reason' => 'Ununuzi mpya',
        ]);

        $this->assertSame(12, $variant->refresh()->stock_quantity);
        $this->assertSame(0, $product->refresh()->stock_quantity);
    }

    public function test_sales_staff_cannot_adjust_stock(): void
    {
        [$business] = $this->createBusinessWithOwner();
        $salesStaff = $this->addStaff($business, 'sales_staff');
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id]);

        $this->actingAs($salesStaff)->put(route('products.stock.update', $product), [
            'new_quantity' => 100,
            'reason' => 'test',
        ])->assertForbidden();
    }

    public function test_stock_manager_can_adjust_stock(): void
    {
        [$business] = $this->createBusinessWithOwner();
        $stockManager = $this->addStaff($business, 'stock_manager');
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 5]);

        $this->actingAs($stockManager)->put(route('products.stock.update', $product), [
            'new_quantity' => 8,
            'reason' => 'test',
        ])->assertRedirect();

        $this->assertSame(8, $product->refresh()->stock_quantity);
    }

    public function test_low_stock_product_appears_in_business_alerts(): void
    {
        [$business] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        Product::factory()->lowStock()->create(['business_id' => $business->id, 'name' => 'Perfume 50ml']);

        $alerts = $business->activeAlerts();

        $this->assertTrue(collect($alerts)->contains(fn ($alert) => str_contains($alert['text'], 'Perfume 50ml')));
    }
}
