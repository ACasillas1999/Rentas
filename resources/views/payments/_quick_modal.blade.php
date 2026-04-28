{{-- _quick_modal.blade.php — Modal de cobro rápido reutilizable --}}
{{-- Variables requeridas: $payment, $modalPrefix (string único, ej: 'ov' o 'up') --}}
@php
    $mid   = $modalPrefix . '-' . $payment->id;
    $step  = match($payment->status) { 'invoiced','partial' => 2, 'paid' => 3, default => 1 };
    $dot   = fn($s) => $step >= $s ? 'background:#1e40af;color:#fff' : 'background:#e2e8f0;color:#94a3b8';
    $line  = fn($s) => $step >  $s ? 'background:#1e40af' : 'background:#e2e8f0';
    $periodLabel = ($payment->period_start && $payment->period_end)
        ? $payment->period_start->locale('es')->isoFormat('D MMM') . ' – ' . $payment->period_end->locale('es')->isoFormat('D MMM YYYY')
        : ($payment->period_label ?? $payment->due_date?->format('d/m/Y'));
@endphp

<div class="modal-overlay" id="modal-pay-{{ $mid }}">
    <div class="modal-dialog" style="width:min(600px,96vw);">

        {{-- Cabecera --}}
        <div class="modal-head" style="padding:1rem 1.25rem;">
            <div>
                <h2 class="modal-title" style="font-size:1rem;margin-bottom:0.1rem;">
                    {{ $payment->type === 'rent' ? '🏠 Renta' : '⚙️ Mantenimiento' }}
                    @if($payment->period_number && $payment->total_periods)
                        &nbsp;—&nbsp;{{ $payment->period_number }}/{{ $payment->total_periods }}
                    @endif
                </h2>
                <div class="muted" style="font-size:0.8rem;">{{ $periodLabel }}</div>
            </div>
            <button class="modal-close" data-modal-close>✕</button>
        </div>

        <div class="modal-body" style="padding:1.25rem;">

            {{-- Info card --}}
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.8rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:0.9rem 1rem;margin-bottom:1.2rem;">
                <div>
                    <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;color:#64748b;">Inquilino</div>
                    <div style="font-weight:600;font-size:0.9rem;">{{ $payment->lease->tenant->full_name ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;color:#64748b;">Unidad / Propiedad</div>
                    <div style="font-weight:600;font-size:0.9rem;">{{ ($payment->lease->unit->code ?? '—') }} / {{ ($payment->lease->unit->property->name ?? '—') }}</div>
                </div>
                <div>
                    <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;color:#64748b;">Monto pactado</div>
                    <div style="font-weight:800;font-size:1.1rem;color:#1e40af;">${{ number_format((float)$payment->amount, 2) }}</div>
                </div>
            </div>

            {{-- Indicador de pasos --}}
            <div style="display:flex;align-items:center;margin-bottom:1.4rem;">
                @foreach([1 => 'Por facturar', 2 => 'Facturado', 3 => 'Pagado'] as $n => $label)
                    <div style="display:flex;flex-direction:column;align-items:center;gap:0.3rem;min-width:88px;">
                        <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:700;{{ $dot($n) }}">{{ $n }}</div>
                        <span style="font-size:0.72rem;font-weight:600;color:{{ $step >= $n ? '#1e40af' : '#94a3b8' }};">{{ $label }}</span>
                    </div>
                    @if($n < 3)
                        <div style="flex:1;height:2px;margin-bottom:1.1rem;{{ $line($n) }};"></div>
                    @endif
                @endforeach
            </div>

            @if($payment->status === 'paid')
                {{-- Pago ya confirmado --}}
                <div style="text-align:center;padding:1.5rem;background:#f0fdf4;border-radius:10px;border:1px solid #bbf7d0;">
                    <div style="font-size:2rem;margin-bottom:0.5rem;">✅</div>
                    <div style="font-weight:700;color:#166534;">Pago completado</div>
                    <div class="muted" style="font-size:0.85rem;">Pagado el {{ $payment->paid_at?->format('d/m/Y') }} · ${{ number_format((float)$payment->paid_amount,2) }}</div>
                </div>
            @else
                {{-- Tabs --}}
                <div style="display:flex;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;margin-bottom:1.2rem;">
                    <button type="button"
                        id="tab-btn-inv-{{ $mid }}"
                        style="flex:1;padding:0.65rem;font-size:0.85rem;font-weight:600;border:none;cursor:pointer;transition:background 0.15s;{{ $step < 2 ? 'background:#1e40af;color:#fff' : 'background:#f8fafc;color:#475569' }}"
                        onclick="qmTab('{{ $mid }}','invoice')">
                        📋 Registrar Factura
                    </button>
                    <button type="button"
                        id="tab-btn-pay-{{ $mid }}"
                        style="flex:1;padding:0.65rem;font-size:0.85rem;font-weight:600;border:none;border-left:1px solid #e2e8f0;cursor:pointer;transition:background 0.15s;{{ $step >= 2 ? 'background:#1e40af;color:#fff' : 'background:#f8fafc;color:#475569' }}"
                        onclick="qmTab('{{ $mid }}','pay')">
                        💰 Registrar Pago
                    </button>
                </div>

                {{-- Tab: Factura --}}
                <div id="tab-invoice-{{ $mid }}" style="{{ $step >= 2 ? 'display:none;' : '' }}">
                    <form class="ajax-invoice-form" method="POST" action="{{ route('payments.uploadInvoice', $payment) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-grid">
                            <div class="field-span-full">
                                <label>Folio de Factura <span class="muted" style="font-weight:400;">(Número, UUID o serie del CFDI)</span></label>
                                <input type="text" name="invoice_folio" value="{{ $payment->invoice_folio ?? '' }}" placeholder="Ej: A-00123">
                            </div>
                            <div class="field-span-full">
                                <label>Fecha de Facturación</label>
                                <input type="date" name="invoiced_at" value="{{ $payment->invoiced_at?->format('Y-m-d') ?? now()->toDateString() }}">
                            </div>
                            <div>
                                <label>PDF(s) de Factura</label>
                                <input type="file" name="invoice_pdf[]" accept=".pdf" multiple>
                                @if($payment->invoice_pdf)
                                    <div style="margin-top:0.3rem;">
                                        @foreach((array)$payment->invoice_pdf as $file)
                                            <small class="muted" style="display:block;"><a href="{{ route('secure.download', ['file' => encrypt($file)]) }}" target="_blank">📄 {{ basename($file) }}</a></small>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label>XML(s) CFDI</label>
                                <input type="file" name="invoice_xml[]" accept=".xml,.txt" multiple>
                                @if($payment->invoice_xml)
                                    <div style="margin-top:0.3rem;">
                                        @foreach((array)$payment->invoice_xml as $file)
                                            <small class="muted" style="display:block;"><a href="{{ route('secure.download', ['file' => encrypt($file)]) }}" target="_blank">🧩 {{ basename($file) }}</a></small>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">📋 Guardar Factura</button>
                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-light">Ver ficha completa</a>
                        </div>
                    </form>
                </div>

                {{-- Tab: Pago --}}
                <div id="tab-pay-{{ $mid }}" style="{{ $step < 2 ? 'display:none;' : '' }}">
                    <form class="ajax-pay-form" method="POST" action="{{ route('payments.markPaid', $payment) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-grid">
                            <div>
                                <label>Fecha de pago</label>
                                <input type="date" name="paid_at" value="{{ $payment->paid_at?->format('Y-m-d') ?? now()->toDateString() }}">
                            </div>
                            <div>
                                <label>Monto pagado ($) <small class="muted" style="font-weight:400;">pactado: ${{ number_format((float)$payment->amount,2) }}</small></label>
                                <input type="number" name="paid_amount" min="0" step="0.01" value="{{ number_format((float)$payment->amount,2,'.','') }}">
                            </div>
                            <div>
                                <label>Recargo / Mora ($)</label>
                                <input type="number" name="late_fee" min="0" step="0.01" value="{{ number_format((float)$payment->late_fee,2,'.','') }}">
                            </div>
                            <div>
                                <label>Método de pago</label>
                                <select name="payment_method">
                                    <option value="">— Seleccionar —</option>
                                    @foreach(['Efectivo','Transferencia','Tarjeta','Cheque','Otro'] as $m)
                                        <option value="{{ $m }}" @selected($payment->payment_method === $m)>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field-span-full">
                                <label>No. de operación / Referencia</label>
                                <input type="text" name="reference" value="{{ $payment->reference ?? '' }}" placeholder="No. de transferencia, cheque, etc.">
                            </div>
                            <div class="field-span-full">
                                <label>Comprobante(s) de pago (foto o PDF)</label>
                                <input type="file" name="receipt[]" accept="image/*,.pdf" multiple>
                                @if($payment->receipt)
                                    <div style="margin-top:0.3rem;">
                                        @foreach((array)$payment->receipt as $file)
                                            <small class="muted" style="display:block;"><a href="{{ route('secure.download', ['file' => encrypt($file)]) }}" target="_blank">🧾 {{ basename($file) }}</a></small>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">✓ Confirmar Pago</button>
                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-light">Ver ficha completa</a>
                            <button type="button" class="btn btn-light" data-modal-close>Cancelar</button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</div>
