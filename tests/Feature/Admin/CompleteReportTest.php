<?php

namespace Tests\Feature\Admin;

use App\ActivityType;
use App\Models\Activity;
use App\Models\ActivityTemplate;
use App\Models\AuditLog;
use App\Models\Officer;
use App\Models\Report;
use App\Models\User;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompleteReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_complete_report_and_create_one_linked_external_activity(): void
    {
        Storage::fake('local');
        $this->travelTo(now()->setDate(2026, 9, 9)->setTime(10, 30));

        $admin = User::factory()->create();
        $report = Report::factory()->create([
            'status' => ReportStatus::InProgress,
            'description' => 'Laporan warga tetap tersimpan.',
        ]);
        $template = ActivityTemplate::factory()->create();
        $officers = Officer::factory()->count(2)->create();
        $expectedLatitude = '-7.8123456';
        $expectedLongitude = '110.1234567';

        $this->actingAs($admin)
            ->get(route('admin.pengaduan.detail', $report))
            ->assertOk()
            ->assertSee('form-penyelesaian', false)
            ->assertSee('id="completion-location-search"', false)
            ->assertSee('id="completion-current-location-button"', false)
            ->assertDontSee('name="operational_category"', false);

        $payload = $this->validPayload($report, $officers->pluck('id')->all(), [
            UploadedFile::fake()->image('bukti-satu.jpg'),
            UploadedFile::fake()->image('bukti-dua.png'),
        ], $template->id);
        $payload['latitude'] = $expectedLatitude;
        $payload['longitude'] = $expectedLongitude;

        $response = $this->actingAs($admin)->post(route('admin.pengaduan.complete.store', $report), $payload);

        $response->assertRedirectToRoute('admin.pengaduan.detail', $report);
        $response->assertSessionHas('status', 'Laporan berhasil diselesaikan dan dicatat sebagai kegiatan eksternal.');

        $freshReport = $report->fresh();
        $this->assertSame(ReportStatus::Completed, $freshReport->status);
        $this->assertSame('2026-09-09 10:30:00', $freshReport->completed_at->format('Y-m-d H:i:s'));
        $this->assertSame('Laporan warga tetap tersimpan.', $freshReport->description);
        $this->assertSame($expectedLatitude, $freshReport->latitude);
        $this->assertSame($expectedLongitude, $freshReport->longitude);

        $activity = Activity::query()->sole();
        $this->assertSame($report->id, $activity->report_id);
        $this->assertSame($template->id, $activity->activity_template_id);
        $this->assertSame($admin->id, $activity->created_by_user_id);
        $this->assertSame(ActivityType::External, $activity->type);
        $this->assertNull($activity->category);
        $this->assertDatabaseCount('reports', 1);
        $this->assertDatabaseCount('activities', 1);
        $this->assertDatabaseCount('activity_officer', 2);
        $this->assertDatabaseCount('attachments', 2);
        $this->assertDatabaseHas('report_status_histories', [
            'report_id' => $report->id,
            'changed_by_user_id' => $admin->id,
            'status' => ReportStatus::Completed->value,
            'note' => 'Lokasi sudah kembali tertib.',
        ]);

        $this->assertCount(2, $activity->attachments);
        foreach ($activity->attachments as $attachment) {
            $this->assertSame('activity', $attachment->attachable_type);
            Storage::disk('local')->assertExists($attachment->file_path);
        }

        $auditLog = AuditLog::query()->sole();
        $this->assertSame(['status' => ReportStatus::InProgress->value], $auditLog->old_values);
        $this->assertSame([
            'status' => ReportStatus::Completed->value,
            'activity_id' => $activity->id,
        ], $auditLog->new_values);

        $this->get(route('admin.pengaduan.detail', $report))
            ->assertOk()
            ->assertSee('Kegiatan eksternal')
            ->assertSee("Terhubung ke {$report->code}")
            ->assertSee('Latitude')
            ->assertSee('Longitude')
            ->assertSee('completed-activity-map', false)
            ->assertSee(route('admin.kegiatan.export-attachments.show', [$activity, $activity->attachments->first()]), false)
            ->assertSee('alt="Bukti penanganan bukti-satu.jpg"', false)
            ->assertSee('Lokasi sudah kembali tertib.')
            ->assertDontSee('Kategori operasional')
            ->assertDontSee('form-penyelesaian');

        $this->get(route('admin.kegiatan'))
            ->assertOk()
            ->assertSeeText("Penanganan {$report->code}")
            ->assertSeeText("Dari Laporan {$report->code}");
    }

    public function test_report_that_is_not_in_progress_cannot_create_an_activity_and_stored_evidence_is_cleaned(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $report = Report::factory()->create(['status' => ReportStatus::Verifying]);
        $officer = Officer::factory()->create();

        $response = $this->from(route('admin.pengaduan.detail', $report))
            ->actingAs($admin)
            ->post(route('admin.pengaduan.complete.store', $report), $this->validPayload(
                $report,
                [$officer->id],
                [UploadedFile::fake()->image('bukti.jpg')],
                currentStatus: ReportStatus::Verifying,
            ));

        $response->assertRedirect(route('admin.pengaduan.detail', $report));
        $response->assertSessionHasErrors('status');
        $this->assertSame(ReportStatus::Verifying, $report->fresh()->status);
        $this->assertDatabaseCount('activities', 0);
        $this->assertDatabaseCount('attachments', 0);
        $this->assertDatabaseCount('report_status_histories', 0);
        $this->assertDatabaseCount('audit_logs', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('activities/evidence'));
    }

    public function test_duplicate_completion_is_rejected_without_creating_a_second_activity(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $report = Report::factory()->create(['status' => ReportStatus::InProgress]);
        $officer = Officer::factory()->create();

        $firstResponse = $this->actingAs($admin)->post(
            route('admin.pengaduan.complete.store', $report),
            $this->validPayload($report, [$officer->id], [UploadedFile::fake()->image('pertama.jpg')]),
        );
        $firstResponse->assertRedirectToRoute('admin.pengaduan.detail', $report);

        $secondResponse = $this->from(route('admin.pengaduan.detail', $report))
            ->actingAs($admin)
            ->post(route('admin.pengaduan.complete.store', $report), $this->validPayload(
                $report,
                [$officer->id],
                [UploadedFile::fake()->image('kedua.jpg')],
            ));

        $secondResponse->assertRedirect(route('admin.pengaduan.detail', $report));
        $secondResponse->assertSessionHasErrors('status');
        $this->assertDatabaseCount('activities', 1);
        $this->assertDatabaseCount('attachments', 1);
        $this->assertDatabaseCount('report_status_histories', 1);
        $this->assertDatabaseCount('audit_logs', 1);
        $this->assertCount(1, Storage::disk('local')->allFiles('activities/evidence'));
    }

    public function test_inactive_officer_and_missing_evidence_are_rejected_before_any_write(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $report = Report::factory()->create(['status' => ReportStatus::InProgress]);
        $inactiveOfficer = Officer::factory()->inactive()->create();

        $response = $this->from(route('admin.pengaduan.detail', $report))
            ->actingAs($admin)
            ->post(route('admin.pengaduan.complete.store', $report), $this->validPayload(
                $report,
                [$inactiveOfficer->id],
                [],
            ));

        $response->assertRedirect(route('admin.pengaduan.detail', $report));
        $response->assertSessionHasErrors(['officer_ids.0', 'evidence']);
        $this->assertSame(ReportStatus::InProgress, $report->fresh()->status);
        $this->assertDatabaseCount('activities', 0);
        $this->assertDatabaseCount('attachments', 0);
        $this->assertDatabaseCount('report_status_histories', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_guest_is_redirected_and_super_admin_is_forbidden_from_completion(): void
    {
        $report = Report::factory()->create(['status' => ReportStatus::InProgress]);
        $superAdmin = User::factory()->superAdmin()->create();

        $this->post(route('admin.pengaduan.complete.store', $report))->assertRedirectToRoute('login');
        $this->actingAs($superAdmin)
            ->post(route('admin.pengaduan.complete.store', $report))
            ->assertForbidden();

        $this->assertSame(ReportStatus::InProgress, $report->fresh()->status);
        $this->assertDatabaseCount('activities', 0);
    }

    /**
     * @param  list<int>  $officerIds
     * @param  list<UploadedFile>  $evidence
     * @return array<string, mixed>
     */
    private function validPayload(
        Report $report,
        array $officerIds,
        array $evidence,
        ?int $templateId = null,
        ReportStatus $currentStatus = ReportStatus::InProgress,
    ): array {
        return [
            'current_status' => $currentStatus->value,
            'activity_template_id' => $templateId,
            'name' => "Penanganan {$report->code}",
            'activity_date' => now()->toDateString(),
            'start_time' => '09:15',
            'description' => 'Tindak lanjut laporan masyarakat.',
            'handling_result' => 'Lokasi sudah kembali tertib.',
            'location_address' => $report->location_address,
            'latitude' => $report->latitude,
            'longitude' => $report->longitude,
            'officer_ids' => $officerIds,
            'evidence' => $evidence,
        ];
    }
}
