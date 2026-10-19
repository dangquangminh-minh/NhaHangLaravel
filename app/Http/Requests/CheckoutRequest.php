<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CheckoutRequest extends VietnameseRequest
{
    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::in(['cash', 'transfer'])],
            'table_number' => ['nullable', 'integer', 'exists:dining_tables,number'],
            'address_id' => ['nullable', 'integer', Rule::exists('addresses', 'id')->where('user_id', $this->user()->id)],
            'voucher_code' => ['nullable', 'string', 'max:32'],
            'note' => ['nullable', 'string', 'max:255'],
            'checkout_token' => ['required', 'uuid'],
        ];
    }
}
