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
        $buyPrice = fake()->randomFloat(2, 1, 300);

        return [
            'name' => fake()->words(3, true),
            'barcode' => fake()->unique()->numerify('############'),
            'buy_price' => $buyPrice,
            'price' => $buyPrice + fake()->randomFloat(2, 0.5, 200),
            'stock_quantity' => fake()->numberBetween(1, 100),
            'unit' => fake()->randomElement(['piece', 'kg', 'g', 'liter', 'ml', 'box', 'bag', 'pack']),
            'category' => fake()->randomElement(['Dairy', 'Beverages', 'Snacks', 'Groceries']),
            'expire_date' => fake()->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
