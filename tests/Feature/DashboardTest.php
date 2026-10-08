<?php

namespace Tests\Feature;

use App\ActivityType;
use App\Models\Activity;
use App\Models\Report;
use App\Models\User;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_uses_database_statistics_and_weekly_report_data(): void
    {
        $this->travelTo('2026-09-09 10:00:00');
        $admin = User::factory()->create();
        Report::factory()->create([
            'created_at' => now()->setTime(8, 0),
            'status' => ReportStatus::Submitted,
        ]);
        Report::factory()->create([
            'created_at' => now()->subDay(),
            'status' => ReportStatus::Completed,
            'completed_at' => now()->subDay(),
        ]);
        Report::factory()->create(['status' => ReportStatus::InProgress]);
        $this->employees(2, []);
        $this->employee([], available: false);
        Activity::factory()->create([
            'type' => ActivityType::Internal,
            'activity_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['period' => '3']));

        $response->assertOk();
        $response->assertSeeText('1', false);
        $response->assertSee('const weeklyChart =', false);
        $response->assertSee('"incoming":1', false);
        $response->assertSee('"completed":1', false);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_guest_and_super_admin_cannot_access_admin_dashboard(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->get(route('admin.dashboard'))->assertRedirectToRoute('login');
        $this->actingAs($superAdmin)->get(route('admin.dashboard'))->assertForbidden();
    }
}
