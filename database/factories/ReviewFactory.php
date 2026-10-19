<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'dish_id' => Dish::factory(), 'rating' => 5, 'message' => 'Món ăn ngon, phục vụ chu đáo.'];
    }
}
