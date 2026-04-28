<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas del módulo de Bitácora de Actividad.
 */
class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    // ── Acceso ────────────────────────────────────────────────────

    public function test_only_admin_can_view_activity_log(): void
    {
        $admin   = $this->admin();
        $manager = User::factory()->create(['role' => 'manager']);
        $viewer  = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($admin)->get(route('activity.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('activity.index'))->assertStatus(403);
        $this->actingAs($viewer)->get(route('activity.index'))->assertStatus(403);
        $this->get(route('activity.index'))->assertRedirect(route('login'));
    }

    // ── Se registra la bitácora al crear un inquilino ─────────────

    public function test_creating_tenant_logs_activity(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('tenants.store'), [
            'full_name' => 'María López',
            'email'     => 'maria@test.com',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action'  => 'created',
            'module'  => 'tenant',
        ]);
    }

    // ── Se registra al eliminar ───────────────────────────────────

    public function test_deleting_tenant_logs_activity(): void
    {
        $admin  = $this->admin();
        $tenant = Tenant::factory()->create(['full_name' => 'Inquilino A Borrar']);

        $this->actingAs($admin)->delete(route('tenants.destroy', $tenant));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action'  => 'deleted',
            'module'  => 'tenant',
        ]);
    }

    // ── Login registra bitácora ───────────────────────────────────

    public function test_login_logs_activity(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->post(route('login.submit'), [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action'  => 'login',
            'module'  => 'auth',
        ]);
    }

    // ── Filtros de la bitácora funcionan ─────────────────────────

    public function test_activity_log_can_filter_by_module(): void
    {
        ActivityLog::create([
            'user_id'     => null,
            'user_name'   => 'Sistema',
            'user_role'   => 'admin',
            'action'      => 'created',
            'module'      => 'tenant',
            'description' => 'Test tenant log',
            'created_at'  => now(),
        ]);
        ActivityLog::create([
            'user_id'     => null,
            'user_name'   => 'Sistema',
            'user_role'   => 'admin',
            'action'      => 'created',
            'module'      => 'lease',
            'description' => 'Test lease log',
            'created_at'  => now(),
        ]);

        $admin = $this->admin();

        // Filtrar solo el módulo tenant
        $response = $this->actingAs($admin)
            ->get(route('activity.index', ['module' => 'tenant']));

        $response->assertStatus(200);
        $response->assertSee('Test tenant log');
        $response->assertDontSee('Test lease log');
    }

    // ── Datos correctos guardados ─────────────────────────────────

    public function test_activity_log_stores_user_name_and_role(): void
    {
        $admin = User::factory()->create([
            'name' => 'Carlos Admin',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)->post(route('tenants.store'), [
            'full_name' => 'Inquilino Log Test',
        ]);

        $log = ActivityLog::where('action', 'created')
            ->where('module', 'tenant')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('Carlos Admin', $log->user_name);
        $this->assertEquals('admin', $log->user_role);
        $this->assertEquals($admin->id, $log->user_id);
    }
}
