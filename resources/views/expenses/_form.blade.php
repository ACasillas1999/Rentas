<div class="form-grid">
    <div>
        <label for="property_id">Propiedad *</label>
        <select name="property_id" id="property_id" required>
            <option value="">— Seleccionar —</option>
            @foreach ($properties as $prop)
                <option value="{{ $prop->id }}" {{ old('property_id', $expense->property_id ?? '') == $prop->id ? 'selected' : '' }}>
                    {{ $prop->name }}
                </option>
            @endforeach
        </select>
        @error('property_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div>
        <label for="unit_id">Local / Unidad (opcional)</label>
        <select name="unit_id" id="unit_id">
            <option value="">— General de la propiedad —</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" {{ old('unit_id', $expense->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                    {{ $unit->code }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="category">Categoría *</label>
        <select name="category" id="category" required>
            <option value="">— Seleccionar —</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat }}" {{ old('category', $expense->category ?? '') == $cat ? 'selected' : '' }}>
                    {{ $cat }}
                </option>
            @endforeach
        </select>
        @error('category') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div>
        <label for="expense_date">Fecha del Gasto *</label>
        <input type="date" id="expense_date" name="expense_date" value="{{ old('expense_date', isset($expense) ? $expense->expense_date?->format('Y-m-d') : now()->toDateString()) }}" required>
        @error('expense_date') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div>
        <label for="description">Descripción *</label>
        <input type="text" id="description" name="description" value="{{ old('description', $expense->description ?? '') }}" required placeholder="Ej. Reparación de fuga en tubería">
        @error('description') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div>
        <label for="amount">Monto ($) *</label>
        <input type="number" id="amount" name="amount" min="0" step="0.01" value="{{ old('amount', isset($expense) ? number_format((float)$expense->amount, 2, '.', '') : '') }}" required placeholder="0.00">
        @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div>
        <label for="paid_to">Pagado a (Proveedor / Técnico)</label>
        <input type="text" id="paid_to" name="paid_to" value="{{ old('paid_to', $expense->paid_to ?? '') }}" placeholder="Nombre del proveedor">
    </div>
    <div>
        <label for="receipt">Comprobante (Factura / Foto)</label>
        @if (isset($expense) && $expense->receipt)
            <p class="muted" style="font-size:0.82rem;margin-bottom:0.3rem;">
                📎 <a href="{{ route('secure.download', ['file' => encrypt($expense->receipt)]) }}" target="_blank">Ver comprobante actual</a>
            </p>
        @endif
        <input type="file" id="receipt" name="receipt" accept="image/*,.pdf">
    </div>
    <div class="span-2">
        <label for="notes">Notas adicionales</label>
        <textarea id="notes" name="notes" rows="2" placeholder="Información adicional sobre el gasto...">{{ old('notes', $expense->notes ?? '') }}</textarea>
    </div>
</div>

<script>
document.getElementById('property_id')?.addEventListener('change', function () {
    const propertyId = this.value;
    const unitSelect = document.getElementById('unit_id');
    unitSelect.innerHTML = '<option value="">— General de la propiedad —</option>';
    if (!propertyId) return;

    fetch(`/expenses/units/${propertyId}`)
        .then(r => r.json())
        .then(units => {
            units.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = u.code;
                unitSelect.appendChild(opt);
            });
        });
});
</script>
