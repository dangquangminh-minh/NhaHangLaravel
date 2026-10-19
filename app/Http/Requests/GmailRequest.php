<?php

namespace App\Http\Requests;

use App\Models\User;
use Closure;

class GmailRequest extends VietnameseRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('email_local') && is_string($this->input('email_local'))) {
            $local = strtolower(trim($this->input('email_local')));
            $this->merge(['email_local' => $local, 'email' => $local.'@gmail.com']);
        } elseif (is_string($this->input('email'))) {
            $email = strtolower(trim($this->input('email')));
            $this->merge(['email' => str_contains($email, '@') ? $email : $email.'@gmail.com']);
        }
    }

    protected function gmailRule(?User $existing = null): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($existing): void {
            $legacyUnchanged = $existing && ! $this->has('email_local') && strtolower((string) $value) === strtolower($existing->email);
            if (! $legacyUnchanged && ! str_ends_with((string) $value, '@gmail.com')) {
                $fail('Email phải có đuôi @gmail.com. Chỉ nhập phần trước dấu @.');
            }
        };
    }

    protected function localEmailRules(): array
    {
        return ['sometimes', 'required', 'string', 'max:64', 'regex:/^[a-z0-9][a-z0-9._%+\-]*$/i'];
    }
}
