<?php

namespace Database\Factories;

use App\Models\GuestAccess;
use App\Models\Report;
use App\Models\User;
use App\ReportCategory;
use App\ReportSource;
use App\ReportSourcePlatform;
use App\ReportStatus;
use App\ReportType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('SPJ-######'),
            'guest_access_id' => GuestAccess::factory(),
            'created_by_user_id' => null,
            'type' => ReportType::Report,
            'category' => fake()->randomElement(ReportCategory::cases()),
            'source' => ReportSource::Online,
            'source_platform' => ReportSourcePlatform::Website,
            'reporter_name' => null,
            'reporter_phone' => fake()->numerify('08##########'),
            'description' => fake()->sentence(),
            'location_address' => fake()->address(),
            'latitude' => fake()->latitude(-7.95, -7.60),
            'longitude' => fake()->longitude(110.00, 110.35),
            'status' => ReportStatus::Submitted,
            'completed_at' => null,
            'feedback_rating' => null,
            'feedback_comment' => null,
            'feedback_submitted_at' => null,
        ];
    }

    public function recordedBy(
        User $user,
        ReportSource $source = ReportSource::Offline,
        ?ReportSourcePlatform $platform = null,
    ): static {
        return $this->state(fn (array $attributes): array => [
            'guest_access_id' => null,
            'created_by_user_id' => $user,
            'source' => $source,
            'source_platform' => $source === ReportSource::Online ? $platform : null,
            'reporter_name' => fake()->name(),
        ]);
    }

    public function assignedTo(User $admin): static
    {
        return $this->state(fn (array $attributes): array => ['assigned_admin_id' => $admin->id]);
    }
}
