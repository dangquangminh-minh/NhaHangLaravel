<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    public function definition(): array
    {
        return ['code' => fake()->unique()->lexify('test_????'), 'name' => 'Vai trò thử nghiệm'];
    }
}
