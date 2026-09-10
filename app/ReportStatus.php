<?php

namespace App;

enum ReportStatus: string
{
    case Submitted = 'submitted';
    case Received = 'received';
    case Verifying = 'verifying';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Laporan dibuat',
            self::Received => 'Laporan diterima',
            self::Verifying => 'Sedang diverifikasi',
            self::InProgress => 'Ditindaklanjuti',
            self::Completed => 'Laporan selesai',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Submitted => 'Laporan telah diterima oleh sistem.',
            self::Received => 'Laporan telah diterima oleh petugas.',
            self::Verifying => 'Petugas sedang memverifikasi laporan.',
            self::InProgress => 'Laporan sedang ditangani di lapangan.',
            self::Completed => 'Penanganan laporan telah selesai.',
        };
    }

    public function nextProcessingStatus(): ?self
    {
        return match ($this) {
            self::Submitted => self::Received,
            self::Received => self::Verifying,
            self::Verifying => self::InProgress,
            self::InProgress, self::Completed => null,
        };
    }
}
