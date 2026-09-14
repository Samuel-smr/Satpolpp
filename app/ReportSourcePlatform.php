<?php

namespace App;

enum ReportSourcePlatform: string
{
    case Website = 'website';
    case WhatsApp = 'whatsapp';
    case Instagram = 'instagram';
    case X = 'x';
    case Facebook = 'facebook';
    case TikTok = 'tiktok';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Website => 'Website',
            self::WhatsApp => 'WhatsApp',
            self::Instagram => 'Instagram',
            self::X => 'X',
            self::Facebook => 'Facebook',
            self::TikTok => 'TikTok',
            self::Other => 'Lainnya',
        };
    }
}
