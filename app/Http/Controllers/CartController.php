<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Dish;
use App\Services\OrderService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->session()->get('cart', []);
        $dishes = Dish::query()->whereIn('id', array_keys($cart))->get()->keyBy('id');
        $subtotal = 0;
        foreach ($cart as $id => $quantity) {
            if (isset($dishes[$id])) {
                $subtotal += Money::cents($dishes[$id]->price) * $quantity;
            }
        }
        if (! $request->session()->has('checkout_token')) {
            $request->session()->put('checkout_token', (string) Str::uuid());
        }

        return view('cart.index', ['cart' => $cart, 'dishes' => $dishes, 'subtotal' => Money::decimal($subtotal), 'addresses' => $request->user()->addresses()->orderByDesc('is_default')->get(), 'token' => $request->session()->get('checkout_token')]);
    }

    public function add(Request $request, Dish $dish): RedirectResponse
    {
        abort_unless($dish->is_available, 404);
        $cart = $request->session()->get('cart', []);
        if (! isset($cart[$dish->id]) && count($cart) >= 50) {
            throw ValidationException::withMessages(['cart' => 'Giỏ hàng tối đa 50 món khác nhau.']);
        }
        $quantity = ($cart[$dish->id] ?? 0) + 1;
        if ($quantity > 99) {
            throw ValidationException::withMessages(['cart' => 'Mỗi món tối đa 99 phần.']);
        }
        $cart[$dish->id] = $quantity;
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Đã thêm món vào giỏ.');
    }

    public function update(Request $request, int $dish): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'between:0,99']]);
        $cart = $request->session()->get('cart', []);
        abort_unless(isset($cart[$dish]), 404);
        if ((int) $data['quantity'] === 0) {
            unset($cart[$dish]);
        } else {
            $cart[$dish] = (int) $data['quantity'];
        }
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Đã cập nhật số lượng.');
    }

    public function destroy(Request $request, int $dish): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$dish]);
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Đã bỏ món khỏi giỏ.');
    }

    public function checkout(CheckoutRequest $request, OrderService $service): RedirectResponse
    {
        if (! hash_equals((string) $request->session()->get('checkout_token', ''), $request->validated('checkout_token'))) {
            throw ValidationException::withMessages(['checkout_token' => 'Phiên đặt hàng đã kết thúc. Vui lòng tải lại giỏ hàng.']);
        }
        $order = $service->checkout($request->user(), $request->session()->get('cart', []), $request->validated());
        $request->session()->forget(['cart', 'checkout_token']);

        return redirect()->route('orders.show', $order)->with('success', 'Đặt món thành công. Nhà hàng sẽ xác nhận đơn và thanh toán.');
    }
}
