<?php

namespace Tests\Feature;

use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OtpFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['status' => true], 200)]);
    }

    public function test_sending_otp_issues_code_and_returns_debug_code_in_debug_mode(): void
    {
        $response = $this->postJson(route('pelapor.otp.send'), ['telepon' => '082233445566']);

        $response->assertOk()
            ->assertJsonPath('message', 'Kode OTP berhasil dikirim ke nomor WhatsApp Anda.')
            ->assertJsonStructure(['message', 'debug_code']);

        $stored = Cache::get('sipraja_otp:code:'.hash('sha256', '6282233445566'));
        $this->assertSame($response->json('debug_code'), $stored);
        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $stored);
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/send'));
    }

    public function test_sending_otp_never_leaks_code_when_debug_is_disabled(): void
    {
        $this->app['config']->set('app.debug', false);

        $this->postJson(route('pelapor.otp.send'), ['telepon' => '082233445566'])
            ->assertOk()
            ->assertJsonMissingPath('debug_code');
    }

    public function test_sending_otp_rejects_invalid_phone(): void
    {
        $this->postJson(route('pelapor.otp.send'), ['telepon' => 'abc'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('telepon');
    }

    public function test_resending_otp_is_blocked_for_sixty_seconds(): void
    {
        $this->postJson(route('pelapor.otp.send'), ['telepon' => '082233445577'])->assertOk();

        $this->postJson(route('pelapor.otp.send'), ['telepon' => '082233445577'])
            ->assertStatus(429)
            ->assertJsonPath('message', 'Mohon tunggu 60 detik sebelum meminta kode OTP kembali.');
    }

    public function test_verifying_otp_marks_phone_as_verified(): void
    {
        $code = $this->issuedCode('085511223344');

        $this->postJson(route('pelapor.otp.verify'), ['telepon' => '085511223344', 'kode' => $code])
            ->assertOk()
            ->assertJsonPath('message', 'Nomor berhasil diverifikasi.');

        $this->assertTrue(app(OtpService::class)->isVerified('085511223344'));
    }

    public function test_verifying_otp_rejects_wrong_code_and_locks_after_max_attempts(): void
    {
        app(OtpService::class)->issue('085511223355');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson(route('pelapor.otp.verify'), ['telepon' => '085511223355', 'kode' => '000000'])
                ->assertStatus(422)
                ->assertJsonPath('message', 'Kode OTP salah atau sudah kedaluwarsa.');
        }

        $this->postJson(route('pelapor.otp.verify'), ['telepon' => '085511223355', 'kode' => '123456'])
            ->assertStatus(422);
        $this->assertFalse(app(OtpService::class)->isVerified('085511223355'));
    }

    public function test_verifying_otp_rejects_expired_code(): void
    {
        $code = $this->issuedCode('085511223366');
        Cache::forget('sipraja_otp:code:'.hash('sha256', '6285511223366'));

        $this->postJson(route('pelapor.otp.verify'), ['telepon' => '085511223366', 'kode' => $code])
            ->assertStatus(422);
    }

    public function test_report_submission_requires_verified_phone(): void
    {
        $this->postJson(route('pelapor.store'), [
            'telepon' => '081299887766',
            'deskripsi' => 'Gangguan di jalan.',
            'lokasi' => 'Wates',
            'setuju' => '1',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('telepon');

        $this->assertDatabaseCount('reports', 0);
    }

    private function issuedCode(string $phone): string
    {
        $code = app(OtpService::class)->issue($phone);
        $this->assertIsString($code);

        return $code;
    }
}
