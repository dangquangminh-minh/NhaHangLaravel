<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'name' => 'Khách thử nghiệm', 'email' => 'booking@example.test', 'phone' => '0901234567', 'reserved_at' => now()->addDay(), 'guests' => 4, 'status' => 'pending'];
    }
}
