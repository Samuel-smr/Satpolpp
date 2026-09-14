<?php

namespace App\Models;

use App\OrganizationalUnitType;
use Database\Factories\OrganizationalUnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['parent_id', 'operational_zone_id', 'leader_officer_id', 'name', 'code', 'type', 'description', 'is_active'])]
class OrganizationalUnit extends Model
{
    /** @use HasFactory<OrganizationalUnitFactory> */
    use HasFactory, SoftDeletes;

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function operationalZone(): BelongsTo
    {
        return $this->belongsTo(OperationalZone::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Officer::class, 'leader_officer_id');
    }

    public function officers(): HasMany
    {
        return $this->hasMany(Officer::class);
    }

    protected function casts(): array
    {
        return ['type' => OrganizationalUnitType::class, 'is_active' => 'boolean'];
    }
}
