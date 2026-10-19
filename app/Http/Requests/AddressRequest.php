<?php

namespace App\Http\Requests;

class AddressRequest extends VietnameseRequest
{
    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^\+?[0-9]{9,15}$/'],
            'address' => ['required', 'string', 'max:255'],
            'is_default' => ['required', 'boolean'],
        ];
    }
}
