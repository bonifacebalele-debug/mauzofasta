<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'sku' => fake()->unique()->bothify('SKU-####??'),
            'selling_price' => fake()->numberBetween(5000, 100000),
            'cost_price' => fake()->numberBetween(2000, 50000),
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
            'status' => 'active',
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn () => [
            'stock_quantity' => 3,
            'low_stock_threshold' => 5,
        ]);
    }
}
