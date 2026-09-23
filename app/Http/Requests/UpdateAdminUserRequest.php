<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-super-admin') ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user instanceof User ? $user->id : null)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user instanceof User ? $user->id : null)],
            'phone' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+()][0-9+\-\s()]*$/'],
            'password' => ['bail', 'nullable', 'string', 'regex:/\A[^\x00]{1,72}\z/s', Password::min(8)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'password.regex' => 'Password maksimal 72 byte dan tidak boleh mengandung karakter null.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
        ];
    }
}
