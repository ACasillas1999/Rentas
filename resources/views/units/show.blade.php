@extends('layouts.app')

@section('title', 'Detalle de Unidad')

@section('content')
    <div class="page-head">
        <h1>Unidad {{ $unit->code }}</h1>
        <a class="btn btn-light" href="{{ route('units.index') }}">Volver</a>
    </div>

    <div class="card" style="display: flex; gap: 2rem; align-items: start;">
        @if($unit->photo)
            <div style="flex-shrink: 0;">
                <img src="{{ asset('storage/' . $unit->photo) }}" alt="Foto del local" style="max-width: 300px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
            </div>
        @endif
        <div style="flex-grow: 1;">
            <p><strong>Propiedad:</strong> {{ $unit->property->name ?? '-' }}</p>
            <p><strong>Piso:</strong> {{ $unit->floor ?: '-' }}</p>
            <p><strong>Área:</strong> {{ $unit->area_m2 ? number_format((float) $unit->area_m2, 2) . ' m²' : '-' }}</p>
            <p><strong>Beneficiario:</strong> {{ $unit->beneficiary->name ?? 'N/A' }}</p>
            <p><strong>Estatus:</strong> {{ ucfirst($unit->status) }}</p>
            <p><strong>Notas:</strong> {{ $unit->notes ?: '-' }}</p>
        </div>
    </div>

    <div class="card">
        <h3>Contratos Asociados</h3>
        @if ($unit->leases->isEmpty())
            <p class="muted">No hay contratos para esta unidad.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Contrato</th>
                        <th>Inquilino</th>
                        <th>Periodo</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($unit->leases as $lease)
                        <tr>
                            <td>{{ $lease->contract_number ?: 'Sin folio' }}</td>
                            <td>{{ $lease->tenant->full_name ?? '-' }}</td>
                            <td>{{ $lease->start_date?->format('Y-m-d') }} a {{ $lease->end_date?->format('Y-m-d') ?: 'Abierto' }}</td>
                            <td>{{ ucfirst($lease->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

