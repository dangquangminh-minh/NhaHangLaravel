<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('contacts.create');
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        Contact::query()->create($request->validated());

        return back()->with('success', 'Cảm ơn bạn đã liên hệ. Nhà hàng đã nhận được lời nhắn.');
    }

    public function index(Request $request): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        return view('contacts.index', ['records' => Contact::query()->when($data['q'] ?? null, fn ($query, $q) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$q.'%')->orWhere('subject', 'like', '%'.$q.'%')))->latest('id')->paginate(12)->withQueryString()]);
    }

    public function update(Contact $contact): RedirectResponse
    {
        $contact->is_read = true;
        $contact->save();

        return back()->with('success', 'Đã đánh dấu đã đọc.');
    }
}
