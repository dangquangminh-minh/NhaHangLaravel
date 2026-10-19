<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'order_status_id' => 1, 'checkout_token' => (string) Str::uuid(), 'subtotal' => '100000.00', 'discount' => '0.00', 'total' => '100000.00', 'payment_method' => 'cash', 'is_paid' => false];
    }
}
