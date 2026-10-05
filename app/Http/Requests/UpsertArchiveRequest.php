<?php

namespace App\Http\Requests;

use App\ArchiveConfidentiality;
use App\Models\Archive;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
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
        $archive = $this->route('archive');
        $remainingFileSlots = $archive instanceof Archive ? max(0, 5 - $archive->files()->count()) : 5;

        return [
            'archive_context_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'operational_category' => [
                'required',
                'string',
                'max:255',
                Rule::exists('activity_templates', 'name')->where(
                    fn (Builder $query): Builder => $query->where('is_active', true),
                ),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'confidentiality' => ['required', 'string', Rule::enum(ArchiveConfidentiality::class)],
            'files' => [
                Rule::requiredIf(! $this->route('archive') instanceof Archive),
                'nullable',
                'array',
                'min:1',
                'max:'.$remainingFileSlots,
            ],
            'files.*' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip',
                'extensions:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip',
                'max:20480',
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
            'operational_category.exists' => 'Kategori kegiatan tidak aktif atau tidak tersedia.',
            'confidentiality.required' => 'Sifat dokumen wajib dipilih.',
            'confidentiality.enum' => 'Sifat dokumen tidak valid.',
            'files.required' => 'Minimal satu file arsip wajib diunggah.',
            'files.min' => 'Minimal satu file arsip wajib diunggah.',
            'files.max' => 'Jumlah file dalam satu arsip maksimal lima file.',
            'files.*.mimes' => 'File harus berformat PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, atau ZIP.',
            'files.*.extensions' => 'Ekstensi file harus PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, atau ZIP.',
            'files.*.max' => 'Ukuran setiap file arsip maksimal 20 MB.',
        ];
    }
}
