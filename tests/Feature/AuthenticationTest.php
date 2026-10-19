<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_customer_logs_in_with_hashed_password(): void
    {
        $user = User::factory()->create();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Demo@12345'])->assertRedirect(route('menu'));
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('Demo@12345', $user->password));
    }

    public function test_admin_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->admin()->create();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Demo@12345'])->assertRedirect(route('admin.orders.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected_in_vietnamese(): void
    {
        $user = User::factory()->create();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password'])->assertSessionHasErrors(['email' => 'Email hoặc mật khẩu không đúng.']);
        $this->assertGuest();
    }

    public function test_registration_cannot_select_admin_role(): void
    {
        $this->post(route('register.store'), ['name' => 'Khách mới', 'email' => 'new@gmail.com', 'phone' => '0901234567', 'password' => 'New@12345', 'password_confirmation' => 'New@12345', 'role_id' => 3, 'role' => 'owner', 'user_id' => 999])->assertRedirect(route('login'));
        $user = User::query()->where('email', 'new@gmail.com')->sole();
        $this->assertSame('user', $user->role->code);
        $this->assertTrue(Hash::check('New@12345', $user->password));
    }

    public function test_duplicate_registration_email_does_not_create_account(): void
    {
        $user = User::factory()->create(['email' => 'taken@gmail.com']);
        $this->post(route('register.store'), ['name' => 'Khách mới', 'email' => $user->email, 'phone' => '0901234567', 'password' => 'New@12345', 'password_confirmation' => 'New@12345'])->assertSessionHasErrors(['email' => 'Email đã tồn tại.']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_logout_clears_cart_and_authentication(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['cart' => [1 => 2], 'checkout_token' => 'old'])->post(route('logout'))->assertRedirect(route('login'))->assertSessionMissing('cart')->assertSessionMissing('checkout_token');
        $this->assertGuest();
    }

    public function test_password_reset_sends_token_without_changing_password(): void
    {
        $user = User::factory()->create();
        Notification::fake();
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('success');
        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertTrue(Hash::check('Demo@12345', $user->fresh()->password));
    }

    public function test_valid_reset_token_changes_hashed_password(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $this->post(route('password.update'), ['token' => $token, 'email' => $user->email, 'password' => 'Changed@123', 'password_confirmation' => 'Changed@123'])->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('Changed@123', $user->fresh()->password));
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), ['email' => 'missing@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post(route('login.store'), ['email' => 'missing@example.test', 'password' => 'wrong'])->assertTooManyRequests();
    }
}
