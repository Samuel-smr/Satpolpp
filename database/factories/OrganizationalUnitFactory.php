<?php

namespace Database\Factories;

use App\Models\OperationalZone;
use App\Models\OrganizationalUnit;
use App\OrganizationalUnitType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrganizationalUnit> */
class OrganizationalUnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'operational_zone_id' => OperationalZone::factory(),
            'leader_officer_id' => null,
            'name' => fake()->unique()->words(3, true),
            'code' => fake()->unique()->bothify('UNIT-###'),
            'type' => OrganizationalUnitType::Team,
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
