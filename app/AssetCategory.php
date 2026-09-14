<?php

namespace App;

enum AssetCategory: string
{
    case Vehicle = 'vehicle';
    case Supplies = 'supplies';
    case Equipment = 'equipment';
    case Infrastructure = 'infrastructure';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Vehicle => 'Kendaraan',
            self::Supplies => 'Perlengkapan',
            self::Equipment => 'Peralatan',
            self::Infrastructure => 'Sarana & Prasarana',
            self::Other => 'Lainnya',
        };
    }
}
