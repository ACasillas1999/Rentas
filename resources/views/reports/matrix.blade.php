@php $isExport = $isExport ?? false; @endphp
@if(!$isExport)
    @extends('layouts.app')
    @section('title', 'Matriz de Pagos')
    @push('styles')
@else
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
@endif
<style>
    .matrix-wrapper {
        overflow-x: auto;
        max-height: 80vh;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #fff;
    }

    .matrix-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        font-size: 0.8rem;
    }

    .matrix-table th, .matrix-table td {
        border: 1px solid #dee2e6;
        padding: 4px 8px;
        text-align: center;
        white-space: nowrap;
        min-width: 100px;
    }

    /* Sticky headers */
    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 10;
        background: #f8f9fa;
        font-weight: bold;
        border-right: 2px solid #adb5bd !important;
    }

    .sticky-row-1 { position: sticky; top: 0; z-index: 20; background: #e9ecef !important; }
    .sticky-row-2 { position: sticky; top: 25px; z-index: 20; background: #e9ecef !important; }
    .sticky-row-3 { position: sticky; top: 50px; z-index: 20; background: #f8f9fa !important; }

    .sticky-intersect {
        z-index: 30;
    }

    /* Column colors based on Excel */
    .header-tenant { font-size: 0.75rem; color: #1a3b6d; text-transform: uppercase; height: 25px; }
    .header-dates { font-size: 0.7rem; color: #495057; font-weight: normal; height: 25px; }
    .header-unit { background: #d0e2ff !important; color: #004085; font-weight: 800; height: 25px; }

    .period-label {
        text-align: left !important;
        font-weight: 600;
        background: #f8f9fa;
    }

    /* Status colors */
    .cell-paid { background: #dcfce7 !important; color: #166534 !important; }
    .cell-invoiced { background: #dbeafe !important; color: #1e40af !important; }
    .cell-overdue { background: #fee2e2 !important; color: #b91c1c !important; font-weight: bold; }
    .cell-pending { background: #fef3c7 !important; color: #92400e !important; }
    .cell-empty { background: #ffffff; color: #dee2e6; }

    .total-row { background: #343a40 !important; color: #fff; font-weight: bold; }
    .total-side { background: #e9ecef; font-weight: bold; }

    .matrix-section-title {
        background: #f1f3f5;
        padding: 8px 12px;
        margin: 20px 0 0 0;
        border: 1px solid #dee2e6;
        border-bottom: none;
        border-radius: 8px 8px 0 0;
        font-weight: 800;
        color: #1a3b6d;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .matrix-section-title.maintenance { color: #856404; }

    /* Estilos Pestañas */
    .tabs-nav {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 0px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-right: 1rem;
    }
    .tab-item {
        padding: 10px 20px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-bottom: none;
        border-radius: 8px 8px 0 0;
        color: #64748b;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
        margin-bottom: -2px; 
        white-space: nowrap;
    }
    .tab-item:hover {
        background: #e2e8f0;
    }
    .tab-item.active {
        background: #ffffff;
        color: #0f172a;
        border: 2px solid #e2e8f0;
        border-bottom: 2px solid #ffffff;
    }

    /* Colores mensuales */
    .th-renta { background: #dcfce7 !important; color: #166534; }
    .th-manto { background: #e0f2fe !important; color: #0369a1; }
    .bg-row-renta { background: #f0fdf4 !important; }
    .bg-row-manto { background: #f0f9ff !important; }
    .grand-total { background: #1e293b; color: #fff; font-size: 0.95rem; }

    @media print {
        .matrix-wrapper { max-height: none; overflow: visible; }
        .no-print { display: none !important; }
        .sticky-col, .sticky-row-1, .sticky-row-2, .sticky-row-3 { position: static; }
    }
</style>
@if(!$isExport)
    @endpush
    @section('content')
@else
    </head>
    <body style="font-family: Arial, sans-serif; margin: 20px;">
@endif

@if(!$isExport)
<div class="page-head no-print">
    
    <div class="actions" style="display:flex;gap:0.5rem;">
        <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn" style="background: #10b981; border-color: #10b981; color: white;">
            📥 Exportar a Excel
        </a>
        <button onclick="window.print()" class="btn btn-light">
            <svg style="width:16px;vertical-align:middle;margin-right:4px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
            Imprimir
        </button>
    </div>
</div>

<div class="tabs-nav no-print">
    <a href="{{ route('reports.matrix', ['mode' => 'annual', 'year' => $year, 'property_id' => $propertyId]) }}" class="tab-item {{ $mode === 'annual' ? 'active' : '' }}">
        📅 Matriz Anual
    </a>
    <a href="{{ route('reports.matrix', ['mode' => 'monthly', 'year' => $year, 'month' => $month, 'property_id' => $propertyId]) }}" class="tab-item {{ $mode === 'monthly' ? 'active' : '' }}">
        📊 Desglose Mensual
    </a>
</div>

<div class="card no-print" style="margin-bottom: 2rem;">
    <form method="GET" action="{{ route('reports.matrix') }}" class="form-grid">
        <input type="hidden" name="mode" value="{{ $mode }}">
        
        <div>
            <label for="year">Año</label>
            <select name="year" id="year" onchange="this.form.submit()">
                @for($y = date('Y') + 1; $y >= 2023; $y--)
                    <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                @endfor
            </select>
        </div>

        @if($mode === 'monthly')
            <div>
                <label for="month">Mes</label>
                <select name="month" id="month" onchange="this.form.submit()">
                    @foreach([1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'] as $m => $name)
                        <option value="{{ $m }}" @selected($m == $month)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label for="property_id">Propiedad</label>
            <select name="property_id" id="property_id" onchange="this.form.submit()">
                <option value="">Todas las propiedades</option>
                @foreach($properties as $prop)
                    <option value="{{ $prop->id }}" @selected($prop->id == $propertyId)>{{ $prop->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="date_filter">Ver por:</label>
            <select name="date_filter" id="date_filter" onchange="this.form.submit()">
                <option value="period" @selected($dateFilter === 'period')>Periodo (Devengado)</option>
                <option value="paid_at" @selected($dateFilter === 'paid_at')>Fecha de Pago (Caja)</option>
            </select>
        </div>
        
        <div class="field-span-full">
            <p class="muted">
                @if($mode === 'annual')
                    Esta matriz replica el formato de matriz anual para cruzar Locales vs Meses del año.
                @else
                    Esta vista detalla el desglose contable mensual de todos los conceptos (Rentas y Manteniemientos) para el mes seleccionado.
                @endif
            </p>
        </div>
    </form>
</div>
@endif

@if($mode === 'annual')
    {{-- TABLA DE RENTAS --}}
    <div class="matrix-section-title">
        <span>RENTA DEL MES - {{ $year }}</span>
    </div>
    <div class="matrix-wrapper">
        <table class="matrix-table">
            <thead>
                <tr class="sticky-row-1">
                    <th rowspan="3" class="sticky-col sticky-intersect" style="width: 200px;">PERIODO</th>
                    @foreach($units as $unit)
                        <th class="header-tenant">
                            {{ $unit->leases->first()->tenant->full_name ?? 'DISPONIBLE' }}
                        </th>
                    @endforeach
                    <th colspan="3" class="total-side">TOTALES FILA</th>
                </tr>
                <tr class="sticky-row-2">
                    @foreach($units as $unit)
                        <th class="header-dates">
                            @if($lease = $unit->leases->first())
                                {{ $lease->start_date?->format('d/m/y') }} - {{ $lease->end_date?->format('d/m/y') }}
                            @else
                                -
                            @endif
                        </th>
                    @endforeach
                    <th class="total-side">SUBTOTAL</th>
                    <th class="total-side">IVA</th>
                    <th class="total-side">TOTAL</th>
                </tr>
                <tr class="sticky-row-3">
                    @foreach($units as $unit)
                        <th class="header-unit">{{ $unit->code }}</th>
                    @endforeach
                    <th colspan="3" class="total-side">MXN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $period)
                    <tr>
                        <td class="sticky-col period-label">{{ $period }}</td>
                        @php $rowSubtotal = 0; $rowTax = 0; $rowTotal = 0; @endphp
                        
                        @foreach($units as $unit)
                            @php 
                                $p = $matrix[$period][$unit->code]['rent'] ?? null;
                                $statusClass = $p ? 'cell-'.$p->status : 'cell-empty';
                            @endphp
                            <td class="{{ $statusClass }}">
                                @if($p)
                                    <a href="{{ route('payments.show', $p) }}" style="text-decoration: none; color: inherit; display: block;">
                                        <div style="font-weight: bold;">${{ number_format((float) $p->amount, 2) }}</div>
                                        @if($p->invoice_folio)
                                            <div style="font-size: 0.6rem; opacity: 0.8;">F: {{ $p->invoice_folio }}</div>
                                        @elseif($p->status === 'paid' && $p->paid_at)
                                            <div style="font-size: 0.6rem; opacity: 0.8;">P: {{ $p->paid_at->format('d/m/y') }}</div>
                                        @endif
                                    </a>
                                    @php 
                                        $rowSubtotal += (float)$p->subtotal;
                                        $rowTax      += (float)$p->tax_amount;
                                        $rowTotal    += (float)$p->amount;
                                    @endphp
                                @else
                                    -
                                @endif
                            </td>
                        @endforeach
                        
                        <td class="total-side">${{ number_format($rowSubtotal, 2) }}</td>
                        <td class="total-side">${{ number_format($rowTax, 2) }}</td>
                        <td class="total-side" style="background:#dbe7f8">${{ number_format($rowTotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td class="sticky-col">TOTALES</td>
                    @foreach($units as $unit)
                        @php $colTotal = 0; @endphp
                        @foreach($periods as $period)
                            @php $colTotal += (float) ($matrix[$period][$unit->code]['rent']->amount ?? 0); @endphp
                        @endforeach
                        <td>${{ number_format($colTotal, 2) }}</td>
                    @endforeach
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- TABLA DE MANTENIMIENTOS --}}
    <div class="matrix-section-title maintenance">
        <span>MANTENIMIENTO DEL MES - {{ $year }}</span>
    </div>
    <div class="matrix-wrapper">
        <table class="matrix-table">
            <thead>
                <tr class="sticky-row-1">
                    <th rowspan="3" class="sticky-col sticky-intersect" style="width: 200px;">PERIODO</th>
                    @foreach($units as $unit)
                        <th class="header-tenant">
                            {{ $unit->leases->first()->tenant->full_name ?? 'DISPONIBLE' }}
                        </th>
                    @endforeach
                    <th colspan="3" class="total-side">TOTALES FILA</th>
                </tr>
                <tr class="sticky-row-2">
                    @foreach($units as $unit)
                        <th class="header-dates">
                            @if($lease = $unit->leases->first())
                                {{ $lease->start_date?->format('d/m/y') }} - {{ $lease->end_date?->format('d/m/y') }}
                            @else
                                -
                            @endif
                        </th>
                    @endforeach
                    <th class="total-side">SUBTOTAL</th>
                    <th class="total-side">IVA</th>
                    <th class="total-side">TOTAL</th>
                </tr>
                <tr class="sticky-row-3">
                    @foreach($units as $unit)
                        <th class="header-unit" style="background: #fff3cd !important; color: #856404;">{{ $unit->code }}</th>
                    @endforeach
                    <th colspan="3" class="total-side">MXN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $period)
                    <tr>
                        <td class="sticky-col period-label">{{ $period }}</td>
                        @php $rowSubtotalM = 0; $rowTaxM = 0; $rowTotalM = 0; @endphp
                        
                        @foreach($units as $unit)
                            @php 
                                $p = $matrix[$period][$unit->code]['maintenance'] ?? null;
                                $statusClass = $p ? 'cell-'.$p->status : 'cell-empty';
                            @endphp
                            <td class="{{ $statusClass }}">
                                @if($p)
                                    <a href="{{ route('payments.show', $p) }}" style="text-decoration: none; color: inherit; display: block;">
                                        <div style="font-weight: bold;">${{ number_format((float) $p->amount, 2) }}</div>
                                        @if($p->invoice_folio)
                                            <div style="font-size: 0.6rem; opacity: 0.8;">F: {{ $p->invoice_folio }}</div>
                                        @elseif($p->status === 'paid' && $p->paid_at)
                                            <div style="font-size: 0.6rem; opacity: 0.8;">P: {{ $p->paid_at->format('d/m/y') }}</div>
                                        @endif
                                    </a>
                                    @php 
                                        $rowSubtotalM += (float)$p->subtotal;
                                        $rowTaxM      += (float)$p->tax_amount;
                                        $rowTotalM    += (float)$p->amount;
                                    @endphp
                                @else
                                    -
                                @endif
                            </td>
                        @endforeach
                        
                        <td class="total-side">${{ number_format($rowSubtotalM, 2) }}</td>
                        <td class="total-side">${{ number_format($rowTaxM, 2) }}</td>
                        <td class="total-side" style="background:#fff3cd">${{ number_format($rowTotalM, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row" style="background: #856404 !important;">
                    <td class="sticky-col">TOTALES</td>
                    @foreach($units as $unit)
                        @php $colTotalM = 0; @endphp
                        @foreach($periods as $period)
                            @php $colTotalM += (float) ($matrix[$period][$unit->code]['maintenance']->amount ?? 0); @endphp
                        @endforeach
                        <td>${{ number_format($colTotalM, 2) }}</td>
                    @endforeach
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
@else
    {{-- MODO MENSUAL --}}
    @php
        $selectedPeriod = ucfirst(\Carbon\Carbon::create($year, $month, 1)->locale('es')->isoFormat('MMMM YYYY'));
        $gTotalRentSubtotal = 0; $gTotalRentTax = 0; $gTotalRentAmount = 0;
        $gTotalMaintSubtotal = 0; $gTotalMaintTax = 0; $gTotalMaintAmount = 0;
    @endphp

    <div class="matrix-section-title" style="background: #1e293b; color: white;">
        <span>REPORTE PORMENORIZADO DEL MES - {{ strtoupper($selectedPeriod) }}</span>
    </div>
    <div class="matrix-wrapper payment-table-wrap">
        <table class="matrix-table">
            <thead>
                <tr class="sticky-row-1">
                    <th rowspan="2" class="sticky-col sticky-intersect">NO.</th>
                    <th rowspan="2" class="sticky-col sticky-intersect" style="left: 45px;">LOCAL</th>
                    <th rowspan="2">INQUILINO</th>
                    <th rowspan="2">VIGENCIA</th>
                    <th colspan="4" class="th-renta">CONCEPTO DE RENTA</th>
                    <th colspan="4" class="th-manto">CONCEPTO DE MANTENIMIENTO</th>
                    <th rowspan="2" class="grand-total">GRAN TOTAL</th>
                </tr>
                <tr class="sticky-row-2">
                    <th class="th-renta">Estatus</th>
                    <th class="th-renta">Subtotal</th>
                    <th class="th-renta">IVA</th>
                    <th class="th-renta">Total</th>
                    <th class="th-manto">Estatus</th>
                    <th class="th-manto">Subtotal</th>
                    <th class="th-manto">IVA</th>
                    <th class="th-manto">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $statusLabels = [
                        'pending'  => 'Por Facturar',
                        'overdue'  => 'Vencido',
                        'invoiced' => 'Facturado',
                        'paid'     => 'Pagado',
                        'partial'  => 'Parcial',
                    ];
                @endphp
                @foreach($units as $idx => $unit)
                    @php
                        $lease = $unit->leases->first();
                        $rentP = $matrix[$selectedPeriod][$unit->code]['rent'] ?? null;
                        $mainP = $matrix[$selectedPeriod][$unit->code]['maintenance'] ?? null;
                        
                        $rSub = $rentP ? (float)$rentP->subtotal : 0;
                        $rTax = $rentP ? (float)$rentP->tax_amount : 0;
                        $rAmt = $rentP ? (float)$rentP->amount : 0;
 
                        $mSub = $mainP ? (float)$mainP->subtotal : 0;
                        $mTax = $mainP ? (float)$mainP->tax_amount : 0;
                        $mAmt = $mainP ? (float)$mainP->amount : 0;
 
                        $gTotalRentSubtotal += $rSub; $gTotalRentTax += $rTax; $gTotalRentAmount += $rAmt;
                        $gTotalMaintSubtotal += $mSub; $gTotalMaintTax += $mTax; $gTotalMaintAmount += $mAmt;
                    @endphp
                    <tr>
                        <td class="sticky-col">{{ $idx + 1 }}</td>
                        <td class="sticky-col" style="left: 45px;">{{ $unit->code }}</td>
                        <td>{{ $lease->tenant->full_name ?? 'VACANTE' }}</td>
                        <td>{{ $lease ? $lease->start_date?->format('d/m/y') . ' - ' . $lease->end_date?->format('d/m/y') : '-' }}</td>
 
                        {{-- Renta --}}
                        @php $rStatusClass = $rentP ? 'cell-'.$rentP->status : 'cell-empty'; @endphp
                        <td class="{{ $rStatusClass }}" style="line-height: 1.1; padding: 6px 4px;">
                            @if($rentP)
                                <a href="{{ route('payments.show', $rentP) }}" style="text-decoration: none; color: inherit; display: block;">
                                    <div style="font-weight: 800; font-size: 0.7rem; margin-bottom: 2px;">{{ strtoupper($statusLabels[$rentP->status] ?? $rentP->status) }}</div>
                                    @if($rentP->invoice_folio)
                                        <div style="font-size: 0.65rem;">Folio: {{ $rentP->invoice_folio }}</div>
                                    @endif
                                    <div style="font-size: 0.6rem; color: #64748b; font-weight: normal; margin-top: 2px;">{{ $rentP->period_label }}</div>
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="bg-row-renta">${{ number_format($rSub, 2) }}</td>
                        <td class="bg-row-renta">${{ number_format($rTax, 2) }}</td>
                        <td class="th-renta">${{ number_format($rAmt, 2) }}</td>
 
                        {{-- Manto --}}
                        @php $mStatusClass = $mainP ? 'cell-'.$mainP->status : 'cell-empty'; @endphp
                        <td class="{{ $mStatusClass }}" style="line-height: 1.1; padding: 6px 4px;">
                            @if($mainP)
                                <a href="{{ route('payments.show', $mainP) }}" style="text-decoration: none; color: inherit; display: block;">
                                    <div style="font-weight: 800; font-size: 0.7rem; margin-bottom: 2px;">{{ strtoupper($statusLabels[$mainP->status] ?? $mainP->status) }}</div>
                                    @if($mainP->invoice_folio)
                                        <div style="font-size: 0.65rem;">Folio: {{ $mainP->invoice_folio }}</div>
                                    @endif
                                    <div style="font-size: 0.6rem; color: #64748b; font-weight: normal; margin-top: 2px;">{{ $mainP->period_label }}</div>
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="bg-row-manto">${{ number_format($mSub, 2) }}</td>
                        <td class="bg-row-manto">${{ number_format($mTax, 2) }}</td>
                        <td class="th-manto">${{ number_format($mAmt, 2) }}</td>
 
                        <td class="grand-total">${{ number_format($rAmt + $mAmt, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4">TOTALES:</td>
                    <td style="font-size:0.7rem;color:#94a3b8;">—</td>
                    <td>${{ number_format($gTotalRentSubtotal, 2) }}</td>
                    <td>${{ number_format($gTotalRentTax, 2) }}</td>
                    <td>${{ number_format($gTotalRentAmount, 2) }}</td>
                    <td style="font-size:0.7rem;color:#94a3b8;">—</td>
                    <td>${{ number_format($gTotalMaintSubtotal, 2) }}</td>
                    <td>${{ number_format($gTotalMaintTax, 2) }}</td>
                    <td>${{ number_format($gTotalMaintAmount, 2) }}</td>
                    <td style="color: #fbbf24;">${{ number_format($gTotalRentAmount + $gTotalMaintAmount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Vista de tarjetas para matriz mensual en móvil --}}
    <div class="payment-cards-grid" style="margin-top:1rem;">
        @foreach($units as $unit)
            @php
                $lease = $unit->leases->first();
                $rentP = $matrix[$selectedPeriod][$unit->code]['rent'] ?? null;
                $mainP = $matrix[$selectedPeriod][$unit->code]['maintenance'] ?? null;
                $rAmt = $rentP ? (float)$rentP->amount : 0;
                $mAmt = $mainP ? (float)$mainP->amount : 0;
            @endphp
            <div class="payment-card" style="border-radius:16px; background:#fff; border:1px solid var(--border); box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
                    <div>
                        <div style="font-weight:800; color:var(--primary); font-size:1.1rem;">{{ $unit->code }}</div>
                        <div style="font-size:0.85rem; font-weight:700; color:var(--text);">{{ $lease->tenant->full_name ?? 'VACANTE' }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:0.7rem; color:var(--muted); text-transform:uppercase;">Total Mes</div>
                        <div style="font-weight:800; font-size:1.2rem; color:var(--text);">${{ number_format($rAmt + $mAmt, 2) }}</div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; background:#f8fafc; border-radius:12px; padding:0.75rem;">
                    {{-- Renta Card Part --}}
                    <div style="border-right:1px solid #e2e8f0; padding-right:0.5rem;">
                        <div style="font-size:0.65rem; font-weight:800; color:#166534; text-transform:uppercase; margin-bottom:0.2rem;">Renta</div>
                        @if($rentP)
                            <div style="font-weight:700; font-size:0.9rem;">${{ number_format($rAmt, 2) }}</div>
                            <div class="badge" style="font-size:0.6rem; padding:0.1rem 0.4rem; margin-top:0.2rem; background:{{ $rentP->status === 'paid' ? '#dcfce7' : ($rentP->status === 'overdue' ? '#fee2e2' : '#fef3c7') }}; color:inherit;">
                                {{ strtoupper($statusLabels[$rentP->status] ?? $rentP->status) }}
                            </div>
                        @else
                            <div style="color:var(--muted); font-size:0.8rem;">—</div>
                        @endif
                    </div>
                    {{-- Manto Card Part --}}
                    <div style="padding-left:0.2rem;">
                        <div style="font-size:0.65rem; font-weight:800; color:#0369a1; text-transform:uppercase; margin-bottom:0.2rem;">Manto.</div>
                        @if($mainP)
                            <div style="font-weight:700; font-size:0.9rem;">${{ number_format($mAmt, 2) }}</div>
                            <div class="badge" style="font-size:0.6rem; padding:0.1rem 0.4rem; margin-top:0.2rem; background:{{ $mainP->status === 'paid' ? '#dcfce7' : ($mainP->status === 'overdue' ? '#fee2e2' : '#fef3c7') }}; color:inherit;">
                                {{ strtoupper($statusLabels[$mainP->status] ?? $mainP->status) }}
                            </div>
                        @else
                            <div style="color:var(--muted); font-size:0.8rem;">—</div>
                        @endif
                    </div>
                </div>
                
                <div style="display:flex; gap:0.5rem; margin-top:0.75rem;">
                    @if($rentP) <a href="{{ route('payments.show', $rentP) }}" class="btn btn-light" style="flex:1; font-size:0.75rem; padding:0.4rem;">Renta</a> @endif
                    @if($mainP) <a href="{{ route('payments.show', $mainP) }}" class="btn btn-light" style="flex:1; font-size:0.75rem; padding:0.4rem;">Manto.</a> @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@if(!$isExport)
    @endsection
@else
    </body>
    </html>
@endif
