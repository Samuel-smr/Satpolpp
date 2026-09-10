<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminUserRequest;
use App\Http\Requests\UpdateAdminUserRequest;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Officer;
use App\Models\OrganizationalUnit;
use App\Models\Report;
use App\Models\User;
use App\OfficerStatus;
use App\ReportCategory;
use App\ReportStatus;
use App\UserRole;
use App\UserStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function dashboard(): View
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        return view('super_admin.dashboard', [
            'systemStats' => [
                'users' => User::query()->count(),
                'active_officers' => Officer::query()->where('status', OfficerStatus::Active)->count(),
                'monthly_reports' => Report::query()->whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'monthly_activities' => Activity::query()->whereBetween('activity_date', [$monthStart->toDateString(), $monthEnd->toDateString()])->count(),
            ],
        ]);
    }

    public function kegiatan(): View
    {
        $activities = Activity::query()
            ->with([
                'report:id,code',
                'template:id,name',
                'createdByUser:id,name',
                'evaluatedByUser:id,name',
                'officers:id,name,position,organizational_unit_id',
                'officers.organizationalUnit:id,name',
                'attachments:id,attachable_type,attachable_id,original_name,mime_type,file_type,audience',
            ])
            ->orderByDesc('activity_date')
            ->orderByDesc('start_time')
            ->get()
            ->map(fn (Activity $activity): array => [
                'id' => $activity->id,
                'nama' => $activity->name,
                'tanggal' => $activity->activity_date->toDateString(),
                'waktu' => $activity->start_time?->format('H:i'),
                'idLaporan' => $activity->report_id,
                'idTemplate' => $activity->activity_template_id,
                'namaTemplate' => $activity->template?->name,
                'dibuatOleh' => $activity->createdByUser?->name,
                'dievaluasiOleh' => $activity->evaluatedByUser?->name,
                'deskripsi' => $activity->description,
                'hasil' => $activity->handling_result,
                'kategoriOperasional' => $activity->operational_category?->label(),
                'lokasi' => $activity->location_address,
                'lat' => $activity->latitude,
                'lng' => $activity->longitude,
                'peserta' => $activity->officers->pluck('id')->all(),
                'jenis' => $activity->type->label(),
                'dariLaporan' => $activity->report_id !== null,
                'kodeLaporan' => $activity->report?->code,
                'tindakan' => $activity->handling_result,
                'catatanEvaluasi' => $activity->evaluation_notes,
                'kendalaEvaluasi' => $activity->evaluation_obstacles,
                'dievaluasiPada' => $activity->evaluated_at?->format('Y-m-d H:i:s'),
                'dibuatPada' => $activity->created_at?->format('Y-m-d H:i:s'),
                'diperbaruiPada' => $activity->updated_at?->format('Y-m-d H:i:s'),
                'dihapusPada' => $activity->deleted_at?->format('Y-m-d H:i:s'),
                'dokumentasi' => $activity->attachments
                    ->filter(fn ($attachment): bool => $attachment->file_type === 'image'
                        && $attachment->audience === 'internal'
                        && in_array($attachment->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true))
                    ->map(fn ($attachment): array => [
                        'nama' => $attachment->original_name,
                        'url' => route('super_admin.kegiatan.attachments.show', [$activity, $attachment]),
                    ])
                    ->values()
                    ->all(),
            ]);
        $officers = Officer::query()
            ->with('organizationalUnit:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'position', 'organizational_unit_id'])
            ->map(fn (Officer $officer): array => [
                'id' => $officer->id,
                'nama' => $officer->name,
                'jabatan' => $officer->position ?? '-',
                'seksi' => $officer->organizationalUnit?->name ?? '-',
            ]);

        return view('super_admin.kegiatan', compact('activities', 'officers'));
    }

    public function user(): View
    {
        $users = User::query()
            ->whereIn('role', ['admin', 'super_admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'email', 'role', 'status', 'created_at'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'nama' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role->value,
                'status' => $user->status->value,
                'dibuat' => $user->created_at?->format('d/m/Y'),
            ]);

        return view('super_admin.user', ['users' => $users]);
    }

    public function storeUser(StoreAdminUserRequest $request): RedirectResponse
    {
        User::create([
            ...$request->validated(),
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
        ]);

        return redirect()->route('super_admin.user')->with('status', 'Akun admin berhasil ditambahkan.');
    }

    public function updateUser(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        if (($validated['password'] ?? '') === '') {
            unset($validated['password']);
        }
        $user->update($validated);

        return redirect()->route('super_admin.user')->with('status', 'Akun berhasil diperbarui.');
    }

    public function toggleUserStatus(User $user): JsonResponse
    {
        abort_if($user->is(auth()->user()), 422, 'Akun yang sedang digunakan tidak dapat diblokir.');
        $user->update(['status' => $user->status === UserStatus::Active ? UserStatus::Blocked : UserStatus::Active]);

        return response()->json(['status' => $user->status->value]);
    }

    public function destroyUser(User $user): JsonResponse
    {
        abort_if($user->is(auth()->user()), 422, 'Akun yang sedang digunakan tidak dapat dihapus.');
        $user->delete();

        return response()->json(['message' => 'Akun berhasil dihapus.']);
    }

    public function organisasi()
    {
        return view('super_admin.organisasi');
    }

    public function petugas()
    {
        return view('super_admin.petugas');
    }

    public function monitoring(): View
    {
        $logs = AuditLog::query()
            ->with('actorUser:id,name')
            ->latest('created_at')
            ->limit(100)
            ->get()
            ->map(fn (AuditLog $log): array => [
                'waktu' => $log->created_at->format('d/m/Y H:i:s'),
                'aksi' => $log->description,
                'user' => $log->actorUser?->name ?? 'Sistem',
                'jenis' => $log->module,
                'action' => $log->action,
            ]);
        $activityByDate = AuditLog::query()
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['actor_user_id', 'created_at'])
            ->groupBy(fn (AuditLog $log): string => $log->created_at->toDateString());
        $chart = collect(range(6, 0))->map(function (int $daysAgo) use ($activityByDate): array {
            $date = now()->subDays($daysAgo);
            $dailyLogs = $activityByDate->get($date->toDateString(), collect());

            return [
                'label' => $date->translatedFormat('D'),
                'activities' => $dailyLogs->count(),
                'actors' => $dailyLogs->pluck('actor_user_id')->filter()->unique()->count(),
            ];
        });

        $monitoringStats = [
            'today' => AuditLog::query()->whereDate('created_at', today())->count(),
            'actors' => AuditLog::query()->whereDate('created_at', today())->whereNotNull('actor_user_id')->distinct()->count('actor_user_id'),
            'modules' => AuditLog::query()->distinct()->count('module'),
        ];

        return view('super_admin.monitoring', compact('logs', 'chart', 'monitoringStats'));
    }

    public function peta(): View
    {
        $reportPoints = Report::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->get(['id', 'code', 'category', 'description', 'location_address', 'latitude', 'longitude', 'status', 'created_at'])
            ->map(fn (Report $report): array => [
                'id' => 'L-'.$report->id,
                'type' => 'laporan',
                'title' => $report->category?->label() ?? $report->code,
                'desc' => $report->description,
                'location' => $report->location_address,
                'lat' => (float) $report->latitude,
                'lng' => (float) $report->longitude,
                'status' => $report->status->label(),
                'date' => $report->created_at->format('d/m/Y H:i'),
            ]);
        $activityPoints = Activity::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByDesc('activity_date')
            ->get(['id', 'name', 'type', 'description', 'location_address', 'latitude', 'longitude', 'activity_date', 'start_time'])
            ->map(fn (Activity $activity): array => [
                'id' => 'K-'.$activity->id,
                'type' => 'kegiatan_'.$activity->type->value,
                'title' => $activity->name,
                'desc' => $activity->description,
                'location' => $activity->location_address,
                'lat' => (float) $activity->latitude,
                'lng' => (float) $activity->longitude,
                'status' => $activity->activity_date->isFuture() ? 'Dijadwalkan' : 'Selesai',
                'date' => trim($activity->activity_date->format('d/m/Y').' '.($activity->start_time?->format('H:i') ?? '')),
            ]);
        $categoryCounts = Report::query()->selectRaw('category, COUNT(*) as total')->whereNotNull('category')->groupBy('category')->pluck('total', 'category');
        $statusCounts = Report::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $dailyReports = Report::query()
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['created_at'])
            ->countBy(fn (Report $report): string => $report->created_at->toDateString());
        $reportTrend = collect(range(6, 0))->map(function (int $daysAgo) use ($dailyReports): array {
            $date = now()->subDays($daysAgo);

            return ['label' => $date->translatedFormat('D'), 'total' => (int) ($dailyReports[$date->toDateString()] ?? 0)];
        });

        return view('super_admin.peta', [
            'mapPoints' => $reportPoints->concat($activityPoints)->values(),
            'mapStatistics' => [
                'active_units' => OrganizationalUnit::query()->where('is_active', true)->count(),
                'open_reports' => Report::query()->where('status', '!=', ReportStatus::Completed)->count(),
            ],
            'categoryChart' => collect(ReportCategory::cases())->map(fn (ReportCategory $category): array => ['label' => $category->label(), 'total' => (int) ($categoryCounts[$category->value] ?? 0)]),
            'statusChart' => collect(ReportStatus::cases())->map(fn (ReportStatus $status): array => ['label' => $status->label(), 'total' => (int) ($statusCounts[$status->value] ?? 0)]),
            'reportTrend' => $reportTrend,
        ]);
    }
}
