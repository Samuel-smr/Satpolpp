<?php

namespace App\Http\Requests;

use App\Services\OtpService;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

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
            'telepon' => ['required', 'string', 'regex:/^\+?[0-9][0-9\s-]{8,18}$/', function (string $attribute, mixed $value, Closure $fail): void {
                if (! app(OtpService::class)->isVerified(is_string($value) ? $value : '')) {
                    $fail('Nomor WhatsApp harus diverifikasi dengan kode verifikasi terlebih dahulu.');
                }
            }],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'lokasi' => ['required', 'string', 'max:1000'],
            'bukti' => ['nullable', 'array', 'max:3'],
            'bukti.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
            'setuju' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'telepon.required' => 'Nomor WhatsApp atau telepon wajib diisi.',
            'telepon.regex' => 'Format nomor WhatsApp atau telepon tidak valid.',
            'telepon.otp' => 'Nomor WhatsApp harus diverifikasi dengan kode verifikasi terlebih dahulu.',
            'deskripsi.required' => 'Deskripsi laporan wajib diisi.',
            'deskripsi.max' => 'Deskripsi laporan maksimal 5000 karakter.',
            'lokasi.required' => 'Lokasi kejadian wajib diisi.',
            'lokasi.max' => 'Lokasi kejadian maksimal 1000 karakter.',
            'bukti.array' => 'Format bukti laporan tidak valid.',
            'bukti.max' => 'Bukti laporan maksimal 3 foto.',
            'bukti.*.image' => 'Bukti laporan harus berupa gambar.',
            'bukti.*.mimes' => 'Format bukti hanya boleh JPG, JPEG, PNG, atau WEBP.',
            'bukti.*.extensions' => 'Ekstensi bukti harus JPG, JPEG, PNG, atau WEBP.',
            'bukti.*.max' => 'Ukuran setiap bukti maksimal 5 MB.',
            'setuju.accepted' => 'Pernyataan kebenaran data wajib disetujui.',
        ];
    }
}
