<?php

namespace Tests\Feature;

use App\ActivityType;
use App\Models\Activity;
use App\Models\ActivityTemplate;
use App\Models\Attachment;
use App\Models\Officer;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_activity_page_uses_database_activities_and_officers(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $officer = Officer::factory()->create(['name' => 'Petugas Kegiatan Database']);
        $report = Report::factory()->create();
        $activity = Activity::factory()->create([
            'report_id' => $report->id,
            'type' => ActivityType::External,
            'name' => 'Kegiatan Eksternal Database',
        ]);
        $activity->officers()->attach($officer, ['assignment_role' => 'Petugas Kegiatan']);
        $reportAttachment = Attachment::factory()->create([
            'attachable_type' => 'report',
            'attachable_id' => $report->id,
            'original_name' => 'foto-laporan-super-admin.jpg',
            'file_type' => 'image',
            'mime_type' => 'image/jpeg',
            'audience' => 'public',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('super_admin.kegiatan'));

        $response->assertOk()
            ->assertSee('Kegiatan Eksternal Database')
            ->assertSee('Petugas Kegiatan Database')
            ->assertSee($report->code)
            ->assertSee(route('super_admin.pengaduan.detail', $report), false)
            ->assertViewHas('exportActivities', function ($activities) use ($reportAttachment): bool {
                return collect($activities->first()['dokumentasi'])->contains(
                    fn (array $item): bool => $item['nama'] === 'foto-laporan-super-admin.jpg'
                        && str_contains($item['url'], "/attachments/{$reportAttachment->id}"),
                );
            })
            ->assertDontSee('const newId = Date.now()', false)
            ->assertDontSee('Operasi Yustisi KTP');

        $this->get(route('super_admin.pengaduan.detail', $report))
            ->assertOk()
            ->assertSee($report->code)
            ->assertSee('Kegiatan Eksternal Database')
            ->assertSee('Petugas Kegiatan Database')
            ->assertSee('mode baca-saja')
            ->assertDontSee('Hapus Laporan');
    }

    public function test_admin_cannot_access_super_admin_activity_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('super_admin.kegiatan'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_update_and_delete_an_internal_activity(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $template = ActivityTemplate::factory()->create(['name' => 'Patroli Super Admin']);
        $officer = Officer::factory()->create();

        $createPage = $this->actingAs($superAdmin)->get(route('super_admin.kegiatan.create'));

        $createPage->assertOk()
            ->assertSee('Tambah Kegiatan Internal')
            ->assertSee(route('super_admin.kegiatan.store'), false);

        $response = $this->post(route('super_admin.kegiatan.store'), [
            'activity_template_id' => $template->id,
            'name' => 'Kegiatan Baru Super Admin',
            'category' => $template->name,
            'activity_date' => now()->toDateString(),
            'start_time' => '08:00',
            'description' => 'Kegiatan dari Super Admin.',
            'location_address' => 'Wates, Kulon Progo',
            'latitude' => -7.8578,
            'longitude' => 110.1589,
            'officer_ids' => [$officer->id],
        ]);

        $activity = Activity::query()->sole();
        $response->assertRedirectToRoute('super_admin.kegiatan.show', $activity);

        $updatedAt = $activity->updated_at->format('Y-m-d H:i:s');
        $this->put(route('super_admin.kegiatan.update', $activity), [
            'current_updated_at' => $updatedAt,
            'activity_template_id' => $template->id,
            'name' => 'Kegiatan Super Admin Diperbarui',
            'category' => $template->name,
            'activity_date' => now()->toDateString(),
            'start_time' => '09:00',
            'description' => 'Diperbarui.',
            'location_address' => 'Pengasih, Kulon Progo',
            'latitude' => -7.8462,
            'longitude' => 110.173,
            'officer_ids' => [$officer->id],
        ])->assertRedirectToRoute('super_admin.kegiatan.show', $activity);

        $this->assertSame('Kegiatan Super Admin Diperbarui', $activity->fresh()->name);

        $this->delete(route('super_admin.kegiatan.destroy', $activity))
            ->assertRedirectToRoute('super_admin.kegiatan');
        $this->assertSoftDeleted($activity);
    }
}
