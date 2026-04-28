<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas unitarias del sistema de permisos del modelo User.
 *
 * Estas pruebas NO hacen requests HTTP — solo prueban la lógica
 * del método hasPermission() directamente.
 */
class UserPermissionsTest extends TestCase
{
    use RefreshDatabase;

    // ── Admin bypass ──────────────────────────────────────────────

    public function test_admin_has_all_permissions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->hasPermission('properties.view'));
        $this->assertTrue($admin->hasPermission('properties.delete'));
        $this->assertTrue($admin->hasPermission('users.create'));
        $this->assertTrue($admin->hasPermission('leases.delete'));
        $this->assertTrue($admin->hasPermission('payments.edit'));
    }

    // ── Manager defaults ──────────────────────────────────────────

    public function test_manager_has_view_permissions(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $this->assertTrue($manager->hasPermission('properties.view'));
        $this->assertTrue($manager->hasPermission('tenants.view'));
        $this->assertTrue($manager->hasPermission('leases.view'));
        $this->assertTrue($manager->hasPermission('payments.view'));
        $this->assertTrue($manager->hasPermission('reports.view'));
    }

    public function test_manager_has_create_permissions(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $this->assertTrue($manager->hasPermission('tenants.create'));
        $this->assertTrue($manager->hasPermission('leases.create'));
        $this->assertTrue($manager->hasPermission('payments.create'));
    }

    public function test_manager_cannot_manage_users(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $this->assertFalse($manager->hasPermission('users.view'));
        $this->assertFalse($manager->hasPermission('users.create'));
        $this->assertFalse($manager->hasPermission('users.delete'));
    }

    // ── Viewer defaults ───────────────────────────────────────────

    public function test_viewer_only_has_view_permissions(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->assertTrue($viewer->hasPermission('properties.view'));
        $this->assertTrue($viewer->hasPermission('payments.view'));
        $this->assertTrue($viewer->hasPermission('leases.view'));
    }

    public function test_viewer_has_no_write_permissions(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->assertFalse($viewer->hasPermission('properties.create'));
        $this->assertFalse($viewer->hasPermission('properties.edit'));
        $this->assertFalse($viewer->hasPermission('properties.delete'));
        $this->assertFalse($viewer->hasPermission('tenants.create'));
        $this->assertFalse($viewer->hasPermission('leases.create'));
        $this->assertFalse($viewer->hasPermission('payments.create'));
        $this->assertFalse($viewer->hasPermission('users.view'));
    }

    // ── Property access ───────────────────────────────────────────

    public function test_admin_can_access_any_property(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->canAccessProperty(1));
        $this->assertTrue($admin->canAccessProperty(999));
    }

    public function test_manager_without_restrictions_can_access_any_property(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        // Sin allowedProperties → acceso total
        $this->assertTrue($manager->canAccessProperty(1));
    }

    public function test_allowedPropertyIds_returns_null_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertNull($admin->allowedPropertyIds());
    }

    public function test_allowedPropertyIds_returns_null_when_no_restrictions(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $this->assertNull($manager->allowedPropertyIds());
    }

    // ── Role helpers ──────────────────────────────────────────────

    public function test_isAdmin_returns_correct_values(): void
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        $manager = User::factory()->create(['role' => 'manager']);
        $viewer  = User::factory()->create(['role' => 'viewer']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($manager->isAdmin());
        $this->assertFalse($viewer->isAdmin());
    }

    public function test_isManager_returns_correct_values(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $viewer  = User::factory()->create(['role' => 'viewer']);

        $this->assertTrue($manager->isManager());
        $this->assertFalse($viewer->isManager());
    }

    public function test_isViewer_returns_correct_values(): void
    {
        $viewer  = User::factory()->create(['role' => 'viewer']);
        $manager = User::factory()->create(['role' => 'manager']);

        $this->assertTrue($viewer->isViewer());
        $this->assertFalse($manager->isViewer());
    }
}
