<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\ReportStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportStatusHistory>
 */
class ReportStatusHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'changed_by_user_id' => User::factory(),
            'status' => ReportStatus::Received,
            'note' => fake()->sentence(),
            'created_at' => now(),
        ];
    }
}
