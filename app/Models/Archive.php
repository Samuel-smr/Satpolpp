<?php

namespace App\Models;

use App\ArchiveConfidentiality;
use Database\Factories\ArchiveFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'created_by_user_id',
    'title',
    'operational_category',
    'description',
    'confidentiality',
    'file_path',
    'original_name',
    'mime_type',
    'file_extension',
    'file_size',
])]
class Archive extends Model
{
    /** @use HasFactory<ArchiveFactory> */
    use HasFactory, SoftDeletes;

    /** @return BelongsTo<User, $this> */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'confidentiality' => ArchiveConfidentiality::class,
            'file_size' => 'integer',
        ];
    }
}
