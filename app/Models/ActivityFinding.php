<?php

namespace App\Models;

use App\FindingType;
use App\FindingUnit;
use Database\Factories\ActivityFindingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['activity_id', 'type', 'quantity', 'unit'])]
class ActivityFinding extends Model
{
    /** @use HasFactory<ActivityFindingFactory> */
    use HasFactory;

    /** @return BelongsTo<Activity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => FindingType::class,
            'quantity' => 'integer',
            'unit' => FindingUnit::class,
        ];
    }
}
