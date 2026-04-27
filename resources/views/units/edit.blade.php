@extends('layouts.app')

@section('title', 'Editar Unidad')

@section('content')
    <h1>Editar Unidad</h1>

    <div class="card">
        <form method="POST" action="{{ route('units.update', $unit) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('units._form')
            <div class="form-actions">
                <button class="btn btn-primary">Actualizar</button>
                <a class="btn btn-light" href="{{ route('units.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

