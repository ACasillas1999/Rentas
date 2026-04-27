@extends('layouts.app')

@section('title', 'Inquilinos')

@section('content')
    <div class="page-head">
        
        <button type="button" class="btn btn-primary" data-modal-target="#modal-create-tenant">Nuevo inquilino</button>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('tenants.index') }}">
            <div class="form-grid">
                <div>
                    <label for="filter_q">Buscar</label>
                    <input id="filter_q" name="q" value="{{ $filters['q'] }}" placeholder="Nombre, documento, telefono, correo">
                </div>
                <div>
                    <label for="filter_sort">Ordenar por</label>
                    <select id="filter_sort" name="sort">
                        <option value="created_at" @selected($filters['sort'] === 'created_at')>Fecha de alta</option>
                        <option value="full_name" @selected($filters['sort'] === 'full_name')>Nombre</option>
                        <option value="email" @selected($filters['sort'] === 'email')>Correo</option>
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
                <a class="btn btn-light" href="{{ route('tenants.index') }}">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <p class="muted">Mostrando {{ $tenants->firstItem() ?? 0 }} - {{ $tenants->lastItem() ?? 0 }} de {{ $tenants->total() }} inquilinos.</p>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Documento</th>
                    <th>Telefono</th>
                    <th>Correo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tenants as $tenant)
                    <tr>
                        <td>{{ $tenant->full_name }}</td>
                        <td>{{ $tenant->document_id ?: '-' }}</td>
                        <td>{{ $tenant->phone ?: '-' }}</td>
                        <td>{{ $tenant->email ?: '-' }}</td>
                        <td class="actions">
                            <a class="btn btn-light" href="{{ route('tenants.show', $tenant) }}">Ver</a>
                            <a class="btn btn-light" href="{{ route('tenants.edit', $tenant) }}">Editar</a>
                            <form class="inline" method="POST" action="{{ route('tenants.destroy', $tenant) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" onclick="return confirm('Eliminar inquilino?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No hay inquilinos registrados con esos filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">{{ $tenants->links() }}</div>
    </div>

@endsection

@push('modals')
    <div class="modal-overlay" id="modal-create-tenant" data-modal-auto-open="true">
        <div class="modal-dialog">
            <div class="modal-head">
                <h3 class="modal-title">Agregar inquilino</h3>
                <button type="button" class="modal-close" data-modal-close>&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('tenants.store') }}" enctype="multipart/form-data">
                    @csrf
                    @php($tenant = null)
                    @include('tenants._form')
                    <div class="form-actions">
                        <button class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-light" data-modal-close>Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

