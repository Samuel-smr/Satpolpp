<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Report;
use App\Models\User;
use App\ReportStatus;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportStatusController extends Controller
{
    public function update(Request $request, Report $report): RedirectResponse
    {
        Gate::authorize('manage-report', $report);

        $validated = $request->validate([
            'current_status' => ['required', 'string', Rule::enum(ReportStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'current_status.required' => 'Status awal laporan tidak tersedia. Muat ulang halaman dan coba lagi.',
            'current_status.enum' => 'Status awal laporan tidak valid. Muat ulang halaman dan coba lagi.',
            'note.max' => 'Catatan status maksimal 1000 karakter.',
        ]);

        /** @var User $user */
        $user = $request->user();

        $nextStatus = DB::transaction(function () use ($report, $request, $user, $validated) {
            $lockedReport = Report::query()
                ->whereKey($report->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            Gate::authorize('manage-report', $lockedReport);

            $previousStatus = $lockedReport->status;

            if ($previousStatus !== ReportStatus::from($validated['current_status'])) {
                throw ValidationException::withMessages([
                    'status' => 'Status laporan sudah berubah. Muat ulang halaman sebelum melanjutkan.',
                ]);
            }

            $nextStatus = $previousStatus->nextProcessingStatus();

            if ($nextStatus === null) {
                throw ValidationException::withMessages([
                    'status' => $previousStatus->value === 'in_progress'
                        ? 'Laporan yang sedang ditindaklanjuti harus diselesaikan melalui formulir penyelesaian.'
                        : 'Laporan ini sudah selesai dan statusnya tidak dapat dimajukan lagi.',
                ]);
            }

            $lockedReport->update(['status' => $nextStatus]);
            $lockedReport->statusHistories()->create([
                'changed_by_user_id' => $user->id,
                'status' => $nextStatus,
                'note' => $validated['note'] ?? null,
            ]);

            AuditLog::create([
                'actor_user_id' => $user->id,
                'subject_type' => 'report',
                'subject_id' => $lockedReport->id,
                'module' => 'reports',
                'action' => 'status_updated',
                'description' => "Status laporan {$lockedReport->code} diubah menjadi {$nextStatus->label()}.",
                'old_values' => ['status' => $previousStatus->value],
                'new_values' => ['status' => $nextStatus->value],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $nextStatus;
        });

        app(WhatsAppService::class)->sendReportStatusUpdate($report, $nextStatus->label(), $validated['note'] ?? null);

        return redirect()
            ->route('admin.pengaduan.detail', $report)
            ->with('status', "Status berhasil diperbarui menjadi {$nextStatus->label()}.");
    }

    public function reject(Request $request, Report $report): RedirectResponse
    {
        Gate::authorize('manage-report', $report);

        $validated = $request->validate([
            'current_status' => ['required', 'string', Rule::enum(ReportStatus::class)],
            'alasan_tolak' => ['required', 'string', 'max:1000'],
        ], [
            'current_status.required' => 'Status awal laporan tidak tersedia. Muat ulang halaman dan coba lagi.',
            'current_status.enum' => 'Status awal laporan tidak valid. Muat ulang halaman dan coba lagi.',
            'alasan_tolak.required' => 'Alasan penolakan wajib diisi agar pelapor memahami penolakan ini.',
            'alasan_tolak.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        /** @var User $user */
        $user = $request->user();

        DB::transaction(function () use ($report, $request, $user, $validated) {
            $lockedReport = Report::query()
                ->whereKey($report->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            Gate::authorize('manage-report', $lockedReport);

            $previousStatus = $lockedReport->status;

            if ($previousStatus !== ReportStatus::from($validated['current_status'])) {
                throw ValidationException::withMessages([
                    'status' => 'Status laporan sudah berubah. Muat ulang halaman sebelum melanjutkan.',
                ]);
            }

            if ($previousStatus !== ReportStatus::Verifying) {
                throw ValidationException::withMessages([
                    'status' => 'Laporan hanya dapat ditolak ketika sedang dalam tahap verifikasi.',
                ]);
            }

            $lockedReport->update(['status' => ReportStatus::Rejected]);
            $lockedReport->statusHistories()->create([
                'changed_by_user_id' => $user->id,
                'status' => ReportStatus::Rejected,
                'note' => $validated['alasan_tolak'],
            ]);

            AuditLog::create([
                'actor_user_id' => $user->id,
                'subject_type' => 'report',
                'subject_id' => $lockedReport->id,
                'module' => 'reports',
                'action' => 'status_updated',
                'description' => "Status laporan {$lockedReport->code} ditolak. Alasan: {$validated['alasan_tolak']}.",
                'old_values' => ['status' => $previousStatus->value],
                'new_values' => ['status' => ReportStatus::Rejected->value],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        app(WhatsAppService::class)->sendReportStatusUpdate(
            $report,
            ReportStatus::Rejected->label(),
            $validated['alasan_tolak'],
        );

        return redirect()
            ->route('admin.pengaduan.detail', $report)
            ->with('status', 'Laporan berhasil ditolak. Pelapor dapat melihat alasan penolakan pada halaman status laporan.');
    }
}
