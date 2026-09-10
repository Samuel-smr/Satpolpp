<?php

namespace Database\Factories;

use App\Models\Officer;
use App\OfficerStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Officer>
 */
class OfficerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'organizational_unit_id' => null,
            'operational_zone_id' => null,
            'name' => fake()->name(),
            'employee_number' => fake()->unique()->numerify('PP-######'),
            'rank' => fake()->randomElement(['Pengatur', 'Penata Muda']),
            'position' => fake()->randomElement(['Anggota', 'Kepala Regu']),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('08##########'),
            'address' => fake()->address(),
            'joined_at' => fake()->date(),
            'status' => OfficerStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => OfficerStatus::Inactive]);
    }
}
