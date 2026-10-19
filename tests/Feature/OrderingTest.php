<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\VoucherRedemption;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderingTest extends TestCase
{
    public function test_cart_add_update_and_remove_use_post_patch_delete(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create();
        $this->actingAs($user)->post(route('cart.add', $dish))->assertSessionHas('cart', [$dish->id => 1]);
        $this->patch(route('cart.update', $dish), ['quantity' => 3])->assertSessionHas('cart', [$dish->id => 3]);
        $this->get(route('cart.index'))->assertOk()->assertSee($dish->name);
        $this->delete(route('cart.destroy', $dish))->assertSessionHas('cart', []);
    }

    public function test_checkout_uses_current_database_prices_and_authenticated_user(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create(['price' => '75000.50']);
        $token = (string) Str::uuid();
        $this->actingAs($user)->withSession(['cart' => [$dish->id => 2], 'checkout_token' => $token])->post(route('checkout'), ['checkout_token' => $token, 'payment_method' => 'transfer', 'user_id' => 999, 'total' => 1, 'price' => 1, 'is_paid' => 1, 'order_status_id' => 4])->assertSessionHasNoErrors()->assertSessionMissing('cart');
        $order = Order::query()->sole();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('150001.00', $order->total);
        $this->assertSame(1, $order->order_status_id);
        $this->assertFalse($order->is_paid);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'dish_id' => $dish->id, 'quantity' => 2, 'unit_price' => '75000.50']);
    }

    public function test_checkout_preserves_price_and_name_snapshot(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create(['name' => 'Món trước đổi', 'price' => '55000']);
        $token = (string) Str::uuid();
        $this->actingAs($user)->withSession(['cart' => [$dish->id => 1], 'checkout_token' => $token])->post(route('checkout'), ['checkout_token' => $token, 'payment_method' => 'cash'])->assertSessionHasNoErrors();
        $dish->update(['name' => 'Món sau đổi', 'price' => '99000']);
        $order = Order::query()->sole();
        $this->get(route('orders.show', $order))->assertOk()->assertSee('Món trước đổi')->assertDontSee('Món sau đổi');
        $this->assertSame('55000.00', $order->items()->sole()->unit_price);
    }

    public function test_invalid_or_foreign_voucher_rolls_back_entire_order(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create();
        $foreign = VoucherRedemption::factory()->create();
        $token = (string) Str::uuid();
        $this->actingAs($user)->withSession(['cart' => [$dish->id => 1], 'checkout_token' => $token])->post(route('checkout'), ['checkout_token' => $token, 'payment_method' => 'cash', 'voucher_code' => $foreign->code])->assertSessionHasErrors('voucher_code')->assertSessionHas('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertNull($foreign->fresh()->used_at);
    }

    public function test_voucher_is_consumed_once_and_discount_cannot_exceed_subtotal(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create(['price' => '25000']);
        $voucher = VoucherRedemption::factory()->for($user)->create(['discount_amount' => '50000']);
        $token = (string) Str::uuid();
        $this->actingAs($user)->withSession(['cart' => [$dish->id => 1], 'checkout_token' => $token])->post(route('checkout'), ['checkout_token' => $token, 'payment_method' => 'cash', 'voucher_code' => $voucher->code])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'subtotal' => '25000', 'discount' => '25000', 'total' => '0']);
        $this->assertNotNull($voucher->fresh()->used_at);
        $token2 = (string) Str::uuid();
        $this->withSession(['cart' => [$dish->id => 1], 'checkout_token' => $token2])->post(route('checkout'), ['checkout_token' => $token2, 'payment_method' => 'cash', 'voucher_code' => $voucher->code])->assertSessionHasErrors('voucher_code');
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_unavailable_dish_cannot_be_checked_out(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create(['is_available' => false]);
        $token = (string) Str::uuid();
        $this->actingAs($user)->withSession(['cart' => [$dish->id => 1], 'checkout_token' => $token])->post(route('checkout'), ['checkout_token' => $token, 'payment_method' => 'cash'])->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_replayed_checkout_does_not_create_duplicate_order(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create();
        $token = (string) Str::uuid();
        $data = ['checkout_token' => $token, 'payment_method' => 'cash'];
        $this->actingAs($user)->withSession(['cart' => [$dish->id => 1], 'checkout_token' => $token])->post(route('checkout'), $data)->assertSessionHasNoErrors();
        $this->post(route('checkout'), $data)->assertSessionHasErrors('checkout_token');
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_customer_cancel_retains_history_and_releases_voucher(): void
    {
        $user = User::factory()->create();
        $voucher = VoucherRedemption::factory()->for($user)->create(['used_at' => now()]);
        $order = Order::factory()->for($user)->create(['voucher_redemption_id' => $voucher->id]);
        $item = OrderItem::factory()->for($order)->create();
        $this->actingAs($user)->post(route('orders.cancel', $order))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status_id' => 5, 'voucher_redemption_id' => null]);
        $this->assertModelExists($item);
        $this->assertNull($voucher->fresh()->used_at);
    }

    public function test_customer_cannot_cancel_processing_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['order_status_id' => 2]);
        $this->actingAs($user)->post(route('orders.cancel', $order))->assertSessionHasErrors('order_status_id');
        $this->assertSame(2, $order->fresh()->order_status_id);
    }

    public function test_admin_payment_updates_paid_status_consistently(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create(['order_status_id' => 3]);
        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['order_status_id' => 4])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status_id' => 4, 'is_paid' => true, 'handled_by' => $admin->id]);
    }

    public function test_paid_order_cannot_be_reverted(): void
    {
        $order = Order::factory()->create(['order_status_id' => 4, 'is_paid' => true]);
        $this->actingAs(User::factory()->admin()->create())->patch(route('admin.orders.update', $order), ['order_status_id' => 1])->assertSessionHasErrors('order_status_id');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status_id' => 4, 'is_paid' => true]);
    }

    public function test_database_error_after_order_insert_rolls_back_header_items_and_voucher(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create();
        $voucher = VoucherRedemption::factory()->for($user)->create();
        OrderItem::creating(function (): void {
            throw new \RuntimeException('Simulated order item storage failure');
        });
        $this->beforeApplicationDestroyed(fn () => OrderItem::flushEventListeners());
        $token = (string) Str::uuid();
        $this->actingAs($user)->withSession(['cart' => [$dish->id => 1], 'checkout_token' => $token])->post(route('checkout'), ['checkout_token' => $token, 'payment_method' => 'cash', 'voucher_code' => $voucher->code])->assertInternalServerError();
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertNull($voucher->fresh()->used_at);
    }
}
