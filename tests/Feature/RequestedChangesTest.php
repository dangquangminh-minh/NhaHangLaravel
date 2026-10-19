<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\User;
use App\Services\LoyaltyService;
use App\Support\Money;
use App\Support\PaymentQr;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RequestedChangesTest extends TestCase
{
    public static function amounts(): array
    {
        return [['1000.00', '1.000'], ['1000.50', '1.000,5'], ['1000.01', '1.000,01'], ['0.00', '0'], ['1234567890123456.12', '1.234.567.890.123.456,12']];
    }

    #[DataProvider('amounts')]
    public function test_money_hides_only_zero_fraction_without_rounding(string $input, string $expected): void
    {
        $this->assertSame($expected, Money::format($input));
    }

    public function test_operations_manager_can_manage_orders_but_cannot_open_statistics(): void
    {
        $employee = User::factory()->admin()->create();
        $this->actingAs($employee)->get(route('admin.orders.index'))->assertOk()->assertDontSee('href="'.route('menu').'"', false)->assertDontSee('href="'.route('admin.dashboard').'"', false);
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('admin.categories.index'))->assertOk();
    }

    public function test_staff_form_lists_positions_but_not_owner(): void
    {
        $this->actingAs(User::factory()->owner()->create())->get(route('admin.accounts.create', 'staff'))->assertOk()->assertSee('Thu ngân')->assertSee('Đầu bếp')->assertSee('Lễ tân')->assertDontSee('<option value="3"', false)->assertDontSee('Quản trị / nhân viên (Admin)');
    }

    public function test_owner_cannot_create_another_owner_through_employee_form(): void
    {
        $this->actingAs(User::factory()->owner()->create())->post(route('admin.accounts.store', 'staff'), ['name' => 'Không hợp lệ', 'email_local' => 'invalid.owner', 'role_id' => 3, 'password' => 'Password@123', 'password_confirmation' => 'Password@123'])->assertSessionHasErrors('role_id');
        $this->assertDatabaseCount('users', 1);
    }

    public static function accountActors(): array
    {
        return [['user'], ['admin'], ['owner']];
    }

    #[DataProvider('accountActors')]
    public function test_profile_gmail_suffix_is_fixed_for_every_role(string $role): void
    {
        $user = User::factory()->create(['role_id' => Role::query()->where('code', $role)->sole()->id]);
        $this->actingAs($user)->put(route('profile.update'), ['name' => $user->name, 'email_local' => 'New.Prefix', 'email' => 'forged@evil.test', 'role_id' => 3])->assertSessionHasNoErrors();
        $this->assertSame('new.prefix@gmail.com', $user->fresh()->email);
        $this->assertSame($user->role_id, $user->fresh()->role_id);
    }

    public function test_registration_accepts_local_gmail_part_and_rejects_embedded_domain(): void
    {
        $data = ['name' => 'Khách mới', 'email_local' => 'new.customer', 'phone' => '0901234567', 'password' => 'Customer@123', 'password_confirmation' => 'Customer@123'];
        $this->post(route('register.store'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'new.customer@gmail.com', 'role_id' => 1]);
        $this->post(route('register.store'), array_replace($data, ['email_local' => 'other@yahoo.com']))->assertSessionHasErrors(['email_local', 'email']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_admin_and_owner_cannot_edit_customer_information(): void
    {
        $customer = User::factory()->create();
        foreach ([User::factory()->admin()->create(), User::factory()->owner()->create()] as $staff) {
            $this->actingAs($staff)->get(route('admin.accounts.edit', ['customers', $customer]))->assertForbidden();
            $this->put(route('admin.accounts.update', ['customers', $customer]), ['name' => 'Không được sửa', 'email_local' => 'stolen'])->assertForbidden();
        }
        $this->assertSame($customer->name, $customer->fresh()->name);
        $this->assertSame($customer->email, $customer->fresh()->email);
    }

    public function test_admin_can_add_and_delete_unreferenced_customer(): void
    {
        $this->actingAs(User::factory()->admin()->create())->post(route('admin.accounts.store', 'customers'), ['name' => 'Khách tạo mới', 'email_local' => 'managed.customer', 'password' => 'Customer@123', 'password_confirmation' => 'Customer@123'])->assertSessionHasNoErrors();
        $customer = User::query()->where('email', 'managed.customer@gmail.com')->sole();
        $this->assertSame('user', $customer->role->code);
        $this->delete(route('admin.accounts.destroy', ['customers', $customer]))->assertSessionHasNoErrors();
        $this->assertModelMissing($customer);
    }

    public function test_empty_search_resets_filter_without_reset_button(): void
    {
        $first = Dish::factory()->create();
        Dish::factory()->create();
        $this->get(route('menu', ['q' => '', 'category_id' => $first->category_id]))->assertOk()->assertViewHas('dishes', fn ($rows): bool => $rows->total() === 2)->assertDontSee('>Đặt lại</a>', false);
    }

    public function test_cart_zero_removes_item_and_page_has_stepper_confirmation(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create(['price' => '55000']);
        $this->actingAs($user)->withSession(['cart' => [$dish->id => 1]])->get(route('cart.index'))->assertOk()->assertSee('Bạn có đồng ý xóa món này khỏi giỏ hàng không?')->assertSee('data-quantity-form', false)->assertDontSee('>Lưu</button>', false)->assertSee('55.000đ');
        $this->patch(route('cart.update', $dish), ['quantity' => 0])->assertSessionHasNoErrors()->assertSessionHas('cart', []);
    }

    public function test_cart_change_recalculates_summary_without_rounding(): void
    {
        $dish = Dish::factory()->create(['price' => '1000.25']);
        $this->actingAs(User::factory()->create())->withSession(['cart' => [$dish->id => 1]])->patch(route('cart.update', $dish), ['quantity' => 3])->assertSessionHas('cart', [$dish->id => 3]);
        $this->get(route('cart.index'))->assertOk()->assertViewHas('subtotal', '3000.75')->assertSee('3.000,75đ');
    }

    public function test_cod_requires_every_forward_step_and_has_exclusive_terminal_state(): void
    {
        $order = Order::factory()->create(['payment_method' => 'cash']);
        $this->actingAs(User::factory()->admin()->create());
        $this->patch(route('admin.orders.update', $order), ['order_status_id' => 4])->assertSessionHasErrors('order_status_id');
        $this->patch(route('admin.orders.update', $order), ['order_status_id' => 2])->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status_id' => 2, 'is_paid' => false]);
        $this->patch(route('admin.orders.update', $order), ['order_status_id' => 1])->assertSessionHasErrors('order_status_id');
        $this->patch(route('admin.orders.update', $order), ['order_status_id' => 3])->assertRedirect();
        $this->patch(route('admin.orders.update', $order), ['order_status_id' => 4])->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status_id' => 4, 'is_paid' => true]);
        $this->patch(route('admin.orders.update', $order), ['order_status_id' => 5])->assertSessionHasErrors('order_status_id');
    }

    public function test_online_report_verify_and_delivery_are_independent_manual_steps(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $dish = Dish::factory()->create(['price' => '55000']);
        $token = (string) Str::uuid();
        $this->actingAs($user)->withSession(['cart' => [$dish->id => 1], 'checkout_token' => $token])->post(route('checkout'), ['checkout_token' => $token, 'payment_method' => 'transfer', 'is_paid' => true])->assertSessionHasNoErrors();
        $order = Order::query()->sole();
        $this->assertFalse($order->is_paid);
        $this->get(route('orders.show', $order))->assertOk()->assertSee('data-payment-qr=', false)->assertSee('0333820793')->assertSee('QUAN AN VUI VE')->assertSee('Chờ xác nhận');
        $this->post(route('orders.payment.report', $order))->assertSessionHasNoErrors();
        $this->assertNotNull($order->fresh()->payment_reported_at);
        $this->assertFalse($order->fresh()->is_paid);
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['order_status_id' => 2])->assertSessionHasErrors('order_status_id');
        $this->post(route('admin.orders.payment.verify', $order))->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status_id' => 1, 'is_paid' => true, 'payment_verified_by' => $admin->id]);
        $this->post(route('admin.orders.payment.verify', $order))->assertSessionHasErrors('payment');
        foreach ([2, 3, 6] as $status) {
            $this->patch(route('admin.orders.update', $order), ['order_status_id' => $status])->assertRedirect();
            $this->assertSame($status, $order->fresh()->order_status_id);
        }
        $this->assertSame(50, app(LoyaltyService::class)->balance($user));
        $this->patch(route('admin.orders.update', $order), ['order_status_id' => 5])->assertSessionHasErrors('order_status_id');
    }

    public function test_payment_report_and_verification_require_correct_actor(): void
    {
        $owner = User::factory()->create();
        $order = Order::factory()->for($owner)->create(['payment_method' => 'transfer']);
        $this->actingAs(User::factory()->create())->post(route('orders.payment.report', $order))->assertForbidden();
        $this->actingAs($owner)->post(route('admin.orders.payment.verify', $order))->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->post(route('admin.orders.payment.verify', $order))->assertSessionHasErrors('payment');
        $this->assertFalse($order->fresh()->is_paid);
    }

    public function test_cancel_received_transfer_marks_refund_due_without_faking_refund(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['payment_method' => 'transfer', 'is_paid' => true, 'payment_reported_at' => now(), 'payment_verified_at' => now()]);
        $this->actingAs($user)->post(route('orders.cancel', $order))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status_id' => 5, 'is_paid' => true, 'refund_required' => true]);
        $this->assertSame(0, app(LoyaltyService::class)->balance($user));
    }

    public function test_qr_encodes_bank_account_exact_amount_reference_and_valid_crc_vector(): void
    {
        $order = Order::factory()->create(['total' => '123456.75', 'payment_method' => 'transfer']);
        $payload = PaymentQr::payload($order);
        $this->assertStringContainsString('000697042201100333820793', $payload);
        $this->assertStringContainsString('5409123456.75', $payload);
        $this->assertStringContainsString('THANHTOAN '.$order->id, $payload);
        $this->assertSame('29B1', PaymentQr::crc16('123456789'));
    }

    public function test_reserved_table_rejects_other_email_and_accepts_matching_account(): void
    {
        $this->freezeTime();
        $user = User::factory()->create(['email' => 'booked@gmail.com']);
        Reservation::factory()->for($user)->create(['email' => $user->email, 'table_number' => 1, 'reserved_at' => now()->addMinutes(10), 'status' => 'confirmed']);
        $dish = Dish::factory()->create();
        $token = (string) Str::uuid();
        $data = ['payment_method' => 'cash', 'table_number' => 1, 'checkout_token' => $token];
        $this->actingAs(User::factory()->create())->withSession(['cart' => [$dish->id => 1], 'checkout_token' => $token])->post(route('checkout'), $data)->assertSessionHasErrors('table_number');
        $this->assertDatabaseCount('orders', 0);
        $this->actingAs($user)->withSession(['cart' => [$dish->id => 1], 'checkout_token' => $token])->post(route('checkout'), $data)->assertRedirect();
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'table_number' => 1]);
    }

    public function test_overlapping_booking_is_rejected_and_expired_booking_releases_table(): void
    {
        $this->freezeTime();
        $data = ['name' => 'Khách', 'email' => 'guest@gmail.com', 'phone' => '0901234567', 'reserved_at' => now()->addHour()->format('Y-m-d H:i'), 'guests' => 3, 'table_number' => 2];
        $this->post(route('reservations.store'), $data)->assertSessionHasNoErrors();
        $this->post(route('reservations.store'), array_replace($data, ['email' => 'other@gmail.com']))->assertSessionHasErrors('table_number');
        $this->post(route('reservations.store'), array_replace($data, ['reserved_at' => now()->addHours(4)->format('Y-m-d H:i')]))->assertRedirect();
        $this->assertDatabaseCount('reservations', 2);
    }

    public function test_menu_top_ten_counts_completed_sales_including_online(): void
    {
        $paid = Order::factory()->create(['is_paid' => true, 'order_status_id' => 6, 'payment_method' => 'transfer']);
        $dishes = Dish::factory()->count(11)->create();
        foreach ($dishes as $i => $dish) {
            OrderItem::factory()->for($paid)->for($dish)->create(['quantity' => $i + 1]);
        }
        $unpaidDish = Dish::factory()->create();
        OrderItem::factory()->for($unpaidDish)->create(['quantity' => 99]);
        $this->get(route('menu'))->assertOk()->assertViewHas('top', fn ($rows): bool => $rows->count() === 10 && $rows->first()->id === $dishes->last()->id && ! $rows->contains('id', $unpaidDish->id))->assertSee('10 món ăn bán chạy nhất quán');
    }

    public function test_owner_charts_count_only_completed_paid_orders(): void
    {
        $this->freezeTime();
        Order::factory()->create(['order_status_id' => 6, 'is_paid' => true, 'total' => '55000', 'payment_method' => 'transfer']);
        Order::factory()->create(['order_status_id' => 5, 'is_paid' => true, 'total' => '99999']);
        Order::factory()->create();
        $this->actingAs(User::factory()->owner()->create())->get(route('admin.dashboard'))->assertOk()->assertViewHas('net', '55000.00')->assertViewHas('paidCount', 1)->assertSee('<svg', false)->assertSee('Biểu đồ lượt mua')->assertDontSee('href="'.route('menu').'"', false);
    }

    public function test_fresh_demo_seed_has_consistent_roles_bookings_and_payment_history(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('dining_tables', 200);
        $this->assertSame(7, User::query()->whereHas('role', fn ($query) => $query->whereIn('code', array_keys(Role::EMPLOYEE_NAMES)))->distinct()->count('role_id'));
        $this->assertSame(0, Reservation::query()->whereNull('table_number')->count());
        foreach (Reservation::query()->with('user')->get() as $booking) {
            $this->assertSame($booking->user->email, $booking->email);
        }
        $this->assertSame(0, Order::query()->where('payment_method', 'transfer')->whereIn('order_status_id', [2, 3, 6])->where('is_paid', false)->count());
        $this->assertSame(0, Order::query()->where('payment_method', 'transfer')->where('order_status_id', 4)->count());
        $this->assertSame(0, Order::query()->whereIn('order_status_id', [4, 6])->whereNull('payment_verified_at')->count());
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('users', 40);
    }

    public static function reportViewers(): array
    {
        return ['cashier' => ['cashier'], 'owner' => ['owner']];
    }

    #[DataProvider('reportViewers')]
    public function test_report_viewers_can_open_revenue_statistics_and_see_navigation(string $role): void
    {
        $user = User::factory()->create(['role_id' => Role::query()->where('code', $role)->sole()->id]);
        $this->assertTrue(Gate::forUser($user)->allows('view-reports'));
        $this->actingAs($user)->get(route('admin.orders.index'))->assertOk()->assertSee('href="'.route('admin.dashboard').'"', false);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Biểu đồ lượt mua')->assertSee('Biểu đồ doanh thu');
    }

    public static function nonReportViewers(): array
    {
        return ['operations' => ['admin'], 'waiter' => ['waiter'], 'chef' => ['chef'], 'receptionist' => ['receptionist'], 'delivery' => ['delivery'], 'warehouse' => ['warehouse'], 'customer' => ['user']];
    }

    #[DataProvider('nonReportViewers')]
    public function test_other_roles_cannot_open_revenue_statistics_or_see_navigation(string $role): void
    {
        $user = User::factory()->create(['role_id' => Role::query()->where('code', $role)->sole()->id]);
        $this->assertFalse(Gate::forUser($user)->allows('view-reports'));
        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('profile.edit'))->assertOk()->assertDontSee('href="'.route('admin.dashboard').'"', false);
    }

    public function test_cashier_reporting_access_does_not_allow_owner_account_or_voucher_management(): void
    {
        $cashier = User::factory()->create(['role_id' => Role::query()->where('code', 'cashier')->sole()->id]);
        $this->assertFalse(Gate::forUser($cashier)->allows('owner'));
        $this->actingAs($cashier)->get(route('admin.accounts.index', 'staff'))->assertForbidden();
        $this->get(route('admin.vouchers.index'))->assertForbidden();
    }

    public function test_guest_must_log_in_to_view_revenue_statistics(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }
}
