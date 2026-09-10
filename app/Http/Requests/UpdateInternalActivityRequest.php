<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class UpdateInternalActivityRequest extends StoreInternalActivityRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'current_updated_at' => ['required', 'date_format:Y-m-d H:i:s'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'current_updated_at.required' => 'Versi data kegiatan tidak ditemukan. Muat ulang halaman.',
            'current_updated_at.date_format' => 'Versi data kegiatan tidak valid. Muat ulang halaman.',
        ];
    }
}
