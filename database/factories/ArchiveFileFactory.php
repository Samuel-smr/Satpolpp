<?php

namespace Database\Factories;

use App\Models\Archive;
use App\Models\ArchiveFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArchiveFile>
 */
class ArchiveFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'archive_id' => Archive::factory(),
            'file_path' => 'archives/files/example.pdf',
            'original_name' => 'example.pdf',
            'mime_type' => 'application/pdf',
            'file_extension' => 'pdf',
            'file_size' => 1024,
        ];
    }
}
