<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas del módulo de Contratos (Leases).
 */
class LeaseTest extends TestCase
{
    use RefreshDatabase;

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

    private function makeLeaseData(?int $unitId = null, ?int $tenantId = null): array
    {
        $unit   = $unitId   ? Unit::find($unitId)   : Unit::factory()->create();
        $tenant = $tenantId ? Tenant::find($tenantId) : Tenant::factory()->create();

        return [
            'unit_id'            => $unit->id,
            'tenant_id'          => $tenant->id,
            'contract_number'    => 'TEST-' . rand(1000, 9999),
            'start_date'         => now()->toDateString(),
            'end_date'           => now()->addYear()->toDateString(),
            'first_period_start' => now()->toDateString(),
            'monthly_amount'     => 10000,
            'maintenance_amount' => 1000,
            'status'             => 'active',
        ];
    }

    // ── Index ─────────────────────────────────────────────────────

    public function test_admin_can_view_leases_index(): void
    {
        $this->actingAs($this->admin())
            ->get(route('leases.index'))
            ->assertStatus(200);
    }

    public function test_viewer_can_view_leases_index(): void
    {
        $this->actingAs($this->viewer())
            ->get(route('leases.index'))
            ->assertStatus(200);
    }

    // ── Create / Store ────────────────────────────────────────────

    public function test_admin_can_create_lease(): void
    {
        $data = $this->makeLeaseData();

        $this->actingAs($this->admin())
            ->post(route('leases.store'), $data)
            ->assertRedirect(route('leases.index'));

        $this->assertDatabaseHas('leases', [
            'contract_number' => $data['contract_number'],
            'status'          => 'active',
        ]);
    }

    public function test_manager_can_create_lease(): void
    {
        $data = $this->makeLeaseData();

        $this->actingAs($this->manager())
            ->post(route('leases.store'), $data)
            ->assertRedirect(route('leases.index'));

        $this->assertDatabaseHas('leases', ['contract_number' => $data['contract_number']]);
    }

    public function test_viewer_cannot_create_lease(): void
    {
        $data = $this->makeLeaseData();

        $this->actingAs($this->viewer())
            ->post(route('leases.store'), $data)
            ->assertStatus(403);
    }

    // ── Duplicate contract number ─────────────────────────────────

    public function test_duplicate_contract_number_rejected(): void
    {
        $existing = Lease::factory()->create(['contract_number' => 'CONT-DUPE']);
        $data = $this->makeLeaseData();
        $data['contract_number'] = 'CONT-DUPE';

        $this->actingAs($this->admin())
            ->post(route('leases.store'), $data)
            ->assertSessionHasErrors('contract_number');
    }

    // ── Show ──────────────────────────────────────────────────────

    public function test_admin_can_view_lease_detail(): void
    {
        $lease = Lease::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('leases.show', $lease))
            ->assertStatus(200);
    }

    // ── Update ────────────────────────────────────────────────────

    public function test_admin_can_update_lease(): void
    {
        $lease = Lease::factory()->create();
        $data  = $this->makeLeaseData($lease->unit_id, $lease->tenant_id);
        $data['notes'] = 'Nota actualizada desde test';

        $this->actingAs($this->admin())
            ->put(route('leases.update', $lease), $data)
            ->assertRedirect(route('leases.index'));

        $this->assertDatabaseHas('leases', [
            'id'    => $lease->id,
            'notes' => 'Nota actualizada desde test',
        ]);
    }

    public function test_viewer_cannot_update_lease(): void
    {
        $lease = Lease::factory()->create();
        $data  = $this->makeLeaseData($lease->unit_id, $lease->tenant_id);

        $this->actingAs($this->viewer())
            ->put(route('leases.update', $lease), $data)
            ->assertStatus(403);
    }

    // ── Delete ────────────────────────────────────────────────────

    public function test_admin_can_delete_lease(): void
    {
        $lease = Lease::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('leases.destroy', $lease))
            ->assertRedirect(route('leases.index'));

        $this->assertDatabaseMissing('leases', ['id' => $lease->id]);
    }

    public function test_viewer_cannot_delete_lease(): void
    {
        $lease = Lease::factory()->create();

        $this->actingAs($this->viewer())
            ->delete(route('leases.destroy', $lease))
            ->assertStatus(403);

        $this->assertDatabaseHas('leases', ['id' => $lease->id]);
    }

    public function test_delete_lease_also_deletes_payments(): void
    {
        $lease = Lease::factory()->create();
        $lease->payments()->createMany([
            ['type' => 'rent', 'due_date' => now()->toDateString(), 'amount' => 1000, 'status' => 'pending'],
        ]);

        $this->assertDatabaseHas('payments', ['lease_id' => $lease->id]);

        $this->actingAs($this->admin())
            ->delete(route('leases.destroy', $lease));

        $this->assertDatabaseMissing('payments', ['lease_id' => $lease->id]);
    }

    // ── Cannot activate unit with existing active lease ───────────

    public function test_cannot_create_second_active_lease_on_same_unit(): void
    {
        $unit   = Unit::factory()->create();
        $tenant = Tenant::factory()->create();

        // Primer contrato activo
        Lease::factory()->create([
            'unit_id'   => $unit->id,
            'tenant_id' => $tenant->id,
            'status'    => 'active',
        ]);

        // Intento de segundo contrato activo en la misma unidad
        $data = $this->makeLeaseData($unit->id);
        $data['unit_id'] = $unit->id;

        $this->actingAs($this->admin())
            ->post(route('leases.store'), $data)
            ->assertSessionHasErrors('unit_id');
    }
}
