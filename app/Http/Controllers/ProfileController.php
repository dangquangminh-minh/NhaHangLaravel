<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Voucher;
use App\Services\LoyaltyService;
use App\Support\Images;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    public function edit(Request $request, LoyaltyService $loyalty): View
    {
        return view('profile.edit', ['record' => $request->user(), 'points' => $loyalty->balance($request->user()), 'redemptions' => $request->user()->redemptions()->with('voucher')->latest()->paginate(10)->withQueryString()]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except(['avatar', 'current_password', 'password_confirmation', 'email_local']);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $old = $user->avatar;
        $new = $request->hasFile('avatar') ? Images::store($request->file('avatar'), 'avatars') : null;
        if ($new) {
            $data['avatar'] = $new;
        }
        try {
            $user->update($data);
        } catch (Throwable $exception) {
            Images::delete($new);
            throw $exception;
        }
        if ($new) {
            Images::delete($old);
        }

        return back()->with('success', 'Đã cập nhật hồ sơ cá nhân.');
    }

    public function vouchers(Request $request, LoyaltyService $loyalty): View
    {
        return view('profile.vouchers', ['points' => $loyalty->balance($request->user()), 'records' => Voucher::query()->where('quantity', '>', 0)->where('expires_at', '>', now())->orderBy('points_cost')->paginate(12)->withQueryString()]);
    }

    public function redeem(Request $request, Voucher $voucher, LoyaltyService $loyalty): RedirectResponse
    {
        $loyalty->redeem($request->user(), $voucher);

        return redirect()->route('profile.edit')->with('success', 'Đã đổi voucher. Mã voucher nằm trong hồ sơ của bạn.');
    }
}
