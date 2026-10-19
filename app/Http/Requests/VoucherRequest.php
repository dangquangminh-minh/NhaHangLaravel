<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class VoucherRequest extends VietnameseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('vouchers')->ignore($this->route('voucher')?->id)],
            'discount_amount' => ['required', 'numeric', 'decimal:0,2', 'between:1000,100000000'],
            'points_cost' => ['required', 'integer', 'between:1,1000000'],
            'quantity' => ['required', 'integer', 'between:0,1000000'],
            'expires_at' => ['required', 'date', 'after:now'],
        ];
    }
}
