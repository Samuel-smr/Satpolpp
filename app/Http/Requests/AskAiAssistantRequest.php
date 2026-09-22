<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AskAiAssistantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'message' => ['bail', 'required', 'string', 'max:600', 'not_regex:/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/'],
            'history' => ['prohibited'],
            'system_instruction' => ['prohibited'],
            'tools' => ['prohibited'],
            'model' => ['prohibited'],
            'base_url' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'message.required' => 'Pertanyaan wajib diisi.',
            'message.max' => 'Pertanyaan maksimal 600 karakter.',
            'message.not_regex' => 'Pertanyaan mengandung karakter kontrol yang tidak diizinkan.',
            'history.prohibited' => 'Riwayat percakapan dikelola oleh server. Muat ulang halaman dan coba lagi.',
        ];
    }
}
