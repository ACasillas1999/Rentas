@extends('layouts.app')
@section('title', 'Editar Gasto')
@section('content')
    <div class="page-head">
        <div style="display:flex;align-items:center;gap:0.75rem;">
            <a href="{{ route('expenses.index') }}" class="btn btn-light" style="padding:0.4rem;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;" title="Volver">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </a>
            <h1>Editar Gasto</h1>
        </div>
    </div>

    <div class="card" style="max-width:900px;">
        <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('expenses._form')
            <div class="form-actions" style="margin-top:2rem;border-top:1px solid #e8edf3;padding-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="{{ route('expenses.index') }}" class="btn btn-light">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
