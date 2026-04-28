<div class="form-grid">
    <div class="field-span-full">
        <label for="lease_id">Contrato</label>
        <select id="lease_id" name="lease_id" required>
            <option value="">Seleccionar</option>
            @foreach ($leases as $leaseOption)
                <option value="{{ $leaseOption->id }}" @selected((string) old('lease_id', $payment->lease_id ?? '') === (string) $leaseOption->id)>
                    {{ $leaseOption->contract_number ?: 'Sin folio' }} -
                    {{ $leaseOption->tenant->full_name ?? '-' }} -
                    {{ $leaseOption->unit->property->name ?? '-' }}/{{ $leaseOption->unit->code ?? '-' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="field-span-full">
        <label for="type">Tipo de Cobro</label>
        <select id="type" name="type" required>
            <option value="rent" @selected(old('type', $payment->type ?? 'rent') === 'rent')>Renta</option>
            <option value="maintenance" @selected(old('type', $payment->type ?? '') === 'maintenance')>Mantenimiento</option>
        </select>
    </div>
    <div>
        <label for="period_label">Periodo (ej. 2026-02)</label>
        <input id="period_label" name="period_label" value="{{ old('period_label', $payment->period_label ?? '') }}">
    </div>
    <div>
        <label for="due_date">Fecha Vencimiento</label>
        <input id="due_date" name="due_date" type="date" value="{{ old('due_date', isset($payment) && $payment->due_date ? $payment->due_date->format('Y-m-d') : '') }}" required>
    </div>
    <div>
        <label for="paid_at">Fecha de Pago</label>
        <input id="paid_at" name="paid_at" type="date" value="{{ old('paid_at', isset($payment) && $payment->paid_at ? $payment->paid_at->format('Y-m-d') : '') }}">
    </div>
    <div>
        <label for="amount">Monto Total</label>
        <input id="amount" name="amount" type="number" step="0.01" min="0" value="{{ old('amount', $payment->amount ?? '') }}" required>
    </div>
    <div>
        <label for="subtotal">Subtotal (opcional)</label>
        <input id="subtotal" name="subtotal" type="number" step="0.01" min="0" value="{{ old('subtotal', $payment->subtotal ?? '') }}" placeholder="Se calcula si está vacío">
    </div>
    <div>
        <label for="tax_amount">IVA (opcional)</label>
        <input id="tax_amount" name="tax_amount" type="number" step="0.01" min="0" value="{{ old('tax_amount', $payment->tax_amount ?? '') }}" placeholder="Se calcula si está vacío">
    </div>
    <div>
        <label for="late_fee">Recargo</label>
        <input id="late_fee" name="late_fee" type="number" step="0.01" min="0" value="{{ old('late_fee', $payment->late_fee ?? 0) }}">
    </div>
    <div>
        <label for="status">Estatus</label>
        <select id="status" name="status" required>
            @foreach (['pending' => 'Por facturar', 'invoiced' => 'Facturado', 'paid' => 'Pagado', 'overdue' => 'Vencido', 'partial' => 'Parcial'] as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $payment->status ?? 'pending') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="payment_method">Método Pago</label>
        <select id="payment_method" name="payment_method">
            <option value="">Seleccionar</option>
            @foreach(['Transferencia', 'Efectivo', 'Cheque', 'Depósito', 'Tarjeta'] as $method)
                <option value="{{ $method }}" @selected(old('payment_method', $payment->payment_method ?? '') === $method)>{{ $method }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="reference">Referencia</label>
        <input id="reference" name="reference" value="{{ old('reference', $payment->reference ?? '') }}">
    </div>
    <div>
        <label for="invoice_folio">Folio de Factura</label>
        <input id="invoice_folio" name="invoice_folio" value="{{ old('invoice_folio', $payment->invoice_folio ?? '') }}">
    </div>
    <div>
        <label for="invoiced_at">Fecha de Facturación</label>
        <input id="invoiced_at" name="invoiced_at" type="date" value="{{ old('invoiced_at', isset($payment) && $payment->invoiced_at ? $payment->invoiced_at->format('Y-m-d') : '') }}">
    </div>
    <div class="field-span-full">
        <label for="notes">Notas</label>
        <textarea id="notes" name="notes">{{ old('notes', $payment->notes ?? '') }}</textarea>
    </div>
</div>

