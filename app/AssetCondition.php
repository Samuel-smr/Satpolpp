<?php

namespace App;

enum AssetCondition: string
{
    case Good = 'good';
    case NeedsRepair = 'needs_repair';
    case SeverelyDamaged = 'severely_damaged';

    public function label(): string
    {
        return match ($this) {
            self::Good => 'Baik',
            self::NeedsRepair => 'Perlu Perbaikan',
            self::SeverelyDamaged => 'Rusak Berat',
        };
    }
}
