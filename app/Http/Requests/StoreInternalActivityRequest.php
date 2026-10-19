<?php

namespace App\Http\Requests;

use App\FindingType;
use App\FindingUnit;
use App\Models\Activity;
use App\Models\User;
use App\Services\EprofilService;
use App\Support\DataYear;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInternalActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && ($user->can('access-admin') || $user->can('access-super-admin'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(EprofilService $eprofil): array
    {
        $employeeNips = $eprofil->officers()->pluck('employee_number');
        $activity = $this->route('activity');
        if ($activity instanceof Activity) {
            $employeeNips = $employeeNips->merge($activity->personnel()->pluck('employee_nip'));
        }

        return [
            'activity_template_id' => [
                'nullable',
                'integer',
                Rule::exists('activity_templates', 'id')->where(
                    fn (Builder $query): Builder => $query->where('is_active', true),
                ),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => [
                'required',
                'string',
                'max:255',
                Rule::exists('activity_templates', 'name')->where(
                    fn (Builder $query): Builder => $query->where('is_active', true),
                ),
            ],
            'activity_date' => ['required', 'date', ...app(DataYear::class)->dateRules()],
            'start_time' => ['nullable', 'date_format:H:i'],
            'description' => ['nullable', 'string', 'max:5000'],
            'handling_result' => ['nullable', 'string', 'max:5000'],
            'findings' => ['nullable', 'array', 'max:50'],
            'findings.*.type' => ['required', Rule::enum(FindingType::class)],
            'findings.*.quantity' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'findings.*.unit' => ['required', Rule::enum(FindingUnit::class)],
            'location_address' => ['required', 'string', 'max:2000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'employee_nips' => ['required', 'array', 'min:1'],
            'employee_nips.*' => [
                'required',
                'string',
                'max:50',
                'distinct:strict',
                Rule::in($employeeNips->all()),
            ],
            'documentation' => ['nullable', 'array', 'max:3'],
            'documentation.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'activity_template_id.exists' => 'Template kegiatan tidak aktif atau tidak tersedia.',
            'name.required' => 'Nama kegiatan wajib diisi.',
            'category.required' => 'Kategori kegiatan wajib dipilih.',
            'category.exists' => 'Kategori kegiatan tidak aktif atau tidak tersedia.',
            'activity_date.required' => 'Tanggal pelaksanaan wajib diisi.',
            'start_time.date_format' => 'Format waktu mulai tidak valid.',
            'findings.max' => 'Temuan maksimal 50 baris.',
            'findings.*.type.required' => 'Jenis pada setiap temuan wajib dipilih.',
            'findings.*.type.enum' => 'Jenis temuan tidak valid.',
            'findings.*.quantity.required' => 'Jumlah pada setiap temuan wajib diisi.',
            'findings.*.quantity.integer' => 'Jumlah temuan harus berupa angka bulat.',
            'findings.*.quantity.min' => 'Jumlah temuan minimal satu.',
            'findings.*.quantity.max' => 'Jumlah temuan maksimal 4.294.967.295.',
            'findings.*.unit.required' => 'Satuan pada setiap temuan wajib dipilih.',
            'findings.*.unit.enum' => 'Satuan temuan tidak valid.',
            'location_address.required' => 'Lokasi kegiatan wajib diisi.',
            'latitude.required' => 'Latitude wajib diisi.',
            'latitude.between' => 'Latitude harus berada antara -90 dan 90.',
            'longitude.required' => 'Longitude wajib diisi.',
            'longitude.between' => 'Longitude harus berada antara -180 dan 180.',
            'employee_nips.required' => 'Pilih minimal satu petugas.',
            'employee_nips.min' => 'Pilih minimal satu petugas.',
            'employee_nips.*.in' => 'Pegawai yang dipilih tidak tersedia di e-Profil.',
            'documentation.max' => 'Foto dokumentasi maksimal tiga file.',
            'documentation.*.image' => 'Setiap dokumentasi harus berupa gambar.',
            'documentation.*.mimes' => 'Foto dokumentasi hanya boleh berformat JPG, JPEG, PNG, atau WEBP.',
            'documentation.*.extensions' => 'Ekstensi foto dokumentasi harus JPG, JPEG, PNG, atau WEBP.',
            'documentation.*.max' => 'Ukuran setiap foto dokumentasi maksimal 5 MB.',
        ];
    }
}
