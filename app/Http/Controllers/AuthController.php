<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function loginForm(): View
    {
        return view('auth.login');
    }

    public function registerForm(): View
    {
        return view('auth.register');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->validated())) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }
        $request->session()->regenerate();

        if ($request->user()->isAdmin()) {
            return redirect()->route($request->user()->hasRole('owner') ? 'admin.dashboard' : 'admin.orders.index');
        }

        return redirect()->intended(route('menu'));
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->except(['password_confirmation', 'email_local']));
        $user->role_id = Role::query()->where('code', 'user')->sole()->id;
        $user->save();

        return redirect()->route('login')->with('success', 'Đăng ký thành công. Bạn có thể đăng nhập.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất.');
    }

    public function forgotForm(): View
    {
        return view('auth.forgot');
    }

    public function forgot(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:100']]);
        Password::sendResetLink($data);

        return back()->with('success', 'Nếu email đã đăng ký, liên kết đặt lại mật khẩu sẽ được gửi theo cấu hình mail.');
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('auth.reset', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate(['token' => ['required', 'string'], 'email' => ['required', 'email', 'max:100'], 'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed']]);
        $status = Password::reset($data, function (User $user, string $password): void {
            $user->password = $password;
            $user->setRememberToken(Str::random(60));
            $user->save();
            event(new PasswordReset($user));
        });
        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect()->route('login')->with('success', __($status));
    }
}
