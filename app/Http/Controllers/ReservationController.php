<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationRequest;
use App\Models\Reservation;
use App\Services\TableBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function create(): View
    {
        return view('reservations.create');
    }

    public function store(ReservationRequest $request): RedirectResponse
    {
        app(TableBookingService::class)->reserve($request->validated(), $request->user());

        return back()->with('success', 'Đã nhận yêu cầu đặt bàn. Nhà hàng sẽ liên hệ xác nhận.');
    }

    public function index(Request $request): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $records = Reservation::query()->when(! $request->user()->isAdmin(), fn ($query) => $query->where('user_id', $request->user()->id))->when($data['q'] ?? null, fn ($query, $q) => $query->where('name', 'like', '%'.$q.'%'))->latest('id')->paginate(12)->withQueryString();

        return view('reservations.index', compact('records'));
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['confirmed', 'cancelled'])]]);
        DB::transaction(function () use ($request, $reservation, $data): void {
            $locked = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);
            abort_unless($request->user()->isAdmin() || ($locked->user_id === $request->user()->id && $data['status'] === 'cancelled'), 403);
            if ($locked->status === 'cancelled' || (! $request->user()->isAdmin() && $locked->status !== 'pending') || $locked->status === $data['status']) {
                throw ValidationException::withMessages(['status' => 'Không thể thay đổi trạng thái đặt bàn này.']);
            }
            $locked->status = $data['status'];
            $locked->save();
        });

        return back()->with('success', 'Đã cập nhật yêu cầu đặt bàn.');
    }
}
