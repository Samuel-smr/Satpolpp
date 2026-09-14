<?php

namespace Database\Factories;

use App\Models\GuestAccess;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GuestAccess>
 */
class GuestAccessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token_hash' => hash('sha256', Str::random(64)),
            'last_used_at' => now(),
            'expires_at' => now()->addYear(),
        ];
    }
}
