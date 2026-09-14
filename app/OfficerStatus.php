<?php

namespace App;

enum OfficerStatus: string
{
    case Active = 'active';
    case Leave = 'leave';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Leave => 'Cuti',
            self::Inactive => 'Nonaktif',
        };
    }
}
