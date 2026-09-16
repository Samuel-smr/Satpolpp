<?php

namespace Database\Factories;

use App\ArchiveConfidentiality;
use App\Models\Archive;
use App\Models\ArchiveFile;
use App\Models\User;
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
            'operational_category' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'confidentiality' => ArchiveConfidentiality::Public,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Archive $archive): void {
            if ($archive->files()->doesntExist()) {
                ArchiveFile::factory()->for($archive)->create();
            }
        });
    }
}
