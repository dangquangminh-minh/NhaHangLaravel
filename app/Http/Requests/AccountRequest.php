<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Validation\Rule;

class AccountRequest extends GmailRequest
{
    public function authorize(): bool
    {
        return ! ($this->route('group') === 'customers' && ! $this->isMethod('post'));
    }

    public function rules(): array
    {
        $staff = $this->route('group') === 'staff';

        return [
            'name' => ['required', 'string', 'max:100'],
            'email_local' => $this->localEmailRules(),
            'email' => ['required', 'email', 'max:100', $this->gmailRule($this->route('account')), Rule::unique('users')->ignore($this->route('account')?->id)],
            'phone' => ['nullable', 'regex:/^\+?[0-9]{9,15}$/'],
            'gender' => ['nullable', Rule::in(['Nam', 'Nữ', 'Khác'])],
            'birth_year' => ['nullable', 'integer', 'between:1900,'.date('Y')],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'min:8', 'max:72', 'confirmed'],
            'role_id' => $staff ? ['required', 'integer', Rule::exists('roles', 'id')->whereIn('code', array_keys(Role::EMPLOYEE_NAMES))] : ['prohibited'],
        ];
    }
}
