<div class="form-grid">
    <div>
        <label for="property_id">Propiedad</label>
        <select id="property_id" name="property_id" required>
            <option value="">Seleccionar</option>
            @foreach ($properties as $property)
                <option value="{{ $property->id }}" @selected((string) old('property_id', $unit->property_id ?? '') === (string) $property->id)>
                    {{ $property->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="beneficiary_id">Beneficiario (Usuario)</label>
        <select id="beneficiary_id" name="beneficiary_id">
            <option value="">Ninguno</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected((string) old('beneficiary_id', $unit->beneficiary_id ?? '') === (string) $user->id)>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="code">Código del Local/Unidad</label>
        <input id="code" name="code" value="{{ old('code', $unit->code ?? '') }}" required>
    </div>
    <div>
        <label for="floor">Piso</label>
        <input id="floor" name="floor" value="{{ old('floor', $unit->floor ?? '') }}">
    </div>
    <div>
        <label for="area_m2">Área (m²)</label>
        <input id="area_m2" name="area_m2" type="number" step="0.01" min="0" value="{{ old('area_m2', $unit->area_m2 ?? '') }}">
    </div>
    <div>
        <label for="status">Estatus</label>
        <select id="status" name="status" required>
            @foreach (['available' => 'Disponible', 'rented' => 'Rentado', 'maintenance' => 'Mantenimiento'] as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $unit->status ?? 'available') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="field-span-full">
        <label for="notes">Notas</label>
        <textarea id="notes" name="notes">{{ old('notes', $unit->notes ?? '') }}</textarea>
    </div>
    <div class="field-span-full">
        <label for="photo">Foto del Local/Unidad</label>
        <input type="file" id="photo" name="photo" accept="image/*">
        @if(isset($unit) && $unit->photo)
            <div style="margin-top: 0.5rem;">
                <img src="{{ asset('storage/' . $unit->photo) }}" alt="Foto actual" style="max-width: 200px; border-radius: 8px;">
            </div>
        @endif
    </div>
</div>

