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
        ];
    }
}
