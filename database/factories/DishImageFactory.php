<?php

namespace Database\Factories;

use App\Models\Dish;
use Illuminate\Database\Eloquent\Factories\Factory;

class DishImageFactory extends Factory
{
    public function definition(): array
    {
        return ['dish_id' => Dish::factory(), 'path' => 'assets/images/menu-2.jpg'];
    }
}
