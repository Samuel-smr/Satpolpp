<?php

namespace App\Http\Controllers;

use App\ActivityType;
use App\Http\Requests\StoreInternalActivityRequest;
use App\Http\Requests\UpdateInternalActivityRequest;
use App\Models\Activity;
use App\Models\ActivityTemplate;
use App\Models\AuditLog;
use App\Models\Officer;
use App\Models\User;
use App\OfficerStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ActivityController extends Controller
{
    public function create(): View
    {
        $activityTemplates = ActivityTemplate::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'default_description']);
        $activeOfficers = Officer::query()
            ->where('status', OfficerStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_number', 'position']);

        return view('admin.kegiatan-create', [
            'activityTemplates' => $activityTemplates,
            'activeOfficers' => $activeOfficers,
            ...$this->viewContext(),
        ]);
    }

    public function store(StoreInternalActivityRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $storedDocumentation = [];

        /** @var User $user */
        $user = $request->user();

        try {
            $storedDocumentation = $this->storeDocumentation($request);

            $activity = DB::transaction(function () use ($request, $user, $validated, $storedDocumentation): Activity {
                $officerIds = collect($validated['officer_ids'])
                    ->map(fn (mixed $id): int => (int) $id)
                    ->unique()
                    ->values();
                $activeOfficerIds = Officer::query()
                    ->whereKey($officerIds)
                    ->where('status', OfficerStatus::Active)
                    ->lockForUpdate()
                    ->pluck('id');

                if ($activeOfficerIds->sort()->values()->all() !== $officerIds->sort()->values()->all()) {
                    throw ValidationException::withMessages([
                        'officer_ids' => 'Salah satu petugas sudah tidak aktif. Muat ulang formulir dan pilih kembali petugas.',
                    ]);
                }

                $activityTemplate = null;

                if (isset($validated['activity_template_id'])) {
                    $activityTemplate = ActivityTemplate::query()
                        ->whereKey($validated['activity_template_id'])
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    if ($activityTemplate === null) {
                        throw ValidationException::withMessages([
                            'activity_template_id' => 'Template kegiatan sudah tidak aktif. Muat ulang formulir.',
                        ]);
                    }
                }

                $activity = Activity::create([
                    'report_id' => null,
                    'activity_template_id' => $validated['activity_template_id'] ?? null,
                    'created_by_user_id' => $user->id,
                    'evaluated_by_user_id' => null,
                    'name' => $validated['name'],
                    'type' => ActivityType::Internal,
                    'category' => $validated['category'],
                    'activity_date' => $validated['activity_date'],
                    'start_time' => $validated['start_time'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'handling_result' => $validated['handling_result'] ?? null,
                    'location_address' => $validated['location_address'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ]);

                $activity->findings()->createMany($validated['findings'] ?? []);

                $officerAssignments = $officerIds->mapWithKeys(
                    fn (int $officerId): array => [$officerId => ['assignment_role' => 'Petugas Kegiatan']],
                )->all();
                $activity->officers()->attach($officerAssignments);

                foreach ($storedDocumentation as $documentation) {
                    $activity->attachments()->create([
                        'uploaded_by_user_id' => $user->id,
                        'file_path' => $documentation['path'],
                        'original_name' => $documentation['original_name'],
                        'file_type' => 'image',
                        'mime_type' => $documentation['mime_type'],
                        'file_size' => $documentation['file_size'],
                        'audience' => 'internal',
                    ]);
                }

                AuditLog::create([
                    'actor_user_id' => $user->id,
                    'subject_type' => 'activity',
                    'subject_id' => $activity->id,
                    'module' => 'activities',
                    'action' => 'created',
                    'description' => "Kegiatan internal {$activity->name} dibuat.",
                    'old_values' => null,
                    'new_values' => ['type' => ActivityType::Internal->value],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $activity;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_column($storedDocumentation, 'path'));

            throw $exception;
        }

        return redirect()
            ->route($this->routeName('show'), $activity)
            ->with('status', 'Kegiatan internal berhasil disimpan.');
    }

    public function show(Activity $activity): View
    {
        $this->ensureInternalActivity($activity);

        $activity->load([
            'template:id,name',
            'createdByUser:id,name',
            'officers:id,name,employee_number,position,work_unit_name,unit_name',
            'attachments' => fn ($query) => $query->oldest('created_at'),
            'findings' => fn ($query) => $query->oldest('id'),
        ]);

        return view('admin.kegiatan-detail', ['activity' => $activity, ...$this->viewContext()]);
    }

    public function edit(Activity $activity): View
    {
        $this->ensureInternalActivity($activity);
        $activity->load([
            'officers:id,name,employee_number,position',
            'attachments' => fn ($query) => $query->oldest('created_at'),
            'findings' => fn ($query) => $query->oldest('id'),
        ]);

        $activityTemplates = ActivityTemplate::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'default_description']);
        $activeOfficers = Officer::query()
            ->where('status', OfficerStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_number', 'position']);

        return view('admin.kegiatan-create', [
            'activity' => $activity,
            'activityTemplates' => $activityTemplates,
            'activeOfficers' => $activeOfficers,
            ...$this->viewContext(),
        ]);
    }

    public function update(UpdateInternalActivityRequest $request, Activity $activity): RedirectResponse
    {
        $this->ensureInternalActivity($activity);
        $validated = $request->validated();
        $storedDocumentation = [];

        /** @var User $user */
        $user = $request->user();

        try {
            $storedDocumentation = $this->storeDocumentation($request);

            DB::transaction(function () use ($activity, $request, $user, $validated, $storedDocumentation): void {
                $lockedActivity = Activity::query()
                    ->whereKey($activity->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->ensureInternalActivity($lockedActivity);

                if ($lockedActivity->updated_at->format('Y-m-d H:i:s') !== $validated['current_updated_at']) {
                    throw ValidationException::withMessages([
                        'activity' => 'Data kegiatan sudah berubah. Muat ulang halaman sebelum menyimpan kembali.',
                    ]);
                }

                $existingDocumentationCount = $lockedActivity->attachments()->count();

                if ($existingDocumentationCount + count($storedDocumentation) > 3) {
                    throw ValidationException::withMessages([
                        'documentation' => 'Jumlah dokumentasi lama dan baru maksimal tiga foto.',
                    ]);
                }

                $officerIds = collect($validated['officer_ids'])
                    ->map(fn (mixed $id): int => (int) $id)
                    ->unique()
                    ->values();
                $activeOfficerIds = Officer::query()
                    ->whereKey($officerIds)
                    ->where('status', OfficerStatus::Active)
                    ->lockForUpdate()
                    ->pluck('id');

                if ($activeOfficerIds->sort()->values()->all() !== $officerIds->sort()->values()->all()) {
                    throw ValidationException::withMessages([
                        'officer_ids' => 'Salah satu petugas sudah tidak aktif. Muat ulang formulir dan pilih kembali petugas.',
                    ]);
                }

                $activityTemplate = null;

                if (isset($validated['activity_template_id'])) {
                    $activityTemplate = ActivityTemplate::query()
                        ->whereKey($validated['activity_template_id'])
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    if ($activityTemplate === null) {
                        throw ValidationException::withMessages([
                            'activity_template_id' => 'Template kegiatan sudah tidak aktif. Muat ulang formulir.',
                        ]);
                    }
                }

                $oldValues = [
                    'name' => $lockedActivity->name,
                    'activity_date' => $lockedActivity->activity_date->toDateString(),
                ];

                $lockedActivity->update([
                    'activity_template_id' => $validated['activity_template_id'] ?? null,
                    'name' => $validated['name'],
                    'category' => $validated['category'],
                    'activity_date' => $validated['activity_date'],
                    'start_time' => $validated['start_time'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'handling_result' => $validated['handling_result'] ?? null,
                    'location_address' => $validated['location_address'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ]);

                $lockedActivity->findings()->delete();
                $lockedActivity->findings()->createMany($validated['findings'] ?? []);

                $officerAssignments = $officerIds->mapWithKeys(
                    fn (int $officerId): array => [$officerId => ['assignment_role' => 'Petugas Kegiatan']],
                )->all();
                $lockedActivity->officers()->sync($officerAssignments);

                foreach ($storedDocumentation as $documentation) {
                    $lockedActivity->attachments()->create([
                        'uploaded_by_user_id' => $user->id,
                        'file_path' => $documentation['path'],
                        'original_name' => $documentation['original_name'],
                        'file_type' => 'image',
                        'mime_type' => $documentation['mime_type'],
                        'file_size' => $documentation['file_size'],
                        'audience' => 'internal',
                    ]);
                }

                AuditLog::create([
                    'actor_user_id' => $user->id,
                    'subject_type' => 'activity',
                    'subject_id' => $lockedActivity->id,
                    'module' => 'activities',
                    'action' => 'updated',
                    'description' => "Kegiatan internal {$lockedActivity->name} diperbarui.",
                    'old_values' => $oldValues,
                    'new_values' => [
                        'name' => $lockedActivity->name,
                        'activity_date' => $lockedActivity->activity_date->toDateString(),
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_column($storedDocumentation, 'path'));

            throw $exception;
        }

        return redirect()
            ->route($this->routeName('show'), $activity)
            ->with('status', 'Kegiatan internal berhasil diperbarui.');
    }

    public function destroy(Request $request, Activity $activity): RedirectResponse
    {
        $this->ensureInternalActivity($activity);

        DB::transaction(function () use ($request, $activity): void {
            $lockedActivity = Activity::query()
                ->whereKey($activity->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureInternalActivity($lockedActivity);

            $lockedActivity->delete();

            AuditLog::create([
                'actor_user_id' => $request->user()->id,
                'subject_type' => 'activity',
                'subject_id' => $lockedActivity->id,
                'module' => 'activities',
                'action' => 'deleted',
                'description' => "Kegiatan internal {$lockedActivity->name} dihapus.",
                'old_values' => [
                    'name' => $lockedActivity->name,
                    'type' => ActivityType::Internal->value,
                ],
                'new_values' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()
            ->route($this->routeName())
            ->with('status', 'Kegiatan internal berhasil dihapus.');
    }

    /** @return list<array{path: string, original_name: string, mime_type: string, file_size: int}> */
    private function storeDocumentation(StoreInternalActivityRequest $request): array
    {
        /** @var list<UploadedFile> $files */
        $files = $request->file('documentation', []);
        $storedDocumentation = [];

        try {
            foreach ($files as $file) {
                $path = $file->store('activities/documentation', 'local');

                if ($path === false) {
                    throw new RuntimeException('Foto dokumentasi gagal disimpan.');
                }

                $storedDocumentation[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => (int) $file->getSize(),
                ];
            }
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_column($storedDocumentation, 'path'));

            throw $exception;
        }

        return $storedDocumentation;
    }

    private function ensureInternalActivity(Activity $activity): void
    {
        if ($activity->type !== ActivityType::Internal) {
            throw new NotFoundHttpException;
        }
    }

    /** @return array{activityRoutePrefix: string, activityLayout: string} */
    private function viewContext(): array
    {
        $isSuperAdmin = request()->routeIs('super_admin.*');

        return [
            'activityRoutePrefix' => $isSuperAdmin ? 'super_admin.kegiatan' : 'admin.kegiatan',
            'activityLayout' => $isSuperAdmin ? 'layouts.super_admin' : 'layouts.admin',
        ];
    }

    private function routeName(?string $action = null): string
    {
        $routePrefix = request()->routeIs('super_admin.*') ? 'super_admin.kegiatan' : 'admin.kegiatan';

        return $action === null ? $routePrefix : "{$routePrefix}.{$action}";
    }
}
