<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CategoryRequest extends VietnameseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('categories')->ignore($this->route('category')?->id)],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
