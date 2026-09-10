<?php

namespace Database\Factories;

use App\AssetCategory;
use App\AssetCondition;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
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
            'asset_code' => fake()->unique()->bothify('AST-###??'),
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement(AssetCategory::cases()),
            'condition' => fake()->randomElement(AssetCondition::cases()),
            'location' => fake()->city(),
            'acquisition_year' => fake()->numberBetween(2015, 2026),
            'tax_due_date' => null,
            'next_oil_service_km' => null,
        ];
    }
}
