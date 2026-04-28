<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Traits\FiltersByUserAccess;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeaseController extends Controller
{
    use FiltersByUserAccess, LogsActivity;

    public function index(Request $request)
    {
        $this->authorizePermission('leases.view');
        $filters = [
            'q'          => trim((string) $request->query('q', '')),
            'status'     => (string) $request->query('status', ''),
            'tenant_id'  => (string) $request->query('tenant_id', ''),
            'unit_id'    => (string) $request->query('unit_id', ''),
            'sort'       => (string) $request->query('sort', 'start_date'),
            'direction'  => (string) $request->query('direction', 'desc'),
            'per_page'   => (int) $request->query('per_page', 15),
        ];

        $perPageOptions = [15, 30, 50];
        if (! in_array($filters['per_page'], $perPageOptions, true)) {
            $filters['per_page'] = 15;
        }

        $sortable = [
            'start_date'     => 'start_date',
            'monthly_amount' => 'monthly_amount',
            'status'         => 'status',
            'created_at'     => 'created_at',
        ];
        if (! array_key_exists($filters['sort'], $sortable)) {
            $filters['sort'] = 'start_date';
        }

        if (! in_array($filters['direction'], ['asc', 'desc'], true)) {
            $filters['direction'] = 'desc';
        }

        $query = Lease::query()->with(['unit.property', 'tenant']);

        // ── Filtro de acceso por propiedad ──
        $this->applyLeasePropertyFilter($query);

        $query
            ->when($filters['q'] !== '', function ($builder) use ($filters) {
                $like = '%' . $filters['q'] . '%';
                $builder->where(function ($where) use ($like) {
                    $where
                        ->where('contract_number', 'like', $like)
                        ->orWhereHas('tenant', fn ($tenant) => $tenant->where('full_name', 'like', $like))
                        ->orWhereHas('unit', fn ($unit) => $unit->where('code', 'like', $like))
                        ->orWhereHas('unit.property', fn ($property) => $property->where('name', 'like', $like));
                });
            })
            ->when($filters['status'] !== '', fn ($builder) => $builder->where('status', $filters['status']))
            ->when($filters['tenant_id'] !== '', fn ($builder) => $builder->where('tenant_id', $filters['tenant_id']))
            ->when($filters['unit_id'] !== '', fn ($builder) => $builder->where('unit_id', $filters['unit_id']));

        $leases = $query
            ->orderBy($sortable[$filters['sort']], $filters['direction'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        $unitsQuery = Unit::with('property')->orderBy('code');
        $this->applyPropertyFilter($unitsQuery);
        $units = $unitsQuery->get();
        $tenants = Tenant::orderBy('full_name')->get();

        return view('leases.index', compact('leases', 'units', 'tenants', 'filters', 'perPageOptions'));
    }

    public function create()
    {
        $this->authorizePermission('leases.create');

        $unitsQuery = Unit::with(['property', 'leases' => fn($q) => $q->where('status', 'active')])->orderBy('code');
        $this->applyPropertyFilter($unitsQuery);
        $units = $unitsQuery->get();
        $tenants = Tenant::orderBy('full_name')->get();

        return view('leases.create', compact('units', 'tenants'));
    }

    public function store(Request $request)
    {
        $this->authorizePermission('leases.create');

        $data = $this->validateLease($request);

        if ($data['status'] === 'active') {
            $this->ensureUnitCanBeActivated((int) $data['unit_id']);
        }

        if ($request->hasFile('contract_pdf')) {
            $data['contract_pdf'] = $request->file('contract_pdf')->store('leases');
        }

        // Si no se especifica first_period_start, usar start_date
        if (empty($data['first_period_start'])) {
            $data['first_period_start'] = $data['start_date'];
        }

        $lease = Lease::create($data);
        $this->syncUnitStatus($lease->unit);

        $generated = 0;
        if ($lease->status === 'active' && $lease->end_date) {
            $generated = $this->generatePeriodPayments($lease, $request->boolean('mark_past_as_paid'));
        }

        $this->logActivity('created', 'lease', $lease->id, "Creó contrato #{$lease->contract_number} para {$lease->tenant?->full_name} — Unidad {$lease->unit?->code}");

        $message = 'Contrato creado.';
        if ($generated > 0) {
            $message .= " Se generaron {$generated} pagos por periodo automáticamente.";
        }

        return redirect()->route('leases.index')->with('success', $message);
    }

    public function show(Lease $lease)
    {
        $this->authorizePermission('leases.view');
        $this->authorizeProperty($lease->unit->property_id);

        $lease->load(['unit.property', 'tenant', 'payments' => function ($q) {
            $q->orderBy('period_number')->orderBy('type');
        }]);

        $this->logActivity('viewed', 'lease', $lease->id, "Consultó contrato #{$lease->contract_number} — {$lease->tenant?->full_name}");

        return view('leases.show', compact('lease'));
    }

    public function edit(Lease $lease)
    {
        $this->authorizePermission('leases.edit');
        $this->authorizeProperty($lease->unit->property_id);

        $unitsQuery = Unit::with(['property', 'leases' => fn($q) => $q->where('status', 'active')])->orderBy('code');
        $this->applyPropertyFilter($unitsQuery);
        $units = $unitsQuery->get();
        $tenants = Tenant::orderBy('full_name')->get();

        return view('leases.edit', compact('lease', 'units', 'tenants'));
    }

    public function update(Request $request, Lease $lease)
    {
        $this->authorizePermission('leases.edit');
        $this->authorizeProperty($lease->unit->property_id);

        $previousUnitId = $lease->unit_id;
        $data = $this->validateLease($request, $lease);

        if ($data['status'] === 'active') {
            $this->ensureUnitCanBeActivated((int) $data['unit_id'], $lease->id);
        }

        if ($request->hasFile('contract_pdf')) {
            $data['contract_pdf'] = $request->file('contract_pdf')->store('leases');
        }

        if (empty($data['first_period_start'])) {
            $data['first_period_start'] = $data['start_date'];
        }

        $lease->update($data);
        $lease->refresh();

        $this->logActivity('updated', 'lease', $lease->id, "Editó contrato #{$lease->contract_number} — {$lease->tenant?->full_name}");

        // Sincronizar montos de pagos futuros pendientes
        $this->syncFuturePayments($lease);

        if ($previousUnitId !== $lease->unit_id) {
            $oldUnit = Unit::find($previousUnitId);
            if ($oldUnit) {
                $this->syncUnitStatus($oldUnit);
            }
        }

        $this->syncUnitStatus($lease->unit);

        return redirect()->route('leases.index')->with('success', 'Contrato actualizado y pagos futuros sincronizados.');
    }

    private function syncFuturePayments(Lease $lease): void
    {
        $today = Carbon::today();

        // Solo pagos pendientes/vencidos cuyo periodo aún no ha terminado o que empiezan hoy o después
        $pendingPayments = $lease->payments()
            ->whereIn('status', ['pending', 'overdue'])
            ->where('period_end', '>=', $today->toDateString())
            ->get();

        foreach ($pendingPayments as $payment) {
            if ($payment->type === 'rent') {
                $subtotal = (float) $lease->monthly_amount;
                $total    = $subtotal * (1 + \App\Models\Payment::TAX_RATE);

                $payment->update([
                    'amount'     => $total,
                    'subtotal'   => $subtotal,
                    'tax_amount' => $total - $subtotal,
                ]);
            } elseif ($payment->type === 'maintenance') {
                $subtotal = (float) $lease->maintenance_amount;
                $total    = $subtotal * (1 + \App\Models\Payment::TAX_RATE);

                $payment->update([
                    'amount'     => $total,
                    'subtotal'   => $subtotal,
                    'tax_amount' => $total - $subtotal,
                ]);
            }
        }
    }

    public function renew(Lease $lease)
    {
        $this->authorizePermission('leases.create');
        $this->authorizeProperty($lease->unit->property_id);

        $unitsQuery = Unit::with('property')->orderBy('code');
        $this->applyPropertyFilter($unitsQuery);
        $units = $unitsQuery->get();
        $tenants = Tenant::orderBy('full_name')->get();

        // Sugerir fecha de inicio del primer periodo (día siguiente al fin del anterior)
        $suggestedStart = $lease->end_date ? $lease->end_date->addDay() : Carbon::today();

        // Sugerir folio con sufijo -R
        $suggestedFolio = $lease->contract_number ? $lease->contract_number . '-R' : '';

        return view('leases.renew', compact('lease', 'units', 'tenants', 'suggestedStart', 'suggestedFolio'));
    }

    public function storeRenewal(Request $request, Lease $lease)
    {
        $this->authorizePermission('leases.create');

        $data = $this->validateLease($request);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($data, $lease, $request) {
            // 1. Finalizar contrato anterior
            $lease->update(['status' => 'finished']);

            // 2. Crear nuevo contrato vinculándolo
            $data['previous_lease_id'] = $lease->id;

            if ($request->hasFile('contract_pdf')) {
                $data['contract_pdf'] = $request->file('contract_pdf')->store('leases');
            }

            if (empty($data['first_period_start'])) {
                $data['first_period_start'] = $data['start_date'];
            }

            $newLease = Lease::create($data);

            // 3. Generar pagos para el nuevo periodo
            $generated = 0;
            if ($newLease->status === 'active' && $newLease->end_date) {
                $generated = $this->generatePeriodPayments($newLease, $request->boolean('mark_past_as_paid'));
            }

            $this->logActivity('renewed', 'lease', $newLease->id, "Renovó contrato #{$lease->contract_number} → nuevo #{$newLease->contract_number} — {$newLease->tenant?->full_name}");

            return redirect()->route('leases.show', $newLease)->with('success', "Contrato renovado con éxito. Se generaron {$generated} nuevos pagos.");
        });
    }

    public function destroy(Lease $lease)
    {
        $this->authorizePermission('leases.delete');
        $this->authorizeProperty($lease->unit->property_id);

        $desc = "Eliminó contrato #{$lease->contract_number} — {$lease->tenant?->full_name}";
        $leaseId = $lease->id;

        $unit = $lease->unit;
        // Eliminar pagos asociados primero para evitar registros huérfanos
        $lease->payments()->delete();
        $lease->delete();
        $this->syncUnitStatus($unit);

        $this->logActivity('deleted', 'lease', $leaseId, $desc);

        return redirect()->route('leases.index')->with('success', 'Contrato y sus pagos eliminados.');
    }

    private function validateLease(Request $request, ?Lease $lease = null): array
    {
        return $request->validate([
            'unit_id'             => ['required', 'exists:units,id'],
            'tenant_id'           => ['required', 'exists:tenants,id'],
            'contract_number'     => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('leases', 'contract_number')->ignore($lease?->id),
            ],
            'start_date'          => ['required', 'date'],
            'end_date'            => ['nullable', 'date', 'after_or_equal:start_date'],
            'first_period_start'  => ['nullable', 'date'],
            'monthly_amount'      => ['required', 'numeric', 'min:0'],
            'maintenance_amount'  => ['required', 'numeric', 'min:0'],
            'deposit_amount'      => ['nullable', 'numeric', 'min:0'],
            'status'              => ['required', 'in:active,finished,cancelled'],
            'notes'               => ['nullable', 'string'],
        ]);
    }

    private function ensureUnitCanBeActivated(int $unitId, ?int $ignoreLeaseId = null): void
    {
        $query = Lease::where('unit_id', $unitId)->where('status', 'active');

        if ($ignoreLeaseId !== null) {
            $query->where('id', '!=', $ignoreLeaseId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'unit_id' => 'El local seleccionado ya tiene un contrato activo.',
            ]);
        }
    }

    private function syncUnitStatus(Unit $unit): void
    {
        $hasActiveLease = $unit->leases()->where('status', 'active')->exists();

        if ($hasActiveLease) {
            $unit->update(['status' => 'rented']);
            return;
        }

        if ($unit->status === 'rented') {
            $unit->update(['status' => 'available']);
        }
    }

    /**
     * Genera pagos por periodos consecutivos para el contrato.
     *
     * Cada periodo cubre un mes exacto:
     *   - Periodo 1: first_period_start  →  first_period_start + 1 mes - 1 día
     *   - Periodo 2: inicio_anterior + 1 mes  →  ...
     *
     * El due_date de cada pago = period_end (vence el último día del periodo).
     */
    private function generatePeriodPayments(Lease $lease, bool $markPastAsPaid = false): int
    {
        $today    = Carbon::today();
        $end      = Carbon::parse($lease->end_date);
        $amount   = (float) $lease->monthly_amount;
        $maintAmt = (float) $lease->maintenance_amount;
        $count    = 0;

        // Primer inicio de periodo (o start_date si no se especificó)
        $periodStart = Carbon::parse($lease->first_period_start ?? $lease->start_date);

        // Calcular total de periodos del contrato
        $totalPeriods = $this->countTotalPeriods($periodStart, $end);

        $periodNumber = 1;

        while ($periodStart->lte($end)) {
            // El fin del periodo es exactamente 1 mes después - 1 día
            $periodEnd = $periodStart->copy()->addMonth()->subDay();

            // Si el fin del periodo supera el fin del contrato, recortarlo
            if ($periodEnd->gt($end)) {
                $periodEnd = $end->copy();
            }

            // El pago vence el último día del periodo
            $dueDate = $periodEnd->copy();

            // Etiqueta legible: "20 Abr – 19 May (1/12)"
            $periodLabel = $periodStart->locale('es')->isoFormat('D MMM') . ' – ' .
                           $periodEnd->locale('es')->isoFormat('D MMM YYYY') .
                           " ({$periodNumber}/{$totalPeriods})";

            // ---- RENTA ----
            if ($amount > 0) {
                $rentExists = Payment::where('lease_id', $lease->id)
                    ->where('type', 'rent')
                    ->where('period_number', $periodNumber)
                    ->exists();

                if (! $rentExists) {
                    $isPast      = $dueDate->lt($today);
                    $statusRenta = $isPast ? 'overdue' : 'pending';
                    $paidAtRenta = null;

                    if ($markPastAsPaid && $isPast) {
                        $statusRenta = 'paid';
                        $paidAtRenta = $dueDate->toDateString();
                    }

                    $totalRenta = $amount * (1 + Payment::TAX_RATE);

                    Payment::create([
                        'lease_id'      => $lease->id,
                        'type'          => 'rent',
                        'period_label'  => $periodLabel,
                        'period_start'  => $periodStart->toDateString(),
                        'period_end'    => $periodEnd->toDateString(),
                        'period_number' => $periodNumber,
                        'total_periods' => $totalPeriods,
                        'due_date'      => $dueDate->toDateString(),
                        'amount'        => $totalRenta,
                        'subtotal'      => $amount,
                        'tax_amount'    => $totalRenta - $amount,
                        'status'        => $statusRenta,
                        'paid_at'       => $paidAtRenta,
                        'paid_amount'   => $statusRenta === 'paid' ? $totalRenta : null,
                    ]);
                    $count++;
                }
            }

            // ---- MANTENIMIENTO ----
            if ($maintAmt > 0) {
                $maintExists = Payment::where('lease_id', $lease->id)
                    ->where('type', 'maintenance')
                    ->where('period_number', $periodNumber)
                    ->exists();

                if (! $maintExists) {
                    $isPast      = $dueDate->lt($today);
                    $statusMaint = $isPast ? 'overdue' : 'pending';
                    $paidAtMaint = null;

                    if ($markPastAsPaid && $isPast) {
                        $statusMaint = 'paid';
                        $paidAtMaint = $dueDate->toDateString();
                    }

                    $totalMaint = $maintAmt * (1 + Payment::TAX_RATE);

                    Payment::create([
                        'lease_id'      => $lease->id,
                        'type'          => 'maintenance',
                        'period_label'  => $periodLabel,
                        'period_start'  => $periodStart->toDateString(),
                        'period_end'    => $periodEnd->toDateString(),
                        'period_number' => $periodNumber,
                        'total_periods' => $totalPeriods,
                        'due_date'      => $dueDate->toDateString(),
                        'amount'        => $totalMaint,
                        'subtotal'      => $maintAmt,
                        'tax_amount'    => $totalMaint - $maintAmt,
                        'status'        => $statusMaint,
                        'paid_at'       => $paidAtMaint,
                        'paid_amount'   => $statusMaint === 'paid' ? $totalMaint : null,
                    ]);
                    $count++;
                }
            }

            // Avanzar al siguiente periodo
            $periodStart = $periodStart->addMonth();
            $periodNumber++;
        }

        return $count;
    }

    /**
     * Cuenta cuántos periodos mensuales caben desde $start hasta $end.
     */
    private function countTotalPeriods(Carbon $start, Carbon $end): int
    {
        $count  = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $count++;
            $cursor->addMonth();
        }
        return $count;
    }
}
