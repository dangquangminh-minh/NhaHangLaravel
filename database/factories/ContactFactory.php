<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => 'Khách thử nghiệm', 'email' => 'contact@example.test', 'phone' => '0901234567', 'subject' => 'Hỏi thông tin đặt bàn', 'message' => 'Tôi muốn đặt bàn cho gia đình.', 'is_read' => false];
    }
}
