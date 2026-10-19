<?php

namespace Tests\Feature;

use App\Models\ActivityTemplate;
use App\Models\Report;
use App\Models\User;
use App\ReportStatus;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AuditEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith(['pelapor.otp.send'])]
    #[TestWith(['pelapor.otp.verify'])]
    public function test_array_phone_returns_validation_error_without_sending_otp(string $routeName): void
    {
        Http::preventStrayRequests();
        $this->postJson(route($routeName), ['telepon' => ['082233445566'], 'kode' => '123456'])
            ->assertUnprocessable()->assertJsonValidationErrors('telepon');
        Http::assertNothingSent();
    }

    public function test_verification_in_another_browser_does_not_authorize_report_submission(): void
    {
        Cache::put('sipraja_otp:verified:'.hash('sha256', '6282233445566'), true, 900);
        $this->postJson(route('pelapor.store'), [
            'telepon' => '082233445566', 'deskripsi' => 'Laporan tanpa OTP sendiri.',
            'lokasi' => 'Wates', 'setuju' => '1',
        ])->assertUnprocessable()->assertJsonValidationErrors('telepon');
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_production_never_exposes_otp_even_when_debug_is_enabled(): void
    {
        $this->app->instance('env', 'production');
        config()->set('app.debug', true);
        config()->set('services.whatsapp.bot_url', 'http://localhost:3456');
        Http::preventStrayRequests();
        Http::fake(['http://localhost:3456/send' => Http::response(['status' => true])]);

        $this->withSession(['_token' => 'audit-csrf-token'])
            ->postJson(route('pelapor.otp.send'), ['telepon' => '082233445566', '_token' => 'audit-csrf-token'])
            ->assertOk()->assertJsonMissingPath('debug_code');

        Http::assertSentCount(1);
    }

    public function test_otp_verification_expires_in_the_current_session(): void
    {
        $this->freezeTime();
        $code = app(OtpService::class)->issue('082233445566');
        $this->postJson(route('pelapor.otp.verify'), ['telepon' => '082233445566', 'kode' => $code])->assertOk();
        $this->assertTrue(app(OtpService::class)->isVerified('6282233445566'));
        $this->travel(16)->minutes();
        $this->assertFalse(app(OtpService::class)->isVerified('082233445566'));
    }

    public function test_verified_browser_can_submit_but_new_session_cannot_reuse_verification(): void
    {
        config()->set('services.whatsapp.bot_url', 'http://localhost:3456');
        Http::preventStrayRequests();
        Http::fake(['http://localhost:3456/send' => Http::response(['status' => true])]);
        $code = app(OtpService::class)->issue('082233445566');
        $this->postJson(route('pelapor.otp.verify'), ['telepon' => '082233445566', 'kode' => $code])->assertOk();
        $payload = ['telepon' => '082233445566', 'deskripsi' => 'Laporan terverifikasi.', 'lokasi' => 'Wates', 'setuju' => '1'];

        $this->post(route('pelapor.store'), $payload)->assertSessionHasNoErrors()->assertRedirectToRoute('pelapor.lapor');
        $this->assertDatabaseCount('reports', 1);
        $this->app['session']->flush();
        $this->app['session']->migrate(true);
        $this->postJson(route('pelapor.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('telepon');
        $this->assertDatabaseCount('reports', 1);
        Http::assertSentCount(1);
    }

    #[TestWith([false])]
    #[TestWith([true])]
    public function test_excessive_finding_quantity_is_rejected_before_persistence(bool $completion): void
    {
        Storage::fake('local');
        $employee = $this->employee();
        $template = ActivityTemplate::factory()->create();
        $admin = User::factory()->create();
        $report = Report::factory()->assignedTo($admin)->create(['status' => ReportStatus::InProgress]);
        $payload = [
            'name' => 'Patroli', 'category' => $template->name, 'activity_date' => now()->toDateString(),
            'location_address' => 'Wates', 'latitude' => -7.85, 'longitude' => 110.16,
            'employee_nips' => [$employee->employee_number], 'handling_result' => 'Selesai',
            'findings' => [['type' => 'item', 'unit' => 'piece', 'quantity' => '4294967296']],
        ];
        if ($completion) {
            $payload['current_status'] = ReportStatus::InProgress->value;
            $payload['evidence'] = [UploadedFile::fake()->image('bukti.jpg')];
        }
        $this->actingAs($admin)->postJson(
            $completion ? route('admin.pengaduan.complete.store', $report) : route('admin.kegiatan.store'), $payload,
        )->assertUnprocessable()->assertJsonValidationErrors('findings.0.quantity');
        $this->assertDatabaseCount('activities', 0);
        $this->assertSame(ReportStatus::InProgress, $report->fresh()->status);
        $this->assertSame([], Storage::disk('local')->allFiles());
        Http::assertSentCount(1);
    }

    public function test_distinct_string_nips_with_leading_zero_are_both_displayed(): void
    {
        config()->set('services.eprofil', ['url' => 'https://eprofil.example.test/pegawai', 'token' => 'test-token']);
        Http::preventStrayRequests();
        Http::fake(['https://eprofil.example.test/pegawai' => Http::response(['success' => true, 'data' => [
            ['nip' => '012345678901234567', 'nama' => 'Pegawai Nol', 'jabatan_nama' => 'Petugas'],
            ['nip' => '12345678901234567', 'nama' => 'Pegawai Lain', 'jabatan_nama' => 'Petugas'],
        ]])]);
        $this->actingAs(User::factory()->superAdmin()->create())->get(route('super_admin.petugas'))
            ->assertOk()->assertSeeText('Pegawai Nol')->assertSeeText('Pegawai Lain');
        Http::assertSentCount(1);
    }
}
