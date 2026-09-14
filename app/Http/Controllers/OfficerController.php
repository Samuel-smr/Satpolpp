<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertOfficerRequest;
use App\Models\Officer;
use App\OfficerStatus;
use App\Support\OfficerOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OfficerController extends Controller
{
    public function index(): View
    {
        $officers = Officer::query()
            ->orderBy('name')
            ->get();

        return view('super_admin.petugas', [
            'officers' => $officers,
            'positions' => OfficerOptions::positions(),
            'workUnits' => OfficerOptions::workUnits(),
            'units' => OfficerOptions::units(),
            'statistics' => [
                'total' => $officers->count(),
                'active' => $officers->where('status', OfficerStatus::Active)->count(),
                'leave' => $officers->where('status', OfficerStatus::Leave)->count(),
                'inactive' => $officers->where('status', OfficerStatus::Inactive)->count(),
            ],
        ]);
    }

    public function store(UpsertOfficerRequest $request): RedirectResponse
    {
        Officer::create([...$request->validated(), 'status' => OfficerStatus::Active]);

        return redirect()->route('super_admin.petugas')->with('status', 'Petugas berhasil ditambahkan.');
    }

    public function update(UpsertOfficerRequest $request, Officer $officer): RedirectResponse
    {
        $officer->update($request->validated());

        return redirect()->route('super_admin.petugas')->with('status', 'Data petugas berhasil diperbarui.');
    }

    public function toggleStatus(Officer $officer): JsonResponse
    {
        $nextStatus = match ($officer->status) {
            OfficerStatus::Active => OfficerStatus::Leave,
            OfficerStatus::Leave => OfficerStatus::Inactive,
            OfficerStatus::Inactive => OfficerStatus::Active,
        };
        $officer->update(['status' => $nextStatus]);

        return response()->json(['status' => $nextStatus->value, 'label' => $nextStatus->label()]);
    }

    public function destroy(Officer $officer): JsonResponse
    {
        $officer->delete();

        return response()->json(['message' => 'Petugas berhasil dihapus.']);
    }
}
