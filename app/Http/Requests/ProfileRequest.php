<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ProfileRequest extends GmailRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email_local' => $this->localEmailRules(),
            'email' => ['required', 'email', 'max:100', $this->gmailRule($this->user()), Rule::unique('users')->ignore($this->user()->id)],
            'phone' => ['nullable', 'regex:/^\+?[0-9]{9,15}$/'],
            'gender' => ['nullable', Rule::in(['Nam', 'Nữ', 'Khác'])],
            'birth_year' => ['nullable', 'integer', 'between:1900,'.date('Y')],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'current_password' => ['required_with:password', 'nullable', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'max:72', 'confirmed'],
        ];
    }
}
