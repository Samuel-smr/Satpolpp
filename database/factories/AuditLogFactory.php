<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_user_id' => User::factory(),
            'subject_type' => 'report',
            'subject_id' => Report::factory(),
            'module' => 'reports',
            'action' => 'status_updated',
            'description' => fake()->sentence(),
            'old_values' => ['status' => 'submitted'],
            'new_values' => ['status' => 'received'],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => now(),
        ];
    }
}
