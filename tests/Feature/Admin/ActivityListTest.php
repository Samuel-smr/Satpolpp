<?php

namespace Tests\Feature\Admin;

use App\ActivityType;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Officer;
use App\Models\Report;
use App\Models\User;
use App\OperationalCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivityListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_database_activities_with_report_link_and_officer_count(): void
    {
        $admin = User::factory()->create();
        $report = Report::factory()->create(['code' => 'SPJ-ACTIVITY-001']);
        $officers = Officer::factory()->count(2)->create();
        $externalActivity = Activity::factory()->for($report)->create([
            'name' => 'Penanganan Laporan Pasar',
            'type' => ActivityType::External,
            'operational_category' => OperationalCategory::PublicComplaintHandling,
            'activity_date' => '2026-09-09',
            'start_time' => '08:15:00',
            'location_address' => 'Pasar Wates',
        ]);
        $externalActivity->officers()->attach($officers->pluck('id'));
        Activity::factory()->create([
            'name' => 'Patroli Internal Malam',
            'type' => ActivityType::Internal,
            'activity_date' => '2026-09-08',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kegiatan'));

        $response->assertOk();
        $response->assertSeeText('Penanganan Laporan Pasar');
        $response->assertSeeText('Dari Laporan SPJ-ACTIVITY-001');
        $response->assertSeeText('2 orang');
        $response->assertSeeText('Patroli Internal Malam');
        $response->assertSee(route('admin.pengaduan.detail', $report), false);
    }

    public function test_activity_filters_use_allowlisted_type_month_and_date_range(): void
    {
        $admin = User::factory()->create();
        Activity::factory()->create([
            'name' => 'Eksternal September Dalam Rentang',
            'type' => ActivityType::External,
            'activity_date' => '2026-09-12',
        ]);
        Activity::factory()->create([
            'name' => 'Internal September Dalam Rentang',
            'type' => ActivityType::Internal,
            'activity_date' => '2026-09-13',
        ]);
        Activity::factory()->create([
            'name' => 'Eksternal September Di Luar Rentang',
            'type' => ActivityType::External,
            'activity_date' => '2026-09-25',
        ]);
        Activity::factory()->create([
            'name' => 'Eksternal Oktober',
            'type' => ActivityType::External,
            'activity_date' => '2026-10-12',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kegiatan', [
            'month' => '2026-09',
            'date_start' => '2026-09-10',
            'date_end' => '2026-09-20',
            'type' => ActivityType::External->value,
        ]));

        $response->assertOk();
        $response->assertSeeText('Eksternal September Dalam Rentang');
        $response->assertDontSeeText('Internal September Dalam Rentang');
        $response->assertDontSeeText('Eksternal September Di Luar Rentang');
        $response->assertDontSeeText('Eksternal Oktober');
        $response->assertSeeText('1 kegiatan');
    }

    public function test_invalid_activity_filters_are_rejected(): void
    {
        $admin = User::factory()->create();
        $activity = Activity::factory()->create();

        $response = $this->from(route('admin.kegiatan'))
            ->actingAs($admin)
            ->get(route('admin.kegiatan', [
                'month' => 'not-a-month',
                'date_start' => '2026-09-20',
                'date_end' => '2026-09-10',
                'type' => 'external; DROP TABLE activities',
            ]));

        $response->assertRedirect(route('admin.kegiatan'));
        $response->assertSessionHasErrors([
            'month' => 'Format bulan kegiatan tidak valid.',
            'date_end' => 'Tanggal akhir tidak boleh sebelum tanggal mulai.',
            'type' => 'Jenis kegiatan tidak valid.',
        ]);
        $this->assertModelExists($activity);
    }

    public function test_activity_list_escapes_user_content_and_excludes_soft_deleted_records(): void
    {
        $admin = User::factory()->create();
        $unsafeName = '<script>alert("activity")</script>';
        Activity::factory()->create([
            'name' => $unsafeName,
            'location_address' => '<img src=x onerror=alert(1)>',
            'activity_date' => '2026-09-09',
        ]);
        $deletedActivity = Activity::factory()->create(['name' => 'Kegiatan Sudah Dihapus']);
        $deletedActivity->delete();

        $response = $this->actingAs($admin)->get(route('admin.kegiatan'));

        $response->assertOk();
        $response->assertSee($unsafeName);
        $response->assertDontSee($unsafeName, false);
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
        $response->assertDontSeeText('Kegiatan Sudah Dihapus');
    }

    public function test_activity_list_is_paginated_to_fifteen_records(): void
    {
        $admin = User::factory()->create();
        Activity::factory()->count(16)->create(['activity_date' => '2026-09-09']);

        $response = $this->actingAs($admin)->get(route('admin.kegiatan'));

        $response->assertOk();
        $response->assertViewHas('activities', function ($activities): bool {
            return $activities->count() === 15
                && $activities->total() === 16
                && $activities->perPage() === 15;
        });
        $response->assertViewHas('exportActivities', fn ($activities): bool => $activities->count() === 16);
    }

    public function test_admin_activity_page_provides_working_excel_and_pdf_exports_for_filtered_data(): void
    {
        $admin = User::factory()->create();
        Activity::factory()->create(['name' => 'Kegiatan Juni', 'activity_date' => '2026-06-10']);
        Activity::factory()->create(['name' => 'Kegiatan September', 'activity_date' => '2026-09-05']);
        Activity::factory()->create(['name' => 'Kegiatan Oktober', 'activity_date' => '2026-10-01']);

        $response = $this->actingAs($admin)->get(route('admin.kegiatan', [
            'date_start' => '2026-06-10',
            'date_end' => '2026-09-05',
        ]));

        $response->assertOk()
            ->assertSee('Ekspor Excel')
            ->assertSee('Ekspor PDF')
            ->assertSee('Kegiatan Juni')
            ->assertSee('Kegiatan September')
            ->assertDontSee('Kegiatan Oktober')
            ->assertSee('LAPORAN KEGIATAN')
            ->assertSee('BUDI HARTONO, S.Si, M.Si')
            ->assertSee('NIP. 19690318 199603 1 003')
            ->assertSee('XLSX.writeFile', false)
            ->assertSee('documentPdf.save', false);
        $response->assertViewHas('exportActivities', fn ($activities): bool => $activities->count() === 2);
    }

    public function test_admin_can_load_activity_documentation_for_pdf_export(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $activity = Activity::factory()->create();
        Storage::disk('local')->put('activities/documentation/export.png', 'image-bytes');
        $attachment = Attachment::factory()->create([
            'attachable_type' => 'activity',
            'attachable_id' => $activity->id,
            'file_path' => 'activities/documentation/export.png',
            'original_name' => 'export.png',
            'mime_type' => 'image/png',
            'file_type' => 'image',
            'audience' => 'public',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kegiatan.export-attachments.show', [$activity, $attachment]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertStreamedContent('image-bytes');
    }

    public function test_export_includes_activity_evidence_and_original_report_images(): void
    {
        $admin = User::factory()->create();
        $report = Report::factory()->create();
        $activity = Activity::factory()->create([
            'report_id' => $report->id,
            'type' => ActivityType::External,
        ]);
        $activityAttachment = Attachment::factory()->create([
            'attachable_type' => 'activity',
            'attachable_id' => $activity->id,
            'original_name' => 'bukti-penyelesaian.jpg',
            'file_type' => 'image',
            'mime_type' => 'image/jpeg',
            'audience' => 'public',
        ]);
        $reportAttachment = Attachment::factory()->create([
            'attachable_type' => 'report',
            'attachable_id' => $report->id,
            'original_name' => 'foto-laporan.png',
            'file_type' => 'image',
            'mime_type' => 'image/png',
            'audience' => 'public',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kegiatan'));

        $response->assertOk()
            ->assertSee('bukti-penyelesaian.jpg')
            ->assertSee('foto-laporan.png');
        $response->assertViewHas('exportActivities', function ($activities) use ($activityAttachment, $reportAttachment): bool {
            $documentation = collect($activities->first()['dokumentasi']);

            return $documentation->contains(fn (array $item): bool => $item['nama'] === 'bukti-penyelesaian.jpg'
                && str_contains($item['url'], "/export-attachments/{$activityAttachment->id}"))
                && $documentation->contains(fn (array $item): bool => $item['nama'] === 'foto-laporan.png'
                    && str_contains($item['url'], "/attachments/{$reportAttachment->id}"));
        });
    }

    public function test_export_attachment_endpoint_rejects_another_activities_attachment(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $activity = Activity::factory()->create();
        $otherActivity = Activity::factory()->create();
        $attachment = Attachment::factory()->create([
            'attachable_type' => 'activity',
            'attachable_id' => $otherActivity->id,
            'audience' => 'internal',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kegiatan.export-attachments.show', [$activity, $attachment]))
            ->assertNotFound();
    }

    public function test_guest_is_redirected_and_super_admin_is_forbidden_from_admin_activity_list(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->get(route('admin.kegiatan'))->assertRedirectToRoute('login');
        $this->actingAs($superAdmin)->get(route('admin.kegiatan'))->assertForbidden();
    }
}
