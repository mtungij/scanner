<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->randomFloat(2, 10, 500);
        $payment = $total + fake()->randomFloat(2, 0, 100);

        return [
            'user_id' => User::factory(),
            'total_amount' => $total,
            'payment_received' => $payment,
            'change_given' => $payment - $total,
        ];
    }
}
