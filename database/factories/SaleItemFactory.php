<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleItem>
 */
class SaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $buyPrice = fake()->randomFloat(2, 1, 200);
        $unitPrice = fake()->randomFloat(2, 1, 300);

        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'buy_price' => $buyPrice,
            'unit_price' => $unitPrice,
            'line_total' => $quantity * $unitPrice,
            'profit_amount' => $quantity * ($unitPrice - $buyPrice),
        ];
    }
}
