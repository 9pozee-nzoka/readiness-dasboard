<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'       => ['required', 'confirmed', Password::min(8)],
            'department_id'  => ['required', 'exists:departments,id'],
            'declared_level' => ['required', 'in:hod,employee'],
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.required'  => 'Please select your department.',
            'department_id.exists'    => 'The selected department is invalid.',
            'declared_level.required' => 'Please select your level (HOD or Staff).',
            'declared_level.in'       => 'Level must be either HOD or Staff.',
        ];
    }
}
