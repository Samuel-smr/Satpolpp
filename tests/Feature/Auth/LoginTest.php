<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_username_and_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['username' => 'admin.login']);

        $response = $this->post(route('login.store'), [
            'identifier' => 'admin.login',
            'password' => 'password',
        ]);

        $response->assertRedirectToRoute('admin.dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_super_admin_can_login_with_email_and_is_redirected_to_super_admin_dashboard(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(['email' => 'superadmin@example.test']);

        $response = $this->post(route('login.store'), [
            'identifier' => 'superadmin@example.test',
            'password' => 'password',
        ]);

        $response->assertRedirectToRoute('super_admin.dashboard');
        $this->assertAuthenticatedAs($superAdmin);
    }

    public function test_blocked_user_cannot_login(): void
    {
        User::factory()->blocked()->create(['username' => 'blocked.user']);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'identifier' => 'blocked.user',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['identifier' => 'Email/Username atau Kata sandi tidak cocok.']);
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        RateLimiter::clear('limited.user|127.0.0.1');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'identifier' => 'limited.user',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('login.store'), [
            'identifier' => 'limited.user',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('identifier');
        $this->assertStringContainsString('Terlalu banyak percobaan login', session('errors')->first('identifier'));
        $this->assertGuest();
    }

    public function test_guests_are_redirected_from_protected_dashboards(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirectToRoute('login');
        $this->get(route('super_admin.dashboard'))->assertRedirectToRoute('login');
    }

    public function test_each_account_role_is_forbidden_from_the_other_dashboard(): void
    {
        $admin = User::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->get(route('super_admin.dashboard'))->assertForbidden();
        $this->actingAs($superAdmin)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('logout'));

        $response->assertRedirectToRoute('pelapor.index');
        $this->assertGuest();
    }
}
