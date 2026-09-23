<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-super-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['bail', 'required', 'string', 'regex:/\A[^\x00]{1,72}\z/s', Password::min(8)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['password.regex' => 'Password maksimal 72 byte dan tidak boleh mengandung karakter null.'];
    }
}
