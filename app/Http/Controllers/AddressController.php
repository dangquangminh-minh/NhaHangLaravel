<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        return view('addresses.index', ['records' => $request->user()->addresses()->orderByDesc('is_default')->paginate(12)]);
    }

    public function create(): View
    {
        return view('addresses.form', ['record' => new Address]);
    }

    public function show(Address $address): View
    {
        Gate::authorize('view', $address);

        return view('addresses.show', ['record' => $address]);
    }

    public function edit(Address $address): View
    {
        Gate::authorize('update', $address);

        return view('addresses.form', ['record' => $address]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        return $this->save($request, new Address);
    }

    public function update(AddressRequest $request, Address $address): RedirectResponse
    {
        Gate::authorize('update', $address);

        return $this->save($request, $address);
    }

    private function save(AddressRequest $request, Address $address): RedirectResponse
    {
        DB::transaction(function () use ($request, $address): void {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $address = $address->exists ? $user->addresses()->lockForUpdate()->findOrFail($address->id) : $address;
            $data = $request->validated();
            $default = (bool) $data['is_default'];
            if (! $user->addresses()->where('id', '!=', $address->id ?? 0)->where('is_default', true)->exists()) {
                $default = true;
            }
            if ($default) {
                $user->addresses()->where('id', '!=', $address->id ?? 0)->update(['is_default' => false]);
            }
            $address->fill($data);
            $address->user_id = $user->id;
            $address->is_default = $default;
            $address->save();
        }, 3);

        return redirect()->route('addresses.index')->with('success', 'Đã lưu địa chỉ giao hàng.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        Gate::authorize('delete', $address);
        DB::transaction(function () use ($request, $address): void {
            User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $locked = Address::query()->lockForUpdate()->findOrFail($address->id);
            $wasDefault = $locked->is_default;
            $locked->delete();
            if ($wasDefault) {
                $next = $request->user()->addresses()->orderBy('id')->first();
                if ($next) {
                    $next->update(['is_default' => true]);
                }
            }
        }, 3);

        return redirect()->route('addresses.index')->with('success', 'Đã xóa địa chỉ. Thông tin giao hàng trong đơn cũ vẫn được lưu.');
    }
}
