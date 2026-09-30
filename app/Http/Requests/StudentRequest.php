<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('student');

        if ($this->isMethod('post')) {
            return [
                'name' => ['required', 'string', 'max:255'],
                'mobile' => ['required', 'string', 'unique:users,mobile'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['nullable', 'string', 'min:6'],
                'status' => ['sometimes', 'boolean'],
            ];
        } else {
            return [
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'mobile' => ['sometimes', 'required', 'string', Rule::unique('users', 'mobile')->ignore($userId)],
                'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($userId)],
                'password' => ['nullable', 'string', 'min:6'],
                'status' => ['sometimes', 'boolean'],
            ];
        }

        return [];
    }
}
