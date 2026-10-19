<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SupplierRequest extends VietnameseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('suppliers')->ignore($this->route('supplier')?->id)],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^\+?[0-9]{9,15}$/'],
        ];
    }
}
