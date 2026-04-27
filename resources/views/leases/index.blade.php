@extends('layouts.app')

@section('title', 'Contratos')

@section('content')
    <div class="page-head">
        
        <button type="button" class="btn btn-primary" data-modal-target="#modal-create-lease">Nuevo contrato</button>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('leases.index') }}">
            <div class="form-grid">
                <div>
                    <label for="filter_q">Buscar</label>
                    <input id="filter_q" name="q" value="{{ $filters['q'] }}" placeholder="Folio, inquilino, unidad, propiedad">
                </div>
                <div>
                    <label for="filter_status">Estatus</label>
                    <select id="filter_status" name="status">
                        <option value="">Todos</option>
                        <option value="active" @selected($filters['status'] === 'active')>Activo</option>
                        <option value="finished" @selected($filters['status'] === 'finished')>Finalizado</option>
                        <option value="cancelled" @selected($filters['status'] === 'cancelled')>Cancelado</option>
                    </select>
                </div>
                <div>
                    <label for="filter_tenant_id">Inquilino</label>
                    <select id="filter_tenant_id" name="tenant_id">
                        <option value="">Todos</option>
                        @foreach ($tenants as $tenant)
                            <option value="{{ $tenant->id }}" @selected((string) $filters['tenant_id'] === (string) $tenant->id)>
                                {{ $tenant->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter_unit_id">Unidad</label>
                    <select id="filter_unit_id" name="unit_id">
                        <option value="">Todas</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) $filters['unit_id'] === (string) $unit->id)>
                                {{ $unit->property->name ?? '-' }} / {{ $unit->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter_sort">Ordenar por</label>
                    <select id="filter_sort" name="sort">
                        <option value="start_date" @selected($filters['sort'] === 'start_date')>Fecha de inicio</option>
                        <option value="monthly_amount" @selected($filters['sort'] === 'monthly_amount')>Monto</option>
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
                <a class="btn btn-light" href="{{ route('leases.index') }}">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <p class="muted">Mostrando {{ $leases->firstItem() ?? 0 }} - {{ $leases->lastItem() ?? 0 }} de {{ $leases->total() }} contratos.</p>
        <table>
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Unidad</th>
                    <th>Inquilino</th>
                    <th>Periodo</th>
                    <th>Monto</th>
                    <th>Estatus</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leases as $lease)
                    <tr>
                        <td>{{ $lease->contract_number ?: 'Sin folio' }}</td>
                        <td>{{ $lease->unit->property->name ?? '-' }} / {{ $lease->unit->code ?? '-' }}</td>
                        <td>{{ $lease->tenant->full_name ?? '-' }}</td>
                        <td>{{ $lease->start_date?->format('Y-m-d') }} a {{ $lease->end_date?->format('Y-m-d') ?: 'Abierto' }}</td>
                        <td>${{ number_format((float) $lease->monthly_amount, 2) }}</td>
                        <td>
                            @if($lease->status === 'active')
                                <span class="badge badge-ok">Activo</span>
                            @elseif($lease->status === 'finished')
                                <span class="badge badge-bad">Finalizado</span>
                            @else
                                <span class="badge badge-warn">{{ ucfirst($lease->status) }}</span>
                            @endif
                        </td>
                        <td class="actions" style="display:flex;gap:0.5rem;align-items:center;">
                            @if($lease->status === 'active')
                                <a class="btn btn-light" href="{{ route('leases.renew', $lease) }}" title="Renovar">🔄</a>
                            @endif
                            <a class="btn btn-light" href="{{ route('leases.show', $lease) }}">Ver</a>
                            <a class="btn btn-light" href="{{ route('leases.edit', $lease) }}">Editar</a>
                            <form action="{{ route('leases.destroy', $lease) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este contrato y TODOS SUS PAGOS? Esta acción no se puede deshacer.');" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light" style="color:var(--danger); border-color:var(--danger); background:transparent;">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No hay contratos con esos filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">{{ $leases->links() }}</div>
    </div>

@endsection

@push('modals')
    <div class="modal-overlay" id="modal-create-lease" data-modal-auto-open="true">
        <div class="modal-dialog">
            <div class="modal-head">
                <h3 class="modal-title">Agregar contrato</h3>
                <button type="button" class="modal-close" data-modal-close>&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('leases.store') }}" enctype="multipart/form-data">
                    @csrf
                    @php($lease = null)
                    @include('leases._form')
                    <div class="form-actions">
                        <button class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-light" data-modal-close>Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

