<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_user_page_reads_admin_accounts_from_database(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(['name' => 'Super Database']);
        User::factory()->create(['name' => 'Admin Database']);

        $response = $this->actingAs($superAdmin)->get(route('super_admin.user'));

        $response->assertOk()
            ->assertSee('Super Database')
            ->assertSee('Admin Database')
            ->assertSee('Database')
            ->assertSee('placeholder="Minimal 8 karakter"', false)
            ->assertSee('minlength="8"', false)
            ->assertDontSee('Budi Hartono');
    }

    public function test_super_admin_can_create_account_and_cannot_delete_current_account(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post(route('super_admin.user.store'), [
            'name' => 'Admin Baru',
            'username' => 'admin_baru',
            'email' => 'admin.baru@example.test',
            'password' => 'password-baru',
        ])->assertRedirect(route('super_admin.user'));

        $this->assertDatabaseHas('users', ['username' => 'admin_baru', 'role' => 'admin']);
        $this->actingAs($superAdmin)->deleteJson(route('super_admin.user.destroy', $superAdmin))
            ->assertUnprocessable();
    }

    public function test_super_admin_can_update_toggle_and_delete_another_account(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->create();

        $this->actingAs($superAdmin)->put(route('super_admin.user.update', $admin), [
            'name' => 'Admin Diperbarui',
            'username' => 'admin_diperbarui',
            'email' => 'diperbarui@example.test',
            'password' => '',
        ])->assertRedirect(route('super_admin.user'));

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'username' => 'admin_diperbarui']);

        $this->actingAs($superAdmin)->patchJson(route('super_admin.user.status', $admin))
            ->assertOk()->assertJsonPath('status', 'blocked');

        $this->actingAs($superAdmin)->deleteJson(route('super_admin.user.destroy', $admin))->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_user_validation_rejects_duplicate_identity_invalid_email_and_short_password(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        User::factory()->create(['username' => 'sudah_ada', 'email' => 'sudah@example.test']);

        $this->actingAs($superAdmin)->post(route('super_admin.user.store'), [
            'name' => 'Admin Invalid',
            'username' => 'sudah_ada',
            'email' => 'bukan-email',
            'password' => 'pendek',
        ])->assertSessionHasErrors(['username', 'email', 'password']);
    }
}
