<?php

namespace Tests\Feature;

use App\ActivityType;
use App\Models\Activity;
use App\Models\Officer;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_activity_page_uses_database_activities_and_officers(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $officer = Officer::factory()->create(['name' => 'Petugas Kegiatan Database']);
        $report = Report::factory()->create();
        $activity = Activity::factory()->create([
            'report_id' => $report->id,
            'type' => ActivityType::External,
            'name' => 'Kegiatan Eksternal Database',
        ]);
        $activity->officers()->attach($officer, ['assignment_role' => 'Petugas Kegiatan']);

        $response = $this->actingAs($superAdmin)->get(route('super_admin.kegiatan'));

        $response->assertOk()
            ->assertSee('Kegiatan Eksternal Database')
            ->assertSee('Petugas Kegiatan Database')
            ->assertSee($report->code)
            ->assertDontSee('const newId = Date.now()', false)
            ->assertDontSee('Operasi Yustisi KTP');
    }

    public function test_admin_cannot_access_super_admin_activity_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('super_admin.kegiatan'))
            ->assertForbidden();
    }
}
