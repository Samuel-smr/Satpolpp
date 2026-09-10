<?php

namespace Tests\Feature;

use App\ActivityType;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuperAdminActivityExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_source_contains_complete_activity_data_and_documentation_url(): void
    {
        Storage::fake('local');
        $superAdmin = User::factory()->superAdmin()->create();
        $officer = Officer::factory()->create(['name' => 'Personel Ekspor']);
        $activity = Activity::factory()->create([
            'name' => 'Patroli September',
            'activity_date' => '2026-09-05',
            'description' => 'Melaksanakan patroli wilayah.',
            'handling_result' => 'Situasi aman dan tertib.',
            'evaluation_notes' => 'Kegiatan berjalan baik.',
        ]);
        $activity->officers()->attach($officer);
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
            ->assertSee("kegiatan\\/{$activity->id}\\/attachments\\/{$attachment->id}", false)
            ->assertSee('LAPORAN KEGIATAN')
            ->assertSee("'Tindakan dan Hasil'", false)
            ->assertSee('getExportPeriod(list)');
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
