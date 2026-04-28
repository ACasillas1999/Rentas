<div style="display: grid; gap: 1.5rem;">
    <!-- SECCIÓN 1: IDENTIFICACIÓN -->
    <div style="background: var(--surface-soft); padding: 1.2rem; border-radius: 12px; border: 1px solid var(--border);">
        <h4 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; color: var(--primary);">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
            Identificación de la Unidad
        </h4>
        <div class="form-grid">
            <div class="field-span-full">
                <label for="property_id">Propiedad Perteneciente</label>
                <select id="property_id" name="property_id" required>
                    <option value="">Seleccionar propiedad...</option>
                    @foreach ($properties as $property)
                        <option value="{{ $property->id }}" @selected((string) old('property_id', $unit->property_id ?? '') === (string) $property->id)>
                            {{ $property->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="code">Código / Número</label>
                <input id="code" name="code" value="{{ old('code', $unit->code ?? '') }}" placeholder="Ej. Local 101" required>
            </div>
            <div>
                <label for="floor">Nivel / Piso</label>
                <input id="floor" name="floor" value="{{ old('floor', $unit->floor ?? '') }}" placeholder="Ej. Planta Baja">
            </div>
            <div>
                <label for="area_m2">Superficie (m²)</label>
                <input id="area_m2" name="area_m2" type="number" step="0.01" min="0" value="{{ old('area_m2', $unit->area_m2 ?? '') }}" placeholder="0.00">
            </div>
            <div>
                <label for="status">Estado Inicial</label>
                <select id="status" name="status" required>
                    @foreach (['available' => 'Disponible', 'rented' => 'Rentado', 'maintenance' => 'Mantenimiento'] as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $unit->status ?? 'available') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: ECONOMÍA Y BENEFICIARIO -->
    <div style="background: var(--surface-soft); padding: 1.2rem; border-radius: 12px; border: 1px solid var(--border);">
        <h4 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; color: var(--primary);">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            Valores Sugeridos y Pago
        </h4>
        <div class="form-grid">
            <div>
                <label for="monthly_rent">Renta Mensual Sugerida</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--muted);">$</span>
                    <input id="monthly_rent" name="monthly_rent" type="number" step="0.01" value="{{ old('monthly_rent', $unit->monthly_rent ?? '') }}" style="padding-left: 25px;" placeholder="0.00">
                </div>
            </div>
            <div>
                <label for="maintenance_amount">Cuota Mantenimiento</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--muted);">$</span>
                    <input id="maintenance_amount" name="maintenance_amount" type="number" step="0.01" value="{{ old('maintenance_amount', $unit->maintenance_amount ?? '') }}" style="padding-left: 25px;" placeholder="0.00">
                </div>
            </div>
            <div class="field-span-full">
                <label for="beneficiary_id">Beneficiario del Pago</label>
                <select id="beneficiary_id" name="beneficiary_id">
                    <option value="">Seleccionar beneficiario...</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('beneficiary_id', $unit->beneficiary_id ?? '') === (string) $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <p style="font-size: 0.75rem; color: var(--muted); margin-top: 0.3rem;">Este usuario recibirá los pagos de esta unidad por defecto.</p>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 3: DETALLES EXTRA -->
    <div style="background: var(--surface-soft); padding: 1.2rem; border-radius: 12px; border: 1px solid var(--border);">
        <h4 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; color: var(--primary);">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
            Multimedia y Notas
        </h4>
        <div class="form-grid">
            <div class="field-span-full">
                <label for="photo">Fotografía del Local</label>
                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <input type="file" id="photo" name="photo" accept="image/*" style="padding: 0.5rem 0;">
                    </div>
                    @if(isset($unit) && $unit->photo)
                        <div style="flex-shrink: 0;">
                            <img src="{{ asset('storage/' . $unit->photo) }}" alt="Foto actual" style="width: 80px; height: 60px; object-fit: cover; border-radius: 6px; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        </div>
                    @endif
                </div>
            </div>
            <div class="field-span-full">
                <label for="notes">Notas Internas</label>
                <textarea id="notes" name="notes" placeholder="Características especiales, estado de entrega, etc." style="height: 80px;">{{ old('notes', $unit->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

