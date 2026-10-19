<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Dish;
use App\Models\Review;
use App\Support\Images;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ReviewController extends Controller
{
    public function index(): View
    {
        return view('reviews.index', ['records' => Review::query()->with(['user', 'dish'])->latest()->paginate(12)]);
    }

    public function store(ReviewRequest $request, Dish $dish): RedirectResponse
    {
        abort_unless($dish->is_available, 404);
        if ($dish->reviews()->where('user_id', $request->user()->id)->exists()) {
            throw ValidationException::withMessages(['rating' => 'Bạn đã đánh giá món này. Hãy sửa đánh giá hiện có.']);
        }
        $review = new Review;
        $review->dish_id = $dish->id;
        $review->user_id = $request->user()->id;

        return $this->save($request, $review);
    }

    public function update(ReviewRequest $request, Review $review): RedirectResponse
    {
        Gate::authorize('update', $review);

        return $this->save($request, $review);
    }

    private function save(ReviewRequest $request, Review $review): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $old = $review->image;
        $new = $request->hasFile('image') ? Images::store($request->file('image'), 'reviews') : null;
        if ($new) {
            $data['image'] = $new;
        }
        try {
            $review->fill($data)->save();
        } catch (Throwable $exception) {
            Images::delete($new);
            throw $exception;
        }
        if ($new) {
            Images::delete($old);
        }

        return redirect()->route('dishes.show', $review->dish_id)->with('success', 'Đã lưu đánh giá.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        Gate::authorize('delete', $review);
        $path = $review->image;
        $review->delete();
        Images::delete($path);

        return back()->with('success', 'Đã xóa đánh giá.');
    }
}
