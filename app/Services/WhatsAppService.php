<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WhatsAppService
{
    public function send(string $phone, string $message): void
    {
        if ((string) config('services.whatsapp.driver') === 'fonnte') {
            $this->sendViaFonnte($phone, $message);

            return;
        }

        $this->sendViaLocalBot($phone, $message);
    }

    private function sendViaFonnte(string $phone, string $message): void
    {
        $token = config('services.fonnte.token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Fonnte token is not configured.');
        }

        $response = Http::withHeaders(['Authorization' => $token])
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(15)
            ->retry([250], 0, fn (?Throwable $exception): bool => $exception instanceof ConnectionException
                || ($exception instanceof RequestException
                    && ($exception->response->status() === 429 || $exception->response->serverError())))
            ->post('https://api.fonnte.com/send', [
                'target' => $this->normalizePhone($phone),
                'message' => $message,
            ])
            ->throw();

        if (! $response->successful() || $response->json('status') !== true) {
            throw new RuntimeException('Fonnte rejected the message: '.($response->json('reason') ?? 'unknown error'));
        }
    }

    private function sendViaLocalBot(string $phone, string $message): void
    {
        $baseUrl = rtrim((string) config('services.whatsapp.bot_url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('WhatsApp local bot URL is not configured.');
        }

        $response = Http::acceptJson()
            ->connectTimeout(5)
            ->timeout(15)
            ->post("{$baseUrl}/send", [
                'target' => $this->normalizePhone($phone),
                'message' => $message,
            ])
            ->throw();

        if (! $response->successful() || $response->json('status') !== true) {
            throw new RuntimeException('WhatsApp local bot rejected the message: '.($response->json('reason') ?? 'unknown error'));
        }
    }

    public function sendReportCreated(Report $report): void
    {
        if ($report->reporter_phone === null || trim($report->reporter_phone) === '') {
            return;
        }

        $message = "Laporan Anda telah kami terima.\n\n"
            ."Kode Laporan: {$report->code}\n\n"
            .'Simpan kode ini untuk memantau perkembangan laporan melalui halaman status SIPRAJA.';

        $this->attempt($report, $message);
    }

    public function sendReportStatusUpdate(Report $report, string $statusLabel, ?string $note = null): void
    {
        if ($report->reporter_phone === null || trim($report->reporter_phone) === '') {
            return;
        }

        $message = "Pembaruan Status Laporan SIPRAJA\n\n"
            ."Kode Laporan: {$report->code}\n"
            ."Status: {$statusLabel}\n"
            .($note !== null && $note !== '' ? "\nCatatan: {$note}\n" : '')
            ."\nTerima kasih telah menggunakan SIPRAJA.";

        $this->attempt($report, $message);
    }

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return $phone;
        }

        return match (true) {
            str_starts_with($digits, '0') => '62'.substr($digits, 1),
            str_starts_with($digits, '8') => '62'.$digits,
            default => $digits,
        };
    }

    private function attempt(Report $report, string $message): void
    {
        try {
            $this->send((string) $report->reporter_phone, $message);
        } catch (Throwable $exception) {
            Log::warning("Gagal mengirim notifikasi WhatsApp untuk laporan {$report->code}.", [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}