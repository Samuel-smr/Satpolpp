<?php

namespace Database\Factories;

use App\ArchiveConfidentiality;
use App\Models\Archive;
use App\Models\User;
use App\OperationalCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Archive>
 */
class ArchiveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by_user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'operational_category' => fake()->randomElement(OperationalCategory::cases()),
            'description' => fake()->sentence(),
            'confidentiality' => ArchiveConfidentiality::Public,
            'file_path' => 'archives/files/example.pdf',
            'original_name' => 'example.pdf',
            'mime_type' => 'application/pdf',
            'file_extension' => 'pdf',
            'file_size' => 1024,
        ];
    }
}
