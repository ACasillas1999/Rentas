@extends('layouts.app')

@section('title', 'Editar Pago')

@section('content')
    <h1>Editar Pago</h1>

    <div class="card">
        <form method="POST" action="{{ route('payments.update', $payment) }}">
            @csrf
            @method('PUT')
            @include('payments._form')
            <div class="form-actions">
                <button class="btn btn-primary">Actualizar</button>
                <a class="btn btn-light" href="{{ route('payments.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

