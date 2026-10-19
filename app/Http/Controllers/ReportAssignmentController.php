<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignReportRequest;
use App\Models\AuditLog;
use App\Models\Report;
use App\Models\User;
use App\UserRole;
use App\UserStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportAssignmentController extends Controller
{
    public function update(AssignReportRequest $request, Report $report): RedirectResponse
    {
        $adminId = $request->validated('assigned_admin_id');

        DB::transaction(function () use ($request, $report, $adminId): void {
            $lockedReport = Report::query()->lockForUpdate()->findOrFail($report->id);

            if ($adminId !== null && User::query()->whereKey($adminId)->where('role', UserRole::Admin)->where('status', UserStatus::Active)->lockForUpdate()->first() === null) {
                throw ValidationException::withMessages(['assigned_admin_id' => 'Admin sudah tidak aktif. Pilih admin lain.']);
            }

            $previousAdminId = $lockedReport->assigned_admin_id;
            $lockedReport->update(['assigned_admin_id' => $adminId]);

            AuditLog::create([
                'actor_user_id' => $request->user()->id,
                'subject_type' => 'report',
                'subject_id' => $lockedReport->id,
                'module' => 'reports',
                'action' => 'assigned',
                'description' => "Penanggung jawab laporan {$lockedReport->code} diperbarui.",
                'old_values' => ['assigned_admin_id' => $previousAdminId],
                'new_values' => ['assigned_admin_id' => $adminId === null ? null : (int) $adminId],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->route('super_admin.pengaduan.detail', $report)->with('status', 'Penanggung jawab laporan berhasil diperbarui.');
    }
}
