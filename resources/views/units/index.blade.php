@extends('layouts.app')

@section('title', 'Locales y Unidades')

@section('content')
    <div class="page-head">
        
        <button type="button" class="btn btn-primary" data-modal-target="#modal-create-unit">Nueva unidad</button>
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
        <table>
            <thead>
                <tr>
                    <th>Propiedad</th>
                    <th>Codigo</th>
                    <th>Piso</th>
                    <th>Área (m²)</th>
                    <th>Beneficiario</th>
                    <th>Estatus</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($units as $unit)
                    <tr>
                        <td>{{ $unit->property->name ?? '-' }}</td>
                        <td>{{ $unit->code }}</td>
                        <td>{{ $unit->floor ?: '—' }}</td>
                        <td>{{ $unit->area_m2 ? number_format((float)$unit->area_m2, 2) . ' m²' : '—' }}</td>
                        <td>{{ $unit->beneficiary->name ?? 'N/A' }}</td>
                        <td>
                            @php
                                $statusLabel = ['available' => 'Disponible', 'rented' => 'Rentado', 'maintenance' => 'Mantenimiento'];
                            @endphp
                            @if($unit->status === 'rented')
                                <span class="badge badge-ok">Rentado</span>
                            @elseif($unit->status === 'maintenance')
                                <span class="badge badge-warn">Mantenimiento</span>
                            @else
                                <span class="badge" style="background:#eef2fb;color:#384658;">Disponible</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a class="btn btn-light" href="{{ route('units.show', $unit) }}">Ver</a>
                            <a class="btn btn-light" href="{{ route('units.edit', $unit) }}">Editar</a>
                            <form class="inline" method="POST" action="{{ route('units.destroy', $unit) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" onclick="return confirm('Eliminar unidad?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No hay unidades registradas con esos filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">{{ $units->links() }}</div>
    </div>

@endsection

@push('modals')
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
@endpush

