@extends('layouts.app')

@section('title', 'Propiedades')

@section('content')
    <div class="page-head">
        
        <button type="button" class="btn btn-primary" data-modal-target="#modal-create-property">Nueva propiedad</button>
    </div>

    <div class="card">
        <details class="filter-panel" open>
            <summary>Filtros de búsqueda</summary>
            <form method="GET" action="{{ route('properties.index') }}" style="margin-top:1rem;">
                <div class="form-grid">
                    <div>
                        <label for="filter_q">Buscar</label>
                        <input id="filter_q" name="q" value="{{ $filters['q'] }}" placeholder="Nombre, direccion, ciudad, estado">
                    </div>
                    <div>
                        <label for="filter_type">Tipo</label>
                        <select id="filter_type" name="type">
                            <option value="">Todos</option>
                            @foreach ($types as $key => $label)
                                <option value="{{ $key }}" @selected($filters['type'] === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter_city">Ciudad</label>
                        <input id="filter_city" name="city" value="{{ $filters['city'] }}" placeholder="Ej. Guadalajara">
                    </div>
                    <div>
                        <label for="filter_sort">Ordenar por</label>
                        <select id="filter_sort" name="sort">
                            <option value="name" @selected($filters['sort'] === 'name')>Nombre</option>
                            <option value="city" @selected($filters['sort'] === 'city')>Ciudad</option>
                            <option value="units" @selected($filters['sort'] === 'units')>Numero de unidades</option>
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
                <div class="form-actions" style="margin-top:1.5rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <button class="btn btn-primary" style="flex: 1; min-width: 150px;">Aplicar filtros</button>
                    <a class="btn btn-light" href="{{ route('properties.index') }}" style="flex: 1; min-width: 100px; text-align: center;">Limpiar</a>
                </div>
            </form>
        </details>
    </div>

    <div class="card">
        <h3>Mapa de propiedades filtradas</h3>
        <div id="properties-map" style="width: 100%; height: 420px; border-radius: 12px;"></div>
    </div>

    <div class="card">
        <p class="muted">
            Mostrando {{ $properties->firstItem() ?? 0 }} - {{ $properties->lastItem() ?? 0 }} de {{ $properties->total() }} propiedades.
        </p>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Direccion</th>
                        <th>Ciudad</th>
                        <th>Unidades</th>
                        <th>Disponibles</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($properties as $property)
                        <tr>
                            <td>
                                <div style="font-weight: 600;">{{ $property->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--muted); display: none;" class="mobile-show">{{ ucfirst($property->type) }}</div>
                            </td>
                            <td class="mobile-hide">{{ ucfirst($property->type) }}</td>
                            <td>{{ $property->address }}</td>
                            <td>{{ $property->city ?: '-' }}</td>
                            <td>{{ $property->units_count }}</td>
                            <td>{{ $property->available_units_count }}</td>
                            <td class="actions">
                                <div style="display: flex; gap: 0.3rem;">
                                    <a class="btn btn-light" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" href="{{ route('properties.show', $property) }}">Ver</a>
                                    <a class="btn btn-light" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" href="{{ route('properties.edit', $property) }}">Editar</a>
                                    <form class="inline" method="POST" action="{{ route('properties.destroy', $property) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="return confirm('Eliminar propiedad?')">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No hay propiedades registradas con esos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $properties->links() }}</div>
    </div>
@endsection

@push('modals')
    <div class="modal-overlay" id="modal-create-property" data-modal-auto-open="true">
        <div class="modal-dialog">
            <div class="modal-head">
                <h3 class="modal-title">Agregar propiedad</h3>
                <button type="button" class="modal-close" data-modal-close>&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('properties.store') }}">
                    @csrf
                    @php($property = null)
                    @include('properties._form')
                    <div class="form-actions">
                        <button class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-light" data-modal-close>Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('styles')
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet" />
    <style>
        .filter-panel summary {
            cursor: pointer;
            font-weight: 600;
            color: var(--primary);
            user-select: none;
            outline: none;
        }
        @media (max-width: 768px) {
            #properties-map {
                height: 300px !important;
            }
            .mobile-hide { display: none !important; }
            .mobile-show { display: block !important; }
            .page-head { flex-direction: column; align-items: stretch; gap: 1rem; }
            .page-head h1 { text-align: center; }
            .page-head .btn { width: 100%; }
        }
        
        /* Responsive table wrapper */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-top: 1rem;
        }
        .table-responsive table {
            min-width: 800px;
        }
        @media (max-width: 640px) {
            .table-responsive table {
                min-width: 700px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>
    <script>
        (function() {
            mapboxgl.accessToken = 'pk.eyJ1IjoiYWNhc2lsbGFzNzY2IiwiYSI6ImNsdW12cTZyMjB4NnMya213MDdseXp6ZGgifQ.t7-l1lQfd8mgHILM5YrdNw';

            const typeColors = {
                'commercial': '#2063cf',
                'residential': '#1b7b39',
                'mixed': '#f59e0b',
                'other': '#66768a'
            };

            const mapProperties = @json($mapProperties);
            const map = new mapboxgl.Map({
                container: 'properties-map',
                style: 'mapbox://styles/mapbox/streets-v11',
                center: [-103.3494, 20.6597],
                zoom: 10
            });

            const bounds = new mapboxgl.LngLatBounds();
            let hasMarkers = false;

            mapProperties.forEach((property) => {
                if (!property.latitude || !property.longitude) {
                    return;
                }

                const color = typeColors[property.type] || typeColors['other'];
                const photoHtml = property.photo 
                    ? `<div style="margin-top: 0.5rem;"><img src="/storage/${property.photo}" style="width: 100%; height: 80px; object-fit: cover; border-radius: 6px;"></div>` 
                    : '';

                new mapboxgl.Marker({ color: color })
                    .setLngLat([property.longitude, property.latitude])
                    .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML(
                        `<div style="font-family: inherit; font-size: 0.85rem; min-width: 150px;">
                            <strong style="display: block; color: var(--text-main); margin-bottom: 2px;">${property.name}</strong>
                            <span style="color: var(--text-muted); font-size: 0.75rem;">${property.address}</span>
                            ${photoHtml}
                            <div style="margin-top: 0.5rem; text-align: right;">
                                <a href="/properties/${property.id}" style="color: var(--primary); text-decoration: none; font-weight: 500;">Ver detalle &rarr;</a>
                            </div>
                        </div>`
                    ))
                    .addTo(map);

                bounds.extend([property.longitude, property.latitude]);
                hasMarkers = true;
            });

            if (hasMarkers) {
                map.fitBounds(bounds, { padding: 45, maxZoom: 14 });
            }

            // Cierra el panel de filtros por defecto en móvil para ahorrar espacio
            if (window.innerWidth <= 768) {
                const filterPanel = document.querySelector('.filter-panel');
                if (filterPanel) {
                    filterPanel.removeAttribute('open');
                }
            }

            // Fix map rendering when modal opens
            const createBtn = document.querySelector('[data-modal-target="#modal-create-property"]');
            if (createBtn) {
                createBtn.addEventListener('click', () => {
                    setTimeout(() => {
                        if (window.formMap) {
                            window.formMap.resize();
                        }
                    }, 500);
                });
            }
        })();
    </script>
@endpush

