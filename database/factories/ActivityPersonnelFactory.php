<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivityPersonnel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityPersonnel>
 */
class ActivityPersonnelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'employee_nip' => fake()->unique()->numerify('##################'),
            'assignment_role' => 'Petugas Kegiatan',
            'notes' => null,
        ];
    }
}
