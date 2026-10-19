<?php

namespace App\Models;

use App\ArchiveConfidentiality;
use App\Support\DataYear;
use Database\Factories\ArchiveFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'created_by_user_id',
    'title',
    'operational_category',
    'description',
    'confidentiality',
])]
class Archive extends Model
{
    /** @use HasFactory<ArchiveFactory> */
    use HasFactory, SoftDeletes;

    #[Scope]
    protected function forDataYear(Builder $query): Builder
    {
        return app(DataYear::class)->constrain($query, 'created_at');
    }

    /** @return BelongsTo<User, $this> */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ArchiveFile::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'confidentiality' => ArchiveConfidentiality::class,
        ];
    }
}
