<?php

namespace App\Http\Requests;

use App\ReportSource;
use App\ReportSourcePlatform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'source' => ['required', Rule::enum(ReportSource::class)],
            'source_platform' => [Rule::requiredIf($this->input('source') === ReportSource::Online->value), 'nullable', Rule::enum(ReportSourcePlatform::class)],
            'reporter_name' => ['nullable', 'string', 'max:255'],
            'reporter_phone' => ['required', 'string', 'regex:/^\+?[0-9][0-9\s-]{8,18}$/'],
            'reporter_nik' => [Rule::requiredIf($this->input('source') === ReportSource::Offline->value), 'nullable', 'digits:16'],
            'description' => ['required', 'string', 'max:5000'],
            'location_address' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'evidence' => ['nullable', 'array', 'max:3'],
            'evidence.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'source.required' => 'Sumber laporan wajib dipilih.',
            'source_platform.required' => 'Platform online wajib dipilih.',
            'reporter_phone.required' => 'Nomor telepon wajib diisi.',
            'reporter_phone.regex' => 'Format nomor telepon tidak valid.',
            'reporter_nik.required' => 'NIK wajib diisi untuk laporan offline.',
            'reporter_nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'description.required' => 'Deskripsi laporan wajib diisi.',
            'location_address.required' => 'Lokasi kejadian wajib diisi.',
            'latitude.required' => 'Titik peta wajib dipilih.',
            'longitude.required' => 'Titik peta wajib dipilih.',
            'evidence.max' => 'Bukti laporan maksimal 3 foto.',
            'evidence.*.image' => 'Bukti laporan harus berupa gambar.',
            'evidence.*.mimes' => 'Format bukti hanya JPG, JPEG, PNG, atau WEBP.',
            'evidence.*.extensions' => 'Ekstensi bukti harus JPG, JPEG, PNG, atau WEBP.',
            'evidence.*.max' => 'Ukuran setiap foto maksimal 5 MB.',
        ];
    }
}
