<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de RBAC (Control de Acceso Basado en Roles).
 *
 * Verifica que:
 * - Admin puede hacer todo
 * - Manager puede crear/editar pero NO eliminar usuarios ni acceder a bitácora
 * - Viewer solo puede ver, NO crear/editar/eliminar nada
 * - Sin sesión → redirección al login
 */
class RbacTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    private function viewer(): User
    {
        return User::factory()->create(['role' => 'viewer']);
    }

    // ── Propiedades ───────────────────────────────────────────────

    public function test_viewer_cannot_create_property(): void
    {
        $this->actingAs($this->viewer())
            ->post(route('properties.store'), [
                'name'    => 'Nueva Propiedad',
                'type'    => 'commercial',
                'address' => 'Av. Test 123',
            ])->assertStatus(403);
    }

    public function test_viewer_cannot_delete_property(): void
    {
        $property = Property::factory()->create();

        $this->actingAs($this->viewer())
            ->delete(route('properties.destroy', $property))
            ->assertStatus(403);
    }

    public function test_manager_can_create_property(): void
    {
        $this->actingAs($this->manager())
            ->post(route('properties.store'), [
                'name'    => 'Propiedad Manager',
                'type'    => 'commercial',
                'address' => 'Av. Test 456',
            ])->assertRedirect(route('properties.index'));

        $this->assertDatabaseHas('properties', ['name' => 'Propiedad Manager']);
    }

    public function test_viewer_can_view_properties(): void
    {
        $this->actingAs($this->viewer())
            ->get(route('properties.index'))
            ->assertStatus(200);
    }

    // ── Inquilinos ────────────────────────────────────────────────

    public function test_viewer_cannot_create_tenant(): void
    {
        $this->actingAs($this->viewer())
            ->post(route('tenants.store'), ['full_name' => 'Test Inquilino'])
            ->assertStatus(403);
    }

    public function test_viewer_cannot_edit_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        $this->actingAs($this->viewer())
            ->put(route('tenants.update', $tenant), ['full_name' => 'Editado'])
            ->assertStatus(403);
    }

    public function test_viewer_cannot_delete_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        $this->actingAs($this->viewer())
            ->delete(route('tenants.destroy', $tenant))
            ->assertStatus(403);
    }

    public function test_manager_can_create_tenant(): void
    {
        $this->actingAs($this->manager())
            ->post(route('tenants.store'), [
                'full_name' => 'Juan Pérez Test',
                'email'     => 'juan@test.com',
            ])->assertRedirect(route('tenants.index'));

        $this->assertDatabaseHas('tenants', ['full_name' => 'Juan Pérez Test']);
    }

    // ── Usuarios ──────────────────────────────────────────────────

    public function test_viewer_cannot_access_users(): void
    {
        $this->actingAs($this->viewer())
            ->get(route('users.index'))
            ->assertStatus(403);
    }

    public function test_manager_cannot_access_users(): void
    {
        $this->actingAs($this->manager())
            ->get(route('users.index'))
            ->assertStatus(403);
    }

    public function test_admin_can_access_users(): void
    {
        $this->actingAs($this->admin())
            ->get(route('users.index'))
            ->assertStatus(200);
    }

    // ── Bitácora ──────────────────────────────────────────────────

    public function test_viewer_cannot_access_activity_log(): void
    {
        $this->actingAs($this->viewer())
            ->get(route('activity.index'))
            ->assertStatus(403);
    }

    public function test_manager_cannot_access_activity_log(): void
    {
        $this->actingAs($this->manager())
            ->get(route('activity.index'))
            ->assertStatus(403);
    }

    public function test_admin_can_access_activity_log(): void
    {
        $this->actingAs($this->admin())
            ->get(route('activity.index'))
            ->assertStatus(200);
    }

    // ── Sin sesión ────────────────────────────────────────────────

    public function test_guest_redirected_from_properties(): void
    {
        $this->get(route('properties.index'))->assertRedirect(route('login'));
    }

    public function test_guest_redirected_from_leases(): void
    {
        $this->get(route('leases.index'))->assertRedirect(route('login'));
    }

    public function test_guest_redirected_from_payments(): void
    {
        $this->get(route('payments.index'))->assertRedirect(route('login'));
    }

    // ── Filtro de propiedad ───────────────────────────────────────

    public function test_manager_restricted_to_assigned_property_cannot_see_other(): void
    {
        $allowedProperty = Property::factory()->create();
        $otherProperty   = Property::factory()->create();

        $manager = $this->manager();
        $manager->allowedProperties()->sync([$allowedProperty->id]);

        // Intenta ver unidades de la propiedad NO asignada
        $unit = Unit::factory()->create(['property_id' => $otherProperty->id]);

        $this->actingAs($manager)
            ->get(route('properties.show', $otherProperty))
            ->assertStatus(403);
    }

    public function test_manager_restricted_can_access_own_property(): void
    {
        $allowedProperty = Property::factory()->create();

        $manager = $this->manager();
        $manager->allowedProperties()->sync([$allowedProperty->id]);

        $this->actingAs($manager)
            ->get(route('properties.show', $allowedProperty))
            ->assertStatus(200);
    }
}
