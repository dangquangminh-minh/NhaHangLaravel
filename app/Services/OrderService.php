<?php

namespace App\Services;

use App\Models\Dish;
use App\Models\Order;
use App\Models\User;
use App\Models\VoucherRedemption;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /** @param array<int, int> $cart @param array<string, mixed> $data */
    public function checkout(User $user, array $cart, array $data): Order
    {
        return DB::transaction(function () use ($user, $cart, $data): Order {
            User::query()->lockForUpdate()->findOrFail($user->id);
            $existing = $user->orders()->where('checkout_token', $data['checkout_token'])->first();
            if ($existing) {
                return $existing;
            }
            if (count($cart) < 1 || count($cart) > 50) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng phải có từ 1 đến 50 món.']);
            }
            $dishes = Dish::query()->whereIn('id', array_keys($cart))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0;
            foreach ($cart as $id => $quantity) {
                if (! isset($dishes[$id]) || ! $dishes[$id]->is_available || ! is_int($quantity) || $quantity < 1 || $quantity > 99) {
                    throw ValidationException::withMessages(['cart' => 'Có món ngừng phục vụ hoặc số lượng không hợp lệ. Vui lòng cập nhật giỏ.']);
                }
                $subtotal += Money::cents($dishes[$id]->price) * $quantity;
            }
            $redemption = null;
            $discount = 0;
            if (! empty($data['voucher_code'])) {
                $redemption = $user->redemptions()->where('code', $data['voucher_code'])->lockForUpdate()->first();
                if (! $redemption || $redemption->used_at || $redemption->expires_at->lte(now())) {
                    throw ValidationException::withMessages(['voucher_code' => 'Voucher không thuộc bạn, đã dùng hoặc đã hết hạn.']);
                }
                $discount = min($subtotal, Money::cents($redemption->discount_amount));
            }
            $address = null;
            if (! empty($data['address_id'])) {
                $address = $user->addresses()->lockForUpdate()->find($data['address_id']);
                if (! $address) {
                    throw ValidationException::withMessages(['address_id' => 'Địa chỉ không thuộc tài khoản của bạn.']);
                }
            }
            if ($address && ! empty($data['table_number'])) {
                throw ValidationException::withMessages(['table_number' => 'Chọn giao hàng hoặc số bàn, không chọn cả hai.']);
            }
            if (! empty($data['table_number'])) {
                app(TableBookingService::class)->checkOrderTable((int) $data['table_number'], $user);
            }
            $order = new Order;
            $order->forceFill([
                'user_id' => $user->id, 'order_status_id' => 1, 'checkout_token' => $data['checkout_token'],
                'voucher_redemption_id' => $redemption?->id, 'subtotal' => Money::decimal($subtotal),
                'discount' => Money::decimal($discount), 'total' => Money::decimal($subtotal - $discount),
                'is_paid' => false, 'payment_method' => $data['payment_method'], 'table_number' => $data['table_number'] ?? null,
                'delivery_address' => $address?->address, 'recipient_name' => $address?->recipient_name, 'recipient_phone' => $address?->phone,
                'note' => $data['note'] ?? null,
            ])->save();
            foreach ($cart as $id => $quantity) {
                $order->items()->create(['dish_id' => $id, 'dish_name' => $dishes[$id]->name, 'unit_price' => $dishes[$id]->price, 'quantity' => $quantity]);
            }
            if ($redemption) {
                $redemption->update(['used_at' => now()]);
            }

            return $order;
        }, 3);
    }

    public function changeStatus(Order $order, int $nextStatus, User $actor): void
    {
        DB::transaction(function () use ($order, $nextStatus, $actor): void {
            User::query()->lockForUpdate()->findOrFail($order->user_id);
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            if (! $actor->isAdmin() && ($locked->user_id !== $actor->id || $nextStatus !== 5 || $locked->order_status_id !== 1)) {
                throw ValidationException::withMessages(['order_status_id' => 'Bạn chỉ được hủy đơn của mình khi đang chờ xác nhận.']);
            }
            if (! in_array($nextStatus, $locked->nextStatuses(), true)) {
                throw ValidationException::withMessages(['order_status_id' => 'Phải chuyển đúng từng bước; không được quay lại hoặc đổi đơn đã kết thúc. Online cần xác minh thanh toán trước khi làm.']);
            }
            if ($nextStatus === 5 && $locked->voucher_redemption_id) {
                VoucherRedemption::query()->whereKey($locked->voucher_redemption_id)->update(['used_at' => null]);
                $locked->voucher_redemption_id = null;
            }
            if ($nextStatus === 5) {
                $locked->refund_required = $locked->is_paid;
            }
            if ($nextStatus === 4) {
                $locked->is_paid = true;
                $locked->payment_verified_at = now();
                $locked->payment_verified_by = $actor->id;
            }
            if ($nextStatus === 6 && ! $locked->is_paid) {
                throw ValidationException::withMessages(['order_status_id' => 'Đơn online chưa được xác minh thanh toán.']);
            }
            $locked->order_status_id = $nextStatus;
            if ($actor->isAdmin()) {
                $locked->handled_by = $actor->id;
            }
            $locked->save();
        }, 3);
    }

    public function reportPayment(Order $order, User $actor): void
    {
        DB::transaction(function () use ($order, $actor): void {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            abort_unless($locked->user_id === $actor->id && $actor->hasRole('user'), 403);
            if ($locked->payment_method !== 'transfer' || $locked->isTerminal() || $locked->is_paid || $locked->payment_reported_at) {
                throw ValidationException::withMessages(['payment' => 'Không thể gửi lại thông báo thanh toán cho đơn này.']);
            }
            $locked->payment_reported_at = now();
            $locked->save();
        }, 3);
    }

    public function verifyPayment(Order $order, User $actor): void
    {
        abort_unless($actor->isAdmin(), 403);
        DB::transaction(function () use ($order, $actor): void {
            User::query()->lockForUpdate()->findOrFail($order->user_id);
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($locked->payment_method !== 'transfer' || $locked->isTerminal() || $locked->is_paid || ! $locked->payment_reported_at) {
                throw ValidationException::withMessages(['payment' => 'Chỉ xác minh một lần cho đơn chuyển khoản đã được khách báo thanh toán.']);
            }
            $locked->is_paid = true;
            $locked->payment_verified_at = now();
            $locked->payment_verified_by = $actor->id;
            $locked->save();
        }, 3);
    }
}
