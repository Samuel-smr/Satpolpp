<?php

namespace App\Http\Controllers;

use App\ActivityType;
use App\Models\Activity;
use App\Models\Report;
use App\Services\EprofilService;
use App\Support\Employee;
use App\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request, EprofilService $eprofil): JsonResponse
    {
        $validated = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);
        $term = $validated['q'];
        $isAdmin = $request->user()->role === UserRole::Admin;

        $reports = Report::query()->forDataYear()
            ->where(fn ($query) => $query->where('code', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('location_address', 'like', "%{$term}%"))
            ->latest()->limit(5)->get(['id', 'code', 'description'])
            ->map(fn (Report $report): array => [
                'type' => 'Laporan',
                'title' => $report->code,
                'description' => str($report->description)->limit(70)->toString(),
                'url' => $isAdmin
                    ? route('admin.pengaduan.detail', $report, absolute: false)
                    : route('super_admin.pengaduan.detail', $report, absolute: false),
            ]);

        $activities = Activity::query()->forDataYear()
            ->with('report:id,code')
            ->where(fn ($query) => $query->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('location_address', 'like', "%{$term}%"))
            ->latest('activity_date')->limit(5)
            ->get(['id', 'report_id', 'name', 'type', 'location_address'])
            ->map(function (Activity $activity) use ($isAdmin): array {
                $url = route('super_admin.kegiatan', ['search' => $activity->name], false);

                if ($isAdmin) {
                    $url = $activity->type === ActivityType::Internal
                        ? route('admin.kegiatan.show', $activity, false)
                        : ($activity->report !== null
                            ? route('admin.pengaduan.detail', $activity->report, false)
                            : route('admin.kegiatan', ['search' => $activity->name], false));
                }

                return ['type' => 'Kegiatan', 'title' => $activity->name, 'description' => $activity->location_address, 'url' => $url];
            });

        $officers = $eprofil->officers()
            ->filter(fn (Employee $officer): bool => str_contains(
                mb_strtolower($officer->name.' '.$officer->employee_number.' '.$officer->position), mb_strtolower($term),
            ))
            ->take(5)
            ->map(fn (Employee $officer): array => [
                'type' => 'Petugas',
                'title' => $officer->name,
                'description' => collect([$officer->employee_number, $officer->position])->filter()->join(' · '),
                'url' => $isAdmin
                    ? route('admin.kegiatan', ['search' => $officer->name], false)
                    : route('super_admin.petugas', ['search' => $officer->name], false),
            ]);

        return response()->json(['results' => $reports->concat($activities)->concat($officers)->values()]);
    }
}
