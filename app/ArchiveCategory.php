<?php

namespace App;

use App\Models\ActivityTemplate;
use Illuminate\Support\Collection;

final class ArchiveCategory
{
    public const JURIDICAL = 'Penegakan peraturan daerah dan peraturan kepala daerah yustisi';

    public const NON_JURIDICAL = 'Penegakan peraturan daerah dan peraturan kepala daerah non yustisi';

    /**
     * Kategori arsip mengikuti kategori kegiatan (nama template aktivitas),
     * dengan kategori yustisi dan non yustisi yang tetap dipertahankan.
     *
     * @return Collection<int, string>
     */
    public static function all(): Collection
    {
        return ActivityTemplate::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name')
            ->values()
            ->push(self::JURIDICAL, self::NON_JURIDICAL);
    }

    /**
     * @return list<string>
     */
    public static function fallback(): array
    {
        return [self::JURIDICAL, self::NON_JURIDICAL];
    }
}
