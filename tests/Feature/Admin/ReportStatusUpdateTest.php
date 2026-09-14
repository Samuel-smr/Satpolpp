<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Report;
use App\Models\User;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReportStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('processingTransitions')]
    public function test_admin_can_advance_a_report_exactly_one_processing_status(
        ReportStatus $currentStatus,
        ReportStatus $expectedStatus,
    ): void {
        $admin = User::factory()->create();
        $report = Report::factory()->create(['status' => $currentStatus]);

        $response = $this->actingAs($admin)->patch(route('admin.pengaduan.status.update', $report), [
            'current_status' => $currentStatus->value,
            'note' => 'Catatan perkembangan laporan.',
        ]);

        $response->assertRedirectToRoute('admin.pengaduan.detail', $report);
        $response->assertSessionHas('status', "Status berhasil diperbarui menjadi {$expectedStatus->label()}.");
        $this->assertSame($expectedStatus, $report->fresh()->status);
        $this->assertDatabaseHas('report_status_histories', [
            'report_id' => $report->id,
            'changed_by_user_id' => $admin->id,
            'status' => $expectedStatus->value,
            'note' => 'Catatan perkembangan laporan.',
        ]);

        $auditLog = AuditLog::query()->sole();

        $this->assertSame($admin->id, $auditLog->actor_user_id);
        $this->assertSame('report', $auditLog->subject_type);
        $this->assertSame($report->id, $auditLog->subject_id);
        $this->assertSame(['status' => $currentStatus->value], $auditLog->old_values);
        $this->assertSame(['status' => $expectedStatus->value], $auditLog->new_values);
    }

    /**
     * @return array<string, array{ReportStatus, ReportStatus}>
     */
    public static function processingTransitions(): array
    {
        return [
            'submitted to received' => [ReportStatus::Submitted, ReportStatus::Received],
            'received to verifying' => [ReportStatus::Received, ReportStatus::Verifying],
            'verifying to in progress' => [ReportStatus::Verifying, ReportStatus::InProgress],
        ];
    }

    #[DataProvider('nonAdvanceableStatuses')]
    public function test_status_endpoint_cannot_complete_or_reopen_a_report(ReportStatus $currentStatus): void
    {
        $admin = User::factory()->create();
        $report = Report::factory()->create(['status' => $currentStatus]);

        $response = $this->from(route('admin.pengaduan.detail', $report))
            ->actingAs($admin)
            ->patch(route('admin.pengaduan.status.update', $report), [
                'current_status' => $currentStatus->value,
            ]);

        $response->assertRedirect(route('admin.pengaduan.detail', $report));
        $response->assertSessionHasErrors('status');
        $this->assertSame($currentStatus, $report->fresh()->status);
        $this->assertDatabaseCount('report_status_histories', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    /**
     * @return array<string, array{ReportStatus}>
     */
    public static function nonAdvanceableStatuses(): array
    {
        return [
            'in progress requires completion workflow' => [ReportStatus::InProgress],
            'completed cannot be reopened' => [ReportStatus::Completed],
        ];
    }

    public function test_status_note_longer_than_one_thousand_characters_is_rejected_without_writes(): void
    {
        $admin = User::factory()->create();
        $report = Report::factory()->create(['status' => ReportStatus::Submitted]);

        $response = $this->from(route('admin.pengaduan.detail', $report))
            ->actingAs($admin)
            ->patch(route('admin.pengaduan.status.update', $report), [
                'current_status' => ReportStatus::Submitted->value,
                'note' => str_repeat('a', 1001),
            ]);

        $response->assertRedirect(route('admin.pengaduan.detail', $report));
        $response->assertSessionHasErrors(['note' => 'Catatan status maksimal 1000 karakter.']);
        $this->assertSame(ReportStatus::Submitted, $report->fresh()->status);
        $this->assertDatabaseCount('report_status_histories', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_guest_is_redirected_and_super_admin_is_forbidden_from_status_update(): void
    {
        $report = Report::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->patch(route('admin.pengaduan.status.update', $report))->assertRedirectToRoute('login');
        $this->actingAs($superAdmin)
            ->patch(route('admin.pengaduan.status.update', $report))
            ->assertForbidden();

        $this->assertSame(ReportStatus::Submitted, $report->fresh()->status);
        $this->assertDatabaseCount('report_status_histories', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_stale_status_request_is_rejected_without_advancing_again(): void
    {
        $admin = User::factory()->create();
        $report = Report::factory()->create(['status' => ReportStatus::Received]);

        $response = $this->from(route('admin.pengaduan.detail', $report))
            ->actingAs($admin)
            ->patch(route('admin.pengaduan.status.update', $report), [
                'current_status' => ReportStatus::Submitted->value,
            ]);

        $response->assertRedirect(route('admin.pengaduan.detail', $report));
        $response->assertSessionHasErrors([
            'status' => 'Status laporan sudah berubah. Muat ulang halaman sebelum melanjutkan.',
        ]);
        $this->assertSame(ReportStatus::Received, $report->fresh()->status);
        $this->assertDatabaseCount('report_status_histories', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }
}
