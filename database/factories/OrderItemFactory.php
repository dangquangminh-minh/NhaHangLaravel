<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return ['order_id' => Order::factory(), 'dish_id' => Dish::factory(), 'dish_name' => 'Món ăn thử nghiệm', 'quantity' => 1, 'unit_price' => '100000.00'];
    }
}
