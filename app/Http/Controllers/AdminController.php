<?php

namespace App\Http\Controllers;

use App\ActivityType;
use App\Models\Activity;
use App\Models\ActivityTemplate;
use App\Models\Officer;
use App\Models\Report;
use App\OfficerStatus;
use App\ReportStatus;
use App\ReportType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        $today = Carbon::today();
        $filters = $request->validate([
            'period' => ['nullable', Rule::in(['1', '3', '7', 'custom'])],
            'date_start' => ['nullable', 'date_format:Y-m-d', 'required_if:period,custom'],
            'date_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_start', 'required_if:period,custom'],
        ], [
            'period.in' => 'Periode dashboard tidak valid.',
            'date_start.required_if' => 'Tanggal mulai wajib diisi untuk periode custom.',
            'date_end.required_if' => 'Tanggal akhir wajib diisi untuk periode custom.',
            'date_end.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal mulai.',
        ]);

        $period = $filters['period'] ?? '1';
        $periodStart = $period === 'custom'
            ? Carbon::createFromFormat('Y-m-d', $filters['date_start'])->startOfDay()
            : $today->copy()->subDays((int) $period - 1)->startOfDay();
        $periodEnd = $period === 'custom'
            ? Carbon::createFromFormat('Y-m-d', $filters['date_end'])->endOfDay()
            : $today->copy()->endOfDay();
        $periodDayCount = $periodStart->diffInDays($periodEnd) + 1;
        $periodLabel = $period === '1'
            ? 'Pengaduan Hari Ini'
            : ($period === 'custom' ? 'Pengaduan Periode Ini' : "Pengaduan {$period} Hari Terakhir");

        $incomingByDate = Report::query()
            ->selectRaw('DATE(created_at) as report_date, COUNT(*) as total')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'report_date');
        $completedByDate = Report::query()
            ->selectRaw('DATE(completed_at) as report_date, COUNT(*) as total')
            ->where('status', ReportStatus::Completed)
            ->whereBetween('completed_at', [$periodStart, $periodEnd])
            ->groupByRaw('DATE(completed_at)')
            ->pluck('total', 'report_date');

        $weeklyChart = collect();

        for ($offset = 0; $offset < $periodDayCount; $offset++) {
            $date = $periodStart->copy()->addDays($offset);
            $dateKey = $date->toDateString();

            $weeklyChart->push([
                'label' => $date->format('d/m'),
                'incoming' => (int) ($incomingByDate[$dateKey] ?? 0),
                'completed' => (int) ($completedByDate[$dateKey] ?? 0),
            ]);
        }

        $dashboardStats = [
            'urgent_today' => Report::query()
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->count(),
            'pending' => Report::query()
                ->whereIn('status', [ReportStatus::Submitted, ReportStatus::Received, ReportStatus::Verifying])
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->count(),
            'active_officers' => Officer::query()->where('status', OfficerStatus::Active)->count(),
            'activities_this_week' => Activity::query()
                ->whereBetween('activity_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                ->count(),
        ];

        $latestReports = Report::query()
            ->select(['id', 'code', 'type', 'category', 'status', 'location_address', 'created_at'])
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->latest()
            ->limit(5)
            ->get();

        $dashboardFilters = [
            'period' => $period,
            'date_start' => $periodStart->toDateString(),
            'date_end' => $periodEnd->toDateString(),
        ];

        return view('admin.dashboard', [
            'dashboardStats' => $dashboardStats,
            'weeklyChart' => $weeklyChart->all(),
            'latestReports' => $latestReports,
            'dashboardFilters' => $dashboardFilters,
            'periodLabel' => $periodLabel,
        ]);
    }

    public function pengaduan(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(ReportStatus::class)],
            'date_start' => ['nullable', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_start'],
        ]);
        $selectedType = ReportType::tryFrom($request->string('type')->toString());
        $search = addcslashes($filters['search'] ?? '', '%_\\');
        $reportQuery = Report::query();

        if ($selectedType !== null) {
            $reportQuery->where('type', $selectedType);
        }

        $reportQuery
            ->when($search !== '', fn ($query) => $query->where(function ($nestedQuery) use ($search): void {
                $nestedQuery->where('code', 'like', "%{$search}%")
                    ->orWhere('location_address', 'like', "%{$search}%")
                    ->orWhere('reporter_phone', 'like', "%{$search}%");
            }))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['date_start'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_end'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date));

        $reports = $reportQuery
            ->select([
                'id',
                'code',
                'type',
                'category',
                'source',
                'source_platform',
                'reporter_phone',
                'location_address',
                'status',
                'created_at',
            ])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $reportCounts = Report::query()
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) as emergency', [ReportType::Emergency->value])
            ->selectRaw('SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) as information', [ReportType::Information->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed', [ReportStatus::Completed->value])
            ->first();

        $statistics = [
            'total' => (int) $reportCounts->total,
            'emergency' => (int) $reportCounts->emergency,
            'information' => (int) $reportCounts->information,
            'completed' => (int) $reportCounts->completed,
        ];

        return view('admin.pengaduan', compact('reports', 'selectedType', 'statistics', 'filters'));
    }

    public function pengaduanDetail(Request $request, Report $report): View
    {
        $report->load([
            'statusHistories' => fn ($query) => $query
                ->with('changedByUser:id,name')
                ->oldest('created_at'),
            'attachments' => fn ($query) => $query->oldest('created_at'),
            'activity' => fn ($query) => $query->with([
                'attachments' => fn ($attachmentQuery) => $attachmentQuery->oldest('created_at'),
                'officers:id,name,employee_number,position',
            ]),
        ]);

        $activityTemplates = collect();
        $activeOfficers = collect();

        if ($report->status === ReportStatus::InProgress) {
            $activityTemplates = ActivityTemplate::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'default_description']);
            $activeOfficers = Officer::query()
                ->where('status', OfficerStatus::Active)
                ->orderBy('name')
                ->get(['id', 'name', 'employee_number', 'position']);
        }

        $reportReadOnly = $request->routeIs('super_admin.*');
        $reportLayout = $reportReadOnly ? 'layouts.super_admin' : 'layouts.admin';
        $reportBackRoute = $reportReadOnly ? 'super_admin.kegiatan' : 'admin.pengaduan';
        $activityAttachmentRoute = $reportReadOnly
            ? 'super_admin.kegiatan.export-attachments.show'
            : 'admin.kegiatan.export-attachments.show';
        $reportAttachmentRoute = $reportReadOnly
            ? 'super_admin.pengaduan.attachments.show'
            : 'admin.pengaduan.attachments.show';

        return view('admin.pengaduan-detail', compact(
            'report',
            'activityTemplates',
            'activeOfficers',
            'reportReadOnly',
            'reportLayout',
            'reportBackRoute',
            'activityAttachmentRoute',
            'reportAttachmentRoute',
        ));
    }

    public function kegiatan(Request $request): View
    {
        $activityRoutePrefix = $request->routeIs('super_admin.*') ? 'super_admin.kegiatan' : 'admin.kegiatan';
        $activityLayout = $request->routeIs('super_admin.*') ? 'layouts.super_admin' : 'layouts.admin';
        $reportAttachmentRoute = $request->routeIs('super_admin.*')
            ? 'super_admin.pengaduan.attachments.show'
            : 'admin.pengaduan.attachments.show';
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'date_start' => ['nullable', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_start'],
            'type' => ['nullable', 'string', Rule::enum(ActivityType::class)],
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
        ], [
            'month.date_format' => 'Format bulan kegiatan tidak valid.',
            'date_start.date_format' => 'Format tanggal mulai tidak valid.',
            'date_end.date_format' => 'Format tanggal akhir tidak valid.',
            'date_end.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal mulai.',
            'type.enum' => 'Jenis kegiatan tidak valid.',
        ]);
        $search = addcslashes($filters['search'] ?? '', '%_\\');

        $activityQuery = Activity::query()
            ->when($search !== '', fn ($query) => $query->where(function ($nestedQuery) use ($search): void {
                $nestedQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('location_address', 'like', "%{$search}%")
                    ->orWhereHas('report', fn ($reportQuery) => $reportQuery->where('code', 'like', "%{$search}%"));
            }))
            ->when($filters['month'] ?? null, function ($query, string $month): void {
                $monthStart = Carbon::createFromFormat('Y-m-d', "{$month}-01")->startOfMonth();

                $query->whereBetween('activity_date', [
                    $monthStart->toDateString(),
                    $monthStart->copy()->endOfMonth()->toDateString(),
                ]);
            })
            ->when($filters['date_start'] ?? null, fn ($query, string $date) => $query->whereDate('activity_date', '>=', $date))
            ->when($filters['date_end'] ?? null, fn ($query, string $date) => $query->whereDate('activity_date', '<=', $date))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['category'] ?? null, fn ($query, string $category) => $query->where('category', $category));

        $activities = (clone $activityQuery)
            ->select(['id', 'report_id', 'name', 'type', 'category', 'activity_date', 'start_time', 'location_address'])
            ->with('report:id,code')
            ->withCount('officers')
            ->orderByDesc('activity_date')
            ->orderByDesc('start_time')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $exportActivities = (clone $activityQuery)
            ->with([
                'report:id,code',
                'report.attachments:id,attachable_type,attachable_id,original_name,mime_type,file_type,audience',
                'template:id,name',
                'createdByUser:id,name',
                'evaluatedByUser:id,name',
                'officers:id,name,position',
                'attachments:id,attachable_type,attachable_id,original_name,mime_type,file_type,audience',
            ])
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get()
            ->map(fn (Activity $activity): array => [
                'id' => $activity->id,
                'id_laporan' => $activity->report_id,
                'kode_laporan' => $activity->report?->code,
                'id_template' => $activity->activity_template_id,
                'nama_template' => $activity->template?->name,
                'dibuat_oleh' => $activity->createdByUser?->name,
                'dievaluasi_oleh' => $activity->evaluatedByUser?->name,
                'nama' => $activity->name,
                'jenis' => $activity->type->label(),
                'kategori' => $activity->category,
                'tanggal' => $activity->activity_date->toDateString(),
                'waktu' => $activity->start_time?->format('H:i'),
                'deskripsi' => $activity->description,
                'hasil' => $activity->handling_result,
                'lokasi' => $activity->location_address,
                'lintang' => $activity->latitude,
                'bujur' => $activity->longitude,
                'personel' => $activity->officers->map(fn ($officer): string => trim($officer->name.($officer->position ? " ({$officer->position})" : '')))->all(),
                'catatan_evaluasi' => $activity->evaluation_notes,
                'kendala_evaluasi' => $activity->evaluation_obstacles,
                'waktu_evaluasi' => $activity->evaluated_at?->format('Y-m-d H:i:s'),
                'dibuat_pada' => $activity->created_at?->format('Y-m-d H:i:s'),
                'diperbarui_pada' => $activity->updated_at?->format('Y-m-d H:i:s'),
                'dokumentasi' => $activity->attachments
                    ->filter(fn ($attachment): bool => $attachment->file_type === 'image'
                        && in_array($attachment->audience, ['internal', 'public'], true)
                        && in_array($attachment->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true))
                    ->map(fn ($attachment): array => [
                        'nama' => $attachment->original_name,
                        'url' => route("{$activityRoutePrefix}.export-attachments.show", [$activity, $attachment]),
                    ])
                    ->concat(
                        $activity->report?->attachments
                            ->filter(fn ($attachment): bool => $attachment->file_type === 'image'
                                && $attachment->audience === 'public'
                                && in_array($attachment->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true))
                            ->map(fn ($attachment): array => [
                                'nama' => $attachment->original_name,
                                'url' => route($reportAttachmentRoute, [$activity->report, $attachment]),
                            ]) ?? collect(),
                    )
                    ->unique('url')
                    ->values()
                    ->all(),
            ]);

        return view('admin.kegiatan', [
            'activities' => $activities,
            'exportActivities' => $exportActivities,
            'filters' => $filters,
            'activityTypes' => ActivityType::cases(),
            'categories' => ActivityTemplate::query()->where('is_active', true)->orderBy('sort_order')->pluck('name'),
            'activityRoutePrefix' => $activityRoutePrefix,
            'activityLayout' => $activityLayout,
        ]);
    }

    public function feedback(): View
    {
        $feedbacks = Report::query()
            ->whereNotNull('feedback_submitted_at')
            ->select(['id', 'code', 'type', 'category', 'feedback_rating', 'feedback_comment', 'feedback_submitted_at'])
            ->latest('feedback_submitted_at')
            ->paginate(10)
            ->withQueryString();
        $summary = Report::query()
            ->whereNotNull('feedback_submitted_at')
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('AVG(feedback_rating) as average')
            ->selectRaw('SUM(CASE WHEN feedback_rating = 5 THEN 1 ELSE 0 END) as five_star')
            ->selectRaw('SUM(CASE WHEN feedback_rating < 3 THEN 1 ELSE 0 END) as low_rating')
            ->first();

        return view('admin.feedback', [
            'feedbacks' => $feedbacks,
            'feedbackSummary' => [
                'total' => (int) $summary->total,
                'average' => $summary->average !== null ? number_format((float) $summary->average, 1, ',', '.') : '0,0',
                'five_star' => (int) $summary->five_star,
                'low_rating' => (int) $summary->low_rating,
            ],
        ]);
    }
}
