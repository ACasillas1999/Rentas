@extends('layouts.app')

@section('title', 'Notificaciones de Vencimiento')

@section('content')
    <div class="page-head" style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h1>Gestión de Notificaciones</h1>
            <p class="muted">Configura alertas por correo para contratos próximos a vencer.</p>
        </div>
        @if(auth()->user()->hasPermission('notifications.manage'))
            <div>
                <form method="POST" action="{{ route('notifications.run') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="background:#0f172a; border-color:#0f172a;">
                        🚀 Ejecutar Proceso Ahora
                    </button>
                </form>
            </div>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-ok">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <h3>Contratos Activos</h3>
        <p class="muted" style="margin-bottom: 1.5rem;">Total de contratos activos con/sin notificaciones configuradas.</p>
        
        <table>
            <thead>
                <tr>
                    <th>Unidad / Inquilino</th>
                    <th>Vencimiento</th>
                    <th>Días Restantes</th>
                    <th>Correos Configurados</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leases as $lease)
                    @php
                        $days = \Carbon\Carbon::today()->diffInDays($lease->end_date, false);
                        $statusClass = $days <= 30 ? ($days <= 0 ? 'badge-bad' : 'badge-warn') : 'badge-ok';
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #1e293b;">{{ $lease->unit->code }}</div>
                            <div style="font-size: 0.85rem; color: var(--muted);">{{ $lease->tenant->full_name }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $lease->end_date->format('d/m/Y') }}</div>
                            <div style="font-size: 0.8rem; color: var(--muted);">#{{ $lease->contract_number }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $statusClass }}">
                                {{ $days < 0 ? 'Vencido hace ' . abs($days) . ' días' : ($days == 0 ? 'Vence Hoy' : $days . ' días restantes') }}
                            </span>
                        </td>
                        <td>
                            @if($lease->notifications->count() > 0)
                                <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                                    @foreach($lease->notifications as $notif)
                                        <div style="display: flex; align-items: center; justify-content: space-between; background: #f1f5f9; padding: 0.3rem 0.6rem; border-radius: 6px; font-size: 0.85rem;">
                                            <span>{{ $notif->email }}</span>
                                            @if(auth()->user()->hasPermission('notifications.manage'))
                                                <form method="POST" action="{{ route('notifications.destroy', $notif) }}" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" style="border:none; background:none; color:#dc2626; cursor:pointer;" onclick="return confirm('¿Eliminar esta alerta?')">&times;</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="muted" style="font-style: italic;">Sin notificaciones</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            @if(auth()->user()->hasPermission('notifications.manage'))
                                <button type="button" class="btn btn-primary btn-sm" 
                                        onclick="openNotificationModal('{{ $lease->id }}', '{{ $lease->tenant->full_name }}', '{{ $lease->unit->code }}')">
                                    + Agregar Alerta
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem;">No hay contratos activos para notificar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('modals')
    <div class="modal-overlay" id="modal-notif">
        <div class="modal-dialog">
            <div class="modal-head">
                <h3 class="modal-title">Nueva Notificación</h3>
                <button type="button" class="modal-close" onclick="closeNotificationModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p class="modal-note" id="modal-lease-info"></p>
                <form method="POST" action="{{ route('notifications.store') }}">
                    @csrf
                    <input type="hidden" name="lease_id" id="modal-lease-id">
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="notif-email">Correo Electrónico Destino</label>
                        <input type="email" name="email" id="notif-email" required placeholder="ejemplo@correo.com">
                    </div>

                    <div style="background: #f8fafc; padding: 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <label style="margin-bottom: 0.8rem; display: block; color: #475569;">Opciones de Aviso:</label>
                        
                        <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                            <label style="display: flex; align-items: center; gap: 0.6rem; font-weight: 500; cursor: pointer;">
                                <input type="checkbox" name="notify_30_days" value="1" checked style="width: auto;"> 
                                📅 1 Mes antes (30 días)
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 0.6rem; font-weight: 500; cursor: pointer;">
                                <input type="checkbox" name="notify_15_days" value="1" checked style="width: auto;"> 
                                📅 15 Días antes
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 0.6rem; font-weight: 500; cursor: pointer;">
                                <input type="checkbox" name="notify_end_date" value="1" checked style="width: auto;"> 
                                🚨 El mismo día del vencimiento
                            </label>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                        <button type="button" class="btn btn-light" onclick="closeNotificationModal()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('styles')
<style>
    .btn-sm { padding: 0.3rem 0.6rem; font-size: 0.8rem; }
    input[type="checkbox"] { height: 20px; width: 20px; }
</style>
@endpush

@push('scripts')
<script>
    function openNotificationModal(leaseId, tenantName, unitCode) {
        document.getElementById('modal-lease-id').value = leaseId;
        document.getElementById('modal-lease-info').textContent = 'Configurando alertas para: ' + tenantName + ' (' + unitCode + ')';
        document.getElementById('modal-notif').classList.add('is-open');
        document.body.classList.add('modal-open');
    }

    function closeNotificationModal() {
        document.getElementById('modal-notif').classList.remove('is-open');
        document.body.classList.remove('modal-open');
    }
</script>
@endpush
