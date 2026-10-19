<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class DishFactory extends Factory
{
    public function definition(): array
    {
        return ['category_id' => Category::factory(), 'supplier_id' => Supplier::factory(), 'name' => fake()->unique()->words(4, true), 'price' => '55000.00', 'description' => 'Món ăn thử nghiệm', 'is_available' => true, 'image' => 'assets/images/menu-1.jpg'];
    }
}
