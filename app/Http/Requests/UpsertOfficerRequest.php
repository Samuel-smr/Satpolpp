<?php

namespace App\Http\Requests;

use App\Models\Officer;
use App\Support\OfficerOptions;
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
            'position' => ['required', 'string', 'max:255', Rule::in(OfficerOptions::positions())],
            'work_unit_name' => ['required', 'string', 'max:150', Rule::in(OfficerOptions::workUnits())],
            'unit_name' => ['required', 'string', 'max:150', Rule::in(OfficerOptions::units())],
        ];
    }
}
