<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Report;
use App\Models\User;
use App\UserRole;
use App\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_displays_authenticated_admin_database_data(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Database',
            'username' => 'admin.database',
            'email' => 'admin.database@example.test',
        ]);
        Report::factory()->count(2)->create(['created_by_user_id' => $admin->id]);
        AuditLog::factory()->create(['actor_user_id' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('admin.profile'));

        $response->assertOk();
        $response->assertSeeText('Admin Database');
        $response->assertSeeText('admin.database');
        $response->assertSeeText('admin.database@example.test');
        $response->assertDontSeeText('Budi Santoso, S.STP');
        $response->assertViewHas('profileStats', [
            'reports_created' => 2,
            'activities_logged' => 1,
        ]);
    }

    public function test_admin_can_update_identity_without_changing_password(): void
    {
        $admin = User::factory()->create([
            'password' => 'password-lama',
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
        ]);
        $originalPassword = $admin->password;

        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'Nama Baru',
            'username' => 'username.baru',
            'email' => 'baru@example.test',
            'role' => UserRole::SuperAdmin->value,
            'status' => UserStatus::Blocked->value,
        ]);

        $response->assertRedirectToRoute('admin.profile');
        $response->assertSessionHas('status', 'Profil berhasil diperbarui.');
        $admin->refresh();
        $this->assertSame('Nama Baru', $admin->name);
        $this->assertSame('username.baru', $admin->username);
        $this->assertSame('baru@example.test', $admin->email);
        $this->assertSame($originalPassword, $admin->password);
        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertSame(UserStatus::Active, $admin->status);
        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $admin->id,
            'subject_id' => $admin->id,
            'module' => 'profiles',
            'action' => 'updated',
        ]);
    }

    public function test_password_change_requires_the_correct_current_password(): void
    {
        $admin = User::factory()->create(['password' => 'password-lama']);

        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'username' => $admin->username,
            'email' => $admin->email,
            'current_password' => 'password-salah',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ]);

        $response->assertInvalid(['current_password']);
        $this->assertTrue(Hash::check('password-lama', $admin->fresh()->password));
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_admin_can_change_password_with_confirmation(): void
    {
        $admin = User::factory()->create(['password' => 'password-lama']);

        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'username' => $admin->username,
            'email' => $admin->email,
            'current_password' => 'password-lama',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ]);

        $response->assertRedirectToRoute('admin.profile');
        $this->assertTrue(Hash::check('password-baru', $admin->fresh()->password));
        $auditLog = AuditLog::query()->sole();
        $this->assertTrue($auditLog->new_values['password_changed']);
        $this->assertArrayNotHasKey('password', $auditLog->new_values);
    }

    public function test_username_and_email_must_remain_unique(): void
    {
        $admin = User::factory()->create();
        $otherAdmin = User::factory()->create([
            'username' => 'sudah.dipakai',
            'email' => 'sudah@example.test',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'username' => $otherAdmin->username,
            'email' => $otherAdmin->email,
        ]);

        $response->assertInvalid(['username', 'email']);
        $this->assertNotSame($otherAdmin->username, $admin->fresh()->username);
    }
}
