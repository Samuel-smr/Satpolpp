<?php

namespace Tests\Feature\Database;

use App\Models\GuestAccess;
use App\Models\Report;
use App\Models\User;
use App\ReportSource;
use App\ReportSourcePlatform;
use App\ReportStatus;
use App\ReportType;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InitialDomainSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_report_is_owned_by_the_browser_identity(): void
    {
        $guestAccess = GuestAccess::factory()->create();

        $report = Report::factory()
            ->for($guestAccess)
            ->create();

        $this->assertTrue($report->guestAccess->is($guestAccess));
        $this->assertNull($report->createdByUser);
        $this->assertSame(ReportType::Report, $report->type);
        $this->assertSame(ReportSource::Online, $report->source);
        $this->assertSame(ReportSourcePlatform::Website, $report->source_platform);
        $this->assertSame(ReportStatus::Submitted, $report->status);
    }

    public function test_obsolete_domain_tables_and_officer_foreign_keys_are_removed(): void
    {
        $this->assertFalse(Schema::hasTable('organizational_units'));
        $this->assertFalse(Schema::hasTable('operational_zones'));
        $this->assertFalse(Schema::hasTable('assets'));
        $this->assertFalse(Schema::hasTable('officers'));
        $this->assertFalse(Schema::hasTable('activity_officer'));
    }

    public function test_manual_report_is_attributed_to_the_admin_without_a_guest_identity(): void
    {
        $admin = User::factory()->create();

        $report = Report::factory()->recordedBy($admin)->create();

        $this->assertTrue($report->createdByUser->is($admin));
        $this->assertNull($report->guestAccess);
        $this->assertSame(ReportSource::Offline, $report->source);
        $this->assertNull($report->source_platform);
    }

    public function test_legacy_encrypted_nik_does_not_block_assignment_and_can_be_removed_without_losing_the_report(): void
    {
        $migration = require database_path('migrations/2026_10_19_125442_remove_reporter_nik_from_reports_table.php');
        $migration->down();
        $report = Report::factory()->create(['description' => 'Laporan lama tetap dipertahankan.']);
        $legacyEncrypter = new Encrypter(str_repeat('x', 32), 'AES-256-CBC');
        DB::table('reports')->where('id', $report->id)->update(['reporter_nik' => $legacyEncrypter->encryptString('3401010101010001')]);
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->create();

        $this->actingAs($superAdmin)->get(route('super_admin.pengaduan.detail', $report))
            ->assertOk()->assertSeeText($report->description)->assertDontSeeText('NIK');
        $this->patch(route('super_admin.pengaduan.assignment.update', $report), ['assigned_admin_id' => $admin->id])
            ->assertRedirectToRoute('super_admin.pengaduan.detail', $report);

        $migration->up();

        $this->get(route('super_admin.pengaduan.detail', $report))->assertOk()->assertSeeText($admin->name);
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'code' => $report->code, 'description' => $report->description, 'assigned_admin_id' => $admin->id]);
        $this->assertArrayNotHasKey('reporter_nik', $report->fresh()->getAttributes());
    }
}
