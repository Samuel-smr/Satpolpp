<?php

namespace App\Models;

use App\AssetCategory;
use App\AssetCondition;
use Database\Factories\AssetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

#[Fillable([
    'created_by_user_id',
    'asset_code',
    'name',
    'category',
    'condition',
    'location',
    'acquisition_year',
    'tax_due_date',
    'next_oil_service_km',
    'last_oil_service_date',
])]
class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use HasFactory, SoftDeletes;

    /** @return BelongsTo<User, $this> */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'category' => AssetCategory::class,
            'condition' => AssetCondition::class,
            'acquisition_year' => 'integer',
            'tax_due_date' => 'date',
            'next_oil_service_km' => 'integer',
            'last_oil_service_date' => 'date',
        ];
    }

    /**
     * Peringatan pemeliharaan aset kendaraan (pajak dan jadwal ganti oli).
     *
     * @return Collection<int, array{title: string, message: string, level: string}>
     */
    public static function vehicleAlerts(): Collection
    {
        $today = now()->startOfDay();
        $alerts = collect();

        self::query()
            ->where('category', AssetCategory::Vehicle->value)
            ->orderBy('name')
            ->get(['id', 'name', 'tax_due_date', 'last_oil_service_date'])
            ->each(function (Asset $asset) use ($alerts, $today): void {
                if ($asset->tax_due_date !== null) {
                    $dueDate = $asset->tax_due_date->copy()->startOfDay();
                    $daysLeft = (int) $today->diffInDays($dueDate, false);

                    if ($daysLeft < 0 || $dueDate->isToday()) {
                        $alerts->push([
                            'title' => 'Pajak kendaraan sudah jatuh tempo',
                            'message' => "Pajak kendaraan {$asset->name} jatuh tempo pada {$dueDate->format('d/m/Y')}. Segera lakukan perpanjangan STNK.",
                            'level' => 'danger',
                        ]);
                    } elseif ($daysLeft <= 3) {
                        $alerts->push([
                            'title' => 'Pajak kendaraan hampir jatuh tempo',
                            'message' => "Pajak kendaraan {$asset->name} tersisa {$daysLeft} hari lagi (jatuh tempo {$dueDate->format('d/m/Y')}). Siapkan pembayaran pajak.",
                            'level' => 'danger',
                        ]);
                    } elseif ($daysLeft <= 7) {
                        $alerts->push([
                            'title' => 'Pajak kendaraan dalam seminggu',
                            'message' => "Pajak kendaraan {$asset->name} jatuh tempo dalam {$daysLeft} hari. Ajukan perpanjangan lebih awal.",
                            'level' => 'warning',
                        ]);
                    } elseif ($daysLeft <= 30) {
                        $alerts->push([
                            'title' => 'Pajak kendaraan dalam sebulan',
                            'message' => "Pajak kendaraan {$asset->name} akan jatuh tempo pada {$dueDate->format('d/m/Y')} (sisa {$daysLeft} hari).",
                            'level' => 'warning',
                        ]);
                    }
                }

                if ($asset->last_oil_service_date !== null) {
                    $monthsSince = (int) $asset->last_oil_service_date->diffInMonths($today);

                    if ($monthsSince >= 4) {
                        $alerts->push([
                            'title' => 'Ganti oli kendaraan terlewat',
                            'message' => "Kendaraan {$asset->name} sudah {$monthsSince} bulan sejak servis oli terakhir ({$asset->last_oil_service_date->format('d/m/Y')}). Ganti oli sesegera mungkin.",
                            'level' => 'danger',
                        ]);
                    } elseif ($monthsSince >= 3) {
                        $alerts->push([
                            'title' => 'Jadwal ganti oli kendaraan',
                            'message' => "Kendaraan {$asset->name} sudah memasuki bulan ke-{$monthsSince} sejak servis oli terakhir. Jadwalkan penggantian oli.",
                            'level' => 'warning',
                        ]);
                    }
                }
            });

        return $alerts;
    }
}
