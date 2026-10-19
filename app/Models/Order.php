<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['order_status_id', 'payment_method', 'table_number', 'delivery_address', 'recipient_name', 'recipient_phone', 'note'];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'total' => 'decimal:2', 'is_paid' => 'boolean', 'payment_reported_at' => 'datetime', 'payment_verified_at' => 'datetime', 'refund_required' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function paymentVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function redemption(): BelongsTo
    {
        return $this->belongsTo(VoucherRedemption::class, 'voucher_redemption_id');
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'table_number', 'number');
    }

    public function isTerminal(): bool
    {
        return in_array($this->order_status_id, [4, 5, 6], true);
    }

    public function nextStatuses(): array
    {
        if ($this->isTerminal()) {
            return [];
        }
        if ($this->order_status_id === 1) {
            return $this->payment_method === 'transfer' && ! $this->is_paid ? [5] : [2, 5];
        }
        if ($this->order_status_id === 2) {
            return [3, 5];
        }
        if ($this->order_status_id === 3) {
            return [$this->payment_method === 'transfer' ? 6 : 4, 5];
        }

        return [];
    }

    public function paymentLabel(): string
    {
        if ($this->is_paid) {
            return 'Đã thanh toán';
        }

        return $this->payment_reported_at ? 'Khách báo đã chuyển khoản · Chờ xác minh' : 'Chưa thanh toán';
    }
}
