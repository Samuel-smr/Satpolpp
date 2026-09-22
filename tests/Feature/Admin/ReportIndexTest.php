<?php

namespace Tests\Feature\Admin;

use App\Models\Report;
use App\Models\User;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_reports_and_database_statistics(): void
    {
        $admin = User::factory()->create();
        Report::factory()->create([
            'code' => 'SPJ-100001',
            'status' => ReportStatus::Completed,
        ]);
        Report::factory()->create([
            'code' => 'SPJ-100002',
            'status' => ReportStatus::Verifying,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pengaduan'));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Total Masuk', '2', 'Selesai', '1']);
        $response->assertSeeText('SPJ-100001');
        $response->assertSeeText('SPJ-100002');
        $response->assertViewHas('statistics', [
            'total' => 2,
            'completed' => 1,
        ]);
    }

    public function test_admin_report_list_has_no_type_filter_or_badge(): void
    {
        $admin = User::factory()->create();
        Report::factory()->create(['code' => 'SPJ-LAPOR']);

        $response = $this->actingAs($admin)->get(route('admin.pengaduan'));

        $response->assertSeeText('SPJ-LAPOR');
        $response->assertDontSeeText('Laporan Darurat');
        $response->assertDontSeeText('Informasi & Pengaduan');
        $response->assertDontSeeText('Jenis');
    }

    public function test_legacy_type_query_does_not_filter_reports(): void
    {
        $admin = User::factory()->create();
        Report::factory()->create(['code' => 'SPJ-100003']);
        Report::factory()->create(['code' => 'SPJ-100004']);

        $response = $this->actingAs($admin)->get(route('admin.pengaduan', [
            'type' => "emergency' OR 1=1 --",
        ]));

        $response->assertOk();
        $response->assertSeeText('SPJ-100003');
        $response->assertSeeText('SPJ-100004');
    }

    public function test_admin_can_filter_reports_by_month(): void
    {
        $admin = User::factory()->create();
        Report::factory()->create(['code' => 'SPJ-SEPTEMBER', 'created_at' => '2026-09-15 10:00:00']);
        Report::factory()->create(['code' => 'SPJ-OCTOBER', 'created_at' => '2026-10-01 10:00:00']);

        $response = $this->actingAs($admin)->get(route('admin.pengaduan', ['month' => '2026-09']));

        $response->assertOk();
        $response->assertSee('type="month"', false);
        $response->assertSeeText('SPJ-SEPTEMBER');
        $response->assertDontSeeText('SPJ-OCTOBER');
    }

    public function test_report_location_is_escaped_in_the_list(): void
    {
        $admin = User::factory()->create();
        $unsafeLocation = '<script>alert("location")</script>';
        Report::factory()->create(['location_address' => $unsafeLocation]);

        $response = $this->actingAs($admin)->get(route('admin.pengaduan'));

        $response->assertOk();
        $response->assertSee($unsafeLocation);
        $response->assertDontSee($unsafeLocation, false);
    }
}
