<?php

namespace App;

enum FindingUnit: string
{
    case Piece = 'piece';
    case Stick = 'stick';
    case Bottle = 'bottle';
    case Person = 'person';

    public function label(): string
    {
        return match ($this) {
            self::Piece => 'Buah',
            self::Stick => 'Batang',
            self::Bottle => 'Botol',
            self::Person => 'Orang',
        };
    }
}
