<?php

namespace App\Services;

use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TableBookingService
{
    public function reserve(array $data, ?User $user): Reservation
    {
        return DB::transaction(function () use ($data, $user): Reservation {
            DiningTable::query()->whereKey($data['table_number'])->lockForUpdate()->firstOrFail();
            $at = Carbon::parse($data['reserved_at']);
            $duration = (int) config('restaurant.reservation_minutes', 120);
            $occupied = Reservation::query()->where('table_number', $data['table_number'])->whereIn('status', ['pending', 'confirmed'])->where('reserved_at', '>', $at->copy()->subMinutes($duration))->where('reserved_at', '<', $at->copy()->addMinutes($duration))->exists();
            if ($occupied) {
                throw ValidationException::withMessages(['table_number' => 'Bàn đã được đặt trong khung giờ này. Vui lòng chọn bàn hoặc giờ khác.']);
            }
            if ($user && strcasecmp($user->email, $data['email']) !== 0) {
                throw ValidationException::withMessages(['email' => 'Vui lòng đặt bàn bằng email của tài khoản đang đăng nhập.']);
            }
            if ($at->lte(now()->addMinutes($duration)) && Order::query()->where('table_number', $data['table_number'])->whereIn('order_status_id', [1, 2, 3])->exists()) {
                throw ValidationException::withMessages(['table_number' => 'Bàn đang phục vụ một đơn chưa kết thúc.']);
            }
            $booking = new Reservation($data);
            $booking->email = strtolower(trim($data['email']));
            $booking->user_id = $user?->id;
            $booking->save();

            return $booking;
        }, 3);
    }

    public function checkOrderTable(int $number, User $user): void
    {
        DiningTable::query()->whereKey($number)->lockForUpdate()->firstOrFail();
        $duration = (int) config('restaurant.reservation_minutes', 120);
        $held = Reservation::query()->where('table_number', $number)->whereIn('status', ['pending', 'confirmed'])->whereBetween('reserved_at', [now()->subMinutes($duration), now()->addMinutes(30)])->get();
        foreach ($held as $booking) {
            if (strcasecmp(trim($booking->email), trim($user->email)) !== 0) {
                throw ValidationException::withMessages(['table_number' => 'Bàn đã được đặt bởi email khác. Vui lòng dùng đúng tài khoản đặt bàn hoặc chọn bàn khác.']);
            }
        }
        if (Order::query()->where('table_number', $number)->whereIn('order_status_id', [1, 2, 3])->where('user_id', '!=', $user->id)->exists()) {
            throw ValidationException::withMessages(['table_number' => 'Bàn đã được đặt hoặc đang có khách khác sử dụng.']);
        }
    }
}
