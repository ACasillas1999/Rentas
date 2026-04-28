@extends('layouts.app')

@section('title', 'Pagos')

@section('content')
    <div class="page-head">
        
        <button type="button" class="btn btn-primary" data-modal-target="#modal-create-payment">Registrar pago</button>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('payments.index') }}">
            <div class="form-grid">
                <div>
                    <label for="filter_q">Buscar</label>
                    <input id="filter_q" name="q" value="{{ $filters['q'] }}" placeholder="Contrato, inquilino, propiedad, referencia">
                </div>
                <div>
                    <label for="filter_status">Estatus</label>
                    <select id="filter_status" name="status">
                        <option value="">Todos</option>
                        <option value="pending" @selected($filters['status'] === 'pending')>Por facturar</option>
                        <option value="invoiced" @selected($filters['status'] === 'invoiced')>Facturado</option>
                        <option value="paid" @selected($filters['status'] === 'paid')>Pagado</option>
                        <option value="overdue" @selected($filters['status'] === 'overdue')>Vencido</option>
                    </select>
                </div>
                <div>
                    <label for="filter_type">Tipo</label>
                    <select id="filter_type" name="type">
                        <option value="">Todos</option>
                        <option value="rent" @selected($filters['type'] === 'rent')>Renta</option>
                        <option value="maintenance" @selected($filters['type'] === 'maintenance')>Mto</option>
                    </select>
                </div>
                <div>
                    <label for="filter_lease_id">Contrato</label>
                    <select id="filter_lease_id" name="lease_id">
                        <option value="">Todos</option>
                        @foreach ($leases as $lease)
                            <option value="{{ $lease->id }}" @selected((string) $filters['lease_id'] === (string) $lease->id)>
                                {{ $lease->contract_number ?: 'Sin folio' }} - {{ $lease->tenant->full_name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter_tenant_id">Inquilino</label>
                    <select id="filter_tenant_id" name="tenant_id" data-tenant-options-url="{{ route('payments.tenants') }}">
                        <option value="">Todos</option>
                        @foreach ($tenants as $tenant)
                            <option value="{{ $tenant->id }}" @selected((string) $filters['tenant_id'] === (string) $tenant->id)>
                                {{ $tenant->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter_due_from">Vence desde</label>
                    <input id="filter_due_from" name="due_from" type="date" value="{{ $filters['due_from'] }}">
                </div>
                <div>
                    <label for="filter_due_to">Vence hasta</label>
                    <input id="filter_due_to" name="due_to" type="date" value="{{ $filters['due_to'] }}">
                </div>
                <div>
                    <label for="filter_sort">Ordenar por</label>
                    <select id="filter_sort" name="sort">
                        <option value="due_date" @selected($filters['sort'] === 'due_date')>Vencimiento</option>
                        <option value="amount" @selected($filters['sort'] === 'amount')>Monto</option>
                        <option value="status" @selected($filters['sort'] === 'status')>Estatus</option>
                        <option value="created_at" @selected($filters['sort'] === 'created_at')>Fecha de alta</option>
                    </select>
                </div>
                <div>
                    <label for="filter_direction">Direccion</label>
                    <select id="filter_direction" name="direction">
                        <option value="asc" @selected($filters['direction'] === 'asc')>Ascendente</option>
                        <option value="desc" @selected($filters['direction'] === 'desc')>Descendente</option>
                    </select>
                </div>
                <div>
                    <label for="filter_per_page">Registros</label>
                    <select id="filter_per_page" name="per_page">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" @selected((int) $filters['per_page'] === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary">Aplicar filtros</button>
                <a class="btn btn-light" href="{{ route('payments.index') }}">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card payment-table-wrap">
        <p class="muted">Mostrando {{ $payments->firstItem() ?? 0 }} - {{ $payments->lastItem() ?? 0 }} de {{ $payments->total() }} pagos.</p>
        <table>
            <thead>
                <tr>
                    <th>Contrato</th>
                    <th>Inquilino</th>
                    <th>Tipo</th>
                    <th>Periodo</th>
                    <th>Vence</th>
                    <th>Monto</th>
                    <th>Estatus</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td>{{ $payment->lease->contract_number ?? 'Sin folio' }}</td>
                        <td>{{ $payment->lease->tenant->full_name ?? '-' }}</td>
                        <td>
                            @if($payment->type === 'maintenance')
                                <span class="badge" style="background:#eef2fb; color:#384658; font-size:0.75em;">Mantenimiento</span>
                            @else
                                <span class="badge" style="background:#e8f4eb; color:#1b6336; font-size:0.75em;">Renta</span>
                            @endif
                        </td>
                        <td style="white-space: nowrap;">
                            @if($payment->period_number && $payment->total_periods)
                                <span style="font-weight:700; color:#2a3f5d;">{{ $payment->period_number }}/{{ $payment->total_periods }}</span>
                            @endif
                            @if($payment->period_start && $payment->period_end)
                                <br><span style="font-size:0.8rem; color:#64748b;">
                                    {{ $payment->period_start->locale('es')->isoFormat('D MMM') }}
                                    &ndash;
                                    {{ $payment->period_end->locale('es')->isoFormat('D MMM YY') }}
                                </span>
                            @elseif($payment->period_label)
                                <br><span style="font-size:0.8rem; color:#64748b;">{{ $payment->period_label }}</span>
                            @endif
                        </td>
                        <td>{{ $payment->due_date?->format('Y-m-d') }}</td>
                        <td>${{ number_format((float) $payment->amount + (float) $payment->late_fee, 2) }}</td>
                        <td>
                            @if ($payment->status === 'paid')
                                <span class="badge badge-ok">Pagado</span>
                            @elseif ($payment->status === 'invoiced')
                                <span class="badge" style="background:#dbeafe;color:#1e40af;">Facturado</span>
                            @elseif ($payment->status === 'overdue')
                                <span class="badge badge-bad">Vencido</span>
                            @elseif ($payment->status === 'partial')
                                <span class="badge" style="background:#fef3c7;color:#92400e;">Parcial</span>
                            @else
                                <span class="badge badge-warn">Por facturar</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a class="btn btn-light" href="{{ route('payments.show', $payment) }}">Ver</a>
                            <a class="btn btn-light" href="{{ route('payments.edit', $payment) }}">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No hay pagos registrados con esos filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">{{ $payments->links() }}</div>
    </div>

    {{-- Vista de Tarjetas para Móvil (Global) --}}
    <div class="payment-cards-grid">
        @forelse ($payments as $payment)
            <div class="payment-card" style="background: var(--surface); box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-radius: 16px;">
                <div class="payment-card-status">
                    @if ($payment->status === 'paid')
                        <span class="badge badge-ok">Pagado</span>
                    @elseif ($payment->status === 'overdue')
                        <span class="badge badge-bad">Vencido</span>
                    @else
                        <span class="badge badge-warn">Pendiente</span>
                    @endif
                </div>

                <div class="payment-card-period">
                    <span style="color: var(--primary);">{{ $payment->lease->contract_number ?? 'S/F' }}</span>
                    <span class="payment-card-type" style="margin-left: 0.5rem;">{{ $payment->type === 'rent' ? 'Renta' : 'Mto.' }}</span>
                </div>

                <div style="font-weight: 700; font-size: 1.05rem; color: var(--text);">
                    {{ $payment->lease->tenant->full_name ?? '-' }}
                </div>

                <div class="payment-card-dates" style="font-size: 0.85rem; color: var(--muted);">
                    Periodo {{ $payment->period_number }}/{{ $payment->total_periods }}<br>
                    {{ $payment->period_start?->locale('es')->isoFormat('D MMM') }} – {{ $payment->period_end?->locale('es')->isoFormat('D MMM YYYY') }}
                </div>

                <div class="payment-card-row" style="margin-top: 0.4rem; padding-top: 0.6rem; border-top: 1px solid var(--border);">
                    <div style="font-size: 0.8rem; color: var(--muted);">Vence: {{ $payment->due_date?->format('d/m/y') }}</div>
                    <div class="payment-card-amount" style="color: var(--text); font-size: 1.2rem;">${{ number_format((float) $payment->amount, 2) }}</div>
                </div>

                <div class="payment-card-actions" style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                    <a href="{{ route('payments.show', $payment) }}" class="btn btn-primary" style="flex: 1; text-align: center;">Ver</a>
                    <a href="{{ route('payments.edit', $payment) }}" class="btn btn-light" style="flex: 1; text-align: center;">Editar</a>
                </div>
            </div>
        @empty
            <div class="card" style="text-align: center; color: var(--muted); padding: 2rem;">
                No hay pagos que mostrar.
            </div>
        @endforelse
        <div class="pagination">{{ $payments->links() }}</div>
    </div>
@endsection

@push('modals')
    <div class="modal-overlay" id="modal-create-payment" data-modal-auto-open="true">
        <div class="modal-dialog">
            <div class="modal-head">
                <h3 class="modal-title">Agregar pago</h3>
                <button type="button" class="modal-close" data-modal-close>&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('payments.store') }}">
                    @csrf
                    @php($payment = null)
                    @include('payments._form')
                    <div class="form-actions">
                        <button class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-light" data-modal-close>Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        (() => {
            const dueFrom = document.getElementById('filter_due_from');
            const dueTo = document.getElementById('filter_due_to');
            const lease = document.getElementById('filter_lease_id');
            const status = document.getElementById('filter_status');
            const type = document.getElementById('filter_type');
            const tenant = document.getElementById('filter_tenant_id');

            if (!dueFrom || !dueTo || !tenant) {
                return;
            }

            let requestSeq = 0;

            const rebuildOptions = (items, selectedValue) => {
                tenant.innerHTML = '';

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Todos';
                tenant.appendChild(defaultOption);

                items.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = String(item.id);
                    option.textContent = item.full_name;
                    tenant.appendChild(option);
                });

                if ([...tenant.options].some((option) => option.value === selectedValue)) {
                    tenant.value = selectedValue;
                }
            };

            const refreshTenantOptions = async () => {
                const params = new URLSearchParams();
                const selectedValue = tenant.value;
                const url = tenant.dataset.tenantOptionsUrl;
                const currentRequest = ++requestSeq;

                if (dueFrom.value) {
                    params.set('due_from', dueFrom.value);
                }

                if (dueTo.value) {
                    params.set('due_to', dueTo.value);
                }

                if (lease && lease.value) {
                    params.set('lease_id', lease.value);
                }

                if (status && status.value) {
                    params.set('status', status.value);
                }

                if (type && type.value) {
                    params.set('type', type.value);
                }

                tenant.disabled = true;

                try {
                    const response = await fetch(`${url}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('No se pudieron cargar los inquilinos.');
                    }

                    const data = await response.json();
                    if (currentRequest !== requestSeq) {
                        return;
                    }

                    rebuildOptions(data.tenants ?? [], selectedValue);
                } catch (error) {
                    console.error(error);
                } finally {
                    if (currentRequest === requestSeq) {
                        tenant.disabled = false;
                    }
                }
            };

            [dueFrom, dueTo, lease, status, type]
                .filter(Boolean)
                .forEach((element) => element.addEventListener('change', refreshTenantOptions));
        })();
    </script>
@endpush
