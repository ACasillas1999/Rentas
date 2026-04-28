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
        <!-- VISTA DE TABLA (ESCRITORIO Y TABLETA) -->
        <div class="table-responsive desktop-only">
            <table>
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th class="tablet-hide">RFC / Documento</th>
                        <th>Teléfono</th>
                        <th class="tablet-hide">Correo Electrónico</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenants as $tenant)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--primary);">{{ $tenant->full_name }}</div>
                                <div style="font-size: 0.75rem; color: var(--muted);" class="tablet-show">{{ $tenant->document_id ?: 'Sin RFC' }}</div>
                            </td>
                            <td class="tablet-hide">{{ $tenant->document_id ?: '-' }}</td>
                            <td>{{ $tenant->phone ?: '-' }}</td>
                            <td class="tablet-hide">{{ $tenant->email ?: '-' }}</td>
                            <td class="actions">
                                <div style="display: flex; gap: 0.3rem; justify-content: flex-end;">
                                    <a class="btn btn-light" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" href="{{ route('tenants.show', $tenant) }}">Ver</a>
                                    <a class="btn btn-light" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" href="{{ route('tenants.edit', $tenant) }}">Editar</a>
                                    <form class="inline" method="POST" action="{{ route('tenants.destroy', $tenant) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="return confirm('¿Eliminar inquilino?')">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No hay inquilinos registrados con esos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- VISTA DE TARJETAS (SOLO CELULAR) -->
        <div class="mobile-only tenant-cards">
            @forelse ($tenants as $tenant)
                <div class="tenant-card">
                    <div class="tenant-card-body">
                        <div class="tenant-card-name">{{ $tenant->full_name }}</div>
                        <div class="tenant-card-doc">{{ $tenant->document_id ?: 'Sin RFC/Documento' }}</div>
                        
                        <div class="tenant-card-info">
                            <div class="info-item">
                                <span class="info-icon">📞</span>
                                <span>{{ $tenant->phone ?: 'No registrado' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-icon">✉️</span>
                                <span style="font-size: 0.8rem; word-break: break-all;">{{ $tenant->email ?: 'Sin correo' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="tenant-card-actions">
                        <a href="{{ route('tenants.show', $tenant) }}" class="card-btn">Ver</a>
                        <a href="{{ route('tenants.edit', $tenant) }}" class="card-btn">Editar</a>
                        <form method="POST" action="{{ route('tenants.destroy', $tenant) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="card-btn delete" onclick="return confirm('¿Eliminar?')">Borrar</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="muted" style="text-align: center; padding: 2rem;">No hay inquilinos para mostrar.</p>
            @endforelse
        </div>

        <div class="pagination">{{ $tenants->links() }}</div>
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
            .tablet-show { display: none !important; }
        }

        /* Tenant Cards Style */
        .tenant-cards {
            display: grid;
            gap: 1rem;
            margin-top: 1rem;
        }
        .tenant-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .tenant-card-body {
            padding: 1rem;
        }
        .tenant-card-name {
            font-weight: 700;
            color: var(--primary);
            font-size: 1rem;
            margin-bottom: 0.1rem;
        }
        .tenant-card-doc {
            font-size: 0.75rem;
            color: var(--muted);
            margin-bottom: 0.8rem;
        }
        .tenant-card-info {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-main);
        }
        .info-icon { font-size: 0.9rem; }

        .tenant-card-actions {
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

