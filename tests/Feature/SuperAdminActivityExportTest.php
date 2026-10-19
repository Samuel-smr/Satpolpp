<?php

namespace Tests\Feature;

use App\ActivityType;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SuperAdminActivityExportTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith(['admin'])]
    #[TestWith(['super_admin'])]
    public function test_document_exports_use_officer_names_without_positions_for_each_role(string $role): void
    {
        $user = $role === 'admin' ? User::factory()->create() : User::factory()->superAdmin()->create();
        $officer = $this->employee(['name' => 'AGUNG SUKMAWANTORO', 'position' => 'Polisi Pamong Praja Mahir pada Seksi Perlindungan Masyarakat']);
        $activity = Activity::factory()->create();
        $activity->personnel()->create(['employee_nip' => $officer->employee_number]);

        $response = $this->actingAs($user)->get(route($role.'.kegiatan'));

        $response->assertOk()->assertSee('Ekspor PDF')->assertSee('Ekspor Word')->assertSee('js/activity-word-export.js', false);
        $response->assertViewHas('exportActivities', fn ($activities): bool => $activities->sole()['personel'] === ['AGUNG SUKMAWANTORO']);
    }

    public function test_export_source_contains_complete_activity_data_and_documentation_url(): void
    {
        Storage::fake('local');
        $superAdmin = User::factory()->superAdmin()->create();
        $officer = $this->employee(['name' => 'Personel Ekspor']);
        $activity = Activity::factory()->create([
            'name' => 'Patroli September',
            'activity_date' => '2026-09-05',
            'description' => 'Melaksanakan patroli wilayah.',
            'handling_result' => 'Situasi aman dan tertib.',
            'evaluation_notes' => 'Kegiatan berjalan baik.',
        ]);
        $activity->personnel()->create(['employee_nip' => $officer->employee_number, 'assignment_role' => 'Petugas Kegiatan']);
        Storage::disk('local')->put('activities/documentation/patroli.jpg', 'image-content');
        $attachment = $activity->attachments()->create([
            'file_path' => 'activities/documentation/patroli.jpg',
            'original_name' => 'patroli.jpg',
            'file_type' => 'image',
            'mime_type' => 'image/jpeg',
            'file_size' => 13,
            'audience' => 'internal',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('super_admin.kegiatan'));

        $response->assertOk()
            ->assertSee('Patroli September')
            ->assertSee('Melaksanakan patroli wilayah.')
            ->assertSee('Situasi aman dan tertib.')
            ->assertSee('Kegiatan berjalan baik.')
            ->assertSee('Personel Ekspor')
            ->assertSee('patroli.jpg')
            ->assertSee('export-attachments', false)
            ->assertSee('LAPORAN KEGIATAN')
            ->assertSee("'Tindakan dan Hasil'", false)
            ->assertSee('const photoCellHeight', false)
            ->assertSee('const photoPerRow', false)
            ->assertSee('documentPdf.addImage(photo.data', false)
            ->assertSee('const period =', false);
    }

    public function test_super_admin_can_load_an_activity_image_for_pdf_export(): void
    {
        Storage::fake('local');
        $superAdmin = User::factory()->superAdmin()->create();
        $activity = Activity::factory()->create();
        Storage::disk('local')->put('activities/documentation/export.png', 'image-bytes');
        $attachment = $activity->attachments()->create([
            'file_path' => 'activities/documentation/export.png',
            'original_name' => 'export.png',
            'file_type' => 'image',
            'mime_type' => 'image/png',
            'file_size' => 11,
            'audience' => 'internal',
        ]);

        $response = $this->actingAs($superAdmin)->get(
            route('super_admin.kegiatan.attachments.show', [$activity, $attachment]),
        );

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertStreamedContent('image-bytes');
        $this->assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_export_image_endpoint_rejects_an_attachment_owned_by_another_activity(): void
    {
        Storage::fake('local');
        $superAdmin = User::factory()->superAdmin()->create();
        $activity = Activity::factory()->create(['type' => ActivityType::External]);
        $otherActivity = Activity::factory()->create();
        $attachment = Attachment::factory()->create([
            'attachable_type' => 'activity',
            'attachable_id' => $otherActivity->id,
            'audience' => 'internal',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super_admin.kegiatan.attachments.show', [$activity, $attachment]))
            ->assertNotFound();
    }

    public function test_admin_cannot_access_super_admin_export_images(): void
    {
        $admin = User::factory()->create();
        $activity = Activity::factory()->create();
        $attachment = Attachment::factory()->create([
            'attachable_type' => 'activity',
            'attachable_id' => $activity->id,
            'audience' => 'internal',
        ]);

        $this->actingAs($admin)
            ->get(route('super_admin.kegiatan.attachments.show', [$activity, $attachment]))
            ->assertForbidden();
    }
}
