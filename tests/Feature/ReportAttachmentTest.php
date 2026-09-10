<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_preview_report_image_attachment(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $report = Report::factory()->create();
        $attachment = Attachment::factory()->create([
            'attachable_type' => 'report',
            'attachable_id' => $report->id,
            'file_path' => 'reports/evidence/bukti.png',
            'original_name' => 'bukti.png',
            'mime_type' => 'image/png',
            'audience' => 'public',
        ]);
        Storage::disk('local')->put($attachment->file_path, 'image-bytes');

        $response = $this->actingAs($admin)->get(
            route('admin.pengaduan.attachments.show', [$report, $attachment]),
        );

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertStreamedContent('image-bytes');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_report_attachment_preview_cannot_cross_report_or_use_private_attachment(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $report = Report::factory()->create();
        $otherReport = Report::factory()->create();
        $attachment = Attachment::factory()->create([
            'attachable_type' => 'report',
            'attachable_id' => $otherReport->id,
            'audience' => 'public',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.pengaduan.attachments.show', [$report, $attachment]))
            ->assertNotFound();

        $attachment->update(['attachable_id' => $report->id, 'audience' => 'internal']);

        $this->actingAs($admin)
            ->get(route('admin.pengaduan.attachments.show', [$report, $attachment]))
            ->assertNotFound();
    }
}
