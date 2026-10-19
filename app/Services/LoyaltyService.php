<?php

namespace App\Services;

use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoyaltyService
{
    public function balance(User $user): int
    {
        $paid = Money::cents((string) $user->orders()->whereIn('order_status_id', [4, 6])->where('is_paid', true)->sum('total'));

        return intdiv($paid, 1000000) * 10 - (int) $user->redemptions()->sum('points_spent');
    }

    public function redeem(User $user, Voucher $voucher): VoucherRedemption
    {
        return DB::transaction(function () use ($user, $voucher): VoucherRedemption {
            $customer = User::query()->lockForUpdate()->findOrFail($user->id);
            $offer = Voucher::query()->lockForUpdate()->findOrFail($voucher->id);
            if ($offer->quantity < 1 || $offer->expires_at->lte(now())) {
                throw ValidationException::withMessages(['voucher' => 'Voucher đã hết hoặc hết hạn.']);
            }
            if ($this->balance($customer) < $offer->points_cost) {
                throw ValidationException::withMessages(['voucher' => 'Bạn không đủ điểm để đổi voucher.']);
            }
            $offer->decrement('quantity');

            return $customer->redemptions()->create([
                'voucher_id' => $offer->id, 'code' => 'NH'.Str::upper(Str::random(20)),
                'points_spent' => $offer->points_cost, 'discount_amount' => $offer->discount_amount,
                'expires_at' => $offer->expires_at,
            ]);
        }, 3);
    }
}
