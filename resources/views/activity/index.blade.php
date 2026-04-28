@extends('layouts.app')

@section('title', 'Bitácora de Actividad')

@push('styles')
<style>
    .log-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        align-items: flex-end;
        margin-bottom: 1.25rem;
    }
    .log-filters .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 140px;
    }
    .log-filters label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 0;
    }
    .log-table th {
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: var(--muted);
        font-weight: 700;
        padding: 0.55rem 0.75rem;
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
    }
    .log-table td {
        padding: 0.6rem 0.75rem;
        border-bottom: 1px solid var(--border);
        font-size: 0.875rem;
        vertical-align: middle;
    }
    .log-table tr:hover td {
        background: var(--surface-soft);
    }
    .action-badge {
        display: inline-block;
        padding: 0.18rem 0.55rem;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .role-chip {
        display: inline-block;
        padding: 0.12rem 0.45rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        background: rgba(0,0,0,0.07);
        color: #555;
        margin-left: 0.3rem;
    }
    .module-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.15rem 0.5rem;
        border-radius: 8px;
        background: var(--surface-soft);
        border: 1px solid var(--border);
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text);
        white-space: nowrap;
    }
    .log-desc {
        color: var(--text);
        max-width: 380px;
    }
    .log-meta {
        font-size: 0.75rem;
        color: var(--muted);
    }
    .time-relative {
        font-size: 0.78rem;
        color: var(--muted);
        white-space: nowrap;
    }
    .ip-cell {
        font-size: 0.73rem;
        color: var(--muted);
        font-family: monospace;
    }
    .log-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--muted);
    }
    .log-empty .empty-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    .stats-bar {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .stat-chip {
        background: var(--surface-soft);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 0.5rem 0.85rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .stat-chip span {
        font-size: 0.7rem;
        color: var(--muted);
        font-weight: 400;
    }
</style>
@endpush

@section('content')
<div class="page-head">
    <div>
        <h1 style="margin:0;font-size:1.4rem;">🗂️ Bitácora de Actividad</h1>
        <p style="margin:0.25rem 0 0;color:var(--muted);font-size:0.875rem;">
            Registro de quién hizo qué y cuándo en el sistema
        </p>
    </div>
    <div style="font-size:0.82rem;color:var(--muted);">
        Total: <strong style="color:var(--text);">{{ number_format($logs->total()) }}</strong> registros
    </div>
</div>

{{-- Barra de filtros --}}
<div class="card" style="padding:1rem;margin-bottom:1rem;">
    <form method="GET" action="{{ route('activity.index') }}" id="filterForm">
        <div class="log-filters">
            <div class="filter-group" style="flex:2;min-width:200px;">
                <label>Buscar descripción</label>
                <input type="text" name="q" placeholder="Ej: contrato, pagó, Juan…" value="{{ request('q') }}">
            </div>
            <div class="filter-group">
                <label>Módulo</label>
                <select name="module">
                    <option value="">Todos</option>
                    @foreach($modules as $key => $label)
                        <option value="{{ $key }}" {{ request('module') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Acción</label>
                <select name="action">
                    <option value="">Todas</option>
                    @foreach($actions as $key => $label)
                        <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Usuario</label>
                <select name="user_id">
                    <option value="">Todos</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Desde</label>
                <input type="date" name="from" value="{{ request('from') }}">
            </div>
            <div class="filter-group">
                <label>Hasta</label>
                <input type="date" name="to" value="{{ request('to') }}">
            </div>
            <div class="filter-group" style="justify-content:flex-end;">
                <label>&nbsp;</label>
                <div style="display:flex;gap:0.4rem;">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('activity.index') }}" class="btn btn-secondary">Limpiar</a>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Tabla de bitácora --}}
<div class="card" style="padding:0;">
    @if($logs->count() > 0)
        <div style="overflow-x:auto;">
            <table class="log-table" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Usuario</th>
                        <th>Módulo</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        @php
                            $badge = \App\Models\ActivityLog::actionBadge($log->action);
                            $icon  = \App\Models\ActivityLog::moduleIcon($log->module);
                            $moduleName = $modules[$log->module] ?? ucfirst($log->module);
                        @endphp
                        <tr>
                            {{-- Fecha --}}
                            <td>
                                <div class="time-relative" title="{{ $log->created_at->format('d/m/Y H:i:s') }}">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                                <div class="log-meta">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                            </td>

                            {{-- Usuario --}}
                            <td>
                                <div style="font-weight:600;font-size:0.875rem;">
                                    {{ $log->user_name ?? '—' }}
                                </div>
                                @if($log->user_role)
                                    @php
                                        $roleBadges = ['admin' => '🛡️ Admin', 'manager' => '⚙️ Manager', 'viewer' => '👁️ Viewer'];
                                    @endphp
                                    <span class="role-chip">{{ $roleBadges[$log->user_role] ?? $log->user_role }}</span>
                                @endif
                            </td>

                            {{-- Módulo --}}
                            <td>
                                <span class="module-pill">
                                    {{ $icon }} {{ $moduleName }}
                                </span>
                            </td>

                            {{-- Acción --}}
                            <td>
                                <span class="action-badge"
                                    style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};">
                                    {{ $badge['label'] }}
                                </span>
                            </td>

                            {{-- Descripción --}}
                            <td class="log-desc">
                                {{ $log->description }}
                            </td>

                            {{-- IP --}}
                            <td class="ip-cell">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div style="padding:0.75rem 1rem;border-top:1px solid var(--border);">
            {{ $logs->withQueryString()->links() }}
        </div>
    @else
        <div class="log-empty">
            <div class="empty-icon">📋</div>
            <p style="margin:0;font-weight:600;">No hay registros con esos filtros</p>
            <p class="log-meta" style="margin:0.25rem 0 0;">Prueba ajustando los filtros o borrándolos</p>
        </div>
    @endif
</div>
@endsection
