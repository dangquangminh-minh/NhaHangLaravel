<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoucherRequest;
use App\Models\Voucher;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $records = Voucher::query()->when($data['q'] ?? null, fn ($query, $q) => $query->where('name', 'like', '%'.$q.'%'))->latest('id')->paginate(12)->withQueryString();

        return view('admin.vouchers.index', compact('records'));
    }

    public function create(): View
    {
        return view('admin.vouchers.form', ['record' => new Voucher]);
    }

    public function store(VoucherRequest $request): RedirectResponse
    {
        Voucher::query()->create($request->validated());

        return redirect()->route('admin.vouchers.index')->with('success', 'Đã thêm Voucher.');
    }

    public function show(Voucher $voucher): View
    {
        return view('admin.vouchers.show', ['record' => $voucher]);
    }

    public function edit(Voucher $voucher): View
    {
        return view('admin.vouchers.form', ['record' => $voucher]);
    }

    public function update(VoucherRequest $request, Voucher $voucher): RedirectResponse
    {
        $voucher->update($request->validated());

        return redirect()->route('admin.vouchers.index')->with('success', 'Đã cập nhật Voucher.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        if ($voucher->redemptions()->exists()) {
            return back()->with('error', 'Không thể xóa Voucher đang được tham chiếu.');
        }
        try {
            $voucher->delete();
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            return back()->with('error', 'Dữ liệu vừa được tham chiếu; không thể xóa.');
        }

        return redirect()->route('admin.vouchers.index')->with('success', 'Đã xóa Voucher.');
    }
}
