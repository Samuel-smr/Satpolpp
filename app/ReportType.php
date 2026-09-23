<?php

namespace App;

enum ReportType: string
{
    case Report = 'lapor';

    public function label(): string
    {
        return 'Lapor';
    }
}
