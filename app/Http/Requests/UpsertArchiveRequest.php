<?php

namespace App\Http\Requests;

use App\ArchiveCategory;
use App\ArchiveConfidentiality;
use App\Models\Archive;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertArchiveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->can('access-admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'archive_context_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'operational_category' => ['required', 'string', 'max:255', Rule::in(ArchiveCategory::all()->all())],
            'description' => ['nullable', 'string', 'max:5000'],
            'confidentiality' => ['required', 'string', Rule::enum(ArchiveConfidentiality::class)],
            'file' => [
                Rule::requiredIf(! $this->route('archive') instanceof Archive),
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx,zip',
                'extensions:pdf,jpg,jpeg,png,doc,docx,zip',
                'max:51200',
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => 'Nama arsip wajib diisi.',
            'title.max' => 'Nama arsip maksimal 255 karakter.',
            'operational_category.required' => 'Kategori arsip wajib dipilih.',
            'operational_category.in' => 'Kategori arsip tidak valid.',
            'confidentiality.required' => 'Sifat dokumen wajib dipilih.',
            'confidentiality.enum' => 'Sifat dokumen tidak valid.',
            'file.required' => 'File arsip wajib diunggah.',
            'file.mimes' => 'File harus berformat PDF, JPG, JPEG, PNG, DOC, DOCX, atau ZIP.',
            'file.extensions' => 'Ekstensi file harus PDF, JPG, JPEG, PNG, DOC, DOCX, atau ZIP.',
            'file.max' => 'Ukuran file arsip maksimal 50 MB.',
        ];
    }
}
