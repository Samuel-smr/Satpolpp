<?php

namespace App\Models;

use Database\Factories\ArchiveFileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['archive_id', 'file_path', 'original_name', 'mime_type', 'file_extension', 'file_size'])]
class ArchiveFile extends Model
{
    /** @use HasFactory<ArchiveFileFactory> */
    use HasFactory;

    public function archive(): BelongsTo
    {
        return $this->belongsTo(Archive::class);
    }

    protected function casts(): array
    {
        return ['file_size' => 'integer'];
    }
}
