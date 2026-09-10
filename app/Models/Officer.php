<?php

namespace App\Models;

use App\OfficerStatus;
use Database\Factories\OfficerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'organizational_unit_id',
    'operational_zone_id',
    'name',
    'employee_number',
    'rank',
    'position',
    'email',
    'phone',
    'address',
    'joined_at',
    'status',
])]
class Officer extends Model
{
    /** @use HasFactory<OfficerFactory> */
    use HasFactory, SoftDeletes;

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class);
    }

    public function operationalZone(): BelongsTo
    {
        return $this->belongsTo(OperationalZone::class);
    }

    /** @return BelongsToMany<Activity, $this> */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class)
            ->withPivot(['assignment_role', 'notes'])
            ->withTimestamps();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'status' => OfficerStatus::class,
        ];
    }
}
