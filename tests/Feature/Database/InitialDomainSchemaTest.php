<?php

namespace Tests\Feature\Database;

use App\Models\GuestAccess;
use App\Models\Report;
use App\Models\User;
use App\ReportSource;
use App\ReportSourcePlatform;
use App\ReportStatus;
use App\ReportType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InitialDomainSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_report_is_owned_by_the_browser_identity_and_encrypts_the_nik(): void
    {
        $guestAccess = GuestAccess::factory()->create();
        $plainNik = '3401010101010001';

        $report = Report::factory()
            ->for($guestAccess)
            ->emergency()
            ->create(['reporter_nik' => $plainNik]);

        $storedNik = DB::table('reports')->where('id', $report->id)->value('reporter_nik');

        $this->assertTrue($report->guestAccess->is($guestAccess));
        $this->assertNull($report->createdByUser);
        $this->assertSame(ReportType::Emergency, $report->type);
        $this->assertSame(ReportSource::Online, $report->source);
        $this->assertSame(ReportSourcePlatform::Website, $report->source_platform);
        $this->assertSame(ReportStatus::Submitted, $report->status);
        $this->assertSame($plainNik, $report->reporter_nik);
        $this->assertNotSame($plainNik, $storedNik);
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
}
