<?php

namespace Tests\Feature\Admin;

use App\Models\Report;
use App\Models\User;
use App\ReportStatus;
use App\ReportType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_reports_and_database_statistics(): void
    {
        $admin = User::factory()->create();
        Report::factory()->emergency()->create([
            'code' => 'SPJ-100001',
            'status' => ReportStatus::Completed,
        ]);
        Report::factory()->create([
            'code' => 'SPJ-100002',
            'status' => ReportStatus::Verifying,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pengaduan'));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Total Masuk', '2', 'Laporan Darurat', '1']);
        $response->assertSeeText('SPJ-100001');
        $response->assertSeeText('SPJ-100002');
        $response->assertViewHas('statistics', [
            'total' => 2,
            'emergency' => 1,
            'information' => 1,
            'completed' => 1,
        ]);
    }

    public function test_admin_can_filter_reports_by_an_allowed_type(): void
    {
        $admin = User::factory()->create();
        Report::factory()->emergency()->create(['code' => 'SPJ-EMERGENCY']);
        Report::factory()->create(['code' => 'SPJ-INFORMATION']);

        $response = $this->actingAs($admin)->get(route('admin.pengaduan', [
            'type' => ReportType::Emergency->value,
        ]));

        $response->assertOk();
        $response->assertSeeText('SPJ-EMERGENCY');
        $response->assertDontSeeText('SPJ-INFORMATION');
    }

    public function test_invalid_type_filter_is_not_used_as_a_dynamic_query_value(): void
    {
        $admin = User::factory()->create();
        Report::factory()->emergency()->create(['code' => 'SPJ-100003']);
        Report::factory()->create(['code' => 'SPJ-100004']);

        $response = $this->actingAs($admin)->get(route('admin.pengaduan', [
            'type' => "emergency' OR 1=1 --",
        ]));

        $response->assertOk();
        $response->assertSeeText('SPJ-100003');
        $response->assertSeeText('SPJ-100004');
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
