<?php

namespace App\Models;

use App\OperationalCategory;
use Database\Factories\ActivityTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'operational_category', 'default_description', 'is_active', 'sort_order'])]
class ActivityTemplate extends Model
{
    /** @use HasFactory<ActivityTemplateFactory> */
    use HasFactory;

    /** @return HasMany<Activity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'operational_category' => OperationalCategory::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
