<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'barcode' => fake()->unique()->numerify('############'),
            'price' => fake()->randomFloat(2, 1, 500),
            'stock_quantity' => fake()->numberBetween(1, 100),
        ];
    }
}
