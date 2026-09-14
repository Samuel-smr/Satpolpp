<?php

namespace App\Http\Requests;

use App\Models\User;
use App\OfficerStatus;
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
    public function rules(): array
    {
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
            'activity_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'description' => ['nullable', 'string', 'max:5000'],
            'handling_result' => ['nullable', 'string', 'max:5000'],
            'location_address' => ['required', 'string', 'max:2000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'officer_ids' => ['required', 'array', 'min:1'],
            'officer_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('officers', 'id')->where(
                    fn (Builder $query): Builder => $query
                        ->where('status', OfficerStatus::Active->value)
                        ->whereNull('deleted_at'),
                ),
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
            'location_address.required' => 'Lokasi kegiatan wajib diisi.',
            'latitude.required' => 'Latitude wajib diisi.',
            'latitude.between' => 'Latitude harus berada antara -90 dan 90.',
            'longitude.required' => 'Longitude wajib diisi.',
            'longitude.between' => 'Longitude harus berada antara -180 dan 180.',
            'officer_ids.required' => 'Pilih minimal satu petugas.',
            'officer_ids.min' => 'Pilih minimal satu petugas.',
            'officer_ids.*.exists' => 'Petugas yang dipilih tidak aktif atau tidak tersedia.',
            'documentation.max' => 'Foto dokumentasi maksimal tiga file.',
            'documentation.*.image' => 'Setiap dokumentasi harus berupa gambar.',
            'documentation.*.mimes' => 'Foto dokumentasi hanya boleh berformat JPG, JPEG, PNG, atau WEBP.',
            'documentation.*.extensions' => 'Ekstensi foto dokumentasi harus JPG, JPEG, PNG, atau WEBP.',
            'documentation.*.max' => 'Ukuran setiap foto dokumentasi maksimal 5 MB.',
        ];
    }
}
