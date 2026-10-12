<?php

namespace App\Services;

use App\Models\Report;
use App\Models\User;
use App\UserRole;
use App\UserStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WhatsAppService
{
    public function send(string $phone, string $message): void
    {
        $this->sendViaLocalBot($phone, $message);
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

    public function sendNewReportToAdmins(Report $report): void
    {
        $admins = User::query()
            ->where('role', UserRole::Admin)
            ->where('status', UserStatus::Active)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get(['id', 'phone']);

        foreach ($admins as $admin) {
            $message = "Laporan Baru Masuk SIPRAJA\n\n"
                ."Kode Laporan: {$report->code}\n"
                ."Lokasi: {$report->location_address}\n\n"
                .'Segera periksa dan tindak lanjuti laporan pada panel admin.';

            $this->attemptPhone($admin->phone, $message, "notifikasi laporan baru untuk admin #{$admin->id}");
        }
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
        $this->attemptPhone((string) $report->reporter_phone, $message, "notifikasi WhatsApp untuk laporan {$report->code}");
    }

    private function attemptPhone(string $phone, string $message, string $context): void
    {
        try {
            $this->send($phone, $message);
        } catch (Throwable $exception) {
            Log::warning("Gagal mengirim {$context}.", [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
