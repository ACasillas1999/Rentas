@extends('layouts.app')

@section('title', 'Reporte Mensual Automático')

@section('content')

<div class="page-head">
    <div>
        <h1>📊 Reporte Mensual Automático</h1>
        <p class="muted">Configura los destinatarios y previsualiza o envía el resumen de cobranza.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-ok">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-bad">{{ session('error') }}</div>
@endif
@if(session('artisan_output'))
    <div class="alert" style="background:#f0f9ff; border-color:#bae6fd; color:#0c4a6e; white-space:pre-wrap; font-family:monospace; font-size:0.82rem;">
{{ session('artisan_output') }}
    </div>
@endif

<div style="display:grid; grid-template-columns: 340px 1fr; gap: 1.2rem; align-items: start;">

    {{-- ══════════════════════════════════════
         COLUMNA IZQUIERDA: Configuración
    ══════════════════════════════════════ --}}
    <div>

        {{-- ── Bloque de período ── --}}
        <div class="card" style="margin-bottom:1rem;">
            <h3 style="margin-bottom:1rem;">🗓️ Seleccionar Período</h3>
            <form method="GET" action="{{ route('reports.monthly.index') }}" style="display:flex; gap:0.5rem; align-items:flex-end;">
                <div style="flex:1;">
                    <label>Mes</label>
                    <select name="month">
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" @selected($m == $month)>
                                {{ ucfirst(\Carbon\Carbon::create(2000,$m,1)->locale('es')->isoFormat('MMMM')) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;">
                    <label>Año</label>
                    <select name="year">
                        @for($y = date('Y'); $y >= $firstYear; $y--)
                            <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-light">Ver</button>
            </form>
        </div>

        {{-- ── Configuración de destinatarios ── --}}
        <div class="card" style="margin-bottom:1rem;">
            <h3>⚙️ Configuración</h3>
            <p class="muted" style="margin-bottom:1rem;">Define quién recibirá el resumen el 1° de cada mes.</p>

            <form method="POST" action="{{ route('reports.monthly.save') }}">
                @csrf

                <div style="margin-bottom:1rem;">
                    <label>📧 Correo(s) destinatario</label>
                    <input type="text" name="report_email"
                           value="{{ $config['report_email'] }}"
                           placeholder="correo@ejemplo.com, otro@ejemplo.com">
                    <p class="muted" style="margin-top:0.3rem;">Separa múltiples correos con comas.</p>
                </div>

                <div style="margin-bottom:1.2rem;">
                    <label>💬 Número(s) de WhatsApp</label>
                    <input type="text" name="report_whatsapp"
                           value="{{ $config['report_whatsapp'] }}"
                           placeholder="5211234567890, 5219876543210">
                    <p class="muted" style="margin-top:0.3rem;">Formato internacional sin +, ej: 5212221234567</p>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:0.75rem; margin-bottom:1.2rem;">
                    <p style="font-size:0.78rem; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 0.5rem;">API WhatsApp Business</p>
                    <div style="font-size:0.82rem; color:#64748b; line-height:1.8;">
                        <div>🔑 Token: <code style="background:#e2e8f0; padding:1px 5px; border-radius:4px;">{{ $config['waba_token'] }}</code></div>
                        <div>📱 Phone ID: <code style="background:#e2e8f0; padding:1px 5px; border-radius:4px;">{{ $config['waba_phone_id'] }}</code></div>
                    </div>
                </div>

                <div style="background:#fef3c7; border:1px solid #fcd34d; border-radius:10px; padding:0.75rem; margin-bottom:1.2rem;">
                    <p style="font-size:0.82rem; color:#92400e; margin:0;">
                        ⏰ <strong>Envío automático:</strong> El día 1 de cada mes a las 8:00 AM (via Laravel Scheduler). Requiere que el CRON del servidor esté configurado.
                    </p>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">
                    💾 Guardar Configuración
                </button>
            </form>
        </div>

        {{-- ── Envío manual ── --}}
        <div class="card">
            <h3>🚀 Enviar Ahora</h3>
            <p class="muted" style="margin-bottom:1rem;">Envía el reporte del período seleccionado inmediatamente.</p>

            <form method="POST" action="{{ route('reports.monthly.send') }}"
                  onsubmit="return confirm('¿Enviar el reporte de {{ $periodLabel }} a los destinatarios configurados?')">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year"  value="{{ $year }}">

                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:0.75rem; margin-bottom:1rem;">
                    <p style="font-size:0.82rem; color:#166534; margin:0 0 0.3rem; font-weight:600;">Se enviará a:</p>
                    <p style="font-size:0.82rem; color:#166534; margin:0;">
                        📧 {{ $config['report_email'] ?: '(sin correo configurado)' }}<br>
                        💬 {{ $config['report_whatsapp'] ?: '(sin WhatsApp configurado)' }}
                    </p>
                </div>

                <button type="submit" class="btn btn-primary"
                        style="width:100%; background:#0f172a; border-color:#0f172a;"
                        @if(!$config['report_email'] && !$config['report_whatsapp']) disabled @endif>
                    📤 Enviar Reporte de {{ $periodLabel }}
                </button>
            </form>
        </div>

    </div>

    {{-- ══════════════════════════════════════
         COLUMNA DERECHA: Vista previa del resumen
    ══════════════════════════════════════ --}}
    <div>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
                <div>
                    <h3 style="margin-bottom:0.2rem;">👁️ Vista Previa — {{ $periodLabel }}</h3>
                    <p class="muted" style="margin:0;">Así se verán los datos que recibirán los destinatarios.</p>
                </div>
                <a href="{{ route('reports.income', ['month' => $month, 'year' => $year]) }}"
                   class="btn btn-light" style="white-space:nowrap;">
                    Ver reporte completo →
                </a>
            </div>

            {{-- ── KPI Cards ── --}}
            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:0.75rem; margin-bottom:1.2rem;">
                @php
                    $kpis = [
                        ['icon'=>'💵', 'value'=>$stats['tasaCobranza'].'%',   'label'=>'Tasa Cobranza',
                         'color'=> $stats['tasaCobranza']>=80 ? '#16a34a' : ($stats['tasaCobranza']>=50 ? '#d97706' : '#dc2626')],
                        ['icon'=>'🏠', 'value'=>$stats['tasaOcupacion'].'%',  'label'=>'Ocupación',
                         'color'=> $stats['tasaOcupacion']>=80 ? '#16a34a' : ($stats['tasaOcupacion']>=50 ? '#d97706' : '#dc2626')],
                        ['icon'=>'✅', 'value'=>$stats['pagadosTotal'],        'label'=>'Pagos recibidos',  'color'=>'#16a34a'],
                        ['icon'=>'❌', 'value'=>$stats['vencidosCount'],       'label'=>'Pagos vencidos',
                         'color'=> $stats['vencidosCount'] > 0 ? '#dc2626' : '#16a34a'],
                    ];
                @endphp
                @foreach($kpis as $kpi)
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:1rem; text-align:center;">
                    <div style="font-size:1.5rem; margin-bottom:0.3rem;">{{ $kpi['icon'] }}</div>
                    <div style="font-size:1.6rem; font-weight:800; color:{{ $kpi['color'] }}; line-height:1;">{{ $kpi['value'] }}</div>
                    <div style="font-size:0.75rem; color:#64748b; margin-top:0.3rem;">{{ $kpi['label'] }}</div>
                </div>
                @endforeach
            </div>

            {{-- ── Barra de progreso de cobranza ── --}}
            <div style="margin-bottom:1.2rem;">
                <div style="display:flex; justify-content:space-between; margin-bottom:0.4rem;">
                    <span style="font-size:0.85rem; color:#475569;">Tasa de cobranza</span>
                    <span style="font-size:0.95rem; font-weight:700;
                                 color:{{ $stats['tasaCobranza']>=80 ? '#16a34a' : ($stats['tasaCobranza']>=50 ? '#d97706' : '#dc2626') }}">
                        {{ $stats['tasaCobranza'] }}%
                    </span>
                </div>
                <div style="background:#e2e8f0; border-radius:6px; height:8px; overflow:hidden;">
                    <div style="height:100%; border-radius:6px; width:{{ min($stats['tasaCobranza'], 100) }}%;
                                background:{{ $stats['tasaCobranza']>=80 ? 'linear-gradient(90deg,#16a34a,#22c55e)' : ($stats['tasaCobranza']>=50 ? 'linear-gradient(90deg,#d97706,#fbbf24)' : 'linear-gradient(90deg,#dc2626,#f87171)') }};
                                transition: width 0.6s ease;">
                    </div>
                </div>
            </div>

            {{-- ── Desglose de pagos ── --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; margin-bottom:1.2rem;">
                @php
                    $items = [
                        ['label'=>'✅ A tiempo',      'count'=>$stats['pagadosATiempo'],   'bg'=>'#dcfce7','color'=>'#166534'],
                        ['label'=>'🕐 Con retraso',   'count'=>$stats['pagadosConRetraso'],'bg'=>'#fef9c3','color'=>'#854d0e'],
                        ['label'=>'⏳ Pendientes',    'count'=>$stats['pendientesCount'],  'bg'=>'#f1f5f9','color'=>'#475569'],
                        ['label'=>'❌ Vencidos',      'count'=>$stats['vencidosCount'],    'bg'=>'#fee2e2','color'=>'#991b1b'],
                        ['label'=>'📑 Facturados',    'count'=>$stats['facturadosCount'],  'bg'=>'#ede9fe','color'=>'#5b21b6'],
                        ['label'=>'🔸 Parciales',     'count'=>$stats['parcialesCount'],   'bg'=>'#ffedd5','color'=>'#9a3412'],
                    ];
                @endphp
                @foreach($items as $item)
                <div style="background:{{ $item['bg'] }}; border-radius:8px; padding:0.6rem 0.8rem; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:0.8rem; color:{{ $item['color'] }}; font-weight:600;">{{ $item['label'] }}</span>
                    <span style="font-size:1.1rem; font-weight:800; color:{{ $item['color'] }};">{{ $item['count'] }}</span>
                </div>
                @endforeach
            </div>

            {{-- ── Resumen financiero ── --}}
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:1rem; margin-bottom:1.2rem;">
                <p style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#64748b; margin:0 0 0.8rem;">💰 Resumen Financiero</p>
                <div style="display:flex; flex-direction:column; gap:0.5rem;">
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:0.87rem; color:#475569;">Total cobrado</span>
                        <strong style="color:#16a34a;">${{ number_format($stats['totalCobrado'], 2) }}</strong>
                    </div>
                    @if($stats['totalPendiente'] > 0)
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:0.87rem; color:#475569;">Pendiente / Vencido</span>
                        <strong style="color:#dc2626;">${{ number_format($stats['totalPendiente'], 2) }}</strong>
                    </div>
                    @endif
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:0.87rem; color:#475569;">Gastos del mes</span>
                        <strong style="color:#d97706;">${{ number_format($stats['totalGastos'], 2) }}</strong>
                    </div>
                    <div style="border-top:1px solid #e2e8f0; padding-top:0.5rem; display:flex; justify-content:space-between;">
                        <span style="font-size:0.95rem; font-weight:700; color:#1e293b;">Utilidad Neta</span>
                        <strong style="font-size:1.1rem; color:{{ $stats['utilidadNeta'] >= 0 ? '#16a34a' : '#dc2626' }};">
                            ${{ number_format($stats['utilidadNeta'], 2) }}
                        </strong>
                    </div>
                </div>
            </div>

            {{-- ── Desglose por propiedad ── --}}
            @if($stats['porPropiedad']->count() > 0)
            <div style="margin-bottom:1.2rem;">
                <p style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#64748b; margin:0 0 0.7rem;">🏠 Por Propiedad</p>
                <table style="font-size:0.85rem;">
                    <thead>
                        <tr>
                            <th>Propiedad</th>
                            <th style="text-align:center;">Pagados</th>
                            <th style="text-align:right;">Cobrado</th>
                            <th style="text-align:right;">Pendiente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['porPropiedad'] as $nombre => $prop)
                        <tr>
                            <td>{{ $nombre }}</td>
                            <td style="text-align:center;">{{ $prop['pagados'] }} / {{ $prop['total'] }}</td>
                            <td style="text-align:right; color:#16a34a; font-weight:700;">${{ number_format($prop['cobrado'], 2) }}</td>
                            <td style="text-align:right; color:{{ $prop['pendiente']>0 ? '#dc2626' : '#64748b' }}; font-weight:600;">
                                ${{ number_format($prop['pendiente'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- ── Alerta vencidos ── --}}
            @if($stats['inquilinosVencidos']->count() > 0)
            <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:0.9rem;">
                <p style="font-size:0.82rem; font-weight:700; color:#991b1b; margin:0 0 0.6rem;">
                    🚨 {{ $stats['inquilinosVencidos']->count() }} pago(s) vencido(s) sin regularizar:
                </p>
                @foreach($stats['inquilinosVencidos'] as $v)
                <div style="font-size:0.82rem; color:#7f1d1d; padding:0.3rem 0; border-bottom:1px solid #fecaca;">
                    <strong>{{ $v['nombre'] }}</strong> — {{ $v['propiedad'] }} / {{ $v['unidad'] }}
                    <span style="float:right; font-weight:700;">${{ number_format($v['monto'], 2) }}</span>
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>

</div>

@endsection

@push('styles')
<style>
    code { font-family: monospace; font-size: 0.82rem; }
</style>
@endpush
