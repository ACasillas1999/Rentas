<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
    body { font-family: Arial, Helvetica, sans-serif; margin: 15px; }
    h2 { color: #1e293b; margin-bottom: 4px; }
    p.subtitle { color: #64748b; font-size: 12px; margin-top: 0; margin-bottom: 16px; }
    table { border-collapse: collapse; width: 100%; font-size: 11px; }
    th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: center; }
    .th-main { background-color: #1e293b; color: #ffffff; font-weight: bold; }
    .th-rent { background-color: #166534; color: #dcfce7; font-weight: bold; }
    .th-rent-sub { background-color: #14532d; color: #dcfce7; font-weight: bold; font-size: 10px; }
    .th-maint { background-color: #0369a1; color: #e0f2fe; font-weight: bold; }
    .th-maint-sub { background-color: #075985; color: #e0f2fe; font-weight: bold; font-size: 10px; }
    .th-total { background-color: #0f172a; color: #fbbf24; font-weight: bold; }
    .cell-rent { background-color: #f0fdf4; text-align: right; }
    .cell-maint { background-color: #f0f9ff; text-align: right; }
    .cell-grand { background-color: #f8fafc; font-weight: bold; text-align: right; color: #0f172a; }
    .total-row { background-color: #1e293b; color: #ffffff; font-weight: bold; }
    .badge { font-weight: bold; font-size: 9px; padding: 2px 4px; }
</style>
</head>
<body>
    <h2>REPORTE PORMENORIZADO DEL MES - {{ strtoupper($matrixData['selectedPeriod'] ?? $periodLabel) }}</h2>
    <p class="subtitle">Generado el {{ now()->locale('es')->isoFormat('D [de] MMMM YYYY [a las] HH:mm') }} · Sistema de Arrendamientos</p>

    <table>
        <thead>
            <tr class="th-main">
                <th rowspan="2">NO.</th>
                <th rowspan="2">LOCAL</th>
                <th rowspan="2">INQUILINO</th>
                <th rowspan="2">VIGENCIA</th>
                <th colspan="4" class="th-rent">CONCEPTO DE RENTA</th>
                <th colspan="4" class="th-maint">CONCEPTO DE MANTENIMIENTO</th>
                <th rowspan="2" class="th-total">GRAN TOTAL</th>
            </tr>
            <tr>
                <th class="th-rent-sub">Estatus</th>
                <th class="th-rent-sub">Subtotal</th>
                <th class="th-rent-sub">IVA</th>
                <th class="th-rent-sub">Total</th>
                <th class="th-maint-sub">Estatus</th>
                <th class="th-maint-sub">Subtotal</th>
                <th class="th-maint-sub">IVA</th>
                <th class="th-maint-sub">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($matrixData['rows'] as $row)
            <tr>
                <td>{{ $row['no'] }}</td>
                <td><strong>{{ $row['unit_code'] }}</strong></td>
                <td style="text-align: left;">{{ $row['tenant_name'] }}</td>
                <td>{{ $row['vigencia'] }}</td>

                {{-- Renta --}}
                <td>
                    <span class="badge">{{ strtoupper($row['rent']['status_label']) }}</span>
                    @if($row['rent']['invoice_folio']) (Folio: {{ $row['rent']['invoice_folio'] }}) @endif
                </td>
                <td class="cell-rent">${{ number_format($row['rent']['subtotal'], 2) }}</td>
                <td class="cell-rent">${{ number_format($row['rent']['tax'], 2) }}</td>
                <td class="cell-rent" style="font-weight: bold; color: #166534;">${{ number_format($row['rent']['total'], 2) }}</td>

                {{-- Mantenimiento --}}
                <td>
                    <span class="badge">{{ strtoupper($row['maintenance']['status_label']) }}</span>
                    @if($row['maintenance']['invoice_folio']) (Folio: {{ $row['maintenance']['invoice_folio'] }}) @endif
                </td>
                <td class="cell-maint">${{ number_format($row['maintenance']['subtotal'], 2) }}</td>
                <td class="cell-maint">${{ number_format($row['maintenance']['tax'], 2) }}</td>
                <td class="cell-maint" style="font-weight: bold; color: #0369a1;">${{ number_format($row['maintenance']['total'], 2) }}</td>

                {{-- Gran Total --}}
                <td class="cell-grand">${{ number_format($row['grand_total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">TOTALES:</td>
                <td>—</td>
                <td style="text-align: right;">${{ number_format($matrixData['totals']['rent_subtotal'], 2) }}</td>
                <td style="text-align: right;">${{ number_format($matrixData['totals']['rent_tax'], 2) }}</td>
                <td style="text-align: right; color: #86efac;">${{ number_format($matrixData['totals']['rent_total'], 2) }}</td>
                <td>—</td>
                <td style="text-align: right;">${{ number_format($matrixData['totals']['maintenance_subtotal'], 2) }}</td>
                <td style="text-align: right;">${{ number_format($matrixData['totals']['maintenance_tax'], 2) }}</td>
                <td style="text-align: right; color: #7dd3fc;">${{ number_format($matrixData['totals']['maintenance_total'], 2) }}</td>
                <td style="text-align: right; color: #fde047; font-size: 12px;">${{ number_format($matrixData['totals']['grand_total'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
