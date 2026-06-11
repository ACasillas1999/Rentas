<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Resumen Mensual — {{ $periodLabel }}</title>
<!--[if mso]>
<noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
<![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:'Segoe UI',Arial,Helvetica,sans-serif;-webkit-font-smoothing:antialiased;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4f8;">
<tr><td align="center" style="padding:32px 16px;">

  <!-- WRAPPER -->
  <table width="620" cellpadding="0" cellspacing="0" border="0" style="max-width:620px;width:100%;">

    <!-- ══════════ HEADER ══════════ -->
    <tr>
      <td bgcolor="#1e3a8a" style="background-color:#1e3a8a;background:linear-gradient(135deg,#1e3a8a 0%,#3b82f6 50%,#06b6d4 100%);border-radius:20px 20px 0 0;padding:40px 32px 36px;text-align:center;">
        <!-- Logo mark -->
        <div style="display:inline-block;width:64px;height:64px;background:rgba(255,255,255,0.2);border-radius:18px;line-height:64px;font-size:28px;margin-bottom:18px;border:2px solid rgba(255,255,255,0.3);">🏠</div>
        <br>
        <span style="display:inline-block;font-size:11px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:#bfdbfe;margin-bottom:8px;">Sistema de Arrendamientos · Grupo Ascencio</span>
        <br>
        <span style="font-size:30px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">Resumen Mensual</span>
        <br>
        <span style="display:inline-block;margin-top:8px;background:rgba(255,255,255,0.2);color:#ffffff;font-size:14px;font-weight:600;padding:5px 18px;border-radius:20px;border:1px solid rgba(255,255,255,0.3);">📅 {{ $periodLabel }}</span>
      </td>
    </tr>

    <!-- ══════════ BODY ══════════ -->
    <tr>
      <td style="background:#ffffff;padding:32px 32px 8px;">

        <!-- Section label -->
        <p style="margin:0 0 16px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#94a3b8;">Indicadores Clave del Mes</p>

        <!-- KPI GRID 2x2 -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <!-- KPI 1: Tasa Cobranza -->
            <td width="48%" style="padding-right:8px;padding-bottom:12px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td bgcolor="#eff6ff" style="background-color:#eff6ff;background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1px solid #bfdbfe;border-radius:16px;padding:20px;text-align:center;">
                    <div style="font-size:22px;margin-bottom:6px;">💵</div>
                    <div style="font-size:36px;font-weight:900;line-height:1;color:{{ $stats['tasaCobranza']>=80 ? '#16a34a' : ($stats['tasaCobranza']>=50 ? '#d97706' : '#dc2626') }};">
                      {{ $stats['tasaCobranza'] }}%
                    </div>
                    <div style="font-size:12px;font-weight:700;color:#1e40af;margin-top:5px;">Tasa de Cobranza</div>
                    <div style="font-size:11px;color:#6b7280;margin-top:2px;">${{ number_format($stats['totalCobrado'],2) }} cobrado</div>
                  </td>
                </tr>
              </table>
            </td>
            <!-- KPI 2: Ocupación -->
            <td width="48%" style="padding-left:8px;padding-bottom:12px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td bgcolor="#f0fdf4" style="background-color:#f0fdf4;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;border-radius:16px;padding:20px;text-align:center;">
                    <div style="font-size:22px;margin-bottom:6px;">🏠</div>
                    <div style="font-size:36px;font-weight:900;line-height:1;color:{{ $stats['tasaOcupacion']>=80 ? '#16a34a' : ($stats['tasaOcupacion']>=50 ? '#d97706' : '#dc2626') }};">
                      {{ $stats['tasaOcupacion'] }}%
                    </div>
                    <div style="font-size:12px;font-weight:700;color:#15803d;margin-top:5px;">Ocupación</div>
                    <div style="font-size:11px;color:#6b7280;margin-top:2px;">{{ $stats['unidadesOcupadas'] }} / {{ $stats['totalUnidades'] }} unidades</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <!-- KPI 3: Pagos recibidos -->
            <td width="48%" style="padding-right:8px;padding-bottom:12px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td bgcolor="#f5f3ff" style="background-color:#f5f3ff;background:linear-gradient(135deg,#f5f3ff,#ede9fe);border:1px solid #ddd6fe;border-radius:16px;padding:20px;text-align:center;">
                    <div style="font-size:22px;margin-bottom:6px;">✅</div>
                    <div style="font-size:36px;font-weight:900;line-height:1;color:#7c3aed;">{{ $stats['pagadosTotal'] }}</div>
                    <div style="font-size:12px;font-weight:700;color:#6d28d9;margin-top:5px;">Pagos Recibidos</div>
                    <div style="font-size:11px;color:#6b7280;margin-top:2px;">{{ $stats['pagadosATiempo'] }} a tiempo · {{ $stats['pagadosConRetraso'] }} tarde</div>
                  </td>
                </tr>
              </table>
            </td>
            <!-- KPI 4: Vencidos -->
            <td width="48%" style="padding-left:8px;padding-bottom:12px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td bgcolor="{{ $stats['vencidosCount']>0 ? '#fff1f2' : '#f0fdf4' }}" style="background-color:{{ $stats['vencidosCount']>0 ? '#fff1f2' : '#f0fdf4' }};background:{{ $stats['vencidosCount']>0 ? 'linear-gradient(135deg,#fff1f2,#ffe4e6)' : 'linear-gradient(135deg,#f0fdf4,#dcfce7)' }};border:1px solid {{ $stats['vencidosCount']>0 ? '#fecdd3' : '#bbf7d0' }};border-radius:16px;padding:20px;text-align:center;">
                    <div style="font-size:22px;margin-bottom:6px;">{{ $stats['vencidosCount']>0 ? '⚠️' : '🎉' }}</div>
                    <div style="font-size:36px;font-weight:900;line-height:1;color:{{ $stats['vencidosCount']>0 ? '#dc2626' : '#16a34a' }};">{{ $stats['vencidosCount'] }}</div>
                    <div style="font-size:12px;font-weight:700;color:{{ $stats['vencidosCount']>0 ? '#b91c1c' : '#15803d' }};margin-top:5px;">Pagos Vencidos</div>
                    <div style="font-size:11px;color:#6b7280;margin-top:2px;">{{ $stats['pendientesCount'] }} pendientes · {{ $stats['parcialesCount'] }} parciales</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

      </td>
    </tr>

    <!-- ══════════ COBRANZA PROGRESS ══════════ -->
    <tr>
      <td style="background:#ffffff;padding:0 32px 24px;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
          <tr>
            <td style="padding:20px 24px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="font-size:13px;font-weight:700;color:#374151;">📊 Desglose de Pagos del Mes</td>
                  <td align="right" style="font-size:18px;font-weight:800;color:{{ $stats['tasaCobranza']>=80 ? '#16a34a' : ($stats['tasaCobranza']>=50 ? '#d97706' : '#dc2626') }};">{{ $stats['tasaCobranza'] }}% cobrado</td>
                </tr>
              </table>
              <!-- Progress bar -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:12px 0 16px;">
                <tr>
                  <td style="background:#e2e8f0;border-radius:8px;height:12px;overflow:hidden;">
                      <!--[if mso]><v:rect xmlns:v="urn:schemas-microsoft-com:vml" style="width:{{ min($stats['tasaCobranza'],100) }}%;height:12px;" fillcolor="{{ $stats['tasaCobranza']>=80 ? '#16a34a' : ($stats['tasaCobranza']>=50 ? '#d97706' : '#dc2626') }}" stroked="false"><v:fill type="solid"/></v:rect><![endif]-->
                    <div style="background-color:{{ $stats['tasaCobranza']>=80 ? '#16a34a' : ($stats['tasaCobranza']>=50 ? '#d97706' : '#dc2626') }};background:{{ $stats['tasaCobranza']>=80 ? 'linear-gradient(90deg,#16a34a,#22c55e)' : ($stats['tasaCobranza']>=50 ? 'linear-gradient(90deg,#d97706,#f59e0b)' : 'linear-gradient(90deg,#dc2626,#f87171)') }};width:{{ min($stats['tasaCobranza'],100) }}%;height:12px;border-radius:8px;min-width:4px;"></div>
                  </td>
                </tr>
              </table>
              <!-- Pill badges -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="padding:4px;">
                    <span style="display:inline-block;background:#dcfce7;color:#15803d;font-size:11px;font-weight:700;padding:5px 12px;border-radius:20px;margin:2px 3px 2px 0;">✅ A tiempo: {{ $stats['pagadosATiempo'] }}</span>
                    <span style="display:inline-block;background:#fef9c3;color:#854d0e;font-size:11px;font-weight:700;padding:5px 12px;border-radius:20px;margin:2px 3px 2px 0;">🕐 Con retraso: {{ $stats['pagadosConRetraso'] }}</span>
                    @if($stats['facturadosCount']>0)<span style="display:inline-block;background:#ede9fe;color:#6d28d9;font-size:11px;font-weight:700;padding:5px 12px;border-radius:20px;margin:2px 3px 2px 0;">📑 Facturados: {{ $stats['facturadosCount'] }}</span>@endif
                    @if($stats['pendientesCount']>0)<span style="display:inline-block;background:#f1f5f9;color:#475569;font-size:11px;font-weight:700;padding:5px 12px;border-radius:20px;margin:2px 3px 2px 0;">⏳ Pendientes: {{ $stats['pendientesCount'] }}</span>@endif
                    @if($stats['parcialesCount']>0)<span style="display:inline-block;background:#ffedd5;color:#9a3412;font-size:11px;font-weight:700;padding:5px 12px;border-radius:20px;margin:2px 3px 2px 0;">🔸 Parciales: {{ $stats['parcialesCount'] }}</span>@endif
                    @if($stats['vencidosCount']>0)<span style="display:inline-block;background:#fee2e2;color:#991b1b;font-size:11px;font-weight:700;padding:5px 12px;border-radius:20px;margin:2px 3px 2px 0;">❌ Vencidos: {{ $stats['vencidosCount'] }}</span>@endif
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- ══════════ RESUMEN FINANCIERO ══════════ -->
    <tr>
      <td style="background:#ffffff;padding:0 32px 24px;">
        <p style="margin:0 0 12px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#94a3b8;">💰 Resumen Financiero</p>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
          <tr style="background:#f8fafc;">
            <td style="padding:14px 20px;font-size:13px;color:#374151;border-bottom:1px solid #e2e8f0;">
              <span style="display:inline-block;width:10px;height:10px;background:#22c55e;border-radius:50%;margin-right:8px;vertical-align:middle;"></span>Total Cobrado
            </td>
            <td align="right" style="padding:14px 20px;font-size:15px;font-weight:800;color:#16a34a;border-bottom:1px solid #e2e8f0;">${{ number_format($stats['totalCobrado'],2) }}</td>
          </tr>
          @if($stats['totalPendiente']>0)
          <tr style="background:#fff;">
            <td style="padding:14px 20px;font-size:13px;color:#374151;border-bottom:1px solid #e2e8f0;">
              <span style="display:inline-block;width:10px;height:10px;background:#f87171;border-radius:50%;margin-right:8px;vertical-align:middle;"></span>Pendiente / Vencido
            </td>
            <td align="right" style="padding:14px 20px;font-size:15px;font-weight:800;color:#dc2626;border-bottom:1px solid #e2e8f0;">${{ number_format($stats['totalPendiente'],2) }}</td>
          </tr>
          @endif
          <tr style="background:#fff;">
            <td style="padding:14px 20px;font-size:13px;color:#374151;border-bottom:1px solid #e2e8f0;">
              <span style="display:inline-block;width:10px;height:10px;background:#fb923c;border-radius:50%;margin-right:8px;vertical-align:middle;"></span>Gastos del Mes
            </td>
            <td align="right" style="padding:14px 20px;font-size:15px;font-weight:800;color:#d97706;border-bottom:1px solid #e2e8f0;">${{ number_format($stats['totalGastos'],2) }}</td>
          </tr>
          <tr style="background:{{ $stats['utilidadNeta']>=0 ? '#f0fdf4' : '#fff1f2' }};">
            <td style="padding:18px 20px;font-size:15px;font-weight:800;color:#111827;">
              ⚡ Utilidad Neta
            </td>
            <td align="right" style="padding:18px 20px;font-size:20px;font-weight:900;color:{{ $stats['utilidadNeta']>=0 ? '#16a34a' : '#dc2626' }};">${{ number_format($stats['utilidadNeta'],2) }}</td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- ══════════ POR PROPIEDAD ══════════ -->
    @if($stats['porPropiedad']->count() > 0)
    <tr>
      <td style="background:#ffffff;padding:0 32px 24px;">
        <p style="margin:0 0 12px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#94a3b8;">🏢 Desglose por Propiedad</p>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
          <tr style="background:#f8fafc;">
            <td style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#6b7280;border-bottom:2px solid #e2e8f0;">Propiedad</td>
            <td align="center" style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#6b7280;border-bottom:2px solid #e2e8f0;">Pagados</td>
            <td align="right" style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#6b7280;border-bottom:2px solid #e2e8f0;">Cobrado</td>
          </tr>
          @foreach($stats['porPropiedad'] as $nombre => $prop)
          <tr>
            <td style="padding:13px 16px;font-size:13px;color:#1e293b;font-weight:600;border-bottom:1px solid #f1f5f9;">
              <span style="display:inline-block;width:8px;height:8px;background-color:#3b82f6;background:linear-gradient(135deg,#3b82f6,#8b5cf6);border-radius:50%;margin-right:8px;vertical-align:middle;"></span>
              {{ $nombre }}
              @if($prop['pendiente']>0)
              <br><span style="font-size:11px;font-weight:400;color:#ef4444;margin-left:16px;">↳ Pendiente: ${{ number_format($prop['pendiente'],2) }}</span>
              @endif
            </td>
            <td align="center" style="padding:13px 16px;font-size:13px;color:#6b7280;border-bottom:1px solid #f1f5f9;">{{ $prop['pagados'] }}&thinsp;/&thinsp;{{ $prop['total'] }}</td>
            <td align="right" style="padding:13px 16px;font-size:14px;font-weight:800;color:#16a34a;border-bottom:1px solid #f1f5f9;">${{ number_format($prop['cobrado'],2) }}</td>
          </tr>
          @endforeach
        </table>
      </td>
    </tr>
    @endif

    <!-- ══════════ ALERT VENCIDOS ══════════ -->
    @if($stats['inquilinosVencidos']->count() > 0)
    <tr>
      <td style="background:#ffffff;padding:0 32px 24px;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fff1f2;border:2px solid #fecdd3;border-radius:16px;overflow:hidden;">
          <tr>
            <td style="background:#dc2626;padding:12px 20px;">
              <span style="font-size:13px;font-weight:800;color:#ffffff;">🚨 Pagos Vencidos sin Regularizar ({{ $stats['inquilinosVencidos']->count() }})</span>
            </td>
          </tr>
          @foreach($stats['inquilinosVencidos'] as $v)
          <tr>
            <td style="padding:12px 20px;border-bottom:1px solid #fecdd3;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="font-size:13px;color:#7f1d1d;">
                    <strong style="color:#991b1b;">{{ $v['nombre'] }}</strong>
                    <span style="color:#9f1239;"> — {{ $v['propiedad'] }} / Unid. {{ $v['unidad'] }}</span>
                  </td>
                  <td align="right" style="font-size:14px;font-weight:800;color:#dc2626;white-space:nowrap;">
                    ${{ number_format($v['monto'],2) }}
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          @endforeach
        </table>
      </td>
    </tr>
    @endif

    <!-- ══════════ CTA BUTTON ══════════ -->
    <tr>
      <td style="background:#ffffff;padding:0 32px 32px;text-align:center;">
        <a href="{{ $appUrl }}/reports/income?month={{ $month }}&year={{ $year }}"
           style="display:inline-block;background-color:#1e3a8a;background:linear-gradient(135deg,#1e3a8a,#3b82f6);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:16px 40px;border-radius:12px;letter-spacing:0.02em;box-shadow:0 4px 14px rgba(59,130,246,0.4);">
          📊 Ver Reporte Completo →
        </a>
        <p style="margin:12px 0 0;font-size:12px;color:#94a3b8;">
          También disponible en: <a href="{{ $appUrl }}/reports/monthly" style="color:#3b82f6;text-decoration:none;">Panel de Reportes Mensuales</a>
        </p>
      </td>
    </tr>

    <!-- ══════════ DIVIDER ══════════ -->
    <tr>
      <td style="background:#ffffff;padding:0 32px;">
        <div style="height:1px;background:linear-gradient(90deg,transparent,#e2e8f0,transparent);"></div>
      </td>
    </tr>

    <!-- ══════════ FOOTER ══════════ -->
    <tr>
      <td style="background:#f8fafc;border-radius:0 0 20px 20px;padding:24px 32px;text-align:center;border:1px solid #e2e8f0;border-top:none;">
        <p style="margin:0 0 4px;font-size:13px;font-weight:700;color:#374151;">🏢 Grupo Ascencio — Sistema de Arrendamientos</p>
        <p style="margin:0 0 8px;font-size:12px;color:#94a3b8;">
          Generado automáticamente el {{ now()->locale('es')->isoFormat('D [de] MMMM YYYY [a las] HH:mm') }}
        </p>
        <p style="margin:0;font-size:11px;color:#cbd5e1;">
          Este correo es generado automáticamente · No responder a este mensaje
        </p>
      </td>
    </tr>

    <!-- Spacer -->
    <tr><td style="height:32px;"></td></tr>

  </table>
  <!-- /WRAPPER -->

</td></tr>
</table>

</body>
</html>
