@extends('layouts.app')

@section('title', 'Editar Contrato')

@section('content')
    <h1>Editar Contrato</h1>

    <div class="card">
        <form method="POST" action="{{ route('leases.update', $lease) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('leases._form')
            <div class="form-actions">
                <button class="btn btn-primary">Actualizar</button>
                <a class="btn btn-light" href="{{ route('leases.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

