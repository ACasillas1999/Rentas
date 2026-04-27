{{-- SECCIÓN 1: IDENTIFICACIÓN --}}
<div style="margin-bottom: 2rem;">
    <h4 style="margin-bottom: 1rem; color: var(--primary); display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
        <span>🏷️</span> Identificación del Contrato
    </h4>
    <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
        <div>
            <label for="unit_id">Unidad / Propiedad</label>
            <select id="unit_id" name="unit_id" required>
                <option value="">Seleccionar</option>
                @foreach ($units as $unitOption)
                    @php
                        $isOccupied = $unitOption->leases->isNotEmpty();
                        if (isset($lease) && $isOccupied) {
                            $isOccupied = !$unitOption->leases->contains('id', $lease->id);
                        }
                    @endphp
                    <option value="{{ $unitOption->id }}" @selected((string) old('unit_id', $lease->unit_id ?? '') === (string) $unitOption->id)>
                        {{ $unitOption->property->name ?? '-' }} / {{ $unitOption->code }} {{ $isOccupied ? '(Ocupado)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="tenant_id">Inquilino</label>
            <select id="tenant_id" name="tenant_id" required>
                <option value="">Seleccionar</option>
                @foreach ($tenants as $tenantOption)
                    <option value="{{ $tenantOption->id }}" @selected((string) old('tenant_id', $lease->tenant_id ?? '') === (string) $tenantOption->id)>
                        {{ $tenantOption->full_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="contract_number">Folio de Contrato</label>
            <input id="contract_number" name="contract_number" value="{{ old('contract_number', $lease->contract_number ?? '') }}" placeholder="Ej: CT-2024-001">
        </div>
        <div>
            <label for="status">Estatus</label>
            <select id="status" name="status" required>
                @foreach (['active' => 'Activo', 'finished' => 'Finalizado', 'cancelled' => 'Cancelado'] as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $lease->status ?? 'active') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- SECCIÓN 2: VIGENCIA Y PERIODOS --}}
<div style="margin-bottom: 2rem; background: var(--surface-soft); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--border);">
    <h4 style="margin-top: 0; margin-bottom: 1rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">
        <span>📅</span> Vigencia y Periodos
    </h4>
    <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div>
            <label for="start_date">Fecha Inicio</label>
            <input id="start_date" name="start_date" type="date" value="{{ old('start_date', isset($lease) && $lease->start_date ? $lease->start_date->format('Y-m-d') : '') }}" required>
        </div>
        <div>
            <label for="end_date">Fecha Fin</label>
            <input id="end_date" name="end_date" type="date" value="{{ old('end_date', isset($lease) && $lease->end_date ? $lease->end_date->format('Y-m-d') : '') }}">
        </div>
        <div class="field-span-full" style="margin-top: 0.5rem;">
            <label for="first_period_start">Inicio del Primer Periodo de Pago</label>
            <input id="first_period_start" name="first_period_start" type="date"
                   value="{{ old('first_period_start', isset($lease) && $lease->first_period_start ? $lease->first_period_start->format('Y-m-d') : (isset($lease) && $lease->start_date ? $lease->start_date->format('Y-m-d') : '')) }}">
            <p class="muted" style="margin-top: 0.4rem; font-size: 0.8rem;">
                Determina el ciclo de cobro mensual. Por defecto es igual a la fecha de inicio.
            </p>
        </div>
    </div>
</div>

{{-- SECCIÓN 3: CONDICIONES ECONÓMICAS --}}
<div style="margin-bottom: 2rem;">
    <h4 style="margin-bottom: 1rem; color: var(--success); display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
        <span>💰</span> Condiciones Económicas
    </h4>
    <div class="form-grid">
        <div>
            <label for="monthly_amount">Renta Mensual ($)</label>
            <input id="monthly_amount" name="monthly_amount" type="number" step="0.01" min="0" value="{{ old('monthly_amount', $lease->monthly_amount ?? '') }}" required placeholder="0.00">
        </div>
        <div>
            <label for="deposit_amount">Depósito ($)</label>
            <input id="deposit_amount" name="deposit_amount" type="number" step="0.01" min="0" value="{{ old('deposit_amount', $lease->deposit_amount ?? 0) }}" placeholder="0.00">
        </div>
        <div id="maintenance-amount-field">
            <label for="maintenance_amount">Mantenimiento Mensual ($)</label>
            <input id="maintenance_amount" name="maintenance_amount" type="number" step="0.01" min="0" value="{{ old('maintenance_amount', $lease->maintenance_amount ?? 0) }}" placeholder="0.00">
        </div>
        <div class="field-span-full">
            <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; background: #fff; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px;">
                <input type="checkbox" id="has_maintenance" name="has_maintenance" value="1" style="width: auto;"
                    @checked(old('has_maintenance', (isset($lease) ? ((float)($lease->maintenance_amount ?? 0) > 0 ? 1 : 0) : 1)))>
                <span style="font-weight: 600;">¿Incluye cargo de mantenimiento mensual?</span>
            </label>
        </div>
    </div>
</div>

{{-- SECCIÓN 4: DOCUMENTACIÓN Y NOTAS --}}
<div style="margin-bottom: 1rem;">
    <h4 style="margin-bottom: 1rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
        <span>📝</span> Documentación y Notas
    </h4>
    <div class="form-grid">
        <div class="field-span-full">
            <label for="contract_pdf">Contrato Firmado (PDF)</label>
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <input type="file" id="contract_pdf" name="contract_pdf" accept=".pdf" style="flex: 1; min-width: 250px;">
                @if(isset($lease) && $lease->contract_pdf)
                    <a href="{{ asset('storage/' . $lease->contract_pdf) }}" target="_blank" class="btn btn-light">
                        📄 Ver PDF Actual
                    </a>
                @endif
            </div>
        </div>
        <div class="field-span-full">
            <label for="notes">Notas adicionales o cláusulas especiales</label>
            <textarea id="notes" name="notes" placeholder="Escribe aquí cualquier observación relevante..." style="min-height: 120px;">{{ old('notes', $lease->notes ?? '') }}</textarea>
        </div>
    </div>
</div>

@if(!isset($lease))
    <div style="background: #fffdf2; padding: 1rem; border-radius: 10px; border: 1px solid #f9eec1; margin-bottom: 1.5rem;">
        <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
            <input type="checkbox" name="mark_past_as_paid" value="1" style="width: auto;" @checked(old('mark_past_as_paid'))>
            <span style="font-weight: 700; color: #856404;">Marcar periodos anteriores como pagados</span>
        </label>
        <p class="muted" style="margin: 0.3rem 0 0 1.8rem; font-size: 0.82rem;">
            Útil para contratos que iniciaron hace tiempo y ya están al corriente.
        </p>
    </div>
@endif

<script>
(function () {
    const checkbox = document.getElementById('has_maintenance');
    const field    = document.getElementById('maintenance-amount-field');
    const input    = document.getElementById('maintenance_amount');

    if (!checkbox || !field || !input) return;

    function toggle() {
        if (checkbox.checked) {
            field.style.display = '';
            input.disabled = false;
        } else {
            field.style.display = 'none';
            input.disabled = true;
            input.value = '0';
        }
    }

    checkbox.addEventListener('change', toggle);
    toggle(); // estado inicial
})();
</script>
