<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_monitoring_shows_system_stats_without_log_list(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $actor = User::factory()->create(['name' => 'Admin Audit Database']);
        AuditLog::factory()->create([
            'actor_user_id' => $actor->id,
            'module' => 'reports',
            'description' => 'Status laporan database diperbarui.',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('super_admin.monitoring'));

        $response->assertOk()
            ->assertSee('Status Layanan')
            ->assertSee('Ringkasan Audit Hari Ini')
            ->assertDontSee('Log Aktivitas Sistem')
            ->assertDontSee('Status laporan database diperbarui.')
            ->assertDontSee('Super Admin login ke sistem');
    }

    public function test_admin_cannot_access_system_monitoring(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('super_admin.monitoring'))
            ->assertForbidden();
    }
}
