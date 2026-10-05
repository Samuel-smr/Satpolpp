<?php

namespace App\Models;

use Database\Factories\ActivityPersonnelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_nip', 'assignment_role', 'notes'])]
class ActivityPersonnel extends Model
{
    /** @use HasFactory<ActivityPersonnelFactory> */
    use HasFactory;

    protected $table = 'activity_personnel';

    /** @return BelongsTo<Activity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
