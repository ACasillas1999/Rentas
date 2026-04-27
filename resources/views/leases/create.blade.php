@extends('layouts.app')

@section('title', 'Nuevo Contrato')

@section('content')
    <h1>Nuevo Contrato</h1>

    <div class="card">
        <form method="POST" action="{{ route('leases.store') }}" enctype="multipart/form-data">
            @csrf
            @include('leases._form')
            <div class="form-actions">
                <button class="btn btn-primary">Guardar</button>
                <a class="btn btn-light" href="{{ route('leases.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

