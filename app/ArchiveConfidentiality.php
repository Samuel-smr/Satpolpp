<?php

namespace App;

enum ArchiveConfidentiality: string
{
    case Public = 'public';
    case Confidential = 'confidential';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Biasa / Publik',
            self::Confidential => 'Rahasia / Confidential',
        };
    }
}
