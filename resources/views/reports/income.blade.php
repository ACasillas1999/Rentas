@extends('layouts.app')

@section('title', 'Reporte de ingresos')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
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
    </style>
@endpush

@section('content')
    <div class="page-head">
        <div style="flex: 1;"></div>
        <div class="actions">
            <a href="{{ route('reports.income.export', request()->query()) }}" class="btn" style="background: #10b981; border-color: #10b981; color: white;">
                📥 Descargar Excel (CSV)
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <form method="GET" action="{{ route('reports.income') }}" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
            <div>
                <label for="r_month">Mes</label>
                <select id="r_month" name="month">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" @selected($m == $month)>
                            {{ Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="r_year">Año</label>
                <select id="r_year" name="year">
                    @for($y = $firstYear; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div style="flex-grow:1; display:flex; gap:1rem; align-items:flex-end;">
                <div>
                    <label for="r_mode">Ver por:</label>
                    <select id="r_mode" name="mode" style="background:var(--light); border-color:#cbd5e1; font-weight:600;">
                        <option value="accrual" @selected($mode == 'accrual')>📅 Vencimiento (Devengado)</option>
                        <option value="cash" @selected($mode == 'cash')>💸 Fecha de Pago (Caja)</option>
                    </select>
                </div>
                <button class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>

    {{-- KPIs del mes --}}
    @php
        $monthName = Carbon\Carbon::create($year, $month)->locale('es')->isoFormat('MMMM YYYY');
        $grandTotal = $totalPaid + $totalDeposits;
        $potentialTotal = $totalPaid + $totalPending + $totalDeposits; // Si todo se cobrara al 100%
        $pctPaid = $potentialTotal > 0 ? round($grandTotal / $potentialTotal * 100) : 0;
    @endphp

    <div style="margin-bottom:0.5rem;">
        <h2 style="font-size:1.1rem;font-weight:600;color:var(--muted);text-transform:capitalize;">
            {{ $monthName }}
        </h2>
    </div>

    <div class="grid grid-3" style="gap:1.5rem;margin-bottom:2rem;">
        {{-- Ocupación --}}
        <div class="dash-card" style="border-top:4px solid #3b82f6;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;font-size:0.95rem;color:#1e293b;">📊 Ocupación</h3>
                <span style="font-weight:800;font-size:1.25rem;color:#3b82f6;">{{ $stats['occupancy_rate'] }}%</span>
            </div>
            <div class="progress-track">
                <div class="progress-bar" style="width:{{ $stats['occupancy_rate'] }}%;background:#3b82f6;"></div>
            </div>
            <div style="font-size:0.8rem;color:#64748b;font-weight:500;">
                {{ $stats['occupied_units'] }} de {{ $stats['total_units'] }} locales rentados
            </div>
        </div>

        {{-- Cobranza --}}
        <div class="dash-card" style="border-top:4px solid #10b981;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;font-size:0.95rem;color:#1e293b;">
                    💰 {{ $mode === 'cash' ? 'Recaudación Real' : 'Cobranza del Mes' }}
                </h3>
                <span style="font-weight:800;font-size:1.25rem;color:#10b981;">{{ $stats['collection_rate'] }}%</span>
            </div>
            <div class="progress-track">
                <div class="progress-bar" style="width:{{ $stats['collection_rate'] }}%;background:#10b981;"></div>
            </div>
            <div style="font-size:0.8rem;color:#64748b;display:flex;justify-content:space-between;font-weight:500;">
                <span>Cobrado: ${{ number_format($stats['total_paid'], 2) }}</span>
                <span>Esperado: ${{ number_format($stats['total_paid'] + $stats['total_pending'], 2) }}</span>
            </div>
        </div>

        {{-- Utilidad --}}
        <div class="dash-card" style="border-top:4px solid {{ $stats['net_income'] >= 0 ? '#10b981' : '#ef4444' }};">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                <h3 style="margin:0;font-size:0.95rem;color:#1e293b;">📉 Utilidad Global</h3>
            </div>
            <div style="font-size:1.8rem;font-weight:800;color:{{ $stats['net_income'] >= 0 ? '#10b981' : '#ef4444' }};display:flex;align-items:center;gap:0.4rem;">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" fill="none" stroke-width="2.5">
                    @if($stats['net_income'] >= 0) <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/> @else <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/> @endif
                </svg>
                ${{ number_format(abs($stats['net_income']), 2) }}
            </div>
            <div style="font-size:0.8rem;color:#64748b;margin-top:0.4rem;display:flex;justify-content:space-between;align-items:center;font-weight:500;">
                <span>{{ $mode === 'accrual' ? 'Ingreso Potencial' : 'Ingreso Real' }} - Gastos (${{ number_format($totalExpenses, 2) }})</span>
                <a href="{{ route('expenses.index') }}" style="color:var(--primary);text-decoration:none;">Gastos →</a>
            </div>
        </div>
    </div>

    {{-- KPIs de Facturación y Cobro real del mes --}}
    <div class="grid grid-2" style="gap:1.5rem;margin-bottom:2rem;">
        {{-- Total Facturado en el mes --}}
        <div class="dash-card" style="border-top:4px solid #1e40af;">
            <div style="display:flex;justify-content:space-between;align-items:start;">
                <div>
                    <div style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:#64748b;margin-bottom:0.4rem;">
                        📋 Facturado en {{ ucfirst(\Carbon\Carbon::create($year, $month)->locale('es')->isoFormat('MMMM')) }}
                    </div>
                    <div style="font-size:2rem;font-weight:800;color:#1e40af;">
                        ${{ number_format($stats['invoiced_month'], 2) }}
                    </div>
                    <div style="font-size:0.78rem;color:#64748b;margin-top:0.4rem;">
                        Por fecha de facturación (<code>invoiced_at</code>)
                    </div>
                </div>
                <span style="font-size:2.5rem;opacity:0.12;">📋</span>
            </div>
            @php
                $invPct = ($stats['invoiced_month'] + $stats['cashed_month']) > 0
                    ? round($stats['invoiced_month'] / ($stats['invoiced_month'] + $stats['cashed_month']) * 100)
                    : 0;
            @endphp
            <div class="progress-track">
                <div class="progress-bar" style="width:{{ $invPct }}%;background:#1e40af;"></div>
            </div>
            <div style="font-size:0.75rem;color:#64748b;">{{ $invPct }}% del total facturado+cobrado</div>
        </div>

        {{-- Total Cobrado en el mes --}}
        <div class="dash-card" style="border-top:4px solid #1a7f3c;">
            <div style="display:flex;justify-content:space-between;align-items:start;">
                <div>
                    <div style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:#64748b;margin-bottom:0.4rem;">
                        💸 Cobrado en {{ ucfirst(\Carbon\Carbon::create($year, $month)->locale('es')->isoFormat('MMMM')) }}
                    </div>
                    <div style="font-size:2rem;font-weight:800;color:#1a7f3c;">
                        ${{ number_format($stats['cashed_month'], 2) }}
                    </div>
                    <div style="font-size:0.78rem;color:#64748b;margin-top:0.4rem;">
                        Por fecha de pago real (<code>paid_at</code>)
                    </div>
                </div>
                <span style="font-size:2.5rem;opacity:0.12;">💰</span>
            </div>
            @php
                $cashPct = ($stats['invoiced_month'] + $stats['cashed_month']) > 0
                    ? round($stats['cashed_month'] / ($stats['invoiced_month'] + $stats['cashed_month']) * 100)
                    : 0;
            @endphp
            <div class="progress-track">
                <div class="progress-bar" style="width:{{ $cashPct }}%;background:#1a7f3c;"></div>
            </div>
            <div style="font-size:0.75rem;color:#64748b;">{{ $cashPct }}% del total facturado+cobrado</div>
        </div>
    </div>

    {{-- Desglose de Depósitos (Si existen en el mes) --}}
    @if($depositLeases->isNotEmpty())
        <div class="card" style="margin-bottom:1.5rem; border-top: 4px solid #3b82f6;">
            <h3 style="margin-top:0; font-size:1rem; color:#1e40af;">🔑 Detalle de Depósitos de Garantía</h3>
            <p class="muted" style="font-size:0.85rem; margin-bottom:1rem;">Contratos nuevos que iniciaron en {{ $monthName }}</p>
            <table>
                <thead>
                    <tr>
                        <th>Inquilino</th>
                        <th>Propiedad / Unidad</th>
                        <th>Fecha Inicio</th>
                        <th>Monto Depósito</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($depositLeases as $dLease)
                        <tr>
                            <td style="font-weight:600;">{{ $dLease->tenant->full_name ?? '-' }}</td>
                            <td>{{ $dLease->unit->property->name ?? '-' }} / {{ $dLease->unit->code ?? '-' }}</td>
                            <td>{{ $dLease->start_date?->format('d/m/Y') }}</td>
                            <td style="font-weight:700; color:#2563eb;">${{ number_format($dLease->deposit_amount, 2) }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('leases.show', $dLease) }}" class="btn btn-light" style="padding:0.25rem 0.5rem; font-size:0.75rem;">Ver Contrato</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    
    {{-- Resumen por beneficiario --}}
    <div class="card" style="margin-bottom:1.5rem; border-top: 4px solid var(--dark);">
        <h3 style="margin-top:0; font-size:1rem;">👤 Resumen por Beneficiario</h3>
        <p class="muted" style="font-size:0.85rem; margin-bottom:1rem;">Distribución de lo recaudado en {{ $monthName }}</p>
        <table>
            <thead>
                <tr>
                    <th>Beneficiario</th>
                    <th>Montos Cobrados</th>
                    <th>Pendientes</th>
                    <th>Pagos</th>
                    <th>Eficiencia</th>
                </tr>
            </thead>
            <tbody>
                @foreach($byBeneficiary as $beneficiaryName => $data)
                    @php
                        $beneficialPotential = $data['paid'] + $data['pending'];
                        $beneficialPct = $beneficialPotential > 0 ? round($data['paid'] / $beneficialPotential * 100) : 0;
                    @endphp
                    <tr>
                        <td style="font-weight:600;">{{ $beneficiaryName }}</td>
                        <td style="color:#1a7f3c; font-weight:700;">${{ number_format($data['paid'], 2) }}</td>
                        <td style="color:#c47a0a;">${{ number_format($data['pending'], 2) }}</td>
                        <td>{{ $data['count'] }}</td>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <div style="flex-grow:1; height:6px; background:#e8edf3; border-radius:4px; overflow:hidden; min-width:60px;">
                                    <div style="height:6px; width:{{ $beneficialPct }}%; background:#1a7f3c; border-radius:4px;"></div>
                                </div>
                                <span class="muted" style="font-size:0.75rem;">{{ $beneficialPct }}%</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Desglose por propiedad --}}
    @if($byProperty->isEmpty())
        <div class="card">
            <p class="muted" style="text-align:center;padding:2rem 0;">No hay pagos registrados para este período.</p>
        </div>
    @else
        @foreach($byProperty as $propName => $data)
            <div class="card" style="margin-bottom:1.2rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;">
                    <h3 style="margin:0;font-size:1rem;">🏢 {{ $propName }}</h3>
                    <div style="display:flex;gap:1.2rem;font-size:0.88rem;">
                        <span style="color:#1a7f3c;font-weight:600;">
                            ✓ ${{ number_format($data['paid'], 2) }} cobrado
                        </span>
                        <span style="color:#c47a0a;font-weight:600;">
                            ⏳ ${{ number_format($data['pending'], 2) }} pendiente
                        </span>
                        <span class="muted">{{ $data['count'] }} pagos</span>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Inquilino</th>
                            <th>Tipo</th>
                            <th>Unidad</th>
                            <th>Período</th>
                            <th>Vencimiento</th>
                            <th>F. Factura</th>
                            <th>Folio</th>
                            <th>F. Pago</th>
                            <th>Monto</th>
                            <th>Recargo</th>
                            <th>Estatus</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['payments']->sortBy('due_date') as $payment)
                            <tr>
                                <td>{{ $payment->lease->tenant->full_name ?? '-' }}</td>
                                <td>
                                    @if($payment->type === 'maintenance')
                                        <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:0.7rem;">Mant.</span>
                                    @else
                                        <span class="badge" style="background:#f0fdf4;color:#15803d;font-size:0.7rem;">Renta</span>
                                    @endif
                                </td>
                                <td>{{ $payment->lease->unit->code ?? '-' }}</td>
                                <td>{{ $payment->period_label ?: '-' }}</td>
                                <td style="font-size:0.8rem;">{{ $payment->due_date?->format('d/m/Y') }}</td>
                                <td style="font-size:0.8rem;color:{{ $payment->invoiced_at ? '#1e40af' : '#94a3b8' }};font-weight:600;">
                                    {{ $payment->invoiced_at?->format('d/m/Y') ?: '—' }}
                                </td>
                                <td style="font-size:0.78rem;font-family:monospace;color:#1e40af;">
                                    {{ $payment->invoice_folio ?: '—' }}
                                </td>
                                <td style="font-size:0.8rem;font-weight:600;color:{{ $payment->paid_at ? '#1a7f3c' : '#94a3b8' }};">
                                    {{ $payment->paid_at?->format('d/m/Y') ?: '—' }}
                                </td>
                                <td>${{ number_format((float)$payment->amount, 2) }}</td>
                                <td>
                                    @if((float)$payment->late_fee > 0)
                                        <span style="color:#b82020;">${{ number_format((float)$payment->late_fee, 2) }}</span>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->status === 'paid')
                                        <span class="badge" style="background:#dcfce7;color:#166534;">Pagado</span>
                                    @elseif($payment->status === 'invoiced')
                                        <span class="badge" style="background:#dbeafe;color:#1e40af;">Facturado</span>
                                    @elseif($payment->status === 'partial')
                                        <span class="badge" style="background:#eef2ff;color:#4338ca;">Parcial</span>
                                    @elseif($payment->status === 'overdue')
                                        <span class="badge" style="background:#fee2e2;color:#b91c1c;">Vencido</span>
                                    @else
                                        <span class="badge" style="background:#fef3c7;color:#92400e;">Por Facturar</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-light" href="{{ route('payments.show', $payment) }}">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:600;background:#f6f8fc;">
                            <td colspan="8" style="text-align:right;color:var(--muted);">Subtotal Propiedad</td>
                            <td>${{ number_format($data['payments']->sum(fn($p) => (float)$p->amount), 2) }}</td>
                            <td>${{ number_format($data['payments']->sum(fn($p) => (float)$p->late_fee), 2) }}</td>
                            <td colspan="2" style="color:#1a7f3c;text-align:right;padding-right:2rem;">
                                {{ $mode === 'cash' ? 'Recaudado' : 'Cobrado' }}: ${{ number_format($data['paid'], 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach
    @endif

    {{-- Gastos y Utilidad Neta --}}
    <div class="grid grid-2" style="margin-top:1.5rem;margin-bottom:1.5rem;">
        {{-- Resumen Ingresos vs Gastos --}}
        <div class="card" style="border-top:4px solid {{ $netIncome >= 0 ? '#1a7f3c' : '#b82020' }};">
            <h3 style="margin-top:0;font-size:1rem;">💹 Utilidad Neta del Período</h3>
            <table>
                <tr>
                    <td class="muted">{{ $mode === 'accrual' ? 'Rentas devengadas' : 'Rentas cobradas' }}</td>
                    <td style="text-align:right;color:#1a7f3c;font-weight:600;">
                        +${{ number_format($mode === 'accrual' ? ($totalPaid + $totalPending) : $totalPaid, 2) }}
                    </td>
                </tr>
                <tr>
                    <td class="muted">Depósitos{{ $mode === 'accrual' ? ' por recibir' : ' recibidos' }}</td>
                    <td style="text-align:right;color:#3b82f6;font-weight:600;">+${{ number_format($totalDeposits, 2) }}</td>
                </tr>
                <tr>
                    <td class="muted">Gastos del período</td>
                    <td style="text-align:right;color:#b82020;font-weight:600;">-${{ number_format($totalExpenses, 2) }}</td>
                </tr>
                <tr style="border-top:2px solid #e8edf3;">
                    <td style="font-weight:700;padding-top:0.75rem;">Utilidad Neta</td>
                    <td style="text-align:right;font-size:1.3rem;font-weight:800;color:{{ $netIncome >= 0 ? '#1a7f3c' : '#b82020' }};padding-top:0.75rem;">
                        @if($netIncome >= 0) +@endif${{ number_format($netIncome, 2) }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- Gastos por categoría --}}
        <div class="card" style="border-top:4px solid #b82020;">
            <h3 style="margin-top:0;font-size:1rem;">📉 Gastos por Categoría</h3>
            @if ($expenses->isEmpty())
                <p class="muted">Sin gastos registrados en este período.</p>
            @else
                <table>
                    @foreach ($expensesByCategory as $cat => $total)
                        <tr>
                            <td class="muted">{{ $cat }}</td>
                            <td style="text-align:right;font-weight:600;color:#b82020;">${{ number_format($total, 2) }}</td>
                            <td style="width:30%;">
                                @php $pct = $totalExpenses > 0 ? round($total / $totalExpenses * 100) : 0; @endphp
                                <div style="height:6px;background:#f1f5f9;border-radius:4px;overflow:hidden;">
                                    <div style="height:6px;width:{{ $pct }}%;background:#b82020;border-radius:4px;"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    <tr style="border-top:1px solid #e8edf3;">
                        <td style="font-weight:700;">Total Gastos</td>
                        <td style="text-align:right;font-weight:800;color:#b82020;">${{ number_format($totalExpenses, 2) }}</td>
                        <td></td>
                    </tr>
                </table>
            @endif
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="dash-card" style="margin-top:2rem; margin-bottom:2rem;">
        <h3 style="margin-top:0;font-size:1.1rem;color:#1e293b;margin-bottom:1.5rem;">Ingresos Multi-Eje vs Gastos Operativos (Últimos 6 meses)</h3>
        <div style="position: relative; height: 320px; width: 100%;">
            <canvas id="incomeExpenseChart"></canvas>
        </div>
    </div>

    <div class="grid grid-2" style="gap:1.5rem;margin-bottom:2rem;">
        <div class="dash-card">
            <h3 style="margin-top:0;font-size:1.1rem;color:#1e293b;margin-bottom:1rem;">Índice de Ocupación</h3>
            <div style="position: relative; height: 260px; width: 100%;">
                <canvas id="occupancyChart"></canvas>
            </div>
        </div>
        <div class="dash-card">
            <h3 style="margin-top:0;font-size:1.1rem;color:#1e293b;margin-bottom:1rem;">Estado de Cobranza Histórica</h3>
            <div style="position: relative; height: 260px; width: 100%;">
                <canvas id="collectionChart"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData = @json($chartData);

    // 1. Ingresos vs Gastos
    const ctxIncomeExpense = document.getElementById('incomeExpenseChart').getContext('2d');
    new Chart(ctxIncomeExpense, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Rentas',
                    data: chartData.rent_income,
                    backgroundColor: '#10b981',
                    borderRadius: 4
                },
                {
                    label: 'Mantenimiento',
                    data: chartData.maintenance_income,
                    backgroundColor: '#0ea5e9',
                    borderRadius: 4
                },
                {
                    label: 'Gastos Operativos',
                    data: chartData.expenses,
                    backgroundColor: '#ef4444',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + new Intl.NumberFormat('en-US').format(value);
                        }
                    }
                }
            },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) { label += ': '; }
                            label += new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(context.parsed.y);
                            return label;
                        }
                    }
                }
            }
        }
    });

    // 2. Ocupación
    const ctxOccupancy = document.getElementById('occupancyChart').getContext('2d');
    new Chart(ctxOccupancy, {
        type: 'doughnut',
        data: {
            labels: ['Rentadas', 'Disponibles'],
            datasets: [{
                data: [chartData.occupancy.occupied, chartData.occupancy.vacant],
                backgroundColor: ['#3b82f6', '#e8edf3'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // 3. Cobranza
    const ctxCollection = document.getElementById('collectionChart').getContext('2d');
    new Chart(ctxCollection, {
        type: 'pie',
        data: {
            labels: ['Pagados', 'Facturados', 'Por Facturar', 'Vencidos'],
            datasets: [{
                data: [
                    chartData.collection.paid,
                    chartData.collection.invoiced,
                    chartData.collection.pending,
                    chartData.collection.overdue
                ],
                backgroundColor: ['#1a7f3c', '#1e40af', '#c47a0a', '#b82020'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>
@endpush
