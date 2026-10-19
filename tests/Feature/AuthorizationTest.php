<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    public function test_guest_is_redirected_from_admin(): void
    {
        $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
    }

    public static function adminRequests(): array
    {
        return [['GET', '/admin/categories'], ['GET', '/admin/categories/create'], ['POST', '/admin/categories'], ['PUT', '/admin/categories/1'], ['DELETE', '/admin/categories/1'], ['GET', '/admin'], ['GET', '/admin/accounts/staff'], ['POST', '/admin/dishes/bulk-delete']];
    }

    #[DataProvider('adminRequests')]
    public function test_user_cannot_call_admin_routes(string $method, string $uri): void
    {
        $user = User::factory()->create();
        Category::factory()->create(['id' => 1]);
        $this->actingAs($user)->call($method, $uri, ['name' => 'Không được lưu'])->assertForbidden();
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_admin_cannot_manage_owner_modules(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user)->get(route('admin.accounts.index', 'staff'))->assertForbidden();
        $this->get(route('admin.vouchers.index'))->assertForbidden();
        $this->get(route('admin.accounts.index', 'customers'))->assertOk();
    }

    public function test_policy_matrix_matches_roles_and_ownership(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $order = Order::factory()->for($customer)->create();
        $address = Address::factory()->for($customer)->create();
        $review = Review::factory()->for($customer)->create();
        $this->assertTrue(Gate::forUser($owner)->allows('owner'));
        $this->assertFalse(Gate::forUser($admin)->allows('owner'));
        foreach ([$owner, $admin, $customer] as $actor) {
            $this->assertTrue(Gate::forUser($actor)->allows('view', $order));
        }
        $this->assertFalse(Gate::forUser($other)->allows('view', $order));
        $this->assertTrue(Gate::forUser($customer)->allows('cancel', $order));
        $this->assertFalse(Gate::forUser($other)->allows('cancel', $order));
        $this->assertFalse(Gate::forUser($customer)->allows('update', $order));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $order));
        $this->assertTrue(Gate::forUser($customer)->allows('update', $address));
        foreach ([$owner, $admin, $other] as $actor) {
            $this->assertFalse(Gate::forUser($actor)->allows('delete', $address));
        }
        $this->assertTrue(Gate::forUser($customer)->allows('update', $review));
        $this->assertFalse(Gate::forUser($other)->allows('update', $review));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $review));
    }

    public function test_customer_cannot_view_or_cancel_another_order(): void
    {
        $order = Order::factory()->create();
        $this->actingAs(User::factory()->create())->get(route('orders.show', $order))->assertForbidden();
        $this->post(route('orders.cancel', $order))->assertForbidden();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status_id' => 1]);
    }

    public function test_owner_cannot_delete_current_account_or_change_own_role(): void
    {
        $owner = User::factory()->owner()->create();
        $this->actingAs($owner)->delete(route('admin.accounts.destroy', ['staff', $owner]))->assertNotFound();
        $this->put(route('admin.accounts.update', ['staff', $owner]), ['name' => $owner->name, 'email' => $owner->email, 'role_id' => 2])->assertNotFound();
        $this->assertSame(3, $owner->fresh()->role_id);
    }
}
