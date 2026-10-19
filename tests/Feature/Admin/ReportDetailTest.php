<?php

namespace Tests\Feature\Admin;

use App\Models\Attachment;
use App\Models\Report;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_report_detail_with_status_history_and_attachment_metadata(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Pemeriksa']);
        $report = Report::factory()->create([
            'code' => 'SPJ-DETAIL-001',
            'description' => 'Deskripsi laporan dari database.',
        ]);
        ReportStatusHistory::factory()->for($report)->for($admin, 'changedByUser')->create([
            'status' => ReportStatus::Verifying,
            'note' => 'Lokasi sedang diverifikasi.',
        ]);
        Attachment::factory()->for($report, 'attachable')->create([
            'original_name' => 'bukti-laporan.jpg',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pengaduan.detail', $report));

        $response->assertOk();
        $response->assertSeeText('SPJ-DETAIL-001');
        $response->assertSeeText('Deskripsi laporan dari database.');
        $response->assertSeeText('Lokasi sedang diverifikasi.');
        $response->assertSeeText('Admin Pemeriksa');
        $response->assertSeeText('bukti-laporan.jpg');
    }

    public function test_report_detail_uses_the_public_code_and_returns_not_found_for_an_unknown_code(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get('/admin/pengaduan/SPJ-TIDAK-ADA');

        $response->assertNotFound();
    }

    public function test_soft_deleted_report_is_not_available_from_the_detail_route(): void
    {
        $admin = User::factory()->create();
        $report = Report::factory()->create(['code' => 'SPJ-DELETED']);
        $report->delete();

        $response = $this->actingAs($admin)->get(route('admin.pengaduan.detail', ['report' => 'SPJ-DELETED']));

        $response->assertNotFound();
    }

    public function test_super_admin_cannot_open_admin_report_detail(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $report = Report::factory()->create();

        $response = $this->actingAs($superAdmin)->get(route('admin.pengaduan.detail', $report));

        $response->assertForbidden();
    }

    public function test_free_text_fields_are_escaped_on_the_detail_page(): void
    {
        $admin = User::factory()->create();
        $unsafeText = '<script>alert("detail")</script>';
        $report = Report::factory()->create(['description' => $unsafeText]);
        ReportStatusHistory::factory()->for($report)->create(['note' => $unsafeText]);

        $response = $this->actingAs($admin)->get(route('admin.pengaduan.detail', $report));

        $response->assertOk();
        $response->assertSee($unsafeText);
        $response->assertDontSee($unsafeText, false);
    }
}
