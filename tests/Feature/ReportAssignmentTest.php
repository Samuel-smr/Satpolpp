<?php

namespace Tests\Feature;

use App\ActivityType;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Report;
use App\Models\User;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReportAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_all_reports_and_assign_an_active_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->create(['name' => 'Admin Penanganan', 'username' => 'admin.penanganan']);
        $blockedAdmin = User::factory()->blocked()->create(['name' => 'Admin Diblokir']);
        $report = Report::factory()->create();

        $this->actingAs($superAdmin)->get(route('super_admin.pengaduan'))
            ->assertOk()->assertSeeText($report->code)->assertSeeText('Belum ditugaskan');
        $this->get(route('super_admin.pengaduan.detail', $report))
            ->assertOk()->assertSeeText('Admin Penanganan (admin.penanganan)')->assertDontSeeText($blockedAdmin->name);

        $this->patch(route('super_admin.pengaduan.assignment.update', $report), ['assigned_admin_id' => $admin->id])
            ->assertRedirectToRoute('super_admin.pengaduan.detail', $report)->assertSessionHasNoErrors();

        $this->assertSame($admin->id, $report->fresh()->assigned_admin_id);
        $this->assertSame(['assigned_admin_id' => $admin->id], AuditLog::query()->sole()->new_values);
    }

    public function test_reassignment_immediately_transfers_processing_permissions_and_can_be_cleared(): void
    {
        config()->set('services.whatsapp.bot_url', 'http://localhost:3456');
        Http::preventStrayRequests();
        Http::fake(['http://localhost:3456/send' => Http::response(['status' => true])]);
        $firstAdmin = User::factory()->create();
        $secondAdmin = User::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $report = Report::factory()->assignedTo($firstAdmin)->create();

        $this->actingAs($superAdmin)->patch(route('super_admin.pengaduan.assignment.update', $report), ['assigned_admin_id' => $secondAdmin->id])->assertRedirect();
        $this->actingAs($firstAdmin)->patch(route('admin.pengaduan.status.update', $report), ['current_status' => 'submitted'])->assertForbidden();
        $this->actingAs($secondAdmin)->patch(route('admin.pengaduan.status.update', $report), ['current_status' => 'submitted'])->assertRedirect();
        $this->assertSame(ReportStatus::Received, $report->fresh()->status);

        $this->actingAs($superAdmin)->patch(route('super_admin.pengaduan.assignment.update', $report), ['assigned_admin_id' => null])->assertRedirect();
        $this->assertNull($report->fresh()->assigned_admin_id);
        $this->actingAs($secondAdmin)->delete(route('admin.pengaduan.destroy', $report))->assertForbidden();
    }

    public function test_only_super_admin_can_assign_and_only_active_admin_accounts_are_accepted(): void
    {
        $admin = User::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $blockedAdmin = User::factory()->blocked()->create();
        $report = Report::factory()->create();
        $route = route('super_admin.pengaduan.assignment.update', $report);

        $this->patch($route, ['assigned_admin_id' => $admin->id])->assertRedirectToRoute('login');
        $this->actingAs($admin)->patch($route, ['assigned_admin_id' => $admin->id])->assertForbidden();

        foreach ([$superAdmin->id, $blockedAdmin->id, 999999] as $invalidAdminId) {
            $this->actingAs($superAdmin)->patch($route, ['assigned_admin_id' => $invalidAdminId])->assertInvalid('assigned_admin_id');
        }

        $this->assertNull($report->fresh()->assigned_admin_id);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_other_admins_can_view_reports_but_cannot_see_processing_forms(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $report = Report::factory()->assignedTo($owner)->create(['status' => ReportStatus::InProgress]);
        $unassignedReport = Report::factory()->create();

        $this->actingAs($viewer)->get(route('admin.pengaduan'))
            ->assertOk()->assertSeeText($report->code)->assertSeeText($unassignedReport->code)->assertSeeText($owner->name);
        $this->get(route('admin.pengaduan.detail', $report))
            ->assertOk()->assertSeeText('Perubahan hanya dapat dilakukan oleh admin penanggung jawab.')
            ->assertDontSee('id="form-penyelesaian"', false)
            ->assertDontSee('Hapus Laporan');
    }

    /** @return array<string, array{string, string}> */
    public static function reportMutations(): array
    {
        return [
            'advance status' => ['PATCH', 'admin.pengaduan.status.update'],
            'reject' => ['PATCH', 'admin.pengaduan.reject'],
            'complete' => ['POST', 'admin.pengaduan.complete.store'],
            'delete' => ['DELETE', 'admin.pengaduan.destroy'],
        ];
    }

    #[DataProvider('reportMutations')]
    public function test_unassigned_and_other_admin_reports_reject_every_mutation(string $method, string $route): void
    {
        $admin = User::factory()->create();
        $owner = User::factory()->create();

        foreach ([null, $owner->id] as $assignedAdminId) {
            $report = Report::factory()->create(['assigned_admin_id' => $assignedAdminId, 'status' => ReportStatus::Verifying]);
            $this->actingAs($admin)->call($method, route($route, $report), [
                'current_status' => ReportStatus::Verifying->value,
                'alasan_tolak' => 'Bukan kewenangan.',
            ])->assertForbidden();
            $this->assertSame(ReportStatus::Verifying, $report->fresh()->status);
            $this->assertNotSoftDeleted($report);
        }

        $this->assertDatabaseCount('report_status_histories', 0);
        $this->assertDatabaseCount('activities', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_assigned_admin_can_reject_a_verifying_report(): void
    {
        config()->set('services.whatsapp.bot_url', 'http://localhost:3456');
        Http::preventStrayRequests();
        Http::fake(['http://localhost:3456/send' => Http::response(['status' => true])]);
        $admin = User::factory()->create();
        $report = Report::factory()->assignedTo($admin)->create(['status' => ReportStatus::Verifying]);

        $this->actingAs($admin)->patch(route('admin.pengaduan.reject', $report), [
            'current_status' => ReportStatus::Verifying->value,
            'alasan_tolak' => 'Bukan kewenangan Satpol PP.',
        ])->assertRedirectToRoute('admin.pengaduan.detail', $report);

        $this->assertSame(ReportStatus::Rejected, $report->fresh()->status);
        $this->assertDatabaseHas('report_status_histories', ['report_id' => $report->id, 'status' => 'rejected', 'changed_by_user_id' => $admin->id]);
    }

    public function test_report_ownership_also_protects_external_activity_evaluations(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $report = Report::factory()->assignedTo($owner)->create(['status' => ReportStatus::Completed]);
        $activity = Activity::factory()->create(['report_id' => $report->id, 'type' => ActivityType::External]);

        $this->actingAs($viewer)->post(route('admin.evaluasi.store'), ['activity_id' => $activity->id, 'evaluation_notes' => 'Evaluasi orang lain.'])->assertForbidden();
        $this->actingAs($owner)->post(route('admin.evaluasi.store'), ['activity_id' => $activity->id, 'evaluation_notes' => 'Evaluasi penanggung jawab.'])->assertRedirect();
        $this->get(route('admin.evaluasi'))->assertOk()->assertSee('title="Edit evaluasi"', false);
        $this->actingAs($viewer)->get(route('admin.evaluasi'))->assertOk()->assertDontSee('title="Edit evaluasi"', false)->assertSeeText('Lihat saja');
        $this->actingAs($viewer)->put(route('admin.evaluasi.update', $activity), ['evaluation_notes' => 'Diubah.'])->assertForbidden();
        $this->delete(route('admin.evaluasi.destroy', $activity))->assertForbidden();
        $this->assertSame('Evaluasi penanggung jawab.', $activity->fresh()->evaluation_notes);
    }
}
