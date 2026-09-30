<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('id');

        if ($this->isMethod('post')) {
            return [
                'name' => ['required', 'string', 'max:255'],
                'mobile' => ['required', 'string', 'unique:students,mobile', 'unique:users,mobile'],
                'email' => ['required', 'email', 'unique:students,email', 'unique:users,email'],
                'password' => ['nullable', 'string', 'min:6'],
                'status' => ['sometimes', 'boolean'],
            ];
        } else {
            return [
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'mobile' => ['sometimes', 'required', 'string', 'unique:students,mobile,'.$studentId],
                'email' => ['sometimes', 'required', 'email', 'unique:students,email,'.$studentId],
                'password' => ['nullable', 'string', 'min:6'],
                'status' => ['sometimes', 'boolean'],
            ];
        }

        return [];
    }
}
