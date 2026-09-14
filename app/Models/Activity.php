<?php

namespace App\Models;

use App\ActivityType;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'report_id',
    'activity_template_id',
    'created_by_user_id',
    'evaluated_by_user_id',
    'name',
    'type',
    'category',
    'activity_date',
    'start_time',
    'description',
    'handling_result',
    'location_address',
    'latitude',
    'longitude',
    'evaluation_notes',
    'evaluation_obstacles',
    'evaluated_at',
])]
class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory, SoftDeletes;

    /** @return BelongsTo<Report, $this> */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /** @return BelongsTo<ActivityTemplate, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ActivityTemplate::class, 'activity_template_id');
    }

    /** @return BelongsTo<User, $this> */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function evaluatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by_user_id');
    }

    /** @return BelongsToMany<Officer, $this> */
    public function officers(): BelongsToMany
    {
        return $this->belongsToMany(Officer::class)
            ->withPivot(['assignment_role', 'notes'])
            ->withTimestamps();
    }

    /** @return MorphMany<Attachment, $this> */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'activity_date' => 'date',
            'start_time' => 'datetime:H:i',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'evaluated_at' => 'datetime',
        ];
    }
}
