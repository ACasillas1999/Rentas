@extends('layouts.app')

@section('title', 'Registrar Pago')

@section('content')
    <h1>Registrar Pago</h1>

    <div class="card">
        <form method="POST" action="{{ route('payments.store') }}">
            @csrf
            @include('payments._form')
            <div class="form-actions">
                <button class="btn btn-primary">Guardar</button>
                <a class="btn btn-light" href="{{ route('payments.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

