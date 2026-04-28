<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Tenant;
use App\Traits\FiltersByUserAccess;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use FiltersByUserAccess, LogsActivity;

    public function index(Request $request)
    {
        $this->authorizePermission('payments.view');

        $filters = $this->indexFilters($request);

        $perPageOptions = [15, 30, 50];
        if (! in_array($filters['per_page'], $perPageOptions, true)) {
            $filters['per_page'] = 15;
        }

        $sortable = [
            'due_date' => 'due_date',
            'amount' => 'amount',
            'status' => 'status',
            'created_at' => 'created_at',
        ];
        if (! array_key_exists($filters['sort'], $sortable)) {
            $filters['sort'] = 'due_date';
        }

        if (! in_array($filters['direction'], ['asc', 'desc'], true)) {
            $filters['direction'] = 'desc';
        }

        $query = $this->paymentIndexQuery($filters)->with(['lease.unit.property', 'lease.tenant']);

        // ── Filtro de acceso por propiedad ──
        $this->applyPaymentPropertyFilter($query);

        $payments = $query
            ->orderBy($sortable[$filters['sort']], $filters['direction'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        $leases = Lease::with(['unit.property', 'tenant'])->orderByDesc('start_date')->get();
        $tenants = $this->availableTenants($filters);

        return view('payments.index', compact('payments', 'leases', 'tenants', 'filters', 'perPageOptions'));
    }

    public function tenantOptions(Request $request)
    {
        $filters = $this->indexFilters($request);
        $tenants = $this->availableTenants($filters);

        return response()->json([
            'tenants' => $tenants
                ->map(fn (Tenant $tenant) => [
                    'id' => $tenant->id,
                    'full_name' => $tenant->full_name,
                ])
                ->values(),
        ]);
    }

    public function create()
    {
        $this->authorizePermission('payments.create');

        $leases = Lease::with(['unit.property', 'tenant'])->orderByDesc('start_date');
        $this->applyLeasePropertyFilter($leases);

        return view('payments.create', compact('leases'));
    }

    public function store(Request $request)
    {
        $this->authorizePermission('payments.create');

        $data = $this->validatePayment($request);
        $data = $this->normalizePaymentFields($data);

        $payment = Payment::create($data);

        $tenant = $payment->lease?->tenant?->full_name ?? '-';
        $this->logActivity('created', 'payment', $payment->id, "Creó pago #{$payment->id} ({$payment->period_label}) — {$tenant}");

        return redirect()->route('payments.index')->with('success', 'Pago registrado.');
    }

    public function show(Payment $payment)
    {
        $this->authorizePermission('payments.view');

        $payment->load('lease.unit.property', 'lease.tenant');

        $tenant = $payment->lease?->tenant?->full_name ?? '-';
        $this->logActivity('viewed', 'payment', $payment->id, "Consultó pago #{$payment->id} ({$payment->period_label}) — {$tenant}");

        // Todos los pagos del mismo contrato para el navegador de periodos
        $siblingPayments = Payment::where('lease_id', $payment->lease_id)
            ->orderBy('due_date')
            ->get(['id', 'period_label', 'period_number', 'total_periods', 'due_date', 'status', 'type']);

        return view('payments.show', compact('payment', 'siblingPayments'));
    }

    public function edit(Payment $payment)
    {
        $this->authorizePermission('payments.edit');

        $leases = Lease::with(['unit.property', 'tenant'])->orderByDesc('start_date')->get();

        return view('payments.edit', compact('payment', 'leases'));
    }

    public function update(Request $request, Payment $payment)
    {
        $this->authorizePermission('payments.edit');

        $data = $this->validatePayment($request);
        $data = $this->normalizePaymentFields($data);

        $payment->update($data);

        $tenant = $payment->lease?->tenant?->full_name ?? '-';
        $this->logActivity('updated', 'payment', $payment->id, "Editó pago #{$payment->id} ({$payment->period_label}) — {$tenant}");

        return redirect()->route('payments.index')->with('success', 'Pago actualizado.');
    }

    public function destroy(Payment $payment)
    {
        $this->authorizePermission('payments.delete');

        $tenant = $payment->lease?->tenant?->full_name ?? '-';
        $desc = "Eliminó pago #{$payment->id} ({$payment->period_label}) — {$tenant}";
        $this->logActivity('deleted', 'payment', $payment->id, $desc);

        $payment->delete();

        return redirect()->route('payments.index')->with('success', 'Pago eliminado.');
    }

    public function markPaid(Request $request, Payment $payment)
{
    $this->authorizePermission('payments.edit');

    $data = $request->validate([
        'paid_at'        => ['nullable', 'date'],
        'paid_amount'    => ['nullable', 'numeric', 'min:0'],
        'payment_method' => ['nullable', 'string', 'max:40'],
        'reference'      => ['nullable', 'string', 'max:80'],
        'late_fee'       => ['nullable', 'numeric', 'min:0'],
        'receipt'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
    ]);

    $data['paid_at'] = $data['paid_at'] ?? now()->toDateString();

    // Si pagó menos del monto pactado → pago parcial
    $paidAmount = isset($data['paid_amount']) ? (float) $data['paid_amount'] : (float) $payment->amount;
    $expected   = (float) $payment->amount;

    $data['paid_amount'] = $paidAmount;
    $data['status']      = ($paidAmount < $expected) ? 'partial' : 'paid';

    if ($request->hasFile('receipt')) {
        $data['receipt'] = $request->file('receipt')->store('receipts');
    }

    $payment->update($data);

    $tenant = $payment->lease?->tenant?->full_name ?? '-';
    $action = $data['status'] === 'partial' ? 'paid' : 'paid';
    $this->logActivity('paid', 'payment', $payment->id,
        "Registró cobro de pago #{$payment->id} ({$payment->period_label}) — \${$paidAmount} — {$tenant}"
    );

    $msg = $data['status'] === 'partial'
        ? 'Pago parcial registrado ($' . number_format($paidAmount, 2) . ' de $' . number_format($expected, 2) . ').'
        : 'Pago marcado como pagado.';

    return redirect()->back()->with('success', $msg);
}


    public function uploadInvoice(Request $request, Payment $payment)
    {
        $request->validate([
            'invoice_folio' => ['nullable', 'string', 'max:80'],
            'invoiced_at'   => ['nullable', 'date'],
            'invoice_pdf.*' => ['nullable', 'file', 'max:10240', 'mimes:pdf'],
            'invoice_xml.*' => ['nullable', 'file', 'max:10240', 'mimes:xml,txt'],
        ]);

        $updateData = [];

        if ($request->filled('invoice_folio')) {
            $updateData['invoice_folio'] = $request->input('invoice_folio');
        }
        if ($request->filled('invoiced_at')) {
            $updateData['invoiced_at'] = $request->input('invoiced_at');
        } else {
            // Auto-fecha si no se especificó
            $updateData['invoiced_at'] = $updateData['invoiced_at'] ?? ($payment->invoiced_at?->toDateString() ?? now()->toDateString());
        }

        // Múltiples PDFs (agregar a los existentes)
        if ($request->hasFile('invoice_pdf')) {
            $current = is_array($payment->invoice_pdf) ? $payment->invoice_pdf : ($payment->invoice_pdf ? [$payment->invoice_pdf] : []);
            foreach ($request->file('invoice_pdf') as $file) {
                $current[] = $file->store('invoices');
            }
            $updateData['invoice_pdf'] = $current;
        }

        // Múltiples XMLs (agregar a los existentes)
        if ($request->hasFile('invoice_xml')) {
            $current = is_array($payment->invoice_xml) ? $payment->invoice_xml : ($payment->invoice_xml ? [$payment->invoice_xml] : []);
            foreach ($request->file('invoice_xml') as $file) {
                $current[] = $file->store('invoices');
            }
            $updateData['invoice_xml'] = $current;
        }

        if (count($updateData) <= 1 && !$request->hasFile('invoice_pdf') && !$request->hasFile('invoice_xml') && !$request->filled('invoice_folio')) {
            return response()->json(['success' => false, 'message' => 'No se envió ningún dato.'], 422);
        }

        if (in_array($payment->status, ['pending', 'overdue'])) {
            $updateData['status'] = 'invoiced';
        }

        $payment->update($updateData);

        $folio = $payment->invoice_folio ?? 'sin folio';
        $tenant = $payment->lease?->tenant?->full_name ?? '-';
        $this->logActivity('invoiced', 'payment', $payment->id, "Subió factura ({$folio}) para pago #{$payment->id} — {$tenant}");

        return response()->json(['success' => true, 'message' => 'Factura guardada.']);
    }

    public function uploadReceipt(Request $request, Payment $payment)
    {
        $request->validate([
            'receipt.*' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
            'paid_at'   => ['nullable', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $current = is_array($payment->receipt) ? $payment->receipt : ($payment->receipt ? [$payment->receipt] : []);

        $newPaths = [];
        if ($request->hasFile('receipt')) {
            foreach ($request->file('receipt') as $file) {
                $path      = $file->store('receipts');
                $current[] = $path;
                $newPaths[] = route('secure.download', ['file' => encrypt($path)]);
            }
        }

        $payment->update([
            'receipt'     => $current,
            'status'      => 'paid',
            'paid_at'     => $request->filled('paid_at') ? $request->input('paid_at') : ($payment->paid_at?->toDateString() ?? now()->toDateString()),
            'paid_amount' => $request->filled('paid_amount') ? $request->input('paid_amount') : ($payment->paid_amount ?? ((float)$payment->amount + (float)($payment->late_fee ?? 0))),
        ]);

        $tenant = $payment->lease?->tenant?->full_name ?? '-';
        $this->logActivity('receipt', 'payment', $payment->id, "Subió comprobante de pago #{$payment->id} — {$tenant}");

        return response()->json([
            'success' => true,
            'message' => 'Comprobantes subidos.',
            'paths'   => $newPaths,
        ]);
    }

    public function bulkEdit(Lease $lease)
    {
        $this->authorizePermission('payments.edit');

        $payments = $lease->payments()->orderBy('due_date', 'asc')->get();
        return view('leases.bulk_edit', compact('lease', 'payments'));
    }

    public function bulkUpdate(Request $request, Lease $lease)
    {
        $this->authorizePermission('payments.edit');

        $request->validate([
            'payments' => 'required|array',
            'payments.*.id' => 'required|exists:payments,id',
            'payments.*.paid_at' => 'nullable|date',
            'payments.*.invoiced_at' => 'nullable|date',
            'payments.*.invoice_folio' => 'nullable|string|max:80',
            'payments.*.paid_amount' => 'nullable|numeric|min:0',
            'payments.*.status' => 'required|in:pending,invoiced,paid,overdue,partial'
        ]);

        foreach ($request->input('payments') as $pData) {
            $payment = $lease->payments()->find($pData['id']);
            if ($payment) {
                $payment->update([
                    'paid_at'       => $pData['paid_at'] ?? null,
                    'invoiced_at'   => $pData['invoiced_at'] ?? null,
                    'invoice_folio' => $pData['invoice_folio'] ?? null,
                    'paid_amount'   => $pData['paid_amount'] ?? null,
                    'status'        => $pData['status']
                ]);
            }
        }

        $this->logActivity('bulk', 'payment', $lease->id, "Edición masiva de pagos del contrato #{$lease->contract_number} ({$count} pagos)");

        return redirect()->route('leases.show', $lease)->with('success', 'Pagos actualizados masivamente.');
    }

    private function validatePayment(Request $request): array
    {
        return $request->validate([
            'lease_id' => ['required', 'exists:leases,id'],
            'type' => ['required', 'in:rent,maintenance'],
            'period_label' => ['nullable', 'string', 'max:60'],
            'due_date' => ['required', 'date'],
            'paid_at' => ['nullable', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'late_fee' => ['nullable', 'numeric', 'min:0'],
            'status'         => ['required', 'in:pending,invoiced,paid,overdue,partial'],
            'payment_method' => ['nullable', 'string', 'max:40'],
            'reference' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function normalizePaymentFields(array $data): array
    {
        $dueDate = Carbon::parse($data['due_date']);
        $data['late_fee'] = $data['late_fee'] ?? 0;

        // El modelo Payment se encarga del desglose de IVA (16%) si subtotal/tax_amount vienen vacíos.

        if (! empty($data['paid_at'])) {
            $data['status'] = 'paid';
            if (empty($data['paid_amount'])) {
                $data['paid_amount'] = (float) $data['amount'] + (float) ($data['late_fee'] ?? 0);
            }
            return $data;
        }

        if ($data['status'] === 'paid') {
            $data['paid_at'] = $data['paid_at'] ?? now()->toDateString();
            if (empty($data['paid_amount'])) {
                $data['paid_amount'] = (float) $data['amount'] + (float) ($data['late_fee'] ?? 0);
            }
            return $data;
        }

        // Si ya tiene fecha de pago o status paid, no tocar
        if ($dueDate->isPast() && in_array($data['status'], ['pending', 'invoiced'])) {
            $data['status'] = 'overdue';
        }

        return $data;
    }

    private function indexFilters(Request $request): array
    {
        $today = Carbon::today();

        return [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
            'type' => (string) $request->query('type', ''),
            'lease_id' => (string) $request->query('lease_id', ''),
            'tenant_id' => (string) $request->query('tenant_id', ''),
            'due_from' => (string) $request->query('due_from', $today->copy()->startOfMonth()->toDateString()),
            'due_to' => (string) $request->query('due_to', $today->copy()->endOfMonth()->toDateString()),
            'sort' => (string) $request->query('sort', 'due_date'),
            'direction' => (string) $request->query('direction', 'desc'),
            'per_page' => (int) $request->query('per_page', 15),
        ];
    }

    private function paymentIndexQuery(array $filters): Builder
    {
        return Payment::query()
            ->when($filters['q'] !== '', function ($builder) use ($filters) {
                $like = '%' . $filters['q'] . '%';
                $builder->where(function ($where) use ($like) {
                    $where
                        ->where('period_label', 'like', $like)
                        ->orWhere('reference', 'like', $like)
                        ->orWhereHas('lease', fn ($lease) => $lease->where('contract_number', 'like', $like))
                        ->orWhereHas('lease.tenant', fn ($tenant) => $tenant->where('full_name', 'like', $like))
                        ->orWhereHas('lease.unit', fn ($unit) => $unit->where('code', 'like', $like))
                        ->orWhereHas('lease.unit.property', fn ($property) => $property->where('name', 'like', $like));
                });
            })
            ->when($filters['type'] !== '', fn ($builder) => $builder->where('payments.type', $filters['type']))
            ->when($filters['status'] !== '', fn ($builder) => $builder->where('payments.status', $filters['status']))
            ->when($filters['lease_id'] !== '', fn ($builder) => $builder->where('payments.lease_id', $filters['lease_id']))
            ->when($filters['tenant_id'] !== '', fn ($builder) => $builder->whereHas('lease', fn ($lease) => $lease->where('tenant_id', $filters['tenant_id'])))
            ->when($filters['due_from'] !== '', fn ($builder) => $builder->whereDate('payments.due_date', '>=', $filters['due_from']))
            ->when($filters['due_to'] !== '', fn ($builder) => $builder->whereDate('payments.due_date', '<=', $filters['due_to']));
    }

    private function availableTenants(array $filters)
    {
        $tenantFilters = $filters;
        $selectedTenantId = $tenantFilters['tenant_id'];
        $tenantFilters['tenant_id'] = '';

        $tenantIds = $this->paymentIndexQuery($tenantFilters)
            ->join('leases', 'payments.lease_id', '=', 'leases.id')
            ->whereNotNull('leases.tenant_id')
            ->distinct()
            ->pluck('leases.tenant_id');

        $tenants = Tenant::query()
            ->whereIn('id', $tenantIds)
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        if ($selectedTenantId !== '' && ! $tenants->contains('id', (int) $selectedTenantId)) {
            $selectedTenant = Tenant::query()->select('id', 'full_name')->find($selectedTenantId);
            if ($selectedTenant) {
                $tenants->prepend($selectedTenant);
            }
        }

        return $tenants->unique('id')->values();
    }
}
