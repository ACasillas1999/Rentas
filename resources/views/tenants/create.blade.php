@extends('layouts.app')

@section('title', 'Nuevo Inquilino')

@section('content')
    <h1>Nuevo Inquilino</h1>

    <div class="card">
        <form method="POST" action="{{ route('tenants.store') }}" enctype="multipart/form-data">
            @csrf
            @include('tenants._form')
            <div class="form-actions">
                <button class="btn btn-primary">Guardar</button>
                <a class="btn btn-light" href="{{ route('tenants.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

