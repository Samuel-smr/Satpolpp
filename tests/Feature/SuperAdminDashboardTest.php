<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Report;
use App\Models\User;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_uses_database_statistics(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        Report::factory()->create(['status' => ReportStatus::Submitted]);
        $this->employee([]);
        Activity::factory()->create(['activity_date' => now()->toDateString()]);

        $response = $this->actingAs($superAdmin)->get(route('super_admin.dashboard'));

        $response->assertOk()
            ->assertSee('Database')
            ->assertSee('1');
    }
}
