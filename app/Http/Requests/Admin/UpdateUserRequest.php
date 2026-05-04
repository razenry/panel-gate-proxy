<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'max:255', 'unique:users,username,'.$this->user->id],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.$this->user->id],
            'password' => ['sometimes', Password::defaults()],
            'is_admin' => ['sometimes', 'boolean'],
        ];

    }
}
