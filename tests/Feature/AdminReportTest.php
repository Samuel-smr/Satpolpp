<?php

namespace Tests\Feature;

use App\ActivityType;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Report;
use App\Models\User;
use App\Notifications\NewReportNotification;
use App\ReportSource;
use App\ReportSourcePlatform;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_guides_numeric_nik_and_phone_input(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.pengaduan.create'))
            ->assertOk()
            ->assertSee('name="reporter_nik"', false)
            ->assertSee('pattern="[0-9]{16}"', false)
            ->assertSee('name="reporter_phone"', false)
            ->assertSee('type="tel"', false)
            ->assertSee('id="report-location-search"', false)
            ->assertSee('id="report-current-location-button"', false)
            ->assertDontSee('name="category"', false);
    }

    public function test_admin_can_soft_delete_report_while_retaining_history_files_and_linked_activity(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $report = Report::factory()->create([
            'code' => 'SPJ-HAPUS1',
            'status' => ReportStatus::Completed,
        ]);
        $history = $report->statusHistories()->create([
            'changed_by_user_id' => $admin->id,
            'status' => ReportStatus::Completed,
            'note' => 'Laporan selesai.',
        ]);
        Storage::disk('local')->put('reports/evidence/retained.jpg', 'retained-file');
        $attachment = Attachment::factory()->create([
            'attachable_type' => 'report',
            'attachable_id' => $report->id,
            'file_path' => 'reports/evidence/retained.jpg',
        ]);
        $activity = Activity::factory()->create([
            'report_id' => $report->id,
            'type' => ActivityType::External,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.pengaduan.destroy', $report));

        $response->assertRedirectToRoute('admin.pengaduan');
        $response->assertSessionHas('status', 'Laporan berhasil dihapus. Riwayat dan berkas tetap disimpan untuk audit.');
        $this->assertSoftDeleted('reports', ['id' => $report->id]);
        $this->assertModelExists($history);
        $this->assertModelExists($attachment);
        $this->assertModelExists($activity);
        Storage::disk('local')->assertExists('reports/evidence/retained.jpg');
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => 'report',
            'subject_id' => $report->id,
            'module' => 'reports',
            'action' => 'deleted',
            'description' => 'Laporan SPJ-HAPUS1 dihapus.',
        ]);
        $this->actingAs($admin)
            ->get(route('admin.pengaduan.detail', ['report' => $report->code]))
            ->assertNotFound();
    }

    public function test_guest_and_super_admin_cannot_delete_admin_report(): void
    {
        $report = Report::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->delete(route('admin.pengaduan.destroy', $report))->assertRedirectToRoute('login');
        $this->actingAs($superAdmin)
            ->delete(route('admin.pengaduan.destroy', $report))
            ->assertForbidden();

        $this->assertNotSoftDeleted('reports', ['id' => $report->id]);
    }

    public function test_admin_can_create_offline_report_with_nik(): void
    {
        $admin = User::factory()->create();
        $otherActiveAdmin = User::factory()->create();
        $blockedAdmin = User::factory()->blocked()->create();

        $response = $this->actingAs($admin)->post(route('admin.pengaduan.store'), [
            'source' => ReportSource::Offline->value,
            'reporter_name' => 'Pelapor Offline',
            'reporter_phone' => '081234567890',
            'reporter_nik' => '1234567890123456',
            'description' => 'Laporan offline dari loket.',
            'location_address' => 'Lokasi laporan',
            'latitude' => '-7.7956',
            'longitude' => '110.3695',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reports', [
            'source' => ReportSource::Offline->value,
            'reporter_phone' => '081234567890',
            'status' => ReportStatus::Submitted->value,
            'category' => null,
        ]);
        $this->assertDatabaseHas('notifications', [
            'type' => NewReportNotification::class,
            'notifiable_id' => $admin->id,
            'read_at' => null,
        ]);
        $this->assertDatabaseHas('notifications', [
            'type' => NewReportNotification::class,
            'notifiable_id' => $otherActiveAdmin->id,
            'read_at' => null,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'type' => NewReportNotification::class,
            'notifiable_id' => $blockedAdmin->id,
        ]);
    }

    public function test_online_report_requires_platform_and_does_not_require_nik(): void
    {
        $admin = User::factory()->create();
        $data = [
            'source' => ReportSource::Online->value,
            'reporter_phone' => '081234567890',
            'description' => 'Laporan dari WhatsApp.',
            'location_address' => 'Lokasi online',
            'latitude' => '-7.7956',
            'longitude' => '110.3695',
        ];

        $this->actingAs($admin)->post(route('admin.pengaduan.store'), $data)->assertSessionHasErrors('source_platform');

        $this->actingAs($admin)->post(route('admin.pengaduan.store'), $data + [
            'source_platform' => ReportSourcePlatform::WhatsApp->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('reports', [
            'source' => ReportSource::Online->value,
            'source_platform' => ReportSourcePlatform::WhatsApp->value,
            'reporter_nik' => null,
        ]);
    }
}
