@extends('layouts.app')

@section('title', 'Renovar Contrato')

@section('content')
    <div class="page-head">
        <h1>Renovar Contrato</h1>
        <p class="muted">Estás renovando el contrato #{{ $lease->contract_number }} de {{ $lease->tenant->full_name }}.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-bad">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('leases.renew.store', $lease) }}" enctype="multipart/form-data">
            @csrf
            
            @php
                // Preparamos el objeto para que el _form lo use como "default"
                // Pero sobreescribimos los campos que queremos sugerir
                $lease->contract_number = $suggestedFolio;
                $lease->start_date = $suggestedStart;
                $lease->end_date = null; // Para que el usuario ponga la nueva
                $lease->status = 'active';
            @endphp

            @include('leases._form')

            <div class="form-actions">
                <button class="btn btn-primary" style="background: var(--success); border-color: var(--success);">
                    Confirmar Renovación y Generar Pagos
                </button>
                <a class="btn btn-light" href="{{ route('leases.show', $lease) }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
