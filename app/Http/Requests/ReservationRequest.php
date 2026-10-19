<?php

namespace App\Http\Requests;

class ReservationRequest extends VietnameseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['required', 'regex:/^\+?[0-9]{9,15}$/'],
            'reserved_at' => ['required', 'date', 'after:now'],
            'table_number' => ['required', 'integer', 'exists:dining_tables,number'],
            'guests' => ['required', 'integer', 'between:1,50'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
