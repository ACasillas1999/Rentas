@extends('layouts.app')

@section('title', 'Edición Masiva de Pagos')

@push('styles')
<style>
    .excel-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }
    .excel-table th, .excel-table td {
        border: 1px solid #cbd5e1;
        padding: 0;
        margin: 0;
    }
    .excel-table th {
        background: #f1f5f9;
        padding: 8px 12px;
        font-size: 0.85rem;
        color: #475569;
        font-weight: 600;
        text-align: left;
    }
    .excel-input {
        width: 100%;
        height: 100%;
        border: none;
        padding: 10px;
        box-sizing: border-box;
        outline: none;
        background: transparent;
        font-family: inherit;
        font-size: 0.9rem;
    }
    .excel-input:focus {
        background: #f0f9ff;
        box-shadow: inset 0 0 0 2px #3b82f6;
    }
    .excel-select {
        width: 100%;
        height: 100%;
        border: none;
        padding: 10px;
        outline: none;
        background: transparent;
        cursor: pointer;
    }
    .excel-select:focus {
        background: #f0f9ff;
        box-shadow: inset 0 0 0 2px #3b82f6;
    }
    .readonly-cell {
        padding: 10px;
        background: #f8fafc;
        color: #64748b;
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<div class="page-head">
    <div>
        <h2>Edición Masiva (Estilo Excel)</h2>
        <p class="muted">
            Contrato: {{ $lease->unit->code }} - {{ $lease->tenant->full_name }}
        </p>
    </div>
    <div class="actions">
        <a href="{{ route('leases.show', $lease) }}" class="btn btn-light">← Volver al Contrato</a>
        <button type="submit" form="bulk-edit-form" class="btn" style="background: #10b981; border-color: #10b981; color: white;">
            💾 Guardar Todo
        </button>
    </div>
</div>

<form id="bulk-edit-form" action="{{ route('leases.payments.bulkUpdate', $lease) }}" method="POST">
    @csrf
    
    <div style="overflow-x: auto; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <table class="excel-table">
            <thead>
                <tr>
                    <th>Periodo</th>
                    <th>Tipo</th>
                    <th>Vencimiento</th>
                    <th>F. Factura</th>
                    <th>Folio Factura</th>
                    <th>F. Pago</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $idx => $payment)
                    <tr>
                        <input type="hidden" name="payments[{{ $idx }}][id]" value="{{ $payment->id }}">
                        
                        <td class="readonly-cell" style="font-weight: 500; color: #1e293b;">
                            {{ $payment->period_label }}
                        </td>
                        <td class="readonly-cell">
                            {{ $payment->type === 'maintenance' ? 'Mantenimiento' : 'Renta' }}
                            <br>
                            <small>${{ number_format($payment->amount, 2) }}</small>
                        </td>
                        <td class="readonly-cell">
                            {{ $payment->due_date ? $payment->due_date->format('Y-m-d') : '' }}
                        </td>
                        
                        <!-- Campos Editables -->
                        <td>
                            <input type="date" class="excel-input" 
                                   name="payments[{{ $idx }}][invoiced_at]" 
                                   value="{{ $payment->invoiced_at ? $payment->invoiced_at->format('Y-m-d') : '' }}">
                        </td>
                        <td>
                            <input type="text" class="excel-input" 
                                   name="payments[{{ $idx }}][invoice_folio]" 
                                   value="{{ $payment->invoice_folio }}"
                                   placeholder="Ej. A-1234">
                        </td>
                        <td>
                            <input type="date" class="excel-input" 
                                   name="payments[{{ $idx }}][paid_at]" 
                                   value="{{ $payment->paid_at ? $payment->paid_at->format('Y-m-d') : '' }}">
                        </td>
                        <td>
                            <select class="excel-select" name="payments[{{ $idx }}][status]">
                                <option value="pending" @selected($payment->status === 'pending')>Por Facturar</option>
                                <option value="invoiced" @selected($payment->status === 'invoiced')>Facturado</option>
                                <option value="paid" @selected($payment->status === 'paid')>Pagado</option>
                                <option value="partial" @selected($payment->status === 'partial')>Parcial</option>
                                <option value="overdue" @selected($payment->status === 'overdue')>Vencido</option>
                            </select>
                        </td>
                        
                        <!-- Guardamos el paid_amount actual oculto para no perderlo si no lo editamos aquí -->
                        <input type="hidden" name="payments[{{ $idx }}][paid_amount]" value="{{ $payment->paid_amount }}">
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 1.5rem; text-align: right;">
        <button type="submit" class="btn" style="background: #10b981; border-color: #10b981; color: white; padding: 10px 24px; font-size: 1.1rem;">
            💾 Guardar Cambios Masivos
        </button>
    </div>
</form>

<script>
    // Auto-select text on focus to make overwriting faster
    document.querySelectorAll('.excel-input').forEach(input => {
        input.addEventListener('focus', function() {
            this.select();
        });
        
        // Si ingresan F. Pago y el estatus es 'pending' o 'invoiced' o 'overdue', cambiar a 'paid'
        if (input.type === 'date' && input.name.includes('[paid_at]')) {
            input.addEventListener('change', function() {
                if (this.value) {
                    const statusSelect = this.closest('tr').querySelector('select[name$="[status]"]');
                    if (statusSelect.value !== 'paid') {
                        statusSelect.value = 'paid';
                        statusSelect.style.backgroundColor = '#dcfce7'; // Feedback visual rápido
                        setTimeout(() => statusSelect.style.backgroundColor = 'transparent', 500);
                    }
                }
            });
        }
    });
</script>
@endsection
