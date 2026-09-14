<?php

namespace Tests\Feature\Admin;

use App\ActivityType;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\AuditLog;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InternalActivityAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_preview_internal_activity_image_with_private_security_headers(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        [$activity, $attachment] = $this->createInternalAttachment('image-bytes');

        $response = $this->actingAs($admin)->get(
            route('admin.kegiatan.attachments.show', [$activity, $attachment]),
        );

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertStreamedContent('image-bytes');
        $this->assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('dokumentasi.png', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_missing_physical_file_returns_not_found_without_exposing_metadata(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        [$activity, $attachment] = $this->createInternalAttachment(null);

        $this->actingAs($admin)
            ->get(route('admin.kegiatan.attachments.show', [$activity, $attachment]))
            ->assertNotFound();
    }

    public function test_attachment_from_report_external_activity_or_another_activity_is_not_accessible(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        [$internalActivity, $internalAttachment] = $this->createInternalAttachment('internal');
        $otherInternalActivity = Activity::factory()->create(['type' => ActivityType::Internal]);
        $externalActivity = Activity::factory()->create(['type' => ActivityType::External]);
        $externalAttachment = $this->attachImage($externalActivity, 'external.png');
        $report = Report::factory()->create();
        $reportAttachment = Attachment::factory()->create([
            'attachable_type' => 'report',
            'attachable_id' => $report->id,
            'file_path' => 'reports/report.png',
            'original_name' => 'report.png',
            'mime_type' => 'image/png',
            'audience' => 'internal',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kegiatan.attachments.show', [$otherInternalActivity, $internalAttachment]))
            ->assertNotFound();
        $this->actingAs($admin)
            ->get(route('admin.kegiatan.attachments.show', [$externalActivity, $externalAttachment]))
            ->assertNotFound();
        $this->actingAs($admin)
            ->get(route('admin.kegiatan.attachments.show', [$internalActivity, $reportAttachment]))
            ->assertNotFound();
    }

    public function test_admin_can_permanently_delete_one_internal_documentation_without_deleting_activity(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        [$activity, $attachment] = $this->createInternalAttachment('delete-me');
        $otherAttachment = $this->attachImage($activity, 'keep.png', 'keep-me');

        $response = $this->actingAs($admin)->delete(
            route('admin.kegiatan.attachments.destroy', [$activity, $attachment]),
            ['return_to' => 'edit'],
        );

        $response->assertRedirectToRoute('admin.kegiatan.edit', $activity);
        $response->assertSessionHas('status', 'Dokumentasi kegiatan berhasil dihapus permanen.');
        $this->assertModelMissing($attachment);
        $this->assertModelExists($activity);
        $this->assertModelExists($otherAttachment);
        Storage::disk('local')->assertMissing('activities/documentation/dokumentasi.png');
        Storage::disk('local')->assertExists('activities/documentation/keep.png');
        $this->assertSame([], Storage::disk('local')->allFiles('activities/quarantine'));

        $auditLog = AuditLog::query()->sole();
        $this->assertSame($admin->id, $auditLog->actor_user_id);
        $this->assertSame('attachment_deleted', $auditLog->action);
        $this->assertSame($attachment->id, $auditLog->old_values['attachment_id']);
        $this->assertSame('dokumentasi.png', $auditLog->old_values['original_name']);
    }

    public function test_deleting_missing_physical_file_still_cleans_stale_attachment_metadata(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        [$activity, $attachment] = $this->createInternalAttachment(null);

        $response = $this->actingAs($admin)->delete(
            route('admin.kegiatan.attachments.destroy', [$activity, $attachment]),
        );

        $response->assertRedirectToRoute('admin.kegiatan.show', $activity);
        $this->assertModelMissing($attachment);
        $this->assertModelExists($activity);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_guest_and_super_admin_cannot_preview_or_delete_internal_documentation(): void
    {
        Storage::fake('local');
        [$activity, $attachment] = $this->createInternalAttachment('private-image');
        $superAdmin = User::factory()->superAdmin()->create();
        $showRoute = route('admin.kegiatan.attachments.show', [$activity, $attachment]);
        $destroyRoute = route('admin.kegiatan.attachments.destroy', [$activity, $attachment]);

        $this->get($showRoute)->assertRedirectToRoute('login');
        $this->delete($destroyRoute)->assertRedirectToRoute('login');
        $this->actingAs($superAdmin)->get($showRoute)->assertForbidden();
        $this->actingAs($superAdmin)->delete($destroyRoute)->assertForbidden();
        $this->assertModelExists($attachment);
        Storage::disk('local')->assertExists($attachment->file_path);
    }

    /** @return array{Activity, Attachment} */
    private function createInternalAttachment(?string $content): array
    {
        $activity = Activity::factory()->create(['type' => ActivityType::Internal]);
        $attachment = $this->attachImage($activity, 'dokumentasi.png', $content);

        return [$activity, $attachment];
    }

    private function attachImage(Activity $activity, string $name, ?string $content = 'image'): Attachment
    {
        $path = "activities/documentation/{$name}";

        if ($content !== null) {
            Storage::disk('local')->put($path, $content);
        }

        return $activity->attachments()->create([
            'uploaded_by_user_id' => null,
            'file_path' => $path,
            'original_name' => $name,
            'file_type' => 'image',
            'mime_type' => 'image/png',
            'file_size' => strlen($content ?? ''),
            'audience' => 'internal',
        ]);
    }
}
