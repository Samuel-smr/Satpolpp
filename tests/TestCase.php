<?php

namespace Tests;

use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    /** Menandai nomor telepon sebagai sudah diverifikasi OTP di cache. */
    protected function verifyPhoneOtp(string $phone): void
    {
        Cache::put(
            'sipraja_otp:verified:'.hash('sha256', app(WhatsAppService::class)->normalizePhone($phone)),
            true,
            now()->addMinutes(15),
        );
    }
}
