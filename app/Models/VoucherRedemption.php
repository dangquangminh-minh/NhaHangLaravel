<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VoucherRedemption extends Model
{
    use HasFactory;

    protected $fillable = ['voucher_id', 'code', 'points_spent', 'discount_amount', 'expires_at', 'used_at'];

    protected function casts(): array
    {
        return ['points_spent' => 'integer', 'discount_amount' => 'decimal:2', 'expires_at' => 'datetime', 'used_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}
