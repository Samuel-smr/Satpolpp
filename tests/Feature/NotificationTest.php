<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notificationId = $this->createNotification($user);

        $this->actingAs($user)
            ->post(route('notifications.read', $notificationId))
            ->assertRedirect('/admin/dashboard');

        $this->assertDatabaseMissing('notifications', [
            'id' => $notificationId,
            'read_at' => null,
        ]);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $notificationId = $this->createNotification($owner);

        $this->actingAs($otherUser)
            ->post(route('notifications.read', $notificationId))
            ->assertNotFound();

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationId,
            'read_at' => null,
        ]);
    }

    public function test_user_can_poll_only_their_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $notificationId = $this->createNotification($user);
        $this->createNotification($otherUser);

        $this->actingAs($user)
            ->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.id', $notificationId)
            ->assertJsonPath('notifications.0.title', 'Laporan baru')
            ->assertJsonPath('notifications.0.unread', true);
    }

    private function createNotification(User $user): string
    {
        $notificationId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'title' => 'Laporan baru',
                'message' => 'Ada laporan baru.',
                'url' => '/admin/dashboard',
            ], JSON_THROW_ON_ERROR),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $notificationId;
    }
}
