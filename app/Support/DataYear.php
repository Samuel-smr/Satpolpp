<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class DataYear
{
    public function selected(): int
    {
        return (int) session('data_year', now()->year);
    }

    /** @return list<int> */
    public function available(): array
    {
        $firstYear = min(2026, now()->year);

        foreach ([Report::class => 'created_at', Activity::class => 'activity_date', Archive::class => 'created_at', AuditLog::class => 'created_at'] as $model => $column) {
            $firstDate = $model::query()->min($column);

            if ($firstDate !== null) {
                $firstYear = min($firstYear, Carbon::parse($firstDate)->year);
            }
        }

        return range(now()->year, $firstYear);
    }

    public function start(): Carbon
    {
        return Carbon::create($this->selected(), 1, 1)->startOfDay();
    }

    public function end(): Carbon
    {
        return $this->start()->addYear();
    }

    public function referenceDate(): Carbon
    {
        return $this->selected() === now()->year ? today() : $this->end()->subDay();
    }

    /** @param Builder<Model> $query */
    public function constrain(Builder $query, string $column): Builder
    {
        return $query->where($query->qualifyColumn($column), '>=', $this->start()->toDateString())
            ->where($query->qualifyColumn($column), '<', $this->end()->toDateString());
    }

    /** @return list<string> */
    public function dateRules(): array
    {
        return ['after_or_equal:'.$this->start()->toDateString(), 'before:'.$this->end()->toDateString()];
    }

    public function ensureCurrentYear(): void
    {
        if ($this->selected() !== now()->year) {
            throw ValidationException::withMessages([
                'data_year' => 'Pilih tahun berjalan untuk mencatat laporan atau mengunggah arsip baru. Data lama tetap dapat dilihat dan diperbarui.',
            ]);
        }
    }
}
