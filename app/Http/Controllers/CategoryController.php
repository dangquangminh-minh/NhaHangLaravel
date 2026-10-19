<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $records = Category::query()->when($data['q'] ?? null, fn ($query, $q) => $query->where('name', 'like', '%'.$q.'%'))->latest('id')->paginate(12)->withQueryString();

        return view('admin.categories.index', compact('records'));
    }

    public function create(): View
    {
        return view('admin.categories.form', ['record' => new Category]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::query()->create($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Đã thêm Loại món.');
    }

    public function show(Category $category): View
    {
        return view('admin.categories.show', ['record' => $category]);
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', ['record' => $category]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Đã cập nhật Loại món.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->dishes()->exists()) {
            return back()->with('error', 'Không thể xóa Loại món đang được tham chiếu.');
        }
        try {
            $category->delete();
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            return back()->with('error', 'Dữ liệu vừa được tham chiếu; không thể xóa.');
        }

        return redirect()->route('admin.categories.index')->with('success', 'Đã xóa Loại món.');
    }
}
