<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminReportRequest;
use App\Models\AuditLog;
use App\Models\Report;
use App\Models\User;
use App\Notifications\NewReportNotification;
use App\ReportStatus;
use App\ReportType;
use App\Support\DataYear;
use App\UserRole;
use App\UserStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AdminReportController extends Controller
{
    public function create(): View
    {
        return view('admin.pengaduan-create');
    }

    public function store(StoreAdminReportRequest $request): RedirectResponse
    {
        app(DataYear::class)->ensureCurrentYear();
        $validated = $request->validated();
        $storedEvidence = [];

        try {
            foreach ($request->file('evidence', []) as $file) {
                $path = $file->store('reports/evidence', 'local');

                if ($path === false) {
                    throw new RuntimeException('Bukti laporan gagal disimpan.');
                }

                $storedEvidence[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => (int) $file->getSize(),
                ];
            }

            $report = DB::transaction(function () use ($validated, $storedEvidence, $request): Report {
                $report = Report::create([
                    'code' => $this->generateReportCode(),
                    'guest_access_id' => null,
                    'created_by_user_id' => $request->user()->id,
                    'type' => ReportType::Report,
                    'category' => null,
                    'source' => $validated['source'],
                    'source_platform' => $validated['source_platform'] ?? null,
                    'reporter_name' => $validated['reporter_name'] ?? null,
                    'reporter_phone' => $validated['reporter_phone'],
                    'description' => $validated['description'],
                    'location_address' => $validated['location_address'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'status' => ReportStatus::Submitted,
                ]);

                $report->statusHistories()->create([
                    'changed_by_user_id' => $request->user()->id,
                    'status' => ReportStatus::Submitted,
                    'note' => 'Laporan dicatat oleh admin.',
                ]);

                foreach ($storedEvidence as $evidence) {
                    $report->attachments()->create([
                        'uploaded_by_user_id' => $request->user()->id,
                        'file_path' => $evidence['path'],
                        'original_name' => $evidence['original_name'],
                        'file_type' => 'image',
                        'mime_type' => $evidence['mime_type'],
                        'file_size' => $evidence['file_size'],
                        'audience' => 'public',
                    ]);
                }

                return $report;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_column($storedEvidence, 'path'));
            throw $exception;
        }

        $administrators = User::query()
            ->whereIn('role', [UserRole::Admin, UserRole::SuperAdmin])
            ->where('status', UserStatus::Active)
            ->get();
        Notification::send($administrators, new NewReportNotification($report));

        return redirect()->route('admin.pengaduan.detail', $report)->with('status', 'Laporan berhasil ditambahkan.');
    }

    public function destroy(Request $request, Report $report): RedirectResponse
    {
        Gate::authorize('manage-report', $report);

        DB::transaction(function () use ($request, $report): void {
            $lockedReport = Report::query()
                ->whereKey($report->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            Gate::authorize('manage-report', $lockedReport);

            $lockedReport->delete();

            AuditLog::create([
                'actor_user_id' => $request->user()->id,
                'subject_type' => 'report',
                'subject_id' => $lockedReport->id,
                'module' => 'reports',
                'action' => 'deleted',
                'description' => "Laporan {$lockedReport->code} dihapus.",
                'old_values' => [
                    'code' => $lockedReport->code,
                    'status' => $lockedReport->status->value,
                ],
                'new_values' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()
            ->route('admin.pengaduan')
            ->with('status', 'Laporan berhasil dihapus. Riwayat dan berkas tetap disimpan untuk audit.');
    }

    private function generateReportCode(): string
    {
        do {
            $code = 'SPJ-'.strtoupper(Str::random(6));
        } while (Report::withTrashed()->where('code', $code)->exists());

        return $code;
    }
}
