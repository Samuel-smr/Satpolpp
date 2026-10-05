<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Random\RandomException;
use Throwable;

class OtpService
{
    private const CACHE_PREFIX = 'sipraja_otp:';

    private const CODE_TTL = 300;

    private const VERIFIED_TTL = 900;

    private const RESEND_GUARD_SECONDS = 60;

    private const MAX_ATTEMPTS = 5;

    public function __construct(private readonly WhatsAppService $whatsapp) {}

    /** @return string|false Kode OTP baru, atau false jika masih dalam jeda pengiriman ulang. */
    public function issue(string $phone): string|false
    {
        $phone = $this->normalize($phone);
        $key = $this->key($phone);

        if (Cache::has(self::CACHE_PREFIX.'last_sent:'.$key)) {
            return false;
        }

        try {
            $code = (string) random_int(100000, 999999);
        } catch (RandomException) {
            $code = str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        }

        Cache::put(self::CACHE_PREFIX.'code:'.$key, $code, now()->addSeconds(self::CODE_TTL));
        Cache::put(self::CACHE_PREFIX.'attempts:'.$key, 0, now()->addSeconds(self::CODE_TTL));
        Cache::put(self::CACHE_PREFIX.'last_sent:'.$key, now(), now()->addSeconds(self::RESEND_GUARD_SECONDS));

        return $code;
    }

    /** Mengirim kode via WhatsApp; mengembalikan true jika berhasil dikirim. */
    public function send(string $phone, string $code): bool
    {
        try {
            $this->whatsapp->send($this->normalize($phone), $this->message($code));

            return true;
        } catch (Throwable $exception) {
            Log::warning('Gagal mengirim kode OTP WhatsApp.', [
                'phone' => $this->normalize($phone),
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function verify(string $phone, string $code): bool
    {
        $phone = $this->normalize($phone);
        $key = $this->key($phone);
        $expected = Cache::get(self::CACHE_PREFIX.'code:'.$key);

        if (! is_string($expected) || $expected === '') {
            return false;
        }

        $attempts = (int) Cache::get(self::CACHE_PREFIX.'attempts:'.$key, 0);

        if ($attempts >= self::MAX_ATTEMPTS) {
            Cache::forget(self::CACHE_PREFIX.'code:'.$key);

            return false;
        }

        if (! hash_equals($expected, $code)) {
            Cache::put(self::CACHE_PREFIX.'attempts:'.$key, $attempts + 1, now()->addSeconds(self::CODE_TTL));

            return false;
        }

        Cache::forget(self::CACHE_PREFIX.'code:'.$key);
        Cache::forget(self::CACHE_PREFIX.'attempts:'.$key);
        Cache::put(self::CACHE_PREFIX.'verified:'.$key, true, now()->addSeconds(self::VERIFIED_TTL));

        return true;
    }

    public function isVerified(string $phone): bool
    {
        return Cache::get(self::CACHE_PREFIX.'verified:'.$this->key($this->normalize($phone))) === true;
    }

    /** Menghapus kode/percobaan agar gagal kirim tidak meninggalkan kode aktif. */
    public function forget(string $phone): void
    {
        $key = $this->key($this->normalize($phone));
        Cache::forget(self::CACHE_PREFIX.'code:'.$key);
        Cache::forget(self::CACHE_PREFIX.'attempts:'.$key);
    }

    private function message(string $code): string
    {
        return "Kode verifikasi SIPRAJA Anda: {$code}\n\n"
            .'Kode berlaku 5 menit. Jangan bagikan kode ini kepada siapa pun.';
    }

    private function normalize(string $phone): string
    {
        return $this->whatsapp->normalizePhone($phone);
    }

    private function key(string $phone): string
    {
        return hash('sha256', $phone);
    }
}
