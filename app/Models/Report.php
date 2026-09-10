<?php

namespace App\Models;

use App\ReportCategory;
use App\ReportSource;
use App\ReportSourcePlatform;
use App\ReportStatus;
use App\ReportType;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'code',
    'guest_access_id',
    'created_by_user_id',
    'type',
    'category',
    'source',
    'source_platform',
    'reporter_name',
    'reporter_phone',
    'reporter_nik',
    'description',
    'location_address',
    'latitude',
    'longitude',
    'status',
    'completed_at',
    'feedback_rating',
    'feedback_comment',
    'feedback_submitted_at',
])]
#[Hidden(['reporter_nik'])]
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsTo<GuestAccess, $this>
     */
    public function guestAccess(): BelongsTo
    {
        return $this->belongsTo(GuestAccess::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return HasMany<ReportStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(ReportStatusHistory::class);
    }

    /**
     * @return MorphMany<Attachment, $this>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /** @return HasOne<Activity, $this> */
    public function activity(): HasOne
    {
        return $this->hasOne(Activity::class);
    }

    public function maskedReporterNik(): ?string
    {
        if ($this->reporter_nik === null) {
            return null;
        }

        return Str::mask($this->reporter_nik, '*', 4, 8);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ReportType::class,
            'category' => ReportCategory::class,
            'source' => ReportSource::class,
            'source_platform' => ReportSourcePlatform::class,
            'reporter_nik' => 'encrypted',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'status' => ReportStatus::class,
            'completed_at' => 'datetime',
            'feedback_rating' => 'integer',
            'feedback_submitted_at' => 'datetime',
        ];
    }
}
