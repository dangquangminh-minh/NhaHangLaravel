<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VoucherFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->unique()->words(3, true), 'discount_amount' => '20000.00', 'points_cost' => 200, 'quantity' => 10, 'expires_at' => now()->addMonth()];
    }
}
