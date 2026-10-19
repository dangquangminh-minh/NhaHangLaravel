<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'recipient_name' => 'Khách thử nghiệm', 'phone' => '0901234567', 'address' => '123 Đường Mẫu, TP. Hồ Chí Minh', 'is_default' => true];
    }
}
