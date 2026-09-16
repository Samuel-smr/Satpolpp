<?php

namespace Tests\Feature;

use App\AssetCategory;
use App\Models\Asset;
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

    public function test_super_admin_asset_alerts_returns_vehicle_maintenance_warnings(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        Asset::factory()->create([
            'name' => 'Motor Patroli',
            'category' => AssetCategory::Vehicle,
            'tax_due_date' => now()->addDays(2)->toDateString(),
        ]);
        Asset::factory()->create([
            'name' => 'Roda Empat',
            'category' => AssetCategory::Vehicle,
            'last_oil_service_date' => now()->subMonths(4)->toDateString(),
        ]);

        $response = $this->actingAs($superAdmin)->getJson(route('super_admin.asset-alerts'));

        $response->assertOk()
            ->assertJsonPath('unread_count', 2)
            ->assertJsonFragment(['asset' => true])
            ->assertJsonFragment(['title' => 'Pajak kendaraan hampir jatuh tempo'])
            ->assertJsonFragment(['title' => 'Ganti oli kendaraan terlewat']);
    }
}
