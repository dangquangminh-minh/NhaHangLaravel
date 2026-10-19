<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountController extends Controller
{
    private function query(string $group): Builder
    {
        return User::query()->whereHas('role', fn ($query) => $query->whereIn('code', $group === 'staff' ? array_keys(Role::EMPLOYEE_NAMES) : ['user']));
    }

    private function checkGroup(string $group, User $account): void
    {
        abort_unless($this->query($group)->whereKey($account->id)->exists(), 404);
    }

    public function index(Request $request, string $group): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $records = $this->query($group)->with('role')->when($data['q'] ?? null, fn ($query, $q) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$q.'%')->orWhere('email', 'like', '%'.$q.'%')->orWhere('phone', 'like', '%'.$q.'%')))->latest('id')->paginate(12)->withQueryString();

        return view('admin.accounts.index', compact('records', 'group'));
    }

    public function create(string $group): View
    {
        return $this->form($group, new User);
    }

    public function edit(string $group, User $account): View
    {
        abort_if($group === 'customers', 403, 'Chỉ khách hàng được tự cập nhật hồ sơ của mình.');
        $this->checkGroup($group, $account);

        return $this->form($group, $account);
    }

    private function form(string $group, User $account): View
    {
        return view('admin.accounts.form', ['group' => $group, 'record' => $account, 'roles' => Role::query()->whereIn('code', array_keys(Role::EMPLOYEE_NAMES))->get()]);
    }

    public function show(string $group, User $account): View
    {
        $this->checkGroup($group, $account);

        return view('admin.accounts.show', ['record' => $account, 'group' => $group]);
    }

    public function store(AccountRequest $request, string $group): RedirectResponse
    {
        return $this->save($request, $group, new User);
    }

    public function update(AccountRequest $request, string $group, User $account): RedirectResponse
    {
        abort_if($group === 'customers', 403);
        $this->checkGroup($group, $account);

        return $this->save($request, $group, $account);
    }

    private function save(AccountRequest $request, string $group, User $account): RedirectResponse
    {
        $data = $request->safe()->except(['role_id', 'password_confirmation', 'email_local']);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $roleId = $group === 'staff' ? (int) $request->validated('role_id') : Role::query()->where('code', 'user')->sole()->id;
        if ($account->exists && $account->id === $request->user()->id && $account->role_id !== $roleId) {
            throw ValidationException::withMessages(['role_id' => 'Không được tự đổi quyền của tài khoản đang đăng nhập.']);
        }
        $account->fill($data);
        $account->role_id = $roleId;
        $account->save();

        return redirect()->route('admin.accounts.index', $group)->with('success', 'Đã lưu tài khoản.');
    }

    public function destroy(Request $request, string $group, User $account): RedirectResponse
    {
        $this->checkGroup($group, $account);

        return $this->deleteAccounts($request, $group, [$account->id]);
    }

    public function bulkDestroy(Request $request, string $group): RedirectResponse
    {
        $data = $request->validate(['ids' => ['required', 'array', 'max:50'], 'ids.*' => ['integer', 'distinct', 'exists:users,id']]);

        return $this->deleteAccounts($request, $group, $data['ids']);
    }

    /** @param array<int, int|string> $ids */
    private function deleteAccounts(Request $request, string $group, array $ids): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $group, $ids): void {
                $accounts = $this->query($group)->whereKey($ids)->orderBy('id')->lockForUpdate()->get();
                if ($accounts->count() !== count($ids)) {
                    abort(404);
                }
                foreach ($accounts as $account) {
                    if ($account->id === $request->user()->id) {
                        throw ValidationException::withMessages(['ids' => 'Không được xóa tài khoản đang đăng nhập.']);
                    }
                    if ($account->orders()->exists() || $account->handledOrders()->exists() || $account->redemptions()->exists() || $account->reviews()->exists() || $account->reservations()->exists() || $account->addresses()->exists()) {
                        throw ValidationException::withMessages(['ids' => 'Tài khoản có dữ liệu liên quan; không xóa tài khoản nào trong lần chọn này.']);
                    }
                    $account->delete();
                }
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            return back()->with('error', 'Tài khoản đang được tham chiếu; không thể xóa.');
        }

        return redirect()->route('admin.accounts.index', $group)->with('success', 'Đã xóa tài khoản đã chọn.');
    }
}
