@extends('layouts.app')

@section('title', 'Editar Propiedad')

@section('content')
    <h1>Editar Propiedad</h1>

    <div class="card">
        <form method="POST" action="{{ route('properties.update', $property) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('properties._form')
            <div class="form-actions">
                <button class="btn btn-primary">Actualizar</button>
                <a class="btn btn-light" href="{{ route('properties.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

