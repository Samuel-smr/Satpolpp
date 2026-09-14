<?php

namespace App\Http\Requests;

use App\Models\OrganizationalUnit;
use App\OrganizationalUnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertOrganizationalUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-super-admin') ?? false;
    }

    public function rules(): array
    {
        $unit = $this->route('organizationalUnit');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('organizational_units', 'code')->ignore($unit instanceof OrganizationalUnit ? $unit->id : null)],
            'type' => ['required', Rule::enum(OrganizationalUnitType::class)],
            'parent_id' => ['nullable', 'integer', 'exists:organizational_units,id', Rule::notIn([$unit instanceof OrganizationalUnit ? $unit->id : null])],
            'operational_zone_id' => ['nullable', 'integer', 'exists:operational_zones,id'],
            'leader_officer_id' => ['nullable', 'integer', 'exists:officers,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $unit = $this->route('organizationalUnit');

                if (! $unit instanceof OrganizationalUnit || $validator->errors()->has('parent_id') || ! $this->filled('parent_id')) {
                    return;
                }

                $ancestorId = $this->integer('parent_id');
                $visitedIds = [];

                while ($ancestorId > 0 && ! in_array($ancestorId, $visitedIds, true)) {
                    if ($ancestorId === $unit->id) {
                        $validator->errors()->add('parent_id', 'Unit induk tidak boleh berasal dari unit bawahannya sendiri.');

                        return;
                    }

                    $visitedIds[] = $ancestorId;
                    $ancestorId = (int) (OrganizationalUnit::query()->whereKey($ancestorId)->value('parent_id') ?? 0);
                }
            },
        ];
    }
}
