<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('view-reports');
        $data = $request->validate(['from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from']]);
        $from = Carbon::parse($data['from'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $to = Carbon::parse($data['to'] ?? now()->toDateString())->endOfDay();
        if ($from->gt($to) || $from->diffInDays($to) > 366) {
            throw ValidationException::withMessages(['to' => 'Chọn khoảng ngày hợp lệ không quá 366 ngày.']);
        }
        $orders = Order::query()->where('is_paid', true)->whereIn('order_status_id', [4, 6])->whereBetween('created_at', [$from, $to])->get();
        $netCents = $orders->sum(fn (Order $order): int => Money::cents($order->total));
        $daily = $orders->sortBy('created_at')->groupBy(fn (Order $order): string => $order->created_at->format('d/m/Y'));
        $items = OrderItem::query()->with('dish.category')->whereHas('order', fn ($query) => $query->where('is_paid', true)->whereIn('order_status_id', [4, 6])->whereBetween('created_at', [$from, $to]))->get();
        $top = $items->groupBy('dish_id')->map(fn ($rows): array => ['name' => $rows->first()->dish_name, 'quantity' => $rows->sum('quantity'), 'gross' => Money::decimal($rows->sum(fn ($item): int => Money::cents($item->unit_price) * $item->quantity))])->sortByDesc('quantity')->take(10);
        $byCategory = $items->groupBy('dish.category.name')->map(fn ($rows): array => ['quantity' => $rows->sum('quantity'), 'gross' => Money::decimal($rows->sum(fn ($item): int => Money::cents($item->unit_price) * $item->quantity))]);

        $series = [];
        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $rows = $daily->get($day->format('d/m/Y'), collect());
            $series[] = ['date' => $day->format('d/m'), 'orders' => $rows->count(), 'revenue_cents' => $rows->sum(fn (Order $order): int => Money::cents($order->total))];
        }

        return view('admin.dashboard', ['series' => $series, 'from' => $from, 'to' => $to, 'net' => Money::decimal($netCents), 'paidCount' => $orders->count(), 'pending' => Order::query()->where('order_status_id', 1)->count(), 'dishesCount' => Dish::query()->count(), 'reservationsCount' => Reservation::query()->where('status', 'pending')->count(), 'daily' => $daily, 'top' => $top, 'byCategory' => $byCategory]);
    }
}
