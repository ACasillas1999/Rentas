<div style="display: grid; gap: 1.5rem;">
    <!-- SECCIÓN 1: IDENTIFICACIÓN -->
    <div style="background: var(--surface-soft); padding: 1.2rem; border-radius: 12px; border: 1px solid var(--border);">
        <h4 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; color: var(--primary);">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            Datos de la Propiedad
        </h4>
        <div class="form-grid">
            <div>
                <label for="form_name">Nombre de la Propiedad</label>
                <input id="form_name" name="name" value="{{ old('name', $property->name ?? '') }}" placeholder="Ej. Plaza Olivos" required>
            </div>
            <div>
                <label for="form_type">Tipo de Uso</label>
                <select id="form_type" name="type" required>
                    @foreach (['commercial' => 'Comercial', 'residential' => 'Residencial', 'mixed' => 'Mixto', 'other' => 'Otro'] as $key => $label)
                        <option value="{{ $key }}" @selected(old('type', $property->type ?? 'commercial') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: UBICACIÓN -->
    <div style="background: var(--surface-soft); padding: 1.2rem; border-radius: 12px; border: 1px solid var(--border);">
        <h4 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; color: var(--primary);">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            Ubicación Exacta
        </h4>
        <div class="form-grid">
            <div class="field-span-full">
                <label for="form_address">Dirección Completa</label>
                <div id="geocoder-container" style="margin-bottom: 0.8rem;"></div>
                <input id="form_address" name="address" value="{{ old('address', $property->address ?? '') }}" placeholder="Calle, número, colonia..." required>
            </div>
            <div>
                <label for="form_city">Ciudad</label>
                <input id="form_city" name="city" value="{{ old('city', $property->city ?? '') }}" placeholder="Ej. Guadalajara">
            </div>
            <div>
                <label for="form_state">Estado</label>
                <input id="form_state" name="state" value="{{ old('state', $property->state ?? '') }}" placeholder="Ej. Jalisco">
            </div>
        </div>

        <div style="margin-top: 1.2rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; color: var(--muted);">Arrastra el marcador si la ubicación no es exacta:</label>
            <div id="property-form-map" style="width: 100%; height: 300px; border-radius: 10px; border: 1px solid var(--border);"></div>
            <input type="hidden" id="form_latitude" name="latitude" value="{{ old('latitude', $property->latitude ?? '') }}">
            <input type="hidden" id="form_longitude" name="longitude" value="{{ old('longitude', $property->longitude ?? '') }}">
        </div>
    </div>

    <!-- SECCIÓN 3: DOCUMENTACIÓN Y NOTAS -->
    <div style="background: var(--surface-soft); padding: 1.2rem; border-radius: 12px; border: 1px solid var(--border);">
        <h4 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; color: var(--primary);">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Información Adicional
        </h4>
        <div class="form-grid">
            <div class="field-span-full">
                <label for="photo">Fotografía de la fachada</label>
                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <input type="file" id="photo" name="photo" accept="image/*" style="padding: 0.5rem 0;">
                        <p style="font-size: 0.75rem; color: var(--muted); margin-top: 0.2rem;">Formatos aceptados: JPG, PNG. Máx 5MB.</p>
                    </div>
                    @if(isset($property) && $property->photo)
                        <div style="flex-shrink: 0;">
                            <img src="{{ asset('storage/' . $property->photo) }}" alt="Foto actual" style="width: 80px; height: 60px; object-fit: cover; border-radius: 6px; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        </div>
                    @endif
                </div>
            </div>
            <div class="field-span-full">
                <label for="form_notes">Notas Internas</label>
                <textarea id="form_notes" name="notes" placeholder="Cualquier detalle relevante sobre la propiedad..." style="height: 80px;">{{ old('notes', $property->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

@push('styles')
    @once('mapbox_styles')
        <link href='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css' rel='stylesheet' />
        <link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css' type='text/css' />
    @endonce
    <style>
        .mapboxgl-ctrl-geocoder {
            width: 100%;
            max-width: none;
            box-shadow: none;
            border: 1px solid #ccd7e6;
            border-radius: 8px;
        }
        .mapboxgl-ctrl-geocoder--input {
            height: 40px;
            padding: 6px 35px;
        }
    </style>
@endpush

@push('scripts')
    @once('mapbox_scripts')
        <script src='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js'></script>
        <script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js'></script>
    @endonce
    <script>
        (function() {
            mapboxgl.accessToken = 'pk.eyJ1IjoiYWNhc2lsbGFzNzY2IiwiYSI6ImNsdW12cTZyMjB4NnMya213MDdseXp6ZGgifQ.t7-l1lQfd8mgHILM5YrdNw';
            
            const typeColors = {
                'commercial': '#2063cf',
                'residential': '#1b7b39',
                'mixed': '#f59e0b',
                'other': '#66768a'
            };

            const latInput = document.getElementById('form_latitude');
            const lngInput = document.getElementById('form_longitude');
            const addressInput = document.getElementById('form_address');
            const cityInput = document.getElementById('form_city');
            const stateInput = document.getElementById('form_state');
            const typeSelect = document.getElementById('form_type');
            
            let initialLat = parseFloat(latInput.value) || 20.6597;
            let initialLng = parseFloat(lngInput.value) || -103.3494;
            let zoom = latInput.value ? 15 : 12;

            const formMap = new mapboxgl.Map({
                container: 'property-form-map',
                style: 'mapbox://styles/mapbox/streets-v11',
                center: [initialLng, initialLat],
                zoom: zoom
            });

            // Expose for external access (like modal resize)
            window.formMap = formMap;

            let formMarker;
            updateMarker();

            if (latInput.value && lngInput.value) {
                formMarker.setLngLat([initialLng, initialLat]).addTo(formMap);
            }

            const formGeocoder = new MapboxGeocoder({
                accessToken: mapboxgl.accessToken,
                mapboxgl: mapboxgl,
                placeholder: 'Buscar dirección...',
                countries: 'mx',
                marker: false
            });

            document.getElementById('geocoder-container').appendChild(formGeocoder.onAdd(formMap));

            formGeocoder.on('result', (e) => {
                const coords = e.result.center;
                formMarker.setLngLat(coords).addTo(formMap);
                latInput.value = coords[1].toFixed(8);
                lngInput.value = coords[0].toFixed(8);
                addressInput.value = e.result.place_name;
                
                if (e.result.context) {
                    e.result.context.forEach(ctx => {
                        if (ctx.id.includes('place')) cityInput.value = ctx.text;
                        if (ctx.id.includes('region')) stateInput.value = ctx.text;
                    });
                }
            });

            formMap.on('click', (e) => updateLocation(e.lngLat));
            
            typeSelect.addEventListener('change', () => {
                if (formMarker) {
                    const coords = formMarker.getLngLat();
                    updateMarker();
                    formMarker.setLngLat(coords).addTo(formMap);
                }
            });

            function updateMarker() {
                if (formMarker) formMarker.remove();
                const color = typeColors[typeSelect.value] || typeColors['other'];
                formMarker = new mapboxgl.Marker({ draggable: true, color: color });
                formMarker.on('dragend', () => updateLocation(formMarker.getLngLat()));
            }

            function updateLocation(coords) {
                formMarker.setLngLat(coords).addTo(formMap);
                latInput.value = coords.lat.toFixed(8);
                lngInput.value = coords.lng.toFixed(8);
                
                fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${coords.lng},${coords.lat}.json?access_token=${mapboxgl.accessToken}&countries=mx`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.features && data.features.length > 0) {
                            const feature = data.features[0];
                            addressInput.value = feature.place_name;
                            
                            if (feature.context) {
                                feature.context.forEach(ctx => {
                                    if (ctx.id.includes('place')) cityInput.value = ctx.text;
                                    if (ctx.id.includes('region')) stateInput.value = ctx.text;
                                });
                            }
                        }
                    })
                    .catch(err => console.error('Geocoding error:', err));
            }
        })();
    </script>
@endpush

