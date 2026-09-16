<?php

namespace App;

enum FindingType: string
{
    case Person = 'person';
    case Item = 'item';

    public function label(): string
    {
        return match ($this) {
            self::Person => 'Orang',
            self::Item => 'Barang',
        };
    }
}
