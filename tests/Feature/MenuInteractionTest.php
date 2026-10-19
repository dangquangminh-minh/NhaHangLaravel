<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Order;
use App\Models\User;
use Tests\TestCase;

class MenuInteractionTest extends TestCase
{
    public function test_live_menu_returns_only_filtered_blade_results_and_keeps_pagination(): void
    {
        $category = Category::factory()->create();
        Dish::factory()->count(14)->for($category)->sequence(fn ($sequence) => ['name' => 'Bún ngon '.$sequence->index])->create();
        Dish::factory()->create(['name' => 'Món khác']);
        $response = $this->get(route('menu', ['q' => 'Bún', 'category_id' => $category->id]), ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertOk()->assertViewIs('menu.results')->assertViewHas('dishes', fn ($rows): bool => $rows->total() === 14 && $rows->count() === 12)->assertSee('id="menu-results"', false)->assertDontSee('<html', false)->assertDontSee('Món khác');
        $this->assertStringContainsString('category_id='.$category->id, $response->viewData('dishes')->nextPageUrl());
        $this->get($response->viewData('dishes')->nextPageUrl(), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertViewHas('dishes', fn ($rows): bool => $rows->count() === 2);
    }

    public function test_category_filter_and_zero_keyword_are_applied_to_live_search(): void
    {
        $dish = Dish::factory()->create(['name' => 'Món số 0']);
        Dish::factory()->create(['name' => 'Món khác']);
        $this->get(route('menu', ['category_id' => $dish->category_id]), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertViewHas('dishes', fn ($rows): bool => $rows->total() === 1);
        $this->get(route('menu', ['q' => '0']), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertViewHas('dishes', fn ($rows): bool => $rows->total() === 1)->assertSee('Món số 0')->assertDontSee('Món khác');
        $this->get(route('menu', ['q' => 'Không có']), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertSee('Không có món phù hợp.');
    }

    public function test_cart_quantity_can_be_entered_directly_and_total_uses_updated_quantity(): void
    {
        $dish = Dish::factory()->create(['price' => '1000.25']);
        $this->actingAs(User::factory()->create())->withSession(['cart' => [$dish->id => 1]])->get(route('cart.index'))->assertOk()->assertSee('type="number" name="quantity"', false)->assertSee('data-quantity-step="-1"', false);
        $this->patch(route('cart.update', $dish), ['quantity' => 49])->assertSessionHasNoErrors()->assertSessionHas('cart', [$dish->id => 49]);
        $this->get(route('cart.index'))->assertOk()->assertViewHas('subtotal', '49012.25')->assertSee('49.012,25đ');
        foreach ([100, -1, '1.5', 'abc'] as $quantity) {
            $this->patch(route('cart.update', $dish), ['quantity' => $quantity])->assertSessionHasErrors('quantity')->assertSessionHas('cart', [$dish->id => 49]);
        }
    }

    public function test_online_customer_sees_qr_before_reporting_then_order_details_after_reporting(): void
    {
        $customer = User::factory()->create();
        $order = Order::factory()->for($customer)->create(['payment_method' => 'transfer']);
        $this->actingAs($customer)->get(route('orders.show', $order))->assertOk()->assertViewIs('orders.payment')->assertSee('data-payment-qr=', false)->assertSee('Tôi đã chuyển khoản')->assertDontSee('<h2 class="h5">Thông tin đơn hàng', false)->assertDontSee('class="order-progress', false);
        $this->post(route('orders.payment.report', $order))->assertRedirect(route('orders.show', $order));
        $this->assertNotNull($order->fresh()->payment_reported_at);
        $this->assertFalse($order->fresh()->is_paid);
        $this->assertSame(1, $order->fresh()->order_status_id);
        $this->get(route('orders.show', $order))->assertOk()->assertViewIs('orders.show')->assertSee('Thông tin đơn hàng')->assertSee('Chờ xác nhận')->assertDontSee('data-payment-qr=', false);
    }

    public function test_employee_sees_unreported_order_details_and_cod_skips_qr_screen(): void
    {
        $customer = User::factory()->create();
        $online = Order::factory()->for($customer)->create(['payment_method' => 'transfer']);
        $cod = Order::factory()->for($customer)->create(['payment_method' => 'cash']);
        $this->actingAs(User::factory()->admin()->create())->get(route('orders.show', $online))->assertOk()->assertViewIs('orders.show')->assertSee('Thông tin đơn hàng');
        $this->actingAs($customer)->get(route('orders.show', $cod))->assertOk()->assertViewIs('orders.show')->assertSee('Thông tin đơn hàng')->assertDontSee('data-payment-qr=', false);
        $this->actingAs(User::factory()->create())->get(route('orders.show', $online))->assertForbidden();
    }
}
