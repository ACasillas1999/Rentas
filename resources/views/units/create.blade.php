@extends('layouts.app')

@section('title', 'Nueva Unidad')

@section('content')
    <h1>Nueva Unidad</h1>

    <div class="card">
        <form method="POST" action="{{ route('units.store') }}" enctype="multipart/form-data">
            @csrf
            @include('units._form')
            <div class="form-actions">
                <button class="btn btn-primary">Guardar</button>
                <a class="btn btn-light" href="{{ route('units.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

