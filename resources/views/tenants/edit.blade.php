@extends('layouts.app')

@section('title', 'Editar Inquilino')

@section('content')
    <h1>Editar Inquilino</h1>

    <div class="card">
        <form method="POST" action="{{ route('tenants.update', $tenant) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('tenants._form')
            <div class="form-actions">
                <button class="btn btn-primary">Actualizar</button>
                <a class="btn btn-light" href="{{ route('tenants.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

