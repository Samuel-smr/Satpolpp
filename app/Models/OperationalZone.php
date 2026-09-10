<?php

namespace App\Models;

use Database\Factories\OperationalZoneFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'code', 'description', 'is_active'])]
class OperationalZone extends Model
{
    /** @use HasFactory<OperationalZoneFactory> */
    use HasFactory, SoftDeletes;

    public function organizationalUnits(): HasMany
    {
        return $this->hasMany(OrganizationalUnit::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
