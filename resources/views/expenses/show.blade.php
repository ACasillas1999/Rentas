@extends('layouts.app')
@section('title', 'Detalle de Gasto')
@section('content')
    <div class="page-head">
        <div style="display:flex;align-items:center;gap:0.75rem;">
            <a href="{{ route('expenses.index') }}" class="btn btn-light" style="padding:0.4rem;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </a>
            <h1>Detalle de Gasto</h1>
        </div>
        <div class="form-actions" style="margin-top:0;">
            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-light">Editar</a>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h2 style="font-size:1rem;margin-bottom:1rem;color:#5b7fa6;">📋 Información del Gasto</h2>
            <table>
                <tr><td class="muted">Propiedad</td><td style="font-weight:600;">{{ $expense->property->name ?? '-' }}</td></tr>
                <tr><td class="muted">Local / Unidad</td><td>{{ $expense->unit->code ?? 'General' }}</td></tr>
                <tr><td class="muted">Categoría</td><td><span class="badge">{{ $expense->category }}</span></td></tr>
                <tr><td class="muted">Descripción</td><td>{{ $expense->description }}</td></tr>
                <tr><td class="muted">Fecha</td><td>{{ $expense->expense_date?->format('d/m/Y') }}</td></tr>
                <tr><td class="muted">Pagado a</td><td>{{ $expense->paid_to ?? '-' }}</td></tr>
                <tr>
                    <td class="muted">Monto</td>
                    <td style="font-size:1.3rem;font-weight:800;color:#b82020;">${{ number_format((float)$expense->amount, 2) }}</td>
                </tr>
            </table>
            @if ($expense->notes)
                <p class="muted" style="margin-top:1rem;font-size:0.88rem;">{{ $expense->notes }}</p>
            @endif
        </div>
        <div class="card">
            <h2 style="font-size:1rem;margin-bottom:1rem;color:#5b7fa6;">📎 Comprobante</h2>
            @if ($expense->receipt)
                @php $ext = pathinfo($expense->receipt, PATHINFO_EXTENSION); @endphp
                @if (in_array(strtolower($ext), ['jpg','jpeg','png','webp']))
                    <img src="{{ Storage::url($expense->receipt) }}" style="width:100%;border-radius:8px;max-height:350px;object-fit:contain;">
                @else
                    <a href="{{ Storage::url($expense->receipt) }}" target="_blank" class="btn btn-primary" style="width:100%;justify-content:center;">
                        <svg viewBox="0 0 24 24" width="16" height="16" style="margin-right:0.4rem;" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16h16a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
                        Ver Comprobante (PDF)
                    </a>
                @endif
            @else
                <p class="muted">Sin comprobante adjunto.</p>
            @endif
        </div>
    </div>
@endsection
