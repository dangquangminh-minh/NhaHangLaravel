<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class RegisterRequest extends GmailRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email_local' => $this->localEmailRules(),
            'email' => ['required', 'email', 'max:100', $this->gmailRule(), Rule::unique('users')],
            'phone' => ['required', 'regex:/^\+?[0-9]{9,15}$/'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ];
    }
}
