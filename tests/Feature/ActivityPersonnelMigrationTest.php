<?php

namespace Tests\Feature;

use App\Models\Activity;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ActivityPersonnelMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_preserves_assignments_as_nip_references_and_removes_employee_master_data(): void
    {
        $activity = Activity::factory()->create();
        Schema::drop('activity_personnel');
        Schema::create('officers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('employee_number', 50)->unique();
            $table->softDeletes();
        });
        Schema::create('activity_officer', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities');
            $table->foreignId('officer_id')->constrained('officers');
            $table->string('assignment_role')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        $officerId = DB::table('officers')->insertGetId(['name' => 'Data Lama', 'employee_number' => '0197202121991011001', 'deleted_at' => now()]);
        DB::table('activity_officer')->insert([
            'activity_id' => $activity->id,
            'officer_id' => $officerId,
            'assignment_role' => 'Koordinator',
            'notes' => 'Penugasan lama.',
            'created_at' => '2026-09-01 08:00:00',
            'updated_at' => '2026-09-02 08:00:00',
        ]);
        $migration = require database_path('migrations/2026_10_05_034445_replace_officers_with_activity_personnel_references.php');

        $migration->up();

        $this->assertFalse(Schema::hasTable('officers'));
        $this->assertFalse(Schema::hasTable('activity_officer'));
        $this->assertDatabaseHas('activity_personnel', [
            'activity_id' => $activity->id,
            'employee_nip' => '0197202121991011001',
            'assignment_role' => 'Koordinator',
            'notes' => 'Penugasan lama.',
            'created_at' => '2026-09-01 08:00:00',
            'updated_at' => '2026-09-02 08:00:00',
        ]);
        $this->assertSame('0197202121991011001', $activity->personnel()->sole()->employee_nip);
    }

    public function test_rollback_refuses_to_invent_deleted_employee_master_data(): void
    {
        $migration = require database_path('migrations/2026_10_05_034445_replace_officers_with_activity_personnel_references.php');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data induk pegawai telah dihapus.');

        $migration->down();
    }
}
