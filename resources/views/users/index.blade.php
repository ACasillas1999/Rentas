@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
    <div class="page-head">
        
        <div class="form-actions" style="margin-top:0;">
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" width="16" height="16" style="margin-right:0.3rem;" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                Nuevo Usuario
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Registrado el</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td style="font-weight:600;">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at?->format('d/m/Y') }}</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-light" href="{{ route('users.edit', $user) }}">Editar</a>
                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('¿Eliminar este usuario definitivamente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:1.5rem;">
            {{ $users->links() }}
        </div>
    </div>
@endsection
