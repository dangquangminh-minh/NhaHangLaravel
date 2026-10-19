<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Dish;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function home(): View|RedirectResponse
    {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route(auth()->user()->hasRole('owner') ? 'admin.dashboard' : 'admin.orders.index');
        }
        $top = Dish::query()->where('is_available', true)->with('category')->withSum(['orderItems as sold' => fn ($query) => $query->whereHas('order', fn ($query) => $query->whereIn('order_status_id', [4, 6])->where('is_paid', true))], 'quantity')->orderByDesc('sold')->orderBy('id')->take(10)->get();

        return view('menu.home', compact('top'));
    }

    public function index(Request $request): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'category_id' => ['nullable', 'integer', 'exists:categories,id']]);
        if (array_key_exists('q', $data) && trim((string) $data['q']) === '') {
            unset($data['category_id']);
            $request->query->remove('category_id');
        }
        $dishes = Dish::query()->with('category')->where('is_available', true)->when(isset($data['q']) && $data['q'] !== '', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$data['q'].'%')->orWhere('description', 'like', '%'.$data['q'].'%')))->when($data['category_id'] ?? null, fn ($query, $id) => $query->where('category_id', $id))->orderBy('name')->paginate(12)->withQueryString();

        if ($request->ajax()) {
            return view('menu.results', ['dishes' => $dishes]);
        }

        $top = Dish::query()->where('is_available', true)->with('category')->withSum(['orderItems as sold' => fn ($query) => $query->whereHas('order', fn ($query) => $query->whereIn('order_status_id', [4, 6])->where('is_paid', true))], 'quantity')->whereHas('orderItems', fn ($query) => $query->whereHas('order', fn ($query) => $query->whereIn('order_status_id', [4, 6])->where('is_paid', true)))->orderByDesc('sold')->orderBy('id')->take(10)->get();

        return view('menu.index', ['top' => $top, 'dishes' => $dishes, 'categories' => Category::query()->orderBy('name')->get()]);
    }

    public function show(Dish $dish): View
    {
        abort_unless($dish->is_available, 404);
        $reviews = $dish->reviews()->with('user')->latest()->paginate(8)->withQueryString();
        $related = Dish::query()->where('category_id', $dish->category_id)->whereKeyNot($dish->id)->where('is_available', true)->take(4)->get();
        $myReview = auth()->check() ? $dish->reviews()->where('user_id', auth()->id())->first() : null;

        return view('menu.show', ['dish' => $dish->load(['images', 'category']), 'reviews' => $reviews, 'related' => $related, 'myReview' => $myReview]);
    }
}
