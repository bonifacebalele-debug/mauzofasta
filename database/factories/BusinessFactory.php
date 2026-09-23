<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'owner_user_id' => User::factory(),
            'phone' => fake()->unique()->numerify('07########'),
            'email' => fake()->unique()->companyEmail(),
            'category' => 'Rejareja',
            'region' => 'Dar es Salaam',
            'district' => 'Kinondoni',
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(30),
        ];
    }
}
