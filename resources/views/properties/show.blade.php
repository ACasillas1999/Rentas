@extends('layouts.app')

@section('title', 'Detalle de Propiedad')

@section('content')
    <div class="page-head">
        <h1>{{ $property->name }}</h1>
        <a class="btn btn-light" href="{{ route('properties.index') }}">Volver</a>
    </div>

    <div class="card property-details">
        @if($property->photo)
            <div class="property-photo">
                <img src="{{ asset('storage/' . $property->photo) }}" alt="Foto de la propiedad">
            </div>
        @endif
        <div class="property-info">
            <p><strong>Tipo:</strong> {{ ucfirst($property->type) }}</p>
            <p><strong>Dirección:</strong> {{ $property->address }}</p>
            <p><strong>Ciudad:</strong> {{ $property->city ?: '-' }}</p>
            <p><strong>Estado:</strong> {{ $property->state ?: '-' }}</p>
            <p><strong>Notas:</strong> {{ $property->notes ?: '-' }}</p>
        </div>
    </div>

    <div class="card">
        <h3>Locales/Unidades</h3>
        @if ($property->units->isEmpty())
            <p class="muted">Esta propiedad no tiene unidades registradas.</p>
        @else
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Piso</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($property->units as $unit)
                        <tr>
                            <td>{{ $unit->code }}</td>
                            <td>{{ $unit->floor ?: '-' }}</td>
                            <td>{{ ucfirst($unit->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .property-details {
            display: flex;
            gap: 2rem;
            align-items: start;
        }
        .property-photo img {
            max-width: 300px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        @media (max-width: 768px) {
            .property-details {
                flex-direction: column;
                align-items: stretch;
                gap: 1.5rem;
            }
            .property-photo img {
                width: 100%;
                max-width: none;
            }
            .page-head {
                flex-direction: column;
                align-items: stretch;
                gap: 0.8rem;
            }
            .page-head h1 { text-align: center; font-size: 1.4rem; }
            .page-head .btn { width: 100%; text-align: center; }
        }
    </style>
@endpush

