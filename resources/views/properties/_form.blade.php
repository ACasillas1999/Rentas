<div class="form-grid">
    <div>
        <label for="form_name">Nombre</label>
        <input id="form_name" name="name" value="{{ old('name', $property->name ?? '') }}" required>
    </div>
    <div>
        <label for="form_type">Tipo</label>
        <select id="form_type" name="type" required>
            @foreach (['commercial' => 'Comercial', 'residential' => 'Residencial', 'mixed' => 'Mixto', 'other' => 'Otro'] as $key => $label)
                <option value="{{ $key }}" @selected(old('type', $property->type ?? 'commercial') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="field-span-full">
        <label for="form_address">Dirección</label>
        <div id="geocoder-container" style="margin-bottom: 0.5rem;"></div>
        <input id="form_address" name="address" value="{{ old('address', $property->address ?? '') }}" required>
    </div>
    <div>
        <label for="form_city">Ciudad</label>
        <input id="form_city" name="city" value="{{ old('city', $property->city ?? '') }}">
    </div>
    <div>
        <label for="form_state">Estado</label>
        <input id="form_state" name="state" value="{{ old('state', $property->state ?? '') }}">
    </div>
    <div class="field-span-full">
        <label for="form_notes">Notas</label>
        <textarea id="form_notes" name="notes">{{ old('notes', $property->notes ?? '') }}</textarea>
    </div>
    <div class="field-span-full">
        <label for="photo">Foto de la Propiedad</label>
        <input type="file" id="photo" name="photo" accept="image/*">
        @if(isset($property) && $property->photo)
            <div style="margin-top: 0.5rem;">
                <img src="{{ asset('storage/' . $property->photo) }}" alt="Foto actual" style="max-width: 200px; border-radius: 8px;">
            </div>
        @endif
    </div>
</div>

<div style="margin-top: 1.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Ubicación en el Mapa (Haz clic para seleccionar)</label>
    <div id="property-form-map" style="width: 100%; height: 350px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 2px 8px rgba(0,0,0,0.05);"></div>
    <input type="hidden" id="form_latitude" name="latitude" value="{{ old('latitude', $property->latitude ?? '') }}">
    <input type="hidden" id="form_longitude" name="longitude" value="{{ old('longitude', $property->longitude ?? '') }}">
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

