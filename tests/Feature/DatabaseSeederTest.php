<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_two_connected_development_records_per_application_table(): void
    {
        Storage::fake('local');

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        foreach ($this->seededTables() as $table) {
            $this->assertSame(2, DB::table($table)->count(), "Expected exactly two rows in {$table}.");
        }

        $completedReportId = DB::table('reports')->where('code', 'LAP-2026-0001')->value('id');

        $this->assertDatabaseHas('activities', ['report_id' => $completedReportId, 'type' => 'external']);
        Storage::disk('local')->assertExists('dummy/reports/laporan-pkl.txt');
        Storage::disk('local')->assertExists('dummy/archives/sop-penertiban.txt');
    }

    /**
     * @return list<string>
     */
    private function seededTables(): array
    {
        return [
            'users',
            'guest_accesses',
            'reports',
            'report_status_histories',
            'attachments',
            'activity_templates',
            'activities',
            'activity_personnel',
            'archives',
            'notifications',
            'audit_logs',
        ];
    }
}
