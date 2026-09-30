<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                'user_id' => ['required', 'integer', 'exists:users,id'],
                'seat_id' => ['required', 'integer', 'exists:seats,id'],
                'booking_date' => ['required', 'date_format:Y-m-d'],
                'slot' => ['required', 'in:06:00-09:00,09:00-12:00,12:00-15:00,15:00-18:00,18:00-21:00'],
            ];
        } else {
            return [
                'date' => ['sometimes', 'date_format:Y-m-d'],
                'slot' => ['sometimes', 'in:06:00-09:00,09:00-12:00,12:00-15:00,15:00-18:00,18:00-21:00'],
            ];
        }

        return [];
    }
}
