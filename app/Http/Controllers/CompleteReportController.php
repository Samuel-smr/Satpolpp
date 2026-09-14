<?php

namespace App\Http\Controllers;

use App\ActivityType;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Officer;
use App\Models\Report;
use App\Models\User;
use App\OfficerStatus;
use App\ReportStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class CompleteReportController extends Controller
{
    public function store(Request $request, Report $report): RedirectResponse
    {
        $validated = $this->validateCompletion($request);
        $storedEvidence = [];

        /** @var User $user */
        $user = $request->user();

        try {
            $storedEvidence = $this->storeEvidence($request);

            DB::transaction(function () use ($report, $request, $user, $validated, $storedEvidence): void {
                $lockedReport = Report::query()
                    ->whereKey($report->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedReport->status !== ReportStatus::from($validated['current_status'])) {
                    throw ValidationException::withMessages([
                        'status' => 'Status laporan sudah berubah. Muat ulang halaman sebelum menyelesaikan laporan.',
                    ]);
                }

                if ($lockedReport->status !== ReportStatus::InProgress) {
                    throw ValidationException::withMessages([
                        'status' => 'Hanya laporan yang sedang ditindaklanjuti yang dapat diselesaikan.',
                    ]);
                }

                if (Activity::withTrashed()->where('report_id', $lockedReport->id)->exists()) {
                    throw ValidationException::withMessages([
                        'status' => 'Kegiatan penanganan untuk laporan ini sudah tercatat.',
                    ]);
                }

                $officerIds = collect($validated['officer_ids'])->map(fn (mixed $id): int => (int) $id)->unique()->values();
                $activeOfficerIds = Officer::query()
                    ->whereKey($officerIds)
                    ->where('status', OfficerStatus::Active)
                    ->lockForUpdate()
                    ->pluck('id');

                if ($activeOfficerIds->sort()->values()->all() !== $officerIds->sort()->values()->all()) {
                    throw ValidationException::withMessages([
                        'officer_ids' => 'Salah satu petugas sudah tidak aktif. Muat ulang halaman dan pilih kembali petugas.',
                    ]);
                }

                $activity = Activity::create([
                    'report_id' => $lockedReport->id,
                    'activity_template_id' => $validated['activity_template_id'] ?? null,
                    'created_by_user_id' => $user->id,
                    'evaluated_by_user_id' => null,
                    'name' => $validated['name'],
                    'type' => ActivityType::External,
                    'category' => null,
                    'activity_date' => $validated['activity_date'],
                    'start_time' => $validated['start_time'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'handling_result' => $validated['handling_result'],
                    'location_address' => $validated['location_address'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ]);

                $officerAssignments = $officerIds->mapWithKeys(
                    fn (int $officerId): array => [$officerId => ['assignment_role' => 'Petugas Penanganan']],
                )->all();
                $activity->officers()->attach($officerAssignments);

                foreach ($storedEvidence as $evidence) {
                    $activity->attachments()->create([
                        'uploaded_by_user_id' => $user->id,
                        'file_path' => $evidence['path'],
                        'original_name' => $evidence['original_name'],
                        'file_type' => 'image',
                        'mime_type' => $evidence['mime_type'],
                        'file_size' => $evidence['file_size'],
                        'audience' => 'public',
                    ]);
                }

                $lockedReport->update([
                    'status' => ReportStatus::Completed,
                    'completed_at' => now(),
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ]);
                $lockedReport->statusHistories()->create([
                    'changed_by_user_id' => $user->id,
                    'status' => ReportStatus::Completed,
                    'note' => $validated['handling_result'],
                ]);

                AuditLog::create([
                    'actor_user_id' => $user->id,
                    'subject_type' => 'report',
                    'subject_id' => $lockedReport->id,
                    'module' => 'reports',
                    'action' => 'completed',
                    'description' => "Laporan {$lockedReport->code} diselesaikan dan dicatat sebagai kegiatan eksternal.",
                    'old_values' => ['status' => ReportStatus::InProgress->value],
                    'new_values' => [
                        'status' => ReportStatus::Completed->value,
                        'activity_id' => $activity->id,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_column($storedEvidence, 'path'));

            throw $exception;
        }

        return redirect()
            ->route('admin.pengaduan.detail', $report)
            ->with('status', 'Laporan berhasil diselesaikan dan dicatat sebagai kegiatan eksternal.');
    }

    /**
     * @return array{
     *   current_status: string,
     *   activity_template_id?: int|null,
     *   name: string,
     *   activity_date: string,
     *   start_time?: string|null,
     *   description?: string|null,
     *   handling_result: string,
     *   location_address: string,
     *   latitude: numeric-string|float|int,
     *   longitude: numeric-string|float|int,
     *   officer_ids: list<int|string>,
     *   evidence: list<UploadedFile>
     * }
     */
    private function validateCompletion(Request $request): array
    {
        return $request->validate([
            'current_status' => ['required', 'string', Rule::enum(ReportStatus::class)],
            'activity_template_id' => [
                'nullable',
                'integer',
                Rule::exists('activity_templates', 'id')->where(fn (Builder $query): Builder => $query->where('is_active', true)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'activity_date' => ['required', 'date', 'before_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'description' => ['nullable', 'string', 'max:5000'],
            'handling_result' => ['required', 'string', 'max:5000'],
            'location_address' => ['required', 'string', 'max:2000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'officer_ids' => ['required', 'array', 'min:1'],
            'officer_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('officers', 'id')->where(
                    fn (Builder $query): Builder => $query
                        ->where('status', OfficerStatus::Active->value)
                        ->whereNull('deleted_at'),
                ),
            ],
            'evidence' => ['required', 'array', 'min:1', 'max:3'],
            'evidence.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'activity_template_id.exists' => 'Template kegiatan tidak aktif atau tidak tersedia.',
            'activity_date.before_or_equal' => 'Tanggal pelaksanaan tidak boleh melewati hari ini.',
            'officer_ids.required' => 'Pilih minimal satu petugas.',
            'officer_ids.min' => 'Pilih minimal satu petugas.',
            'officer_ids.*.exists' => 'Petugas yang dipilih tidak aktif atau tidak tersedia.',
            'evidence.required' => 'Unggah minimal satu foto bukti penanganan.',
            'evidence.min' => 'Unggah minimal satu foto bukti penanganan.',
            'evidence.max' => 'Foto bukti penanganan maksimal tiga file.',
            'evidence.*.image' => 'Setiap bukti penanganan harus berupa gambar.',
            'evidence.*.mimes' => 'Foto bukti hanya boleh berformat JPG, JPEG, PNG, atau WEBP.',
            'evidence.*.extensions' => 'Ekstensi foto bukti harus JPG, JPEG, PNG, atau WEBP.',
            'evidence.*.max' => 'Ukuran setiap foto bukti maksimal 5 MB.',
        ]);
    }

    /**
     * @return list<array{path: string, original_name: string, mime_type: string, file_size: int}>
     */
    private function storeEvidence(Request $request): array
    {
        /** @var list<UploadedFile> $files */
        $files = $request->file('evidence', []);
        $storedEvidence = [];

        try {
            foreach ($files as $file) {
                $path = $file->store('activities/evidence', 'local');

                if ($path === false) {
                    throw new RuntimeException('Foto bukti penanganan gagal disimpan.');
                }

                $storedEvidence[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => (int) $file->getSize(),
                ];
            }
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_column($storedEvidence, 'path'));

            throw $exception;
        }

        return $storedEvidence;
    }
}
