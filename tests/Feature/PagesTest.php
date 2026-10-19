<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Contact;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use App\Models\Voucher;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PagesTest extends TestCase
{
    public static function publicPages(): array
    {
        return [['/'], ['/thuc-don'], ['/dang-nhap'], ['/dang-ky'], ['/quen-mat-khau'], ['/dat-ban'], ['/lien-he'], ['/dat-lai-mat-khau/demo?email=user@example.test']];
    }

    #[DataProvider('publicPages')]
    public function test_public_blade_pages_render(string $uri): void
    {
        $this->get($uri)->assertOk();
    }

    public function test_owner_management_pages_render_with_related_data(): void
    {
        $owner = User::factory()->owner()->create();
        $customer = User::factory()->create();
        $dish = Dish::factory()->create();
        $voucher = Voucher::factory()->create();
        $order = Order::factory()->for($customer)->create();
        OrderItem::factory()->for($order)->for($dish)->create();
        Contact::factory()->create();
        Reservation::factory()->create();
        Review::factory()->for($dish)->create();
        $this->actingAs($owner);
        foreach (['/admin', '/admin?from=2026-01-01&to=2026-12-31', '/admin/categories', '/admin/suppliers', '/admin/dishes', '/admin/vouchers', '/admin/contacts', '/admin/reviews', '/admin/accounts/staff', '/admin/accounts/customers', '/admin/accounts/staff/create', '/admin/accounts/customers/create', '/lich-dat-ban', '/ho-so'] as $uri) {
            $this->get($uri)->assertOk();
        }
        foreach (['categories' => $dish->category, 'suppliers' => $dish->supplier, 'dishes' => $dish, 'vouchers' => $voucher] as $resource => $record) {
            $this->get('/admin/'.$resource.'/create')->assertOk();
            $this->get('/admin/'.$resource.'/'.$record->id)->assertOk();
            $this->get('/admin/'.$resource.'/'.$record->id.'/edit')->assertOk();
        }
        $this->get(route('admin.accounts.show', ['customers', $customer]))->assertOk();
        $staff = User::factory()->admin()->create();
        $this->get(route('admin.accounts.edit', ['staff', $staff]))->assertOk();
        $this->get(route('orders.index'))->assertRedirect(route('admin.orders.index'));
        $this->get(route('admin.orders.index'))->assertOk();
        $this->get(route('orders.show', $order))->assertOk();
    }

    public function test_customer_pages_render_with_related_data(): void
    {
        $customer = User::factory()->create();
        $dish = Dish::factory()->create();
        $address = Address::factory()->for($customer)->create();
        $order = Order::factory()->for($customer)->create();
        OrderItem::factory()->for($order)->for($dish)->create();
        Review::factory()->for($customer)->for($dish)->create();
        Reservation::factory()->for($customer)->create();
        $this->actingAs($customer)->withSession(['cart' => [$dish->id => 2]]);
        foreach (['/', '/thuc-don', '/gio-hang', '/ho-so', '/doi-voucher', '/dia-chi', '/dia-chi/create', '/don-hang', '/lich-dat-ban'] as $uri) {
            $this->get($uri)->assertOk();
        }
        $this->get(route('addresses.show', $address))->assertOk();
        $this->get(route('addresses.edit', $address))->assertOk();
        $this->get(route('orders.show', $order))->assertOk();
        $this->get(route('dishes.show', $dish))->assertOk();
    }
}
