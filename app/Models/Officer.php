<?php

namespace App\Models;

use App\OfficerStatus;
use Database\Factories\OfficerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'name',
    'employee_number',
    'position',
    'work_unit_name',
    'unit_name',
    'status',
])]
class Officer extends Model
{
    /** @use HasFactory<OfficerFactory> */
    use HasFactory, SoftDeletes;

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
            'status' => OfficerStatus::class,
        ];
    }
}
