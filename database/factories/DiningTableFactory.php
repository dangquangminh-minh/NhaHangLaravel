<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DiningTableFactory extends Factory
{
    public function definition(): array
    {
        return ['number' => fake()->unique()->numberBetween(201, 65000)];
    }
}
