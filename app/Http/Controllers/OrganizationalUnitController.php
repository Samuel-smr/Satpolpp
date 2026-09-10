<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertOrganizationalUnitRequest;
use App\Models\Officer;
use App\Models\OperationalZone;
use App\Models\OrganizationalUnit;
use App\OfficerStatus;
use App\OrganizationalUnitType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrganizationalUnitController extends Controller
{
    public function index(): View
    {
        $units = OrganizationalUnit::query()
            ->with(['leader:id,name', 'operationalZone:id,name'])
            ->withCount('officers')
            ->orderBy('name')
            ->get();

        return view('super_admin.organisasi', [
            'units' => $units,
            'unitTypes' => OrganizationalUnitType::cases(),
            'zones' => OperationalZone::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'leaders' => Officer::query()->where('status', OfficerStatus::Active)->orderBy('name')->get(['id', 'name', 'employee_number']),
            'statistics' => [
                'total' => $units->count(),
                'divisions' => $units->where('type', OrganizationalUnitType::Division)->count(),
                'officers' => $units->sum('officers_count'),
                'zones' => $units->pluck('operational_zone_id')->filter()->unique()->count(),
            ],
        ]);
    }

    public function store(UpsertOrganizationalUnitRequest $request): RedirectResponse
    {
        OrganizationalUnit::create($request->validated());

        return redirect()->route('super_admin.organisasi')->with('status', 'Unit organisasi berhasil ditambahkan.');
    }

    public function update(UpsertOrganizationalUnitRequest $request, OrganizationalUnit $organizationalUnit): RedirectResponse
    {
        $organizationalUnit->update($request->validated());

        return redirect()->route('super_admin.organisasi')->with('status', 'Unit organisasi berhasil diperbarui.');
    }

    public function destroy(OrganizationalUnit $organizationalUnit): JsonResponse
    {
        if ($organizationalUnit->children()->exists()) {
            return response()->json(['message' => 'Unit yang masih memiliki unit bawahan tidak dapat dihapus.'], 422);
        }

        $organizationalUnit->delete();

        return response()->json(['message' => 'Unit organisasi berhasil dihapus.']);
    }
}
