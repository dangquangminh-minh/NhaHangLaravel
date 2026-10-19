<?php

namespace App\Http\Requests;

class LoginRequest extends VietnameseRequest
{
    protected function prepareForValidation(): void
    {
        $email = trim((string) $this->input('email'));
        $this->merge(['email' => str_contains($email, '@') ? $email : $email.'@gmail.com']);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:100'],
            'password' => ['required', 'string', 'max:72'],
        ];
    }
}
