<?php

namespace Tests\Feature;

use App\ActivityType;
use App\Models\Activity;
use App\Models\Report;
use App\Models\User;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributionMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_map_uses_report_and_activity_coordinates_from_database(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        Report::factory()->create([
            'description' => 'Laporan pada peta database',
            'latitude' => -7.85,
            'longitude' => 110.16,
            'status' => ReportStatus::InProgress,
        ]);
        Activity::factory()->create([
            'name' => 'Kegiatan pada peta database',
            'type' => ActivityType::Internal,
            'latitude' => -7.84,
            'longitude' => 110.17,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('super_admin.peta'));

        $response->assertOk()
            ->assertSee('Laporan pada peta database')
            ->assertSee('Kegiatan pada peta database')
            ->assertSee('-7.85')
            ->assertDontSee('Operasi Yustisi KTP');
    }

    public function test_admin_cannot_access_distribution_map(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('super_admin.peta'))
            ->assertForbidden();
    }
}
