<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                'seat_number' => ['required', 'string', 'max:10', 'unique:seats,seat_number'],
            ];
        } else {
              return [
                'seat_number' => ['required', 'string', 'max:10', 'unique:seats,seat_number'],
                'date' => ['required', 'date_format:Y-m-d'],
                'slot' => ['required', 'in:06:00-09:00,09:00-12:00,12:00-15:00,15:00-18:00,18:00-21:00'],
            ];
        }



        return [];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('seat_number')) {
            $this->merge([
                'seat_number' => strtoupper(trim((string) $this->seat_number)),
            ]);
        }
    }
}