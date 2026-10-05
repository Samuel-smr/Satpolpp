<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_reports_activities_and_officers(): void
    {
        $admin = User::factory()->create();
        $report = Report::factory()->create(['code' => 'SPJ-CARI-001', 'description' => 'Penertiban khusus pencarian']);
        Activity::factory()->create(['name' => 'Patroli khusus pencarian']);
        $this->employee(['name' => 'Petugas Khusus Pencarian']);

        $this->actingAs($admin)
            ->getJson(route('global-search', ['q' => 'khusus pencarian']))
            ->assertOk()
            ->assertJsonFragment(['type' => 'Laporan', 'title' => $report->code])
            ->assertJsonFragment(['type' => 'Kegiatan', 'title' => 'Patroli khusus pencarian'])
            ->assertJsonFragment(['type' => 'Petugas', 'title' => 'Petugas Khusus Pencarian']);
    }

    public function test_global_search_requires_authentication_and_a_meaningful_query(): void
    {
        $this->getJson(route('global-search', ['q' => 'laporan']))->assertUnauthorized();

        $this->actingAs(User::factory()->create())
            ->getJson(route('global-search', ['q' => 'a']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }
}
