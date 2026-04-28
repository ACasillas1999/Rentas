<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas del módulo de Pagos.
 */
class PaymentTest extends TestCase
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

    private function createLeaseWithPayment(string $status = 'pending'): array
    {
        $property = Property::factory()->create();
        $unit     = Unit::factory()->create(['property_id' => $property->id]);
        $tenant   = Tenant::factory()->create();
        $lease    = Lease::factory()->create([
            'unit_id'   => $unit->id,
            'tenant_id' => $tenant->id,
        ]);
        $payment = Payment::factory()->create([
            'lease_id' => $lease->id,
            'status'   => $status,
        ]);

        return compact('property', 'unit', 'tenant', 'lease', 'payment');
    }

    // ── Index ─────────────────────────────────────────────────────

    public function test_admin_can_view_payments_index(): void
    {
        $this->actingAs($this->admin())
            ->get(route('payments.index'))
            ->assertStatus(200);
    }

    public function test_viewer_can_view_payments_index(): void
    {
        $this->actingAs($this->viewer())
            ->get(route('payments.index'))
            ->assertStatus(200);
    }

    // ── Create / Store ────────────────────────────────────────────

    public function test_admin_can_create_payment(): void
    {
        ['lease' => $lease] = $this->createLeaseWithPayment();

        $this->actingAs($this->admin())
            ->post(route('payments.store'), [
                'lease_id'  => $lease->id,
                'type'      => 'rent',
                'due_date'  => now()->endOfMonth()->toDateString(),
                'amount'    => 10000,
                'status'    => 'pending',
            ])->assertRedirect(route('payments.index'));

        $this->assertDatabaseHas('payments', [
            'lease_id' => $lease->id,
            'status'   => 'pending',
        ]);
    }

    public function test_viewer_cannot_create_payment(): void
    {
        ['lease' => $lease] = $this->createLeaseWithPayment();

        $this->actingAs($this->viewer())
            ->post(route('payments.store'), [
                'lease_id' => $lease->id,
                'type'     => 'rent',
                'due_date' => now()->toDateString(),
                'amount'   => 5000,
                'status'   => 'pending',
            ])->assertStatus(403);
    }

    // ── Show ──────────────────────────────────────────────────────

    public function test_admin_can_view_payment_detail(): void
    {
        ['payment' => $payment] = $this->createLeaseWithPayment();

        $this->actingAs($this->admin())
            ->get(route('payments.show', $payment))
            ->assertStatus(200);
    }

    public function test_viewer_can_view_payment_detail(): void
    {
        ['payment' => $payment] = $this->createLeaseWithPayment();

        $this->actingAs($this->viewer())
            ->get(route('payments.show', $payment))
            ->assertStatus(200);
    }

    // ── Mark Paid ─────────────────────────────────────────────────

    public function test_admin_can_mark_payment_as_paid(): void
    {
        ['payment' => $payment] = $this->createLeaseWithPayment('pending');

        $this->actingAs($this->admin())
            ->post(route('payments.markPaid', $payment), [
                'paid_amount' => 11600,
                'paid_at'     => now()->toDateString(),
            ])->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'id'     => $payment->id,
            'status' => 'paid',
        ]);
    }

    public function test_viewer_cannot_mark_payment_as_paid(): void
    {
        ['payment' => $payment] = $this->createLeaseWithPayment('pending');

        $this->actingAs($this->viewer())
            ->post(route('payments.markPaid', $payment), [
                'paid_amount' => 11600,
            ])->assertStatus(403);
    }

    public function test_partial_payment_sets_partial_status(): void
    {
        ['payment' => $payment] = $this->createLeaseWithPayment('pending');
        $payment->update(['amount' => 10000]);

        $this->actingAs($this->admin())
            ->post(route('payments.markPaid', $payment), [
                'paid_amount' => 5000, // Menos que el total
                'paid_at'     => now()->toDateString(),
            ])->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'id'     => $payment->id,
            'status' => 'partial',
        ]);
    }

    // ── Delete ────────────────────────────────────────────────────

    public function test_admin_can_delete_payment(): void
    {
        ['payment' => $payment] = $this->createLeaseWithPayment();

        $this->actingAs($this->admin())
            ->delete(route('payments.destroy', $payment))
            ->assertRedirect(route('payments.index'));

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    public function test_viewer_cannot_delete_payment(): void
    {
        ['payment' => $payment] = $this->createLeaseWithPayment();

        $this->actingAs($this->viewer())
            ->delete(route('payments.destroy', $payment))
            ->assertStatus(403);

        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
    }

    public function test_manager_cannot_delete_payment(): void
    {
        ['payment' => $payment] = $this->createLeaseWithPayment();

        $this->actingAs($this->manager())
            ->delete(route('payments.destroy', $payment))
            ->assertStatus(403);
    }
}
