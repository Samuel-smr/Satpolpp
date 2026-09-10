<?php

namespace Database\Factories;

use App\ActivityType;
use App\Models\Activity;
use App\Models\ActivityTemplate;
use App\Models\User;
use App\OperationalCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_id' => null,
            'activity_template_id' => ActivityTemplate::factory(),
            'created_by_user_id' => User::factory(),
            'evaluated_by_user_id' => null,
            'name' => fake()->sentence(4),
            'type' => ActivityType::Internal,
            'operational_category' => fake()->randomElement(OperationalCategory::cases()),
            'activity_date' => fake()->date(),
            'start_time' => '08:00:00',
            'description' => fake()->sentence(),
            'handling_result' => fake()->sentence(),
            'location_address' => fake()->address(),
            'latitude' => fake()->latitude(-7.95, -7.60),
            'longitude' => fake()->longitude(110.00, 110.35),
            'evaluation_notes' => null,
            'evaluation_obstacles' => null,
            'evaluated_at' => null,
        ];
    }
}
