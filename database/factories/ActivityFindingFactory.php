<?php

namespace Database\Factories;

use App\FindingType;
use App\FindingUnit;
use App\Models\Activity;
use App\Models\ActivityFinding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityFinding>
 */
class ActivityFindingFactory extends Factory
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
            'type' => fake()->randomElement(FindingType::cases()),
            'quantity' => fake()->numberBetween(1, 100),
            'unit' => fake()->randomElement(FindingUnit::cases()),
        ];
    }
}
