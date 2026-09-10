<?php

namespace App\Support;

final class FileSize
{
    public static function format(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        $value = (float) $bytes;
        $units = ['KB', 'MB', 'GB', 'TB'];
        $unit = 'KB';

        foreach ($units as $candidate) {
            $value /= 1024;
            $unit = $candidate;

            if ($value < 1024 || $candidate === 'TB') {
                break;
            }
        }

        $formatted = number_format($value, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.').' '.$unit;
    }
}
