<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use App\Services\LoyaltyService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoyaltyTest extends TestCase
{
    public function test_points_use_aggregate_paid_total_and_preserve_spent_history(): void
    {
        $user = User::factory()->create();
        Order::factory()->for($user)->create(['order_status_id' => 4, 'is_paid' => true, 'total' => '15000']);
        Order::factory()->for($user)->create(['order_status_id' => 4, 'is_paid' => true, 'total' => '9999']);
        Order::factory()->for($user)->create(['total' => '999999']);
        VoucherRedemption::factory()->for($user)->create(['points_spent' => 5, 'used_at' => now()]);
        $this->assertSame(15, app(LoyaltyService::class)->balance($user));
    }

    public function test_redemption_deducts_points_and_inventory_once(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        Order::factory()->for($user)->create(['order_status_id' => 4, 'is_paid' => true, 'total' => '300000']);
        $voucher = Voucher::factory()->create(['quantity' => 1]);
        $this->actingAs($user)->post(route('vouchers.redeem', $voucher))->assertRedirect(route('profile.edit'));
        $this->assertSame(0, $voucher->fresh()->quantity);
        $this->assertSame(100, app(LoyaltyService::class)->balance($user));
        $this->assertDatabaseHas('voucher_redemptions', ['user_id' => $user->id, 'voucher_id' => $voucher->id, 'points_spent' => 200, 'discount_amount' => '20000']);
        $this->post(route('vouchers.redeem', $voucher))->assertSessionHasErrors('voucher');
        $this->assertDatabaseCount('voucher_redemptions', 1);
    }

    public static function unavailableOffers(): array
    {
        return ['hết kho' => [['quantity' => 0]], 'hết hạn' => [['expires_at' => '2020-01-01 00:00:00']]];
    }

    #[DataProvider('unavailableOffers')]
    public function test_redemption_rejects_unavailable_offers(array $state): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        Order::factory()->for($user)->create(['order_status_id' => 4, 'is_paid' => true, 'total' => '500000']);
        $voucher = Voucher::factory()->create($state);
        $this->actingAs($user)->post(route('vouchers.redeem', $voucher))->assertSessionHasErrors('voucher');
        $this->assertDatabaseCount('voucher_redemptions', 0);
    }

    public function test_insufficient_points_do_not_change_inventory(): void
    {
        $user = User::factory()->create();
        $voucher = Voucher::factory()->create(['quantity' => 4]);
        $this->actingAs($user)->post(route('vouchers.redeem', $voucher))->assertSessionHasErrors(['voucher' => 'Bạn không đủ điểm để đổi voucher.']);
        $this->assertSame(4, $voucher->fresh()->quantity);
        $this->assertDatabaseCount('voucher_redemptions', 0);
    }
}
