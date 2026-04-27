@extends('layouts.app')

@section('title', 'Detalle de Inquilino')

@section('content')
    <div class="page-head">
        <h1>{{ $tenant->full_name }}</h1>
        <a class="btn btn-light" href="{{ route('tenants.index') }}">Volver</a>
    </div>

    <div class="card" style="display: flex; gap: 2rem; align-items: start;">
        @if($tenant->photo)
            <div style="flex-shrink: 0;">
                <img src="{{ asset('storage/' . $tenant->photo) }}" alt="Foto del inquilino" style="max-width: 300px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
            </div>
        @endif
        <div style="flex-grow: 1;">
            <p><strong>Documento:</strong> {{ $tenant->document_id ?: '-' }}</p>
            <p><strong>Teléfono:</strong> {{ $tenant->phone ?: '-' }}</p>
            <p><strong>Correo:</strong> {{ $tenant->email ?: '-' }}</p>
            <p><strong>Dirección:</strong> {{ $tenant->address ?: '-' }}</p>
            <p><strong>Notas:</strong> {{ $tenant->notes ?: '-' }}</p>
        </div>
    </div>

    <div class="card">
        <h3>Contratos del Inquilino</h3>
        @if ($tenant->leases->isEmpty())
            <p class="muted">No tiene contratos registrados.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Propiedad / Unidad</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tenant->leases as $lease)
                        <tr>
                            <td>{{ $lease->contract_number ?: 'Sin folio' }}</td>
                            <td>{{ $lease->unit->property->name ?? '-' }} / {{ $lease->unit->code ?? '-' }}</td>
                            <td>{{ $lease->start_date?->format('d/m/Y') }}</td>
                            <td>{{ $lease->end_date?->format('d/m/Y') ?: 'Abierto' }}</td>
                            <td>{{ ucfirst($lease->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <h3>Historial de Pagos</h3>
        @php
            $allPayments = $tenant->leases->flatMap->payments->sortByDesc('due_date');
            $statusColors = [
                'paid'    => ['bg' => '#e6f6ec', 'text' => '#1a7f3c', 'label' => 'Pagado'],
                'pending' => ['bg' => '#fff8eb', 'text' => '#c47a0a', 'label' => 'Pendiente'],
                'partial' => ['bg' => '#eef2ff', 'text' => '#4338ca', 'label' => 'Parcial'],
                'overdue' => ['bg' => '#fff1f1', 'text' => '#c53030', 'label' => 'Vencido'],
            ];
        @endphp

        @if ($allPayments->isEmpty())
            <p class="muted">No hay registros de pagos para este inquilino.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Vencimiento</th>
                        <th>Periodo</th>
                        <th>Unidad</th>
                        <th>Monto Total</th>
                        <th>Pagado</th>
                        <th>Estatus</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allPayments as $payment)
                        @php
                            $st = $statusColors[$payment->status] ?? ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => $payment->status];
                            $totalAmount = (float)$payment->amount + (float)$payment->late_fee;
                        @endphp
                        <tr>
                            <td>{{ $payment->due_date?->format('d/m/Y') }}</td>
                            <td style="font-weight:600;">{{ $payment->period_label }}</td>
                            <td>{{ $payment->lease->unit->code ?? '-' }}</td>
                            <td style="font-weight:600;">${{ number_format($totalAmount, 2) }}</td>
                            <td style="color:var(--success);font-weight:600;">${{ number_format((float)$payment->paid_amount, 2) }}</td>
                            <td>
                                <span class="badge" style="background:{{ $st['bg'] }};color:{{ $st['text'] }};border:none;">
                                    {{ $st['label'] }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('payments.show', $payment) }}" class="btn btn-light" style="padding:0.25rem 0.5rem;font-size:0.75rem;">
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

