<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $records = Supplier::query()->when($data['q'] ?? null, fn ($query, $q) => $query->where('name', 'like', '%'.$q.'%'))->latest('id')->paginate(12)->withQueryString();

        return view('admin.suppliers.index', compact('records'));
    }

    public function create(): View
    {
        return view('admin.suppliers.form', ['record' => new Supplier]);
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        Supplier::query()->create($request->validated());

        return redirect()->route('admin.suppliers.index')->with('success', 'Đã thêm Nhà cung cấp.');
    }

    public function show(Supplier $supplier): View
    {
        return view('admin.suppliers.show', ['record' => $supplier]);
    }

    public function edit(Supplier $supplier): View
    {
        return view('admin.suppliers.form', ['record' => $supplier]);
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()->route('admin.suppliers.index')->with('success', 'Đã cập nhật Nhà cung cấp.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->dishes()->exists()) {
            return back()->with('error', 'Không thể xóa Nhà cung cấp đang được tham chiếu.');
        }
        try {
            $supplier->delete();
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            return back()->with('error', 'Dữ liệu vừa được tham chiếu; không thể xóa.');
        }

        return redirect()->route('admin.suppliers.index')->with('success', 'Đã xóa Nhà cung cấp.');
    }
}
