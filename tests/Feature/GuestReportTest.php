<?php

namespace Tests\Feature;

use App\Models\GuestAccess;
use App\Models\Report;
use App\Models\User;
use App\Notifications\NewReportNotification;
use App\ReportStatus;
use App\ReportType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuestReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_report_form_does_not_request_nik_or_report_type(): void
    {
        $this->get(route('pelapor.lapor'))
            ->assertOk()
            ->assertSee('id="lapor-form"', false)
            ->assertSee('method="POST"', false)
            ->assertSee('novalidate', false)
            ->assertSee('invalidField.reportValidity()', false)
            ->assertSee('name="telepon"', false)
            ->assertSee('inputmode="tel"', false)
            ->assertSee('id="otp-send"', false)
            ->assertSee('id="otp-verified"', false)
            ->assertSee('id="ai-assistant-panel"', false)
            ->assertSee('id="ai-assistant-form"', false)
            ->assertDontSee('name="nik"', false)
            ->assertDontSee('name="jenis_radio"', false)
            ->assertDontSee('name="kategori"', false);
    }

    public function test_guest_can_submit_report_and_receive_browser_session_cookie(): void
    {
        Storage::fake('local');
        $activeAdmin = User::factory()->create();
        $blockedAdmin = User::factory()->blocked()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->verifyPhoneOtp('081234567890');
        $response = $this->post(route('pelapor.store'), [
            'telepon' => '081234567890',
            'deskripsi' => 'Ada aktivitas yang mengganggu ketertiban umum.',
            'lokasi' => 'Alun-alun Wates',
            'bukti' => [$this->fakeImage('bukti.png')],
            'setuju' => '1',
        ]);

        $report = Report::query()->sole();
        $response->assertRedirectToRoute('pelapor.lapor');
        $response->assertSessionHas('report_code', $report->code);
        $response->assertCookie('sipraja_guest_token');
        $this->assertSame(ReportStatus::Submitted, $report->status);
        $this->assertSame(ReportType::Report, $report->type);
        $this->assertNull($report->reporter_nik);
        $this->assertNull($report->category);
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

    public function test_guest_report_validation_rejects_too_many_files_and_ignores_nik(): void
    {
        Storage::fake('local');

        $this->verifyPhoneOtp('081234567890');
        $response = $this->from(route('pelapor.lapor'))->post(route('pelapor.store'), [
            'telepon' => '081234567890',
            'nik' => '123',
            'deskripsi' => 'Laporan darurat.',
            'lokasi' => 'Wates',
            'bukti' => [
                $this->fakeImage('satu.png'),
                $this->fakeImage('dua.png'),
                $this->fakeImage('tiga.png'),
                $this->fakeImage('empat.png'),
            ],
            'setuju' => '1',
        ]);

        $response->assertRedirect(route('pelapor.lapor'));
        $response->assertSessionHasErrors(['bukti']);
        $response->assertSessionDoesntHaveErrors(['nik']);
        $this->assertDatabaseCount('reports', 0);
    }

    private function fakeImage(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true),
        );
    }
}
