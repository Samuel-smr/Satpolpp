<?php

namespace App\Http\Requests;

use App\ReportCategory;
use App\ReportType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuestReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jenis' => ['required', 'string', Rule::enum(ReportType::class)],
            'kategori' => ['required', 'string', Rule::enum(ReportCategory::class)],
            'telepon' => ['required', 'string', 'regex:/^\+?[0-9][0-9\s-]{8,18}$/'],
            'nik' => ['nullable', 'digits:16', 'required_if:jenis,'.ReportType::Emergency->value],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'lokasi' => ['required', 'string', 'max:1000'],
            'bukti' => ['nullable', 'array', 'max:3'],
            'bukti.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'setuju' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'jenis.required' => 'Jenis pengaduan wajib dipilih.',
            'jenis.enum' => 'Jenis pengaduan tidak valid.',
            'kategori.required' => 'Kategori laporan wajib dipilih.',
            'kategori.enum' => 'Kategori laporan tidak valid.',
            'telepon.required' => 'Nomor WhatsApp atau telepon wajib diisi.',
            'telepon.regex' => 'Format nomor WhatsApp atau telepon tidak valid.',
            'nik.required_if' => 'NIK wajib diisi untuk laporan darurat.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'deskripsi.required' => 'Deskripsi laporan wajib diisi.',
            'deskripsi.max' => 'Deskripsi laporan maksimal 5000 karakter.',
            'lokasi.required' => 'Lokasi kejadian wajib diisi.',
            'lokasi.max' => 'Lokasi kejadian maksimal 1000 karakter.',
            'bukti.array' => 'Format bukti laporan tidak valid.',
            'bukti.max' => 'Bukti laporan maksimal 3 foto.',
            'bukti.*.image' => 'Bukti laporan harus berupa gambar.',
            'bukti.*.mimes' => 'Format bukti hanya boleh JPG, JPEG, PNG, atau WEBP.',
            'bukti.*.max' => 'Ukuran setiap bukti maksimal 5 MB.',
            'setuju.accepted' => 'Pernyataan kebenaran data wajib disetujui.',
        ];
    }
}
