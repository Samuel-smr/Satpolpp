<?php

namespace App;

enum OrganizationalUnitType: string
{
    case Division = 'division';
    case Team = 'team';
    case Post = 'post';

    public function label(): string
    {
        return match ($this) {
            self::Division => 'Divisi / Seksi',
            self::Team => 'Regu / Tim',
            self::Post => 'Pos Jaga',
        };
    }
}
