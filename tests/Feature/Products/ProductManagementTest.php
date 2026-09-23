<?php

namespace Tests\Feature\Products;

use App\Models\Category;
use App\Models\Product;
use App\Support\Tenancy\CurrentBusiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesBusiness;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use CreatesBusiness, RefreshDatabase;

    public function test_owner_can_create_a_product_with_opening_stock(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $response = $this->actingAs($owner)->post(route('products.store'), [
            'name' => 'Perfume 50ml',
            'selling_price' => 35000,
            'cost_price' => 20000,
            'stock_quantity' => 10,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Perfume 50ml')->first();
        $this->assertNotNull($product);
        $this->assertSame($business->id, $product->business_id);
        $this->assertSame(10, $product->stock_quantity);
        $this->assertSame('opening', $product->stockMovements()->first()->type);
    }

    public function test_sales_staff_cannot_create_a_product(): void
    {
        [$business] = $this->createBusinessWithOwner();
        $salesStaff = $this->addStaff($business, 'sales_staff');
        app(CurrentBusiness::class)->set($business);

        $this->actingAs($salesStaff)
            ->post(route('products.store'), ['name' => 'X', 'selling_price' => 1000, 'status' => 'active'])
            ->assertForbidden();
    }

    public function test_updating_a_product_does_not_accept_a_stock_quantity_field(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);

        $this->actingAs($owner)->put(route('products.update', $product), [
            'name' => $product->name,
            'selling_price' => $product->selling_price,
            'status' => 'active',
            'stock_quantity' => 9999,
        ]);

        $this->assertSame(10, $product->refresh()->stock_quantity);
    }

    public function test_deleting_a_product_soft_deletes_and_archives_it(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $product = Product::factory()->create(['business_id' => $business->id]);

        $this->actingAs($owner)->delete(route('products.destroy', $product));

        $this->assertSoftDeleted($product);
        $this->assertSame('archived', $product->withTrashed()->find($product->id)->status);
    }

    public function test_product_can_be_assigned_a_category_scoped_to_the_business(): void
    {
        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $category = Category::factory()->create(['business_id' => $business->id]);

        $this->actingAs($owner)->post(route('products.store'), [
            'name' => 'Bracelet',
            'category_id' => $category->id,
            'selling_price' => 15000,
            'status' => 'active',
        ])->assertRedirect(route('products.index'));

        $this->assertSame($category->id, Product::where('name', 'Bracelet')->first()->category_id);
    }

    public function test_product_image_upload_is_stored(): void
    {
        Storage::fake('public');

        [$business, $owner] = $this->createBusinessWithOwner();
        app(CurrentBusiness::class)->set($business);

        $this->actingAs($owner)->post(route('products.store'), [
            'name' => 'Necklace',
            'selling_price' => 25000,
            'status' => 'active',
            'image' => UploadedFile::fake()->image('necklace.png'),
        ]);

        $product = Product::where('name', 'Necklace')->first();
        Storage::disk('public')->assertExists($product->image_path);
    }
}
