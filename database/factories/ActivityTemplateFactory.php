<?php

namespace Database\Factories;

use App\Models\ActivityTemplate;
use App\OperationalCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityTemplate>
 */
class ActivityTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(4),
            'operational_category' => fake()->randomElement(OperationalCategory::cases()),
            'default_description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => false]);
    }
}
