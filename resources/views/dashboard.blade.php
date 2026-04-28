@extends('layouts.app')

@section('title', 'Dashboard de Rentas')

@push('head')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/es.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Modern Premium Styles */
        .dash-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .dash-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.04);
        }
        .kpi-icon {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 5rem;
            opacity: 0.04;
            transform: rotate(-10deg);
            z-index: 0;
            pointer-events: none;
        }
        .dash-metric {
            font-size: 2.2rem;
            font-weight: 800;
            color: #1e293b;
            margin-top: 0.5rem;
            line-height: 1;
            position: relative;
            z-index: 1;
        }
        .dash-muted {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            position: relative;
            z-index: 1;
        }
        .progress-track {
            height: 8px;
            background: #f1f5f9;
            border-radius: 999px;
            overflow: hidden;
            margin: 0.8rem 0;
        }
        .progress-bar {
            height: 100%;
            border-radius: 999px;
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .badge-type {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.6rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        @media (max-width: 768px) {
            .fc-toolbar.fc-header-toolbar { flex-direction: column; gap: 1rem; }
            .fc-toolbar-title { font-size: 1.2rem !important; text-align: center; }
            .fc-toolbar-chunk { display: flex; justify-content: center; flex-wrap: wrap; gap: 0.3rem; }
            .fc .fc-button { padding: 0.3rem 0.5rem !important; font-size: 0.8rem !important; }
            #payment-calendar { min-height: 350px !important; }
            
            .dash-metric { font-size: 1.6rem; }
            .dash-card { padding: 1.2rem 1rem; }
            .kpi-icon { font-size: 3.5rem; }
            
            .legend-container { gap: 0.6rem !important; font-size: 0.75rem !important; }
        }
        
        @media (max-width: 480px) {
            .dash-metric { font-size: 1.4rem; }
            .dash-muted { font-size: 0.75rem; }
            .kpi-icon { font-size: 2.8rem; }
        }

        .fc-scroller { -webkit-overflow-scrolling: touch; }
        
        /* Ensure table actions don't wrap too early or look cramped */
        @media (max-width: 1024px) {
            .dash-card table td:last-child {
                min-width: 120px;
            }
        }
        /* Responsive table wrapper */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 1rem;
        }
        .table-responsive table {
            min-width: 500px; /* Force minimum width to ensure scrolling on small screens */
        }
        @media (max-width: 640px) {
            .table-responsive table {
                min-width: 450px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-head">
        
    </div>

    {{-- KPIs --}}
    <div class="grid grid-4" style="gap:1rem;margin-bottom:2rem;">
        <div class="dash-card">
            <div class="kpi-icon">🏢</div>
            <div class="dash-muted">Propiedades</div>
            <div class="dash-metric">{{ $stats['properties'] }}</div>
        </div>
        <div class="dash-card">
            <div class="kpi-icon">🔑</div>
            <div class="dash-muted">Locales totales</div>
            <div class="dash-metric">{{ $stats['units'] }}</div>
        </div>
        <div class="dash-card">
            <div class="kpi-icon">🤝</div>
            <div class="dash-muted">Unidades rentadas</div>
            <div class="dash-metric">{{ $stats['occupied_units'] }}</div>
        </div>
        <div class="dash-card">
            <div class="kpi-icon">📋</div>
            <div class="dash-muted">Contratos activos</div>
            <div class="dash-metric">{{ $stats['active_leases'] }}</div>
        </div>
    </div>



    {{-- Calendario --}}
    <div class="dash-card" style="margin-bottom:2rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:0.5rem;">
            <h3 style="margin:0;color:#1e293b;">📅 Agenda de Cobranza</h3>
            <div class="legend-container" style="display:flex;gap:1.2rem;flex-wrap:wrap;font-size:0.85rem;align-items:center;font-weight:600;color:#64748b;">
                <span style="display:flex;align-items:center;gap:0.4rem;">
                    <span style="width:14px;height:14px;border-radius:4px;background:#b82020;display:inline-block;"></span> Vencido
                </span>
                <span style="display:flex;align-items:center;gap:0.4rem;">
                    <span style="width:14px;height:14px;border-radius:4px;background:#c47a0a;display:inline-block;"></span> Por facturar
                </span>
                <span style="display:flex;align-items:center;gap:0.4rem;">
                    <span style="width:14px;height:14px;border-radius:4px;background:#1e40af;display:inline-block;"></span> Facturado
                </span>
                <span style="display:flex;align-items:center;gap:0.4rem;">
                    <span style="width:14px;height:14px;border-radius:4px;background:#1a7f3c;display:inline-block;"></span> Pagado
                </span>
            </div>
        </div>
        <div id="payment-calendar" style="width:100%; min-height:450px; display:block; position:relative;"></div>
    </div>

    {{-- Listas --}}
    <div class="grid grid-2" style="gap:1rem;margin-bottom:2rem;">
        {{-- Pagos vencidos --}}
        <div class="dash-card">
            <h3 style="margin-top:0;margin-bottom:1.2rem;color:#1e293b;display:flex;align-items:center;gap:0.5rem;">
                <span style="width:12px;height:12px;border-radius:50%;background:#ef4444;display:inline-block;"></span>
                Atención: Vencidos
            </h3>
            @if ($overduePayments->isEmpty())
                <p style="color:#64748b;font-weight:500;">No hay pagos vencidos. Todo en orden. ✅</p>
            @else
            <div class="table-responsive">
                <table style="width:100%;border-collapse:collapse;font-size:0.9rem;">
                    <thead>
                        <tr style="border-bottom:2px solid #f1f5f9;text-align:left;color:#475569;">
                            <th style="padding:0.8rem 0.5rem;font-weight:600;">Concepto</th>
                            <th style="padding:0.8rem 0.5rem;font-weight:600;">Inquilino</th>
                            <th style="padding:0.8rem 0.5rem;font-weight:600;">Monto</th>
                            <th style="padding:0.8rem 0.5rem;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($overduePayments as $payment)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:0.8rem 0.5rem;">
                                    <div style="font-weight:600;color:#0f172a;">{{ $payment->due_date?->format('d/m/Y') }}</div>
                                    @if ($payment->type === 'rent')
                                        <span class="badge-type" style="background:#dcfce7;color:#166534;"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg> Renta</span>
                                    @else
                                        <span class="badge-type" style="background:#e0f2fe;color:#0369a1;"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg> Mnto.</span>
                                    @endif
                                </td>
                                <td style="padding:0.8rem 0.5rem;">
                                    <div style="font-weight:500;">{{ $payment->lease->tenant->full_name ?? '-' }}</div>
                                    <div style="font-size:0.75rem;color:#64748b;">{{ $payment->lease->unit->code ?? '-' }}</div>
                                </td>
                                <td style="padding:0.8rem 0.5rem;font-weight:700;color:#ef4444;">
                                    ${{ number_format((float) $payment->amount + (float) $payment->late_fee, 2) }}
                                </td>
                                <td style="padding:0.8rem 0.5rem;text-align:right;">
                                    <div style="display:flex;gap:0.4rem;justify-content:flex-end;">
                                        <a class="btn btn-light" style="padding:0.3rem 0.6rem;font-size:0.8rem;" href="{{ route('payments.show', $payment) }}">Ver</a>
                                        @if(auth()->user()->hasPermission('payments.edit'))
                                            @php
                                                $btnColor = match($payment->status) {
                                                    'overdue'  => '#b82020',
                                                    'invoiced' => '#1e40af',
                                                    'partial'  => '#1e40af',
                                                    default    => '#c47a0a',
                                                };
                                            @endphp
                                            <button class="btn" style="padding:0.3rem 0.6rem;font-size:0.8rem;background:{{ $btnColor }};color:#fff;border:none;" data-modal-trigger="#modal-pay-ov-{{ $payment->id }}">
                                                {{ in_array($payment->status, ['invoiced', 'partial']) ? 'Cobrar' : 'Facturar' }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Pagos próximos --}}
        <div class="dash-card">
            <h3 style="margin-top:0;margin-bottom:1.2rem;color:#1e293b;display:flex;align-items:center;gap:0.5rem;">
                <span style="width:12px;height:12px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
                Próximos esperados (7 días)
            </h3>
            @if ($upcomingPayments->isEmpty())
                <p style="color:#64748b;font-weight:500;">No hay pagos próximos programados.</p>
            @else
            <div class="table-responsive">
                <table style="width:100%;border-collapse:collapse;font-size:0.9rem;">
                    <thead>
                        <tr style="border-bottom:2px solid #f1f5f9;text-align:left;color:#475569;">
                            <th style="padding:0.8rem 0.5rem;font-weight:600;">Concepto</th>
                            <th style="padding:0.8rem 0.5rem;font-weight:600;">Inquilino</th>
                            <th style="padding:0.8rem 0.5rem;font-weight:600;">Monto</th>
                            <th style="padding:0.8rem 0.5rem;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($upcomingPayments as $payment)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:0.8rem 0.5rem;">
                                    <div style="font-weight:600;color:#0f172a;">{{ $payment->due_date?->format('d/m/Y') }}</div>
                                    @if ($payment->type === 'rent')
                                        <span class="badge-type" style="background:#dcfce7;color:#166534;"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg> Renta</span>
                                    @else
                                        <span class="badge-type" style="background:#e0f2fe;color:#0369a1;"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg> Mnto.</span>
                                    @endif
                                </td>
                                <td style="padding:0.8rem 0.5rem;">
                                    <div style="font-weight:500;">{{ $payment->lease->tenant->full_name ?? '-' }}</div>
                                    <div style="font-size:0.75rem;color:#64748b;">{{ $payment->lease->unit->code ?? '-' }}</div>
                                </td>
                                <td style="padding:0.8rem 0.5rem;font-weight:700;color:#1e293b;">
                                    ${{ number_format((float) $payment->amount + (float) $payment->late_fee, 2) }}
                                </td>
                                <td style="padding:0.8rem 0.5rem;text-align:right;">
                                    <div style="display:flex;gap:0.4rem;justify-content:flex-end;">
                                        <a class="btn btn-light" style="padding:0.3rem 0.6rem;font-size:0.8rem;" href="{{ route('payments.show', $payment) }}">Ver</a>
                                        @if(auth()->user()->hasPermission('payments.edit'))
                                            @php
                                                $btnColor = match($payment->status) {
                                                    'overdue'  => '#b82020',
                                                    'invoiced' => '#1e40af',
                                                    'partial'  => '#1e40af',
                                                    default    => '#c47a0a',
                                                };
                                            @endphp
                                            <button class="btn" style="padding:0.3rem 0.6rem;font-size:0.8rem;background:{{ $btnColor }};color:#fff;border:none;" data-modal-trigger="#modal-pay-up-{{ $payment->id }}">
                                                {{ in_array($payment->status, ['invoiced', 'partial']) ? 'Cobrar' : 'Facturar' }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Contratos próximos a vencer --}}
    <div class="dash-card" style="margin-bottom:1.5rem;border-left:5px solid #f59e0b;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;">
            <h3 style="margin:0;color:#1e293b;">⚠️ Contratos a vencer (Próximos 30 días)</h3>
            @if ($expiringLeases->isNotEmpty())
                <span style="background:#fef3c7;color:#d97706;padding:0.25rem 0.6rem;font-weight:700;border-radius:999px;font-size:0.8rem;">{{ $expiringLeases->count() }} contrato(s)</span>
            @endif
        </div>

        @if ($expiringLeases->isEmpty())
            <p style="color:#64748b;font-weight:500;">Todos los contratos están vigentes y lejos de su vencimiento. ✅</p>
        @else
            <div class="table-responsive">
                <table style="width:100%;border-collapse:collapse;font-size:0.9rem;">
                    <thead>
                        <tr style="border-bottom:2px solid #f1f5f9;text-align:left;color:#475569;">
                            <th style="padding:0.8rem 0.5rem;font-weight:600;">Vence</th>
                            <th style="padding:0.8rem 0.5rem;font-weight:600;">Días restantes</th>
                            <th style="padding:0.8rem 0.5rem;font-weight:600;">Inquilino</th>
                            <th style="padding:0.8rem 0.5rem;font-weight:600;">Unidad</th>
                            <th style="padding:0.8rem 0.5rem;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expiringLeases as $lease)
                            @php
                                $daysLeft = (int) now()->startOfDay()->diffInDays($lease->end_date, false);
                                $urgencyColor  = $daysLeft <= 7 ? '#ef4444' : '#f59e0b';
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:0.8rem 0.5rem;font-weight:600;color:#0f172a;">{{ $lease->end_date?->format('d/m/Y') }}</td>
                                <td style="padding:0.8rem 0.5rem;">
                                    <span style="color:{{ $urgencyColor }};font-weight:700;display:inline-flex;align-items:center;gap:0.3rem;">
                                        {{ $daysLeft <= 7 ? '🔴' : '🟡' }} {{ $daysLeft }} día{{ $daysLeft !== 1 ? 's' : '' }}
                                    </span>
                                </td>
                                <td style="padding:0.8rem 0.5rem;font-weight:500;">{{ $lease->tenant->full_name ?? '-' }}</td>
                                <td style="padding:0.8rem 0.5rem;color:#64748b;">{{ $lease->unit->code ?? '-' }}</td>
                                <td style="padding:0.8rem 0.5rem;text-align:right;">
                                    <a class="btn btn-light" style="padding:0.3rem 0.6rem;font-size:0.8rem;" href="{{ route('leases.show', $lease) }}">Abrir contrato</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@push('modals')
    {{-- Modales para pagos vencidos --}}
    @foreach ($overduePayments as $payment)
        @include('payments._quick_modal', ['payment' => $payment, 'modalPrefix' => 'ov'])
    @endforeach

    {{-- Modales para pagos próximos --}}
    @foreach ($upcomingPayments as $payment)
        @include('payments._quick_modal', ['payment' => $payment, 'modalPrefix' => 'up'])
    @endforeach

    {{-- Modal dinámico del calendario --}}
    <div class="modal-overlay" id="cal-quick-modal">
        <div class="modal-dialog" style="width:min(600px,96vw);">
            <div class="modal-head" style="padding:1rem 1.25rem;">
                <div>
                    <h2 class="modal-title" style="font-size:1rem;margin-bottom:0.1rem;" id="cal-modal-title">Registrar Pago</h2>
                    <div class="muted" style="font-size:0.8rem;" id="cal-modal-subtitle"></div>
                </div>
                <button class="modal-close" data-cal-close>&#x2715;</button>
            </div>
            <div class="modal-body" style="padding:1.25rem;">

                {{-- Info card --}}
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.8rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:0.9rem 1rem;margin-bottom:1.2rem;">
                    <div>
                        <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;color:#64748b;">Inquilino</div>
                        <strong id="cal-modal-tenant" style="font-size:0.9rem;">&mdash;</strong>
                    </div>
                    <div>
                        <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;color:#64748b;">Unidad / Propiedad</div>
                        <span id="cal-modal-property" style="font-weight:600;font-size:0.9rem;">&mdash;</span>
                    </div>
                    <div>
                        <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;color:#64748b;">Monto pactado</div>
                        <span id="cal-modal-amount" style="font-weight:800;font-size:1.1rem;color:#1e40af;">$0.00</span>
                    </div>
                </div>

                @if(auth()->user()->hasPermission('payments.edit'))
                    {{-- Step indicator --}}
                    <div id="cal-steps" style="display:flex;align-items:center;margin-bottom:1.4rem;"></div>

                    {{-- Tabs --}}
                    <div style="display:flex;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;margin-bottom:1.2rem;">
                        <button type="button" id="cal-tab-btn-invoice"
                            style="flex:1;padding:0.65rem;font-size:0.85rem;font-weight:600;border:none;cursor:pointer;transition:background 0.15s;"
                            onclick="calSwitchTab('invoice')">&#x1F4CB; Registrar Factura</button>
                        <button type="button" id="cal-tab-btn-pay"
                            style="flex:1;padding:0.65rem;font-size:0.85rem;font-weight:600;border:none;border-left:1px solid #e2e8f0;cursor:pointer;transition:background 0.15s;"
                            onclick="calSwitchTab('pay')">&#x1F4B0; Registrar Pago</button>
                    </div>

                    {{-- Tab: Factura --}}
                    <div id="cal-tab-invoice">
                        <form id="cal-invoice-form" method="POST" action="#" enctype="multipart/form-data">
                            @csrf
                            <div class="form-grid">
                                <div class="field-span-full">
                                    <label>Folio de Factura</label>
                                    <input type="text" id="cal-invoice-folio" name="invoice_folio" placeholder="Ej: A-00123 o UUID del CFDI">
                                </div>
                                <div class="field-span-full">
                                    <label>Fecha de Facturación</label>
                                    <input type="date" id="cal-invoice-date" name="invoiced_at">
                                </div>
                                <div>
                                    <label>PDF(s) de Factura</label>
                                    <input type="file" name="invoice_pdf[]" accept=".pdf" multiple>
                                </div>
                                <div>
                                    <label>XML(s) CFDI</label>
                                    <input type="file" name="invoice_xml[]" accept=".xml" multiple>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">&#x1F4CB; Guardar Factura</button>
                                <a id="cal-modal-detail-link" href="#" class="btn btn-light">Ver ficha completa</a>
                            </div>
                        </form>
                    </div>

                    {{-- Tab: Pago --}}
                    <div id="cal-tab-pay" style="display:none;">
                        <form id="cal-pay-form" method="POST" action="#" enctype="multipart/form-data">
                            @csrf
                            <div class="form-grid">
                                <div>
                                    <label>Fecha de pago</label>
                                    <input type="date" id="cal-modal-paid-at" name="paid_at">
                                </div>
                                <div>
                                    <label>Monto pagado ($)</label>
                                    <input type="number" id="cal-modal-paid-amount" name="paid_amount" min="0" step="0.01" placeholder="0.00">
                                </div>
                                <div>
                                    <label>Recargo / Mora ($)</label>
                                    <input type="number" id="cal-modal-late-fee" name="late_fee" min="0" step="0.01" value="0.00">
                                </div>
                                <div>
                                    <label>Método de pago</label>
                                    <select id="cal-modal-method" name="payment_method">
                                        <option value="">&mdash; Seleccionar &mdash;</option>
                                        <option>Efectivo</option><option>Transferencia</option>
                                        <option>Tarjeta</option><option>Cheque</option><option>Otro</option>
                                    </select>
                                </div>
                                <div class="field-span-full">
                                    <label>No. de operación / Referencia</label>
                                    <input type="text" id="cal-modal-reference" name="reference" placeholder="No. de transferencia, etc.">
                                </div>
                                <div class="field-span-full">
                                    <label>Comprobante de pago (foto o PDF)</label>
                                    <input type="file" id="cal-modal-receipt" name="receipt" accept="image/*,.pdf">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">&#x2713; Confirmar Pago</button>
                                <a id="cal-modal-detail-link2" href="#" class="btn btn-light">Ver ficha completa</a>
                                <button type="button" class="btn btn-light" data-cal-close>Cancelar</button>
                            </div>
                        </form>
                    </div>
                @else
                    {{-- Solo lectura: viewer ve la información pero no puede operar --}}
                    <div style="padding:1.2rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;text-align:center;">
                        <p style="margin:0 0 1rem;color:#64748b;font-size:0.9rem;">No tienes permisos para registrar pagos o facturas.</p>
                        <a id="cal-modal-detail-link" href="#" class="btn btn-light">Ver ficha completa</a>
                    </div>
                @endif

            </div>
        </div>
    </div>

@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ---- Tab helper para modales estáticos ----
    window.qmTab = function(mid, tab) {
        const inv = document.getElementById('tab-invoice-' + mid);
        const pay = document.getElementById('tab-pay-' + mid);
        const btnInv = document.getElementById('tab-btn-inv-' + mid);
        const btnPay = document.getElementById('tab-btn-pay-' + mid);
        const active = 'background:#1e40af;color:#fff';
        const inactive = 'background:#f8fafc;color:#475569';
        if (tab === 'invoice') {
            inv && (inv.style.display = '');
            pay && (pay.style.display = 'none');
            btnInv && (btnInv.style.cssText += ';' + active);
            btnPay && (btnPay.style.cssText += ';' + inactive);
        } else {
            inv && (inv.style.display = 'none');
            pay && (pay.style.display = '');
            btnInv && (btnInv.style.cssText += ';' + inactive);
            btnPay && (btnPay.style.cssText += ';' + active);
        }
    };

    // ---- Tab helper para modal del calendario ----
    window.calSwitchTab = function(tab) {
        const invTab = document.getElementById('cal-tab-invoice');
        const payTab = document.getElementById('cal-tab-pay');
        const btnInv = document.getElementById('cal-tab-btn-invoice');
        const btnPay = document.getElementById('cal-tab-btn-pay');
        const active = 'background:#1e40af;color:#fff';
        const inactive = 'background:#f8fafc;color:#475569';
        if (tab === 'invoice') {
            invTab.style.display = '';
            payTab.style.display = 'none';
            btnInv.style.cssText += active; btnPay.style.cssText += inactive;
        } else {
            invTab.style.display = 'none';
            payTab.style.display = '';
            btnInv.style.cssText += inactive; btnPay.style.cssText += active;
        }
    };

    // ---- Calendario ----
    const calendarEl = document.getElementById('payment-calendar');
    if (!calendarEl) return;

    const STEPS = {
        labels: ['Por facturar','Facturado','Pagado'],
        colors: { pending:'#f59e0b', overdue:'#ef4444', invoiced:'#3b82f6', paid:'#10b981', partial:'#f59e0b' }
    };

    function buildSteps(status) {
        const step = (status === 'invoiced' || status === 'partial') ? 2 : (status === 'paid' ? 3 : 1);
        let html = '';
        for (let i = 1; i <= 3; i++) {
            const active = step >= i;
            html += `<div style="display:flex;flex-direction:column;align-items:center;gap:0.3rem;min-width:80px;">
                <div style="width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;${ active ? 'background:#1e40af;color:#fff' : 'background:#e2e8f0;color:#94a3b8' }">${i}</div>
                <span style="font-size:0.7rem;font-weight:600;color:${ active ? '#1e40af' : '#94a3b8' };">${STEPS.labels[i-1]}</span>
            </div>`;
            if (i < 3) html += `<div style="flex:1;height:2px;${ step > i ? 'background:#1e40af' : 'background:#e2e8f0' };margin-bottom:1.2rem;"></div>`;
        }
        return html;
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,listMonth' },
        buttonText: { today:'Hoy', month:'Mes', week:'Semana', list:'Lista' },
        height: 'auto',
        eventSources: [{ url: '{{ route('dashboard.calendarEvents') }}', method: 'GET' }],
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            const p   = info.event.extendedProps;
            const url = info.event.url;
            const id  = info.event.id;

            if (p.status === 'paid') { window.location.href = url; return; }

            // Populate info (use ?. to avoid errors when elements don't exist for viewers)
            document.getElementById('cal-modal-title').textContent    = info.event.title;
            document.getElementById('cal-modal-subtitle').textContent = p.property;
            document.getElementById('cal-modal-tenant').textContent   = info.event.title;
            document.getElementById('cal-modal-property').textContent = p.property;
            document.getElementById('cal-modal-amount').textContent   = p.amount;
            const dlLink  = document.getElementById('cal-modal-detail-link');
            const dlLink2 = document.getElementById('cal-modal-detail-link2');
            if (dlLink)  dlLink.href  = url;
            if (dlLink2) dlLink2.href = url;
            const invoiceForm = document.getElementById('cal-invoice-form');
            const payForm     = document.getElementById('cal-pay-form');
            if (invoiceForm) invoiceForm.action = `/payments/${id}/upload-invoice`;
            if (payForm)     payForm.action     = `/payments/${id}/mark-paid`;
            const elPaidAt     = document.getElementById('cal-modal-paid-at');
            const elPaidAmount = document.getElementById('cal-modal-paid-amount');
            const elLateFee    = document.getElementById('cal-modal-late-fee');
            const elMethod     = document.getElementById('cal-modal-method');
            const elReference  = document.getElementById('cal-modal-reference');
            const elReceipt    = document.getElementById('cal-modal-receipt');
            const elFolio      = document.getElementById('cal-invoice-folio');
            const elInvDate    = document.getElementById('cal-invoice-date');
            if (elPaidAt)     elPaidAt.value     = new Date().toISOString().split('T')[0];
            if (elPaidAmount) elPaidAmount.value  = p.rawAmount || '';
            if (elLateFee)    elLateFee.value     = p.lateFee || 0;
            if (elMethod)     elMethod.value      = '';
            if (elReference)  elReference.value   = '';
            if (elReceipt)    elReceipt.value     = '';
            if (elFolio)      elFolio.value       = '';
            if (elInvDate)    elInvDate.value     = new Date().toISOString().split('T')[0];

            // Build step indicator (only exists for users with payments.edit)
            const stepsEl = document.getElementById('cal-steps');
            if (stepsEl) stepsEl.innerHTML = buildSteps(p.status);

            // Set initial tab based on status (only relevant when tabs exist)
            if (document.getElementById('cal-tab-btn-invoice')) {
                calSwitchTab(p.status === 'invoiced' || p.status === 'partial' ? 'pay' : 'invoice');
            }

            document.getElementById('cal-quick-modal').classList.add('is-open');
        },
        eventDisplay: 'block',
        dayMaxEvents: 4,
    });

    calendar.render();

    document.querySelectorAll('[data-cal-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('cal-quick-modal').classList.remove('is-open');
        });
    });
    document.getElementById('cal-quick-modal').addEventListener('click', function (e) {
        if (e.target === this) this.classList.remove('is-open');
    });

    // ---- AJAX Handlers para evitar redirecciones ----
    function initAjaxForm(form, successMsg) {
        if (!form || form.dataset.ajaxInitialized) return;
        form.dataset.ajaxInitialized = 'true';

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '⌛ Enviando...';
            btn.disabled = true;

            const formData = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;
                if (data.success) {
                    alert(successMsg || data.message);
                    // Cerrar modal (funciona para el de calendario y los estáticos)
                    const modal = form.closest('.modal-overlay');
                    if (modal) modal.classList.remove('is-open');
                    
                    if (typeof calendar !== 'undefined') calendar.refetchEvents();
                    
                    // Si es un modal estático, recargar la página para ver cambios en las listas
                    if (!form.id.startsWith('cal-')) {
                        window.location.reload();
                    }
                } else {
                    alert('Error: ' + (data.message || 'Ocurrió un error inesperado'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    }

    // Inicializar formularios del modal de calendario
    initAjaxForm(document.getElementById('cal-invoice-form'), 'Factura guardada correctamente.');
    initAjaxForm(document.getElementById('cal-pay-form'), 'Pago registrado correctamente.');

    // Inicializar formularios de los modales rápidos (listas)
    document.querySelectorAll('.ajax-invoice-form').forEach(f => initAjaxForm(f, 'Factura guardada correctamente.'));
    document.querySelectorAll('.ajax-pay-form').forEach(f => initAjaxForm(f, 'Pago registrado correctamente.'));

    // ---- Inicialización de Modales Estáticos (Cobrar en listas) ----
    document.querySelectorAll('[data-modal-trigger]').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = document.querySelector(this.dataset.modalTrigger);
            if (target) target.classList.add('is-open');
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            if (modal) modal.classList.remove('is-open');
        });
    });
});
</script>
@endpush

