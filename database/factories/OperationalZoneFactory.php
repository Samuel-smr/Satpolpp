<?php

namespace Database\Factories;

use App\Models\OperationalZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OperationalZone> */
class OperationalZoneFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->city(), 'code' => fake()->unique()->bothify('ZONA-###'), 'description' => fake()->sentence(), 'is_active' => true];
    }
}
