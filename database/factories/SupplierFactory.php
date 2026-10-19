<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->unique()->company(), 'address' => 'TP. Hồ Chí Minh', 'phone' => '0901234567'];
    }
}
