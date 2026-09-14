<?php

namespace App;

enum ReportCategory: string
{
    case PublicOrder = 'public_order';
    case EnvironmentalCleanliness = 'environmental_cleanliness';
    case Security = 'security';

    public function label(): string
    {
        return match ($this) {
            self::PublicOrder => 'Ketertiban Umum',
            self::EnvironmentalCleanliness => 'Kebersihan Lingkungan',
            self::Security => 'Keamanan',
        };
    }
}
