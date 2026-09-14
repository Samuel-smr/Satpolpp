<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_feedback_page_uses_database_feedback(): void
    {
        $admin = User::factory()->create();
        $report = Report::factory()->create([
            'status' => ReportStatus::Completed,
            'feedback_rating' => 5,
            'feedback_comment' => 'Pelayanan sangat cepat.',
            'feedback_submitted_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.feedback'));

        $response->assertOk()
            ->assertSee($report->code)
            ->assertSee('Pelayanan sangat cepat.')
            ->assertSee('Database')
            ->assertDontSee('Data Dummy');
    }

    public function test_guest_can_submit_feedback_once_for_completed_report(): void
    {
        $report = Report::factory()->create([
            'status' => ReportStatus::Completed,
            'completed_at' => now(),
        ]);
        $token = 'guest-feedback-token';
        $report->guestAccess->update(['token_hash' => hash('sha256', $token)]);

        $response = $this->withCookie('sipraja_guest_token', $token)
            ->post(route('pelapor.feedback.store', $report), [
                'rating' => 4,
                'comment' => 'Cukup baik.',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'feedback_rating' => 4,
            'feedback_comment' => 'Cukup baik.',
        ]);
    }
}
