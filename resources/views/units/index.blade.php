@extends('layouts.app')

@section('title', 'Locales y Unidades')

@section('content')
    <div class="page-head">
        
        @if(auth()->user()->hasPermission('units.create'))
        <button type="button" class="btn btn-primary" data-modal-target="#modal-create-unit">Nueva unidad</button>
        @endif
    </div>

    <div class="card">
        <form method="GET" action="{{ route('units.index') }}">
            <div class="form-grid">
                <div>
                    <label for="filter_q">Buscar</label>
                    <input id="filter_q" name="q" value="{{ $filters['q'] }}" placeholder="Codigo, piso o propiedad">
                </div>
                <div>
                    <label for="filter_property_id">Propiedad</label>
                    <select id="filter_property_id" name="property_id">
                        <option value="">Todas</option>
                        @foreach ($properties as $property)
                            <option value="{{ $property->id }}" @selected((string) $filters['property_id'] === (string) $property->id)>
                                {{ $property->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter_status">Estatus</label>
                    <select id="filter_status" name="status">
                        <option value="">Todos</option>
                        <option value="available" @selected($filters['status'] === 'available')>Disponible</option>
                        <option value="rented" @selected($filters['status'] === 'rented')>Rentado</option>
                        <option value="maintenance" @selected($filters['status'] === 'maintenance')>Mantenimiento</option>
                    </select>
                </div>
                <div>
                    <label for="filter_sort">Ordenar por</label>
                    <select id="filter_sort" name="sort">
                        <option value="created_at" @selected($filters['sort'] === 'created_at')>Fecha de alta</option>
                        <option value="code" @selected($filters['sort'] === 'code')>Codigo</option>
                        <option value="status" @selected($filters['sort'] === 'status')>Estatus</option>
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
                <a class="btn btn-light" href="{{ route('units.index') }}">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <p class="muted">Mostrando {{ $units->firstItem() ?? 0 }} - {{ $units->lastItem() ?? 0 }} de {{ $units->total() }} unidades.</p>
        <!-- VISTA DE TABLA (ESCRITORIO Y TABLETA) -->
        <div class="table-responsive desktop-only">
            <table>
                <thead>
                    <tr>
                        <th>Propiedad</th>
                        <th>Código</th>
                        <th class="tablet-hide">Piso</th>
                        <th class="tablet-hide">Área (m²)</th>
                        <th>Beneficiario</th>
                        <th>Estatus</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($units as $unit)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--primary);">{{ $unit->property->name ?? '-' }}</div>
                            </td>
                            <td><span style="font-family: monospace; font-weight: bold;">{{ $unit->code }}</span></td>
                            <td class="tablet-hide">{{ $unit->floor ?: '—' }}</td>
                            <td class="tablet-hide">{{ $unit->area_m2 ? number_format((float)$unit->area_m2, 2) . ' m²' : '—' }}</td>
                            <td>{{ $unit->beneficiary->name ?? 'N/A' }}</td>
                            <td>
                                @if($unit->status === 'rented')
                                    <span class="badge badge-ok">Rentado</span>
                                @elseif($unit->status === 'maintenance')
                                    <span class="badge badge-warn">Mantenimiento</span>
                                @else
                                    <span class="badge" style="background:#eef2fb;color:#384658;">Disponible</span>
                                @endif
                            </td>
                            <td class="actions">
                                <div style="display: flex; gap: 0.3rem; justify-content: flex-end;">
                                    <a class="btn btn-light" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" href="{{ route('units.show', $unit) }}">Ver</a>
                                    @if(auth()->user()->hasPermission('units.edit'))
                                    <a class="btn btn-light" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" href="{{ route('units.edit', $unit) }}">Editar</a>
                                    @endif
                                    @if(auth()->user()->hasPermission('units.delete'))
                                    <form class="inline" method="POST" action="{{ route('units.destroy', $unit) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="return confirm('¿Eliminar unidad?')">Eliminar</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No hay unidades registradas con esos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- VISTA DE TARJETAS (SOLO CELULAR) -->
        <div class="mobile-only unit-cards">
            @forelse ($units as $unit)
                <div class="unit-card">
                    <div class="unit-card-body">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                            <div>
                                <div class="unit-card-code">{{ $unit->code }}</div>
                                <div class="unit-card-property">{{ $unit->property->name ?? 'Sin propiedad' }}</div>
                            </div>
                            <div>
                                @if($unit->status === 'rented')
                                    <span class="badge-mini ok">Rentado</span>
                                @elseif($unit->status === 'maintenance')
                                    <span class="badge-mini warn">Mantenimiento</span>
                                @else
                                    <span class="badge-mini">Disponible</span>
                                @endif
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 0.8rem; font-size: 0.8rem;">
                            <div>
                                <span style="color: var(--muted);">Piso:</span> <strong>{{ $unit->floor ?: '-' }}</strong>
                            </div>
                            <div>
                                <span style="color: var(--muted);">Área:</span> <strong>{{ $unit->area_m2 ? number_format((float)$unit->area_m2, 1) . 'm²' : '-' }}</strong>
                            </div>
                            <div style="grid-column: span 2;">
                                <span style="color: var(--muted);">Beneficiario:</span> <strong>{{ $unit->beneficiary->name ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="unit-card-actions">
                        <a href="{{ route('units.show', $unit) }}" class="card-btn">Ver</a>
                        @if(auth()->user()->hasPermission('units.edit'))
                        <a href="{{ route('units.edit', $unit) }}" class="card-btn">Editar</a>
                        @endif
                        @if(auth()->user()->hasPermission('units.delete'))
                        <form method="POST" action="{{ route('units.destroy', $unit) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="card-btn delete" onclick="return confirm('¿Eliminar?')">Borrar</button>
                        </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="muted" style="text-align: center; padding: 2rem;">No hay unidades para mostrar.</p>
            @endforelse
        </div>

        <div class="pagination">{{ $units->links() }}</div>
    </div>
@endsection

@push('styles')
    <style>
        @media (max-width: 1100px) {
            .tablet-hide { display: none !important; }
            .form-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .desktop-only { display: none !important; }
            .mobile-only { display: block !important; }
            .form-grid { grid-template-columns: 1fr; }
            .page-head { flex-direction: column; align-items: stretch; gap: 0.8rem; padding: 0.5rem 0; }
            .page-head .btn { width: 100%; }
            .card { padding: 0.8rem; margin-bottom: 1rem; }
        }

        @media (min-width: 769px) {
            .mobile-only { display: none !important; }
            .desktop-only { display: block !important; }
        }

        /* Unit Cards Style */
        .unit-cards {
            display: grid;
            gap: 1rem;
            margin-top: 1rem;
        }
        .unit-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .unit-card-body {
            padding: 1rem;
        }
        .unit-card-code {
            font-weight: 800;
            color: var(--primary);
            font-size: 1.1rem;
            font-family: monospace;
        }
        .unit-card-property {
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 0.2rem;
        }
        .badge-mini {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            background: #f1f5f9;
            color: #475569;
            border-radius: 6px;
            font-weight: 600;
        }
        .badge-mini.ok { background: #dcfce7; color: #166534; }
        .badge-mini.warn { background: #fef3c7; color: #92400e; }

        .unit-card-actions {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border-top: 1px solid var(--border);
            background: #f8fafc;
        }
        .card-btn {
            padding: 0.75rem;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            color: var(--text-main);
            border-right: 1px solid var(--border);
            transition: background 0.2s;
        }
        .card-btn:last-child { border-right: none; }
        .card-btn:active { background: #f1f5f9; }
        .card-btn.delete { color: #ef4444; }

        /* Responsive table wrapper */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-top: 1rem;
        }
        .table-responsive table { min-width: 800px; }
    </style>
@endpush

@push('modals')
    @if(auth()->user()->hasPermission('units.create'))
    <div class="modal-overlay" id="modal-create-unit" data-modal-auto-open="true">
        <div class="modal-dialog">
            <div class="modal-head">
                <h3 class="modal-title">Agregar unidad</h3>
                <button type="button" class="modal-close" data-modal-close>&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('units.store') }}" enctype="multipart/form-data">
                    @csrf
                    @php($unit = null)
                    @include('units._form')
                    <div class="form-actions">
                        <button class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-light" data-modal-close>Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endpush

