<?php

namespace App\Http\Controllers;

use App\ActivityType;
use App\Http\Requests\StoreAdminUserRequest;
use App\Http\Requests\UpdateAdminUserRequest;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Report;
use App\Models\User;
use App\ReportCategory;
use App\ReportStatus;
use App\Services\EprofilService;
use App\Support\DataYear;
use App\UserRole;
use App\UserStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function dashboard(): View
    {

        return view('super_admin.dashboard', [
            'systemStats' => [
                'users' => User::query()->count(),
                'active_officers' => app(EprofilService::class)->officers()->count(),
                'yearly_reports' => Report::query()->forDataYear()->count(),
                'yearly_activities' => Activity::query()->forDataYear()->count(),
            ],
            'logs' => $this->systemLogs(12),
            ...$this->mapDashboardData(),
        ]);
    }

    public function user(): View
    {
        $users = User::query()
            ->whereIn('role', ['admin', 'super_admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'email', 'phone', 'role', 'status', 'created_at'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'nama' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
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

    public function monitoring(): View
    {
        $activityByDate = AuditLog::query()->forDataYear()
            ->where('created_at', '>=', app(DataYear::class)->referenceDate()->subDays(6)->startOfDay())
            ->get(['actor_user_id', 'created_at'])
            ->groupBy(fn (AuditLog $log): string => $log->created_at->toDateString());
        $chart = collect(range(6, 0))->map(function (int $daysAgo) use ($activityByDate): array {
            $date = app(DataYear::class)->referenceDate()->subDays($daysAgo);
            $dailyLogs = $activityByDate->get($date->toDateString(), collect());

            return [
                'label' => $date->translatedFormat('D'),
                'activities' => $dailyLogs->count(),
                'actors' => $dailyLogs->pluck('actor_user_id')->filter()->unique()->count(),
            ];
        });

        $monitoringStats = [
            'today' => AuditLog::query()->forDataYear()->whereDate('created_at', app(DataYear::class)->referenceDate())->count(),
            'actors' => AuditLog::query()->forDataYear()->whereDate('created_at', app(DataYear::class)->referenceDate())->whereNotNull('actor_user_id')->distinct()->count('actor_user_id'),
            'modules' => AuditLog::query()->forDataYear()->distinct()->count('module'),
        ];

        return view('super_admin.monitoring', compact('chart', 'monitoringStats'));
    }

    /** @return array<string, mixed> */
    private function mapDashboardData(): array
    {
        $reportPoints = Report::query()->forDataYear()
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
        $activityPoints = Activity::query()->forDataYear()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByDesc('activity_date')
            ->get(['id', 'name', 'type', 'description', 'location_address', 'latitude', 'longitude', 'activity_date', 'start_time'])
            ->map(fn (Activity $activity): array => [
                'id' => 'K-'.$activity->id,
                'type' => match ($activity->type) {
                    ActivityType::External => 'kegiatan_eksternal',
                    ActivityType::Internal => 'kegiatan_internal',
                },
                'title' => $activity->name,
                'desc' => $activity->description,
                'location' => $activity->location_address,
                'lat' => (float) $activity->latitude,
                'lng' => (float) $activity->longitude,
                'status' => $activity->activity_date->isFuture() ? 'Dijadwalkan' : 'Selesai',
                'date' => trim($activity->activity_date->format('d/m/Y').' '.($activity->start_time?->format('H:i') ?? '')),
            ]);
        $categoryCounts = Report::query()->forDataYear()->selectRaw('category, COUNT(*) as total')->whereNotNull('category')->groupBy('category')->pluck('total', 'category');
        $statusCounts = Report::query()->forDataYear()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $dailyReports = Report::query()->forDataYear()
            ->where('created_at', '>=', app(DataYear::class)->referenceDate()->subDays(6)->startOfDay())
            ->get(['created_at'])
            ->countBy(fn (Report $report): string => $report->created_at->toDateString());
        $reportTrend = collect(range(6, 0))->map(function (int $daysAgo) use ($dailyReports): array {
            $date = app(DataYear::class)->referenceDate()->subDays($daysAgo);

            return ['label' => $date->translatedFormat('D'), 'total' => (int) ($dailyReports[$date->toDateString()] ?? 0)];
        });

        return [
            'mapPoints' => $reportPoints->concat($activityPoints)->values(),
            'mapStatistics' => [
                'active_officers' => app(EprofilService::class)->officers()->count(),
                'open_reports' => Report::query()->forDataYear()->where('status', '!=', ReportStatus::Completed)->count(),
            ],
            'categoryChart' => collect(ReportCategory::cases())->map(fn (ReportCategory $category): array => ['label' => $category->label(), 'total' => (int) ($categoryCounts[$category->value] ?? 0)]),
            'statusChart' => collect(ReportStatus::cases())->map(fn (ReportStatus $status): array => ['label' => $status->label(), 'total' => (int) ($statusCounts[$status->value] ?? 0)]),
            'reportTrend' => $reportTrend,
            'districts' => ['Temon', 'Wates', 'Panjatan', 'Galur', 'Lendah', 'Sentolo', 'Pengasih', 'Kokap', 'Girimulyo', 'Nanggulan', 'Kalibawang', 'Samigaluh'],
            'districtCenters' => [
                ['name' => 'Temon', 'lat' => -7.87, 'lng' => 110.07],
                ['name' => 'Wates', 'lat' => -7.85, 'lng' => 110.16],
                ['name' => 'Panjatan', 'lat' => -7.88, 'lng' => 110.11],
                ['name' => 'Galur', 'lat' => -7.95, 'lng' => 110.12],
                ['name' => 'Lendah', 'lat' => -7.91, 'lng' => 110.18],
                ['name' => 'Sentolo', 'lat' => -7.84, 'lng' => 110.22],
                ['name' => 'Pengasih', 'lat' => -7.84, 'lng' => 110.18],
                ['name' => 'Kokap', 'lat' => -7.80, 'lng' => 110.14],
                ['name' => 'Girimulyo', 'lat' => -7.78, 'lng' => 110.14],
                ['name' => 'Nanggulan', 'lat' => -7.74, 'lng' => 110.19],
                ['name' => 'Kalibawang', 'lat' => -7.69, 'lng' => 110.15],
                ['name' => 'Samigaluh', 'lat' => -7.66, 'lng' => 110.20],
            ],
        ];
    }

    /**
     * @return array<int, array{waktu: string, aksi: string, user: string, jenis: string, action: string}>
     */
    private function systemLogs(int $limit): array
    {
        return AuditLog::query()->forDataYear()
            ->with('actorUser:id,name')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (AuditLog $log): array => [
                'waktu' => $log->created_at->format('d/m/Y H:i:s'),
                'aksi' => $log->description,
                'user' => $log->actorUser?->name ?? 'Sistem',
                'jenis' => $log->module,
                'action' => $log->action,
            ])
            ->all();
    }
}
