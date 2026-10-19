<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class DishRequest extends VietnameseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200', Rule::unique('dishes')->ignore($this->route('dish')?->id)],
            'price' => ['required', 'numeric', 'decimal:0,2', 'between:1000,100000000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'is_available' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
