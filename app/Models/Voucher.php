<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'discount_amount', 'points_cost', 'quantity', 'expires_at'];

    protected function casts(): array
    {
        return ['discount_amount' => 'decimal:2', 'points_cost' => 'integer', 'quantity' => 'integer', 'expires_at' => 'datetime'];
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(VoucherRedemption::class);
    }
}
