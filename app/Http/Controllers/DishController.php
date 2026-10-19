<?php

namespace App\Http\Controllers;

use App\Http\Requests\DishRequest;
use App\Models\Category;
use App\Models\Dish;
use App\Models\DishImage;
use App\Models\Supplier;
use App\Support\Images;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class DishController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'category_id' => ['nullable', 'integer', 'exists:categories,id']]);
        if (array_key_exists('q', $data) && trim((string) $data['q']) === '') {
            unset($data['category_id']);
            $request->query->remove('category_id');
        }
        $records = Dish::query()->with(['category', 'supplier'])->when($data['q'] ?? null, fn ($query, $q) => $query->where('name', 'like', '%'.$q.'%'))->when($data['category_id'] ?? null, fn ($query, $id) => $query->where('category_id', $id))->latest('id')->paginate(12)->withQueryString();

        return view('admin.dishes.index', ['records' => $records, 'categories' => Category::query()->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return $this->form(new Dish(['is_available' => true]));
    }

    public function edit(Dish $dish): View
    {
        return $this->form($dish);
    }

    private function form(Dish $dish): View
    {
        return view('admin.dishes.form', ['record' => $dish, 'categories' => Category::query()->orderBy('name')->get(), 'suppliers' => Supplier::query()->orderBy('name')->get()]);
    }

    public function show(Dish $dish): View
    {
        return view('admin.dishes.show', ['record' => $dish->load(['category', 'supplier', 'images'])]);
    }

    public function store(DishRequest $request): RedirectResponse
    {
        return $this->save($request, new Dish);
    }

    public function update(DishRequest $request, Dish $dish): RedirectResponse
    {
        return $this->save($request, $dish);
    }

    private function save(DishRequest $request, Dish $dish): RedirectResponse
    {
        $files = [];
        $oldImage = $dish->image;
        try {
            DB::transaction(function () use ($request, $dish, &$files): void {
                $dish->fill($request->safe()->except(['image', 'images']));
                if ($request->hasFile('image')) {
                    $dish->image = $files[] = Images::store($request->file('image'), 'dishes');
                }
                $dish->save();
                foreach ($request->file('images', []) as $file) {
                    $dish->images()->create(['path' => $files[] = Images::store($file, 'dishes')]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($files as $path) {
                Images::delete($path);
            }
            throw $exception;
        }
        if ($request->hasFile('image')) {
            Images::delete($oldImage);
        }

        return redirect()->route('admin.dishes.index')->with('success', 'Đã lưu món ăn.');
    }

    public function destroy(Dish $dish): RedirectResponse
    {
        return $this->deleteDishes([$dish->id]);
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate(['ids' => ['required', 'array', 'max:50'], 'ids.*' => ['required', 'integer', 'distinct', 'exists:dishes,id']]);

        return $this->deleteDishes($data['ids']);
    }

    /** @param array<int, int|string> $ids */
    private function deleteDishes(array $ids): RedirectResponse
    {
        $files = [];
        try {
            DB::transaction(function () use ($ids, &$files): void {
                $dishes = Dish::query()->with('images')->whereKey($ids)->orderBy('id')->lockForUpdate()->get();
                foreach ($dishes as $dish) {
                    if ($dish->orderItems()->exists() || $dish->reviews()->exists()) {
                        throw ValidationException::withMessages(['ids' => 'Có món thuộc đơn hàng hoặc đánh giá. Không xóa món nào trong lần chọn này.']);
                    }
                    $files[] = $dish->image;
                    foreach ($dish->images as $image) {
                        $files[] = $image->path;
                    }
                    $dish->delete();
                }
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            return back()->with('error', 'Món ăn đang được tham chiếu; không thể xóa.');
        }
        foreach ($files as $path) {
            Images::delete($path);
        }

        return redirect()->route('admin.dishes.index')->with('success', 'Đã xóa món ăn đã chọn.');
    }

    public function deleteImage(Dish $dish, DishImage $image): RedirectResponse
    {
        abort_unless($image->dish_id === $dish->id, 404);
        $path = $image->path;
        $image->delete();
        Images::delete($path);

        return back()->with('success', 'Đã xóa ảnh trong album.');
    }
}
