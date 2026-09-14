<?php

namespace App\Http\Controllers;

use App\AssetCategory;
use App\AssetCondition;
use App\Http\Requests\StoreAssetRequest;
use App\Models\Asset;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::enum(AssetCategory::class)],
            'condition' => ['nullable', Rule::enum(AssetCondition::class)],
        ]);
        $search = addcslashes($filters['search'] ?? '', '%_\\');

        $assets = Asset::query()
            ->when($search !== '', fn ($query) => $query->where(function ($nestedQuery) use ($search): void {
                $nestedQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            }))
            ->when($filters['category'] ?? null, fn ($query, string $category) => $query->where('category', $category))
            ->when($filters['condition'] ?? null, fn ($query, string $condition) => $query->where('condition', $condition))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = Asset::query()
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN `condition` = ? THEN 1 ELSE 0 END) as good', [AssetCondition::Good->value])
            ->selectRaw('SUM(CASE WHEN `condition` IN (?, ?) THEN 1 ELSE 0 END) as needs_attention', [
                AssetCondition::NeedsRepair->value,
                AssetCondition::SeverelyDamaged->value,
            ])
            ->selectRaw('SUM(CASE WHEN category = ? THEN 1 ELSE 0 END) as vehicles', [AssetCategory::Vehicle->value])
            ->first();

        $editingAssetId = (int) $request->old('asset_context_id', 0);
        $editingAsset = $editingAssetId > 0
            ? Asset::query()->find($editingAssetId)
            : null;

        return view('admin.aset', [
            'assets' => $assets,
            'assetCategories' => AssetCategory::cases(),
            'assetConditions' => AssetCondition::cases(),
            'assetSummary' => [
                'total' => (int) $summary->total,
                'good' => (int) $summary->good,
                'needs_attention' => (int) $summary->needs_attention,
                'vehicles' => (int) $summary->vehicles,
            ],
            'editingAsset' => $editingAsset,
            'filters' => $filters,
        ]);
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $asset = Asset::create([
            ...$request->safe()->only($this->editableFields()),
            'created_by_user_id' => $request->user()->id,
        ]);

        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'subject_type' => 'asset',
            'subject_id' => $asset->id,
            'module' => 'assets',
            'action' => 'created',
            'description' => "Aset {$asset->name} ditambahkan.",
            'old_values' => null,
            'new_values' => ['asset_code' => $asset->asset_code],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.aset')->with('status', 'Aset berhasil ditambahkan.');
    }

    public function update(StoreAssetRequest $request, Asset $asset): RedirectResponse
    {
        $oldValues = $asset->only($this->editableFields());
        $attributes = $request->safe()->only($this->editableFields());

        if ($attributes['category'] !== AssetCategory::Vehicle->value) {
            $attributes['tax_due_date'] = null;
            $attributes['next_oil_service_km'] = null;
        }

        $asset->update($attributes);

        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'subject_type' => 'asset',
            'subject_id' => $asset->id,
            'module' => 'assets',
            'action' => 'updated',
            'description' => "Aset {$asset->name} diperbarui.",
            'old_values' => $oldValues,
            'new_values' => $asset->only($this->editableFields()),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.aset')->with('status', 'Aset berhasil diperbarui.');
    }

    public function destroy(Request $request, Asset $asset): RedirectResponse
    {
        Gate::authorize('access-admin');

        $asset->delete();

        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'subject_type' => 'asset',
            'subject_id' => $asset->id,
            'module' => 'assets',
            'action' => 'deleted',
            'description' => "Aset {$asset->name} dihapus.",
            'old_values' => ['asset_code' => $asset->asset_code],
            'new_values' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.aset')->with('status', 'Aset berhasil dihapus.');
    }

    /** @return list<string> */
    private function editableFields(): array
    {
        return [
            'asset_code',
            'name',
            'category',
            'condition',
            'location',
            'acquisition_year',
            'tax_due_date',
            'next_oil_service_km',
        ];
    }
}
