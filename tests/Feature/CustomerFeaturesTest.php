<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Contact;
use App\Models\Dish;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerFeaturesTest extends TestCase
{
    public function test_profile_ignores_client_user_id_and_role(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user)->put(route('profile.update'), ['name' => 'Tên của tôi', 'email' => $user->email, 'phone' => '0901234567', 'user_id' => $other->id, 'id' => $other->id, 'role_id' => 3, 'role' => 'owner'])->assertSessionHasNoErrors();
        $this->assertSame('Tên của tôi', $user->fresh()->name);
        $this->assertSame(1, $user->fresh()->role_id);
        $this->assertSame($other->name, $other->fresh()->name);
    }

    public function test_profile_password_change_requires_current_password(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put(route('profile.update'), ['name' => $user->name, 'email' => $user->email, 'password' => 'Changed@123', 'password_confirmation' => 'Changed@123', 'current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('Demo@12345', $user->fresh()->password));
    }

    public function test_address_crud_and_default_invariant(): void
    {
        $user = User::factory()->create();
        $data = ['recipient_name' => 'Người nhận', 'phone' => '0901234567', 'address' => 'Địa chỉ số 1', 'is_default' => 0, 'user_id' => 999];
        $this->actingAs($user)->post(route('addresses.store'), $data)->assertRedirect(route('addresses.index'));
        $first = Address::query()->sole();
        $this->assertSame($user->id, $first->user_id);
        $this->assertTrue($first->is_default);
        $this->post(route('addresses.store'), array_replace($data, ['address' => 'Địa chỉ số 2', 'is_default' => 1]))->assertSessionHasNoErrors();
        $second = Address::query()->latest('id')->first();
        $this->assertSame(1, $user->addresses()->where('is_default', true)->count());
        $this->get(route('addresses.show', $second))->assertOk()->assertSee('Địa chỉ số 2');
        $this->put(route('addresses.update', $second), array_replace($data, ['address' => 'Địa chỉ đã sửa', 'is_default' => 1]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('addresses', ['id' => $second->id, 'address' => 'Địa chỉ đã sửa']);
        $this->delete(route('addresses.destroy', $second))->assertSessionHasNoErrors();
        $this->assertModelMissing($second);
        $this->assertTrue($first->fresh()->is_default);
    }

    public function test_address_rejects_cross_account_view_update_and_delete(): void
    {
        $address = Address::factory()->create();
        $this->actingAs(User::factory()->create())->get(route('addresses.show', $address))->assertForbidden();
        $this->get(route('addresses.edit', $address))->assertForbidden();
        $this->put(route('addresses.update', $address), ['recipient_name' => 'Người khác', 'phone' => '0901234567', 'address' => 'Địa chỉ khác', 'is_default' => 1])->assertForbidden();
        $this->delete(route('addresses.destroy', $address))->assertForbidden();
        $this->assertModelExists($address);
    }

    public function test_guest_booking_and_contact_ignore_sensitive_state(): void
    {
        $this->freezeTime();
        $this->post(route('reservations.store'), ['name' => 'Khách đặt bàn', 'email' => 'booking@example.test', 'phone' => '0901234567', 'reserved_at' => now()->addDay()->format('Y-m-d H:i'), 'guests' => 4, 'table_number' => 1, 'status' => 'confirmed', 'user_id' => 999])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reservations', ['name' => 'Khách đặt bàn', 'status' => 'pending', 'user_id' => null]);
        $this->post(route('contacts.store'), ['name' => 'Người gửi', 'email' => 'contact@example.test', 'subject' => 'Đặt tiệc', 'message' => 'Tôi muốn đặt tiệc', 'is_read' => 1])->assertSessionHasNoErrors();
        $contact = Contact::query()->sole();
        $this->assertFalse($contact->is_read);
        $this->actingAs(User::factory()->admin()->create())->patch(route('admin.contacts.update', $contact))->assertSessionHasNoErrors();
        $this->assertTrue($contact->fresh()->is_read);
    }

    public function test_past_booking_date_is_rejected(): void
    {
        $this->freezeTime();
        $this->post(route('reservations.store'), ['name' => 'Khách', 'email' => 'booking@example.test', 'phone' => '0901234567', 'reserved_at' => now()->subDay()->format('Y-m-d H:i'), 'guests' => 0])->assertSessionHasErrors(['reserved_at', 'guests']);
        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_customer_cannot_cancel_another_reservation(): void
    {
        $reservation = Reservation::factory()->create();
        $this->actingAs(User::factory()->create())->patch(route('reservations.update', $reservation), ['status' => 'cancelled'])->assertForbidden();
        $this->assertSame('pending', $reservation->fresh()->status);
    }

    public function test_review_is_owned_and_html_is_escaped(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create();
        $this->actingAs($user)->post(route('reviews.store', $dish), ['rating' => 5, 'message' => '<script>x</script>', 'user_id' => 999])->assertSessionHasNoErrors();
        $review = Review::query()->sole();
        $this->assertSame($user->id, $review->user_id);
        $this->get(route('dishes.show', $dish))->assertOk()->assertSee('&lt;script&gt;x&lt;/script&gt;', false)->assertDontSee('<script>x</script>', false);
        $this->actingAs(User::factory()->create())->put(route('reviews.update', $review), ['rating' => 1, 'message' => 'Không được sửa'])->assertForbidden();
        $this->delete(route('reviews.destroy', $review))->assertForbidden();
        $this->actingAs($user)->put(route('reviews.update', $review), ['rating' => 4, 'message' => 'Đã cập nhật'])->assertSessionHasNoErrors();
        $this->assertSame(4, $review->fresh()->rating);
        $this->delete(route('reviews.destroy', $review))->assertSessionHasNoErrors();
        $this->assertModelMissing($review);
    }

    public function test_owner_creates_and_deletes_customer_but_cannot_edit(): void
    {
        $owner = User::factory()->owner()->create();
        $data = ['name' => 'Khách tạo bởi chủ', 'email' => 'managed@gmail.com', 'password' => 'Managed@123', 'password_confirmation' => 'Managed@123'];
        $this->actingAs($owner)->post(route('admin.accounts.store', 'customers'), $data)->assertSessionHasNoErrors();
        $record = User::query()->where('email', 'managed@gmail.com')->sole();
        $this->assertSame('user', $record->role->code);
        $this->put(route('admin.accounts.update', ['customers', $record]), ['name' => 'Tên đã sửa', 'email' => $record->email])->assertForbidden();
        $this->assertSame($data['name'], $record->fresh()->name);
        $this->delete(route('admin.accounts.destroy', ['customers', $record]))->assertSessionHasNoErrors();
        $this->assertModelMissing($record);
    }

    public function test_supplier_crud_and_linked_voucher_delete_protection(): void
    {
        $this->actingAs(User::factory()->owner()->create());
        $data = ['name' => 'Nhà cung cấp mới', 'address' => 'TP. Hồ Chí Minh', 'phone' => '0901234567'];
        $this->post(route('admin.suppliers.store'), $data)->assertSessionHasNoErrors();
        $supplier = Supplier::query()->sole();
        $this->put(route('admin.suppliers.update', $supplier), array_replace($data, ['address' => 'Địa chỉ mới']))->assertSessionHasNoErrors();
        $this->assertSame('Địa chỉ mới', $supplier->fresh()->address);
        $this->delete(route('admin.suppliers.destroy', $supplier))->assertSessionHasNoErrors();
        $this->assertModelMissing($supplier);
        $voucher = Voucher::factory()->create();
        VoucherRedemption::factory()->for($voucher)->create();
        $this->delete(route('admin.vouchers.destroy', $voucher))->assertSessionHas('error');
        $this->assertModelExists($voucher);
    }
}
