<?php

namespace App\Http\Requests;

use App\ActivityType;
use App\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreActivityEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'activity_id' => ['required', 'integer', 'exists:activities,id'],
            'evaluation_notes' => ['required', 'string', 'max:3000'],
            'evaluation_obstacles' => ['nullable', 'string', 'max:3000'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('activity_id')) {
                    return;
                }

                $activity = Activity::query()->find($this->integer('activity_id'));

                if ($activity?->evaluated_at !== null) {
                    $validator->errors()->add('activity_id', 'Kegiatan ini sudah memiliki evaluasi.');
                }

                if ($activity?->type === ActivityType::External && $activity->report_id === null) {
                    $validator->errors()->add('activity_id', 'Kegiatan eksternal harus berasal dari laporan.');
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'activity_id.required' => 'Kegiatan yang akan dievaluasi wajib dipilih.',
            'activity_id.exists' => 'Kegiatan yang dipilih tidak ditemukan.',
            'evaluation_notes.required' => 'Hasil evaluasi wajib diisi.',
            'evaluation_notes.max' => 'Hasil evaluasi maksimal 3000 karakter.',
            'evaluation_obstacles.max' => 'Kendala maksimal 3000 karakter.',
        ];
    }
}
