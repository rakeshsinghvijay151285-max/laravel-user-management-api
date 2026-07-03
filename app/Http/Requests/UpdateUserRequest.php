<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $this->user,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $this->user,
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bio' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,inactive,suspended',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email already exists',
            'phone.unique' => 'Phone number already exists',
            'password.min' => 'Password must be at least 8 characters',
            'status.in' => 'Invalid status value',
        ];
    }
}
