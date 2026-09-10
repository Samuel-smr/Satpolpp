<?php

namespace App\Http\Requests;

use App\Models\Officer;
use App\OfficerStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertOfficerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-super-admin') ?? false;
    }

    public function rules(): array
    {
        $officer = $this->route('officer');

        return [
            'name' => ['required', 'string', 'max:255'],
            'employee_number' => ['required', 'string', 'max:50', Rule::unique('officers', 'employee_number')->ignore($officer instanceof Officer ? $officer->id : null)],
            'rank' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:150'],
            'organizational_unit_id' => ['nullable', 'integer', 'exists:organizational_units,id'],
            'operational_zone_id' => ['nullable', 'integer', 'exists:operational_zones,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9][0-9\s-]{8,18}$/'],
            'address' => ['nullable', 'string', 'max:2000'],
            'joined_at' => ['nullable', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::enum(OfficerStatus::class)],
        ];
    }
}
