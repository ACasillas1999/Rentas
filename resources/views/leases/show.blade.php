@extends('layouts.app')

@section('title', 'Detalle de Contrato')

@section('content')
    <div class="page-head">
        <h1>Contrato {{ $lease->contract_number ?: 'Sin folio' }}</h1>
        <div class="actions" style="display: flex; gap: 0.8rem;">
            <a class="btn btn-primary" href="{{ route('leases.payments.bulkEdit', $lease) }}" style="background: #10b981; border-color: #10b981; color: white;">
                ✏️ Edición Masiva (Excel)
            </a>
            @if($lease->status === 'active')
                <a class="btn btn-primary" href="{{ route('leases.renew', $lease) }}" style="background: var(--success); border-color: var(--success);">
                    Renovar Contrato
                </a>
            @endif
            <a class="btn btn-primary" href="{{ route('leases.edit', $lease) }}">Editar Contrato</a>
            <a class="btn btn-light" href="{{ route('leases.index') }}">Volver</a>
        </div>
    </div>

    <div class="grid grid-2" style="margin-bottom: 1rem;">
        <!-- Información del Contrato -->
        <div class="card" style="margin-bottom: 0;">
            <h3 style="margin-bottom: 1.5rem; color: #153464; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Información del Contrato</h3>
            
            <div class="grid grid-2" style="gap: 1.2rem;">
                <div>
                    <p class="muted" style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Unidad</p>
                    <p style="margin: 0.2rem 0 0; font-weight: 600; font-size: 1.05rem; color: #173763;">{{ $lease->unit->property->name ?? '-' }} / {{ $lease->unit->code ?? '-' }}</p>
                </div>
                <div>
                    <p class="muted" style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Inquilino</p>
                    <p style="margin: 0.2rem 0 0; font-weight: 600; font-size: 1.05rem; color: #173763;">{{ $lease->tenant->full_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="muted" style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Periodo</p>
                    <p style="margin: 0.2rem 0 0; font-weight: 600;">{{ $lease->start_date?->format('d/m/Y') }} a {{ $lease->end_date?->format('d/m/Y') ?: 'Abierto' }}</p>
                </div>
                <div>
                    <p class="muted" style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Inicio de periodo</p>
                    <p style="margin: 0.2rem 0 0; font-weight: 600;">{{ $lease->first_period_start?->format('d/m/Y') ?: $lease->start_date?->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="muted" style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Pagos generados</p>
                    <p style="margin: 0.2rem 0 0; font-weight: 600;">{{ $lease->payments->where('type','rent')->count() }} renta · {{ $lease->payments->where('type','maintenance')->count() }} mant.</p>
                </div>
                <div>
                    <p class="muted" style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Renta mensual</p>
                    <p style="margin: 0.2rem 0 0; font-weight: 800; font-size: 1.1rem; color: #19703a;">${{ number_format((float) $lease->monthly_amount, 2) }}</p>
                </div>
                <div>
                    <p class="muted" style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Mantenimiento</p>
                    <p style="margin: 0.2rem 0 0; font-weight: 600; font-size: 1.1rem; color: #19703a;">${{ number_format((float) $lease->maintenance_amount, 2) }}</p>
                </div>
                <div>
                    <p class="muted" style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Depósito</p>
                    <p style="margin: 0.2rem 0 0; font-weight: 600;">${{ number_format((float) $lease->deposit_amount, 2) }}</p>
                </div>
                <div>
                    <p class="muted" style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Estatus</p>
                    <p style="margin: 0.3rem 0 0;">
                        @if($lease->status === 'active')
                            <span class="badge badge-ok" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Activo</span>
                        @elseif($lease->status === 'finished')
                            <span class="badge badge-bad" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Finalizado</span>
                        @else
                            <span class="badge badge-warn" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">{{ ucfirst($lease->status) }}</span>
                        @endif
                    </p>
                </div>
                <div style="grid-column: 1 / -1;">
                    <p class="muted" style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Notas</p>
                    <p style="margin: 0.2rem 0 0; line-height: 1.5; background: var(--surface-soft); padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">{{ $lease->notes ?: 'Sin notas adicionales.' }}</p>
                </div>
            </div>
        </div>

        <!-- Documento Adjunto (PDF) -->
        <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                <h3 style="margin: 0; color: #153464;">Documento del Contrato</h3>
                @if($lease->contract_pdf)
                    <a href="{{ route('secure.download', ['file' => encrypt($lease->contract_pdf)]) }}" target="_blank" class="btn btn-primary" style="padding: 0.3rem 0.7rem; font-size: 0.8rem;">
                        <span style="display: flex; align-items: center; gap: 0.4rem;">
                            <svg viewBox="0 0 24 24" width="16" height="16" style="stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Descargar
                        </span>
                    </a>
                @endif
            </div>
            
            @if($lease->contract_pdf)
                <div style="flex: 1; min-height: 500px; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; background: #e9eef6;">
                    <iframe src="{{ route('secure.download', ['file' => encrypt($lease->contract_pdf)]) }}" width="100%" height="100%" style="border: none; min-height: 500px; display: block;">
                        Tu navegador no soporta la visualización de PDFs. 
                        <a href="{{ route('secure.download', ['file' => encrypt($lease->contract_pdf)]) }}">Descargar archivo</a>.
                    </iframe>
                </div>
            @else
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 2px dashed var(--border); border-radius: 8px; padding: 2rem; background: var(--surface-soft); color: var(--muted); min-height: 300px;">
                    <svg viewBox="0 0 24 24" width="56" height="56" style="stroke: currentColor; fill: none; stroke-width: 1.5; margin-bottom: 1.25rem; opacity: 0.5;">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <p style="margin: 0 0 0.5rem 0; font-size: 1.05rem; font-weight: 600; color: #384658;">Sin contrato adjunto</p>
                    <p style="margin: 0 0 1.5rem 0; font-size: 0.9rem; text-align: center; max-width: 280px;">Aún no se ha cargado un documento PDF para este contrato.</p>
                    <a href="{{ route('leases.edit', $lease) }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                        <svg viewBox="0 0 24 24" width="16" height="16" style="stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        Cargar Documento
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Pagos del Contrato -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem; color: #153464; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Pagos del Contrato</h3>
        @if ($lease->payments->isEmpty())
            <div style="padding: 2rem; text-align: center; background: var(--surface-soft); border-radius: 8px; border: 1px dashed var(--border);">
                <p class="muted" style="margin: 0;">No hay pagos registrados para este contrato.</p>
            </div>
        @else
            <div class="payment-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Cobertura del Periodo</th>
                            <th>Vence</th>
                            <th>Monto</th>
                            <th>Estatus</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lease->payments as $payment)
                            <tr>
                                <td style="font-weight: 700; color: #2a3f5d; white-space: nowrap;">
                                    @if($payment->period_number && $payment->total_periods)
                                        {{ $payment->period_number }}/{{ $payment->total_periods }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($payment->type === 'maintenance')
                                        <span class="badge" style="background:#eef2fb; color:#384658; font-size:0.72em;">Mant.</span>
                                    @else
                                        <span class="badge" style="background:#e8f4eb; color:#1b6336; font-size:0.72em;">Renta</span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    @if($payment->period_start && $payment->period_end)
                                        <span style="font-size: 0.88rem; color: #384658;">
                                            {{ $payment->period_start->locale('es')->isoFormat('D MMM') }}
                                            –
                                            {{ $payment->period_end->locale('es')->isoFormat('D MMM YYYY') }}
                                        </span>
                                    @else
                                        {{ $payment->period_label ?: '—' }}
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">{{ $payment->due_date?->format('d/m/Y') }}</td>
                                <td><span style="font-weight: 600; color: #173763;">${{ number_format((float) $payment->amount + (float) $payment->late_fee, 2) }}</span></td>
                                <td>
                                    @if($payment->status === 'paid')
                                        <span class="badge badge-ok" style="font-size: 0.75rem;">Pagado</span>
                                    @elseif($payment->status === 'invoiced')
                                        <span class="badge" style="background:#dbeafe;color:#1e40af;font-size:0.75rem;">Facturado</span>
                                    @elseif($payment->status === 'overdue')
                                        <span class="badge badge-bad" style="font-size: 0.75rem;">Vencido</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="badge badge-warn" style="font-size: 0.75rem;">Por facturar</span>
                                    @else
                                        <span class="badge" style="background:#eef2fb; color:#384658; font-size: 0.75rem;">{{ ucfirst($payment->status) }}</span>
                                    @endif
                                </td>
                                <td class="actions">
                                    <a class="btn btn-light" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;" href="{{ route('payments.show', $payment) }}">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Vista de Tarjetas de Pago para Móvil --}}
            <div class="payment-cards-grid">
                @foreach ($lease->payments as $payment)
                    <div class="payment-card">
                        <div class="payment-card-status">
                            @if($payment->status === 'paid')
                                <span class="badge badge-ok">Pagado</span>
                            @elseif($payment->status === 'overdue')
                                <span class="badge badge-bad">Vencido</span>
                            @elseif($payment->status === 'pending')
                                <span class="badge badge-warn">Por facturar</span>
                            @else
                                <span class="badge" style="background:#eef2fb; color:#384658;">{{ ucfirst($payment->status) }}</span>
                            @endif
                        </div>
                        
                        <div class="payment-card-period">
                            Periodo {{ $payment->period_number }}/{{ $payment->total_periods }}
                            <span class="payment-card-type" style="margin-left: 0.5rem;">{{ $payment->type === 'rent' ? 'Renta' : 'Mant.' }}</span>
                        </div>
                        
                        <div class="payment-card-dates">
                            {{ $payment->period_start?->locale('es')->isoFormat('D MMM') }} – {{ $payment->period_end?->locale('es')->isoFormat('D MMM YYYY') }}
                        </div>
                        
                        <div class="payment-card-row">
                            <div style="font-size: 0.8rem; color: var(--muted);">Vence: {{ $payment->due_date?->format('d/m/y') }}</div>
                            <div class="payment-card-amount">${{ number_format((float) $payment->amount, 2) }}</div>
                        </div>

                        <a href="{{ route('payments.show', $payment) }}" class="btn btn-light" style="width: 100%; text-align: center; margin-top: 0.4rem;">Ver Detalle</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

