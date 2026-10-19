<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderStatusFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->unique()->words(3, true)];
    }
}
