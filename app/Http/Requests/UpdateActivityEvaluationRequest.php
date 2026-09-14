<?php

namespace App\Http\Requests;

use App\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $activity = $this->route('activity');

        return $activity instanceof Activity
            && $activity->evaluated_at !== null
            && ($this->user()?->can('access-admin') ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'evaluation_context_id' => ['nullable', 'integer'],
            'evaluation_notes' => ['required', 'string', 'max:3000'],
            'evaluation_obstacles' => ['nullable', 'string', 'max:3000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'evaluation_notes.required' => 'Hasil evaluasi wajib diisi.',
            'evaluation_notes.max' => 'Hasil evaluasi maksimal 3000 karakter.',
            'evaluation_obstacles.max' => 'Kendala maksimal 3000 karakter.',
        ];
    }
}
