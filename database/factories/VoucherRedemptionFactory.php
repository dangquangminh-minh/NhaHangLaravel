<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VoucherRedemptionFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'voucher_id' => Voucher::factory(), 'code' => 'NH'.Str::upper(Str::random(20)), 'points_spent' => 200, 'discount_amount' => '20000.00', 'expires_at' => now()->addMonth(), 'used_at' => null];
    }
}
