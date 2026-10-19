<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return ['role_id' => Role::query()->where('code', 'user')->value('id') ?? 1, 'name' => fake()->name(), 'email' => fake()->unique()->safeEmail(), 'email_verified_at' => now(), 'password' => 'Demo@12345', 'phone' => '090'.fake()->numerify('#######'), 'remember_token' => Str::random(10)];
    }

    public function admin(): static
    {
        return $this->state(fn (): array => ['role_id' => Role::query()->where('code', 'admin')->sole()->id]);
    }

    public function owner(): static
    {
        return $this->state(fn (): array => ['role_id' => Role::query()->where('code', 'owner')->sole()->id]);
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => ['email_verified_at' => null]);
    }
}
