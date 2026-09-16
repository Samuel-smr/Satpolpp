<?php

namespace App\Http\Requests;

use App\AssetCategory;
use App\AssetCondition;
use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $asset = $this->route('asset');

        return [
            'asset_context_id' => ['nullable', 'integer'],
            'asset_code' => ['nullable', 'string', 'max:50', Rule::unique('assets', 'asset_code')->ignore($asset instanceof Asset ? $asset->id : null)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(AssetCategory::class)],
            'condition' => ['required', Rule::enum(AssetCondition::class)],
            'location' => ['nullable', 'string', 'max:255'],
            'acquisition_year' => ['nullable', 'integer', 'between:1900,'.now()->year],
            'tax_due_date' => ['nullable', Rule::excludeIf($this->input('category') !== AssetCategory::Vehicle->value), 'date'],
            'next_oil_service_km' => ['nullable', Rule::excludeIf($this->input('category') !== AssetCategory::Vehicle->value), 'integer', 'min:0', 'max:4294967295'],
            'last_oil_service_date' => ['nullable', Rule::excludeIf($this->input('category') !== AssetCategory::Vehicle->value), 'date'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'asset_code.unique' => 'Kode aset sudah digunakan.',
            'name.required' => 'Nama aset wajib diisi.',
            'name.max' => 'Nama aset maksimal 255 karakter.',
            'category.required' => 'Kategori aset wajib dipilih.',
            'category.enum' => 'Kategori aset tidak valid.',
            'condition.required' => 'Kondisi aset wajib dipilih.',
            'condition.enum' => 'Kondisi aset tidak valid.',
            'acquisition_year.between' => 'Tahun perolehan harus antara 1900 sampai tahun ini.',
            'tax_due_date.date' => 'Tanggal jatuh tempo pajak tidak valid.',
            'next_oil_service_km.integer' => 'Kilometer servis oli harus berupa angka bulat.',
            'last_oil_service_date.date' => 'Tanggal servis oli terakhir tidak valid.',
        ];
    }
}
