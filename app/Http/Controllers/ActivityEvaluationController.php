<?php

namespace App\Http\Controllers;

use App\ActivityType;
use App\Http\Requests\StoreActivityEvaluationRequest;
use App\Http\Requests\UpdateActivityEvaluationRequest;
use App\Models\Activity;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActivityEvaluationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::enum(ActivityType::class)],
            'date_start' => ['nullable', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_start'],
        ]);
        $search = addcslashes($filters['search'] ?? '', '%_\\');

        $evaluations = Activity::query()
            ->whereNotNull('evaluated_at')
            ->when($search !== '', fn ($query) => $query->where(function ($nestedQuery) use ($search): void {
                $nestedQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('evaluation_notes', 'like', "%{$search}%")
                    ->orWhereHas('report', fn ($reportQuery) => $reportQuery->where('code', 'like', "%{$search}%"));
            }))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['date_start'] ?? null, fn ($query, string $date) => $query->whereDate('evaluated_at', '>=', $date))
            ->when($filters['date_end'] ?? null, fn ($query, string $date) => $query->whereDate('evaluated_at', '<=', $date))
            ->with(['report:id,code', 'evaluatedByUser:id,name'])
            ->latest('evaluated_at')
            ->paginate(15)
            ->withQueryString();

        $availableActivities = Activity::query()
            ->whereNull('evaluated_at')
            ->where(function ($query): void {
                $query->where('type', ActivityType::Internal)
                    ->orWhere(function ($externalQuery): void {
                        $externalQuery->where('type', ActivityType::External)
                            ->whereNotNull('report_id');
                    });
            })
            ->with('report:id,code')
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->get();

        $summary = Activity::query()
            ->whereNotNull('evaluated_at')
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) as internal', [ActivityType::Internal->value])
            ->selectRaw('SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) as external', [ActivityType::External->value])
            ->selectRaw("SUM(CASE WHEN evaluation_obstacles IS NOT NULL AND evaluation_obstacles <> '' THEN 1 ELSE 0 END) as with_obstacles")
            ->first();

        $editingEvaluationId = (int) $request->old('evaluation_context_id', 0);
        $editingEvaluation = $editingEvaluationId > 0
            ? Activity::query()->whereNotNull('evaluated_at')->find($editingEvaluationId)
            : null;

        return view('admin.evaluasi', [
            'evaluations' => $evaluations,
            'availableActivities' => $availableActivities,
            'evaluationSummary' => [
                'total' => (int) $summary->total,
                'internal' => (int) $summary->internal,
                'external' => (int) $summary->external,
                'with_obstacles' => (int) $summary->with_obstacles,
            ],
            'editingEvaluation' => $editingEvaluation,
            'filters' => $filters,
            'activityTypes' => ActivityType::cases(),
        ]);
    }

    public function store(StoreActivityEvaluationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        DB::transaction(function () use ($validated, $user, $request): void {
            $activity = Activity::query()->lockForUpdate()->findOrFail($validated['activity_id']);

            if ($activity->evaluated_at !== null) {
                abort(422, 'Kegiatan ini sudah memiliki evaluasi.');
            }

            $activity->update([
                'evaluated_by_user_id' => $user->id,
                'evaluation_notes' => $validated['evaluation_notes'],
                'evaluation_obstacles' => $validated['evaluation_obstacles'] ?? null,
                'evaluated_at' => now(),
            ]);

            AuditLog::create([
                'actor_user_id' => $user->id,
                'subject_type' => 'activity',
                'subject_id' => $activity->id,
                'module' => 'activity_evaluations',
                'action' => 'evaluated',
                'description' => "Kegiatan {$activity->name} dievaluasi.",
                'old_values' => null,
                'new_values' => ['evaluated_at' => $activity->evaluated_at?->toDateTimeString()],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()
            ->route('admin.evaluasi')
            ->with('status', 'Evaluasi kegiatan berhasil disimpan.');
    }

    public function update(UpdateActivityEvaluationRequest $request, Activity $activity): RedirectResponse
    {
        $validated = $request->safe()->only(['evaluation_notes', 'evaluation_obstacles']);
        $user = $request->user();

        DB::transaction(function () use ($activity, $validated, $user, $request): void {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);

            if ($lockedActivity->evaluated_at === null) {
                throw new NotFoundHttpException;
            }

            $oldValues = [
                'evaluation_notes' => $lockedActivity->evaluation_notes,
                'evaluation_obstacles' => $lockedActivity->evaluation_obstacles,
                'evaluated_by_user_id' => $lockedActivity->evaluated_by_user_id,
                'evaluated_at' => $lockedActivity->evaluated_at->toDateTimeString(),
            ];

            $lockedActivity->update([
                ...$validated,
                'evaluated_by_user_id' => $user->id,
                'evaluated_at' => now(),
            ]);

            $this->writeAuditLog(
                $request,
                $lockedActivity,
                'updated',
                "Evaluasi kegiatan {$lockedActivity->name} diperbarui.",
                $oldValues,
            );
        });

        return redirect()->route('admin.evaluasi')->with('status', 'Evaluasi kegiatan berhasil diperbarui.');
    }

    public function destroy(Request $request, Activity $activity): RedirectResponse
    {
        Gate::authorize('access-admin');

        DB::transaction(function () use ($activity, $request): void {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);

            if ($lockedActivity->evaluated_at === null) {
                throw new NotFoundHttpException;
            }

            $oldValues = [
                'evaluation_notes' => $lockedActivity->evaluation_notes,
                'evaluation_obstacles' => $lockedActivity->evaluation_obstacles,
                'evaluated_by_user_id' => $lockedActivity->evaluated_by_user_id,
                'evaluated_at' => $lockedActivity->evaluated_at->toDateTimeString(),
            ];

            $lockedActivity->update([
                'evaluation_notes' => null,
                'evaluation_obstacles' => null,
                'evaluated_by_user_id' => null,
                'evaluated_at' => null,
            ]);

            $this->writeAuditLog(
                $request,
                $lockedActivity,
                'deleted',
                "Evaluasi kegiatan {$lockedActivity->name} dihapus.",
                $oldValues,
            );
        });

        return redirect()->route('admin.evaluasi')->with('status', 'Evaluasi kegiatan berhasil dihapus.');
    }

    /** @param array<string, mixed>|null $oldValues */
    private function writeAuditLog(
        Request $request,
        Activity $activity,
        string $action,
        string $description,
        ?array $oldValues = null,
    ): void {
        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'subject_type' => 'activity',
            'subject_id' => $activity->id,
            'module' => 'activity_evaluations',
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => [
                'evaluation_notes' => $activity->evaluation_notes,
                'evaluation_obstacles' => $activity->evaluation_obstacles,
                'evaluated_by_user_id' => $activity->evaluated_by_user_id,
                'evaluated_at' => $activity->evaluated_at?->toDateTimeString(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
