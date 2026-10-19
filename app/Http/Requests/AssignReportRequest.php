<?php

namespace App\Http\Requests;

use App\UserRole;
use App\UserStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('access-super-admin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assigned_admin_id' => ['present', 'nullable', 'integer', Rule::exists('users', 'id')->where('role', UserRole::Admin->value)->where('status', UserStatus::Active->value)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['assigned_admin_id.exists' => 'Pilih akun admin yang masih aktif.'];
    }
}
