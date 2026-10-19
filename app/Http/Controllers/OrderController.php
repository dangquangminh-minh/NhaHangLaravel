<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Services\OrderService;
use App\Support\PaymentQr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    private function search(Request $request): Builder
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'order_status_id' => ['nullable', 'integer', 'exists:order_statuses,id']]);
        if (array_key_exists('q', $data) && trim((string) $data['q']) === '') {
            unset($data['order_status_id']);
            $request->query->remove('order_status_id');
        }

        return Order::query()->with(['user', 'status'])->when($data['q'] ?? null, fn ($query, $q) => $query->where(fn ($query) => $query->where('id', ctype_digit($q) ? (int) $q : 0)->orWhereHas('user', fn ($query) => $query->where('name', 'like', '%'.$q.'%'))))->when($data['order_status_id'] ?? null, fn ($query, $id) => $query->where('order_status_id', $id));
    }

    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.orders.index');
        }

        return view('orders.index', ['records' => $this->search($request)->where('user_id', $request->user()->id)->latest('id')->paginate(12)->withQueryString(), 'statuses' => OrderStatus::all()]);
    }

    public function management(Request $request): View
    {
        Gate::authorize('admin');

        return view('admin.orders.index', ['records' => $this->search($request)->latest('id')->paginate(12)->withQueryString(), 'statuses' => OrderStatus::all()]);
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $paymentFirst = auth()->user()->hasRole('user') && $order->payment_method === 'transfer' && ! $order->payment_reported_at && ! $order->is_paid && ! $order->isTerminal();

        return view($paymentFirst ? 'orders.payment' : 'orders.show', ['order' => $order->load(['items', 'status', 'user', 'handler', 'paymentVerifier']), 'statuses' => OrderStatus::all(), 'qrPayload' => $order->payment_method === 'transfer' && ! $order->is_paid && ! $order->isTerminal() ? PaymentQr::payload($order) : null]);
    }

    public function cancel(Request $request, Order $order, OrderService $service): RedirectResponse
    {
        Gate::authorize('cancel', $order);
        $service->changeStatus($order, 5, $request->user());

        return back()->with('success', 'Đã hủy đơn, giữ lịch sử và mở lại voucher nếu có. Khoản đã chuyển cần nhà hàng xử lý hoàn tiền riêng.');
    }

    public function update(Request $request, Order $order, OrderService $service): RedirectResponse
    {
        Gate::authorize('update', $order);
        $data = $request->validate(['order_status_id' => ['required', 'integer', 'exists:order_statuses,id']]);
        $service->changeStatus($order, (int) $data['order_status_id'], $request->user());

        return back()->with('success', 'Đã chuyển sang bước tiếp theo.');
    }

    public function reportPayment(Request $request, Order $order, OrderService $service): RedirectResponse
    {
        Gate::authorize('view', $order);
        $service->reportPayment($order, $request->user());

        return redirect()->route('orders.show', $order)->with('success', 'Đã báo chuyển khoản. Nhân viên sẽ kiểm tra tài khoản nhận tiền; trạng thái đơn vẫn chờ xác nhận.');
    }

    public function verifyPayment(Request $request, Order $order, OrderService $service): RedirectResponse
    {
        Gate::authorize('update', $order);
        $service->verifyPayment($order, $request->user());

        return back()->with('success', 'Đã xác minh nhận tiền. Trạng thái đơn được giữ nguyên; bấm Đang làm sau khi gọi xác nhận khách.');
    }
}
