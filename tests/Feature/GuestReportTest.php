<?php

namespace Tests\Feature;

use App\Models\GuestAccess;
use App\Models\Report;
use App\Models\User;
use App\Notifications\NewReportNotification;
use App\ReportCategory;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuestReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_report_form_guides_numeric_nik_and_phone_input(): void
    {
        $this->get(route('pelapor.lapor'))
            ->assertOk()
            ->assertSee('name="nik"', false)
            ->assertSee('inputmode="numeric"', false)
            ->assertSee('pattern="[0-9]{16}"', false)
            ->assertSee('name="telepon"', false)
            ->assertSee('inputmode="tel"', false);
    }

    public function test_guest_can_submit_report_and_receive_browser_session_cookie(): void
    {
        Storage::fake('local');
        $activeAdmin = User::factory()->create();
        $blockedAdmin = User::factory()->blocked()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->post(route('pelapor.store'), [
            'jenis' => 'information',
            'kategori' => ReportCategory::PublicOrder->value,
            'telepon' => '081234567890',
            'deskripsi' => 'Ada aktivitas yang mengganggu ketertiban umum.',
            'lokasi' => 'Alun-alun Wates',
            'bukti' => [UploadedFile::fake()->image('bukti.jpg')],
            'setuju' => '1',
        ]);

        $report = Report::query()->sole();
        $response->assertRedirectToRoute('pelapor.lapor');
        $response->assertSessionHas('report_code', $report->code);
        $response->assertCookie('sipraja_guest_token');
        $this->assertSame(ReportStatus::Submitted, $report->status);
        $this->assertDatabaseCount('guest_accesses', 1);
        $this->assertDatabaseCount('report_status_histories', 1);
        $this->assertDatabaseCount('attachments', 1);
        Storage::disk('local')->assertExists($report->attachments()->sole()->file_path);
        $this->assertDatabaseHas('notifications', [
            'type' => NewReportNotification::class,
            'notifiable_id' => $activeAdmin->id,
            'read_at' => null,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'type' => NewReportNotification::class,
            'notifiable_id' => $blockedAdmin->id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'type' => NewReportNotification::class,
            'notifiable_id' => $superAdmin->id,
        ]);
    }

    public function test_guest_status_page_only_shows_reports_from_browser_session(): void
    {
        $guestAccess = GuestAccess::factory()->create();
        $report = Report::factory()->create(['guest_access_id' => $guestAccess->id, 'description' => 'Laporan dari browser ini.']);
        Report::factory()->create(['description' => 'Laporan browser lain.']);

        $response = $this->withCookie('sipraja_guest_token', 'token-yang-tidak-sama')
            ->get(route('pelapor.status'));

        $response->assertOk();
        $response->assertSee('const reportsFromDatabase = [];', false);
        $response->assertDontSeeText($report->description);

        $rawToken = 'token-rahasia-browser';
        $guestAccess->update(['token_hash' => hash('sha256', $rawToken)]);
        $response = $this->withCookie('sipraja_guest_token', $rawToken)->get(route('pelapor.status'));

        $response->assertSee($report->code, false);
        $response->assertSee($report->description, false);
    }

    public function test_guest_report_validation_rejects_invalid_emergency_nik_and_too_many_files(): void
    {
        Storage::fake('local');

        $response = $this->from(route('pelapor.lapor'))->post(route('pelapor.store'), [
            'jenis' => 'emergency',
            'kategori' => ReportCategory::Security->value,
            'telepon' => '081234567890',
            'nik' => '123',
            'deskripsi' => 'Laporan darurat.',
            'lokasi' => 'Wates',
            'bukti' => [
                UploadedFile::fake()->image('satu.jpg'),
                UploadedFile::fake()->image('dua.jpg'),
                UploadedFile::fake()->image('tiga.jpg'),
                UploadedFile::fake()->image('empat.jpg'),
            ],
            'setuju' => '1',
        ]);

        $response->assertRedirect(route('pelapor.lapor'));
        $response->assertSessionHasErrors(['nik', 'bukti']);
        $this->assertDatabaseCount('reports', 0);
    }
}
