<?php

namespace App;

enum ReportType: string
{
    case Emergency = 'emergency';
    case Information = 'information';

    public function label(): string
    {
        return match ($this) {
            self::Emergency => 'Lapor Darurat',
            self::Information => 'Informasi & Pengaduan',
        };
    }
}
