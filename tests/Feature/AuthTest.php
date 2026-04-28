<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de autenticación y acceso base al sistema.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ── Login ─────────────────────────────────────────────────────

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_login_page_loads(): void
    {
        $this->get(route('login'))->assertStatus(200);
    }

    public function test_admin_can_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->post(route('login.submit'), [
            'email'    => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_wrong_password_rejected(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);

        $this->post(route('login.submit'), [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_from_login(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    // ── Dashboard access by role ──────────────────────────────────

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('dashboard'))->assertStatus(200);
    }

    public function test_manager_can_access_dashboard(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $this->actingAs($manager)->get(route('dashboard'))->assertStatus(200);
    }

    public function test_viewer_can_access_dashboard(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $this->actingAs($viewer)->get(route('dashboard'))->assertStatus(200);
    }
}
