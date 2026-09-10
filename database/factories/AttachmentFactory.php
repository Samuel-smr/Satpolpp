<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attachable_type' => 'report',
            'attachable_id' => Report::factory(),
            'uploaded_by_user_id' => null,
            'file_path' => 'reports/'.fake()->uuid().'.jpg',
            'original_name' => fake()->word().'.jpg',
            'file_type' => 'image',
            'mime_type' => 'image/jpeg',
            'file_size' => fake()->numberBetween(50_000, 2_000_000),
            'audience' => 'public',
        ];
    }
}
