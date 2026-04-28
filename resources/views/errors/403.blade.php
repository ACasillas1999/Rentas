@extends('layouts.app')

@section('title', 'Acceso Denegado')

@section('content')
<div style="display:flex;align-items:center;justify-content:center;min-height:60vh;">
    <div style="text-align:center;max-width:480px;">
        <div style="font-size:5rem;margin-bottom:1rem;">🔒</div>
        <h1 style="font-size:2rem;margin-bottom:0.5rem;color:#ff6b6b;">403</h1>
        <h2 style="font-size:1.2rem;margin-bottom:1rem;color:#c8d4e4;">Acceso Denegado</h2>
        <p style="color:#8899aa;margin-bottom:2rem;">
            {{ $exception->getMessage() ?: 'No tienes permiso para acceder a esta sección.' }}
        </p>
        <a href="{{ route('dashboard') }}" class="btn"
           style="display:inline-block;padding:0.6rem 1.5rem;background:var(--accent, #4a90d9);color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">
            ← Volver al Dashboard
        </a>
    </div>
</div>
@endsection
