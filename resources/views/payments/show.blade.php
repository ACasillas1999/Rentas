@extends('layouts.app')
@section('title', 'Detalle de Pago')

@push('styles')
<style>
.show-grid { display:grid; grid-template-columns:1fr 340px; gap:1.5rem; align-items:start; }
.info-label { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; color:#64748b; margin-bottom:0.3rem; }
.info-value { font-weight:600; font-size:0.95rem; color:#1e293b; }

/* Period navigator */
.period-nav { display:flex; align-items:center; gap:0.5rem; }
.period-nav select { flex:1; padding:0.5rem 0.75rem; border:1px solid #e2e8f0; border-radius:8px; font-size:0.85rem; font-weight:600; background:#fff; cursor:pointer; }
.period-nav a.nav-btn { display:flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; background:#f1f5f9; border:1px solid #e2e8f0; color:#475569; text-decoration:none; font-size:1rem; flex-shrink:0; }
.period-nav a.nav-btn:hover { background:#e2e8f0; }
.period-nav a.nav-btn.disabled { opacity:0.35; pointer-events:none; }

/* Status badge */
.status-badge { display:inline-flex; align-items:center; gap:0.4rem; padding:0.4rem 1rem; border-radius:20px; font-weight:700; font-size:0.82rem; }

/* File chips */
.file-chip { display:inline-flex; align-items:center; gap:0.5rem; padding:0.4rem 0.8rem; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:8px; font-size:0.8rem; color:#334155; text-decoration:none; margin:0.2rem 0; }
.file-chip:hover { background:#e2e8f0; }

/* Upload zones */
.dz { border:2px dashed #cbd5e1; border-radius:10px; padding:1.2rem; text-align:center; cursor:pointer; background:#f8fafc; transition:all 0.2s; }
.dz:hover, .dz.over { border-color:var(--primary); background:#eff6ff; }
.dz input { display:none; }
.dz-icon { color:#94a3b8; margin-bottom:0.4rem; }
.dz-text { font-size:0.82rem; color:#475569; font-weight:500; }

/* Folio input */
.folio-row { display:flex; gap:0.5rem; margin-bottom:0.75rem; }
.folio-row input { flex:1; padding:0.5rem 0.75rem; border:1px solid #e2e8f0; border-radius:8px; font-size:0.85rem; }
.folio-row input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,0.1); }

@media print {
    .no-print { display:none !important; }
    .show-grid { grid-template-columns:1fr; }
    .print-header { display:block !important; }
}
</style>
@endpush

@section('content')

{{-- Print header --}}
<div class="print-header" style="display:none;margin-bottom:2rem;border-bottom:2px solid #000;padding-bottom:1rem;">
    <div style="font-size:1.8rem;font-weight:800;">RentAscencio</div>
    <div style="font-size:0.9rem;color:#555;">Recibo de Pago de Arrendamiento</div>
</div>

@php
    $statusMap = [
        'paid'     => ['bg'=>'#dcfce7','text'=>'#166534','label'=>'Pagado'],
        'invoiced' => ['bg'=>'#dbeafe','text'=>'#1e40af','label'=>'Facturado'],
        'pending'  => ['bg'=>'#fef3c7','text'=>'#92400e','label'=>'Por Facturar'],
        'overdue'  => ['bg'=>'#fee2e2','text'=>'#b91c1c','label'=>'Vencido'],
        'partial'  => ['bg'=>'#f3e8ff','text'=>'#7c3aed','label'=>'Parcial'],
    ];
    $st = $statusMap[$payment->status] ?? $statusMap['pending'];

    $prevPayment = $siblingPayments->where('id', '<', $payment->id)->last();
    $nextPayment = $siblingPayments->where('id', '>', $payment->id)->first();

    $invoicePdfs = is_array($payment->invoice_pdf) ? $payment->invoice_pdf : ($payment->invoice_pdf ? [$payment->invoice_pdf] : []);
    $invoiceXmls = is_array($payment->invoice_xml) ? $payment->invoice_xml : ($payment->invoice_xml ? [$payment->invoice_xml] : []);
    $receipts    = is_array($payment->receipt)      ? $payment->receipt      : ($payment->receipt      ? [$payment->receipt]      : []);
@endphp

{{-- Page header --}}
<div class="page-head no-print">
    <div style="display:flex;align-items:center;gap:0.75rem;flex:1;">
        <a href="{{ route('payments.index') }}" class="btn btn-light" style="padding:0.4rem;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
        <h1 style="margin:0;">Detalle de Pago</h1>

        {{-- Period navigator --}}
        <div class="period-nav" style="margin-left:1rem;">
            <a href="{{ $prevPayment ? route('payments.show', $prevPayment) : '#' }}" class="nav-btn {{ !$prevPayment ? 'disabled' : '' }}" title="Periodo anterior">‹</a>
            <select onchange="window.location=this.value">
                @foreach($siblingPayments as $sib)
                    @php
                        $sibLabel = $sib->period_label ?: ($sib->due_date?->format('M Y') ?? 'Pago #'.$sib->id);
                        $sibType  = $sib->type === 'rent' ? '🏠' : '⚙️';
                        $sibSt    = $statusMap[$sib->status]['label'] ?? $sib->status;
                    @endphp
                    <option value="{{ route('payments.show', $sib) }}" {{ $sib->id === $payment->id ? 'selected' : '' }}>
                        {{ $sibType }} {{ $sibLabel }} — {{ $sibSt }}
                    </option>
                @endforeach
            </select>
            <a href="{{ $nextPayment ? route('payments.show', $nextPayment) : '#' }}" class="nav-btn {{ !$nextPayment ? 'disabled' : '' }}" title="Periodo siguiente">›</a>
        </div>
    </div>
    <div style="display:flex;gap:0.5rem;">
        <button class="btn btn-light" onclick="window.print()">🖨️ Imprimir</button>
        <a class="btn btn-primary" href="{{ route('payments.edit', $payment) }}">✏️ Editar</a>
    </div>
</div>

<div class="show-grid">

    {{-- LEFT COLUMN --}}
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        {{-- Summary card --}}
        <div class="card" style="border-top:4px solid {{ $st['text'] }};display:flex;justify-content:space-between;align-items:center;padding:1.5rem;">
            <div>
                <div class="info-label">Total del Pago</div>
                <div style="font-size:2.8rem;font-weight:800;color:#1e293b;line-height:1;">
                    ${{ number_format((float)$payment->amount + (float)$payment->late_fee, 2) }}
                </div>
                @if($payment->invoice_folio)
                    <div style="margin-top:0.5rem;font-size:0.82rem;color:#64748b;">Folio fiscal: <strong>{{ $payment->invoice_folio }}</strong></div>
                @endif
                @if($payment->invoiced_at)
                    <div style="font-size:0.82rem;color:#64748b;">Facturado el: <strong>{{ $payment->invoiced_at->isoFormat('D [de] MMMM, YYYY') }}</strong></div>
                @endif
            </div>
            <div style="text-align:right;">
                <span class="status-badge" style="background:{{ $st['bg'] }};color:{{ $st['text'] }};border:1px solid {{ $st['text'] }}33;">
                    {{ $st['label'] }}
                </span>
                <div class="muted" style="margin-top:0.6rem;font-size:0.85rem;">
                    @if($payment->period_start && $payment->period_end)
                        Periodo {{ $payment->period_number }}/{{ $payment->total_periods }}
                        &mdash;
                        {{ $payment->period_start->locale('es')->isoFormat('D MMM') }}
                        &ndash;
                        {{ $payment->period_end->locale('es')->isoFormat('D MMM YYYY') }}
                    @else
                        {{ $payment->period_label ?: 'Sin periodo' }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Payment info --}}
        <div class="card">
            <h3 style="margin-bottom:1.2rem;color:#1e293b;">📄 Información del Pago</h3>
            <div class="grid grid-2" style="gap:1.2rem;">
                <div><div class="info-label">Monto Pactado</div><div class="info-value">${{ number_format((float)$payment->amount, 2) }}</div></div>
                <div><div class="info-label">Subtotal (sin IVA)</div><div class="info-value">${{ number_format((float)$payment->subtotal, 2) }}</div></div>
                <div><div class="info-label">IVA (16%)</div><div class="info-value">${{ number_format((float)$payment->tax_amount, 2) }}</div></div>
                <div>
                    <div class="info-label">Recargos / Mora</div>
                    <div class="info-value" style="color:{{ (float)$payment->late_fee > 0 ? '#b91c1c' : 'inherit' }};">
                        ${{ number_format((float)$payment->late_fee, 2) }}
                    </div>
                </div>
                <div><div class="info-label">Vencimiento</div><div class="info-value">{{ $payment->due_date?->isoFormat('D [de] MMMM, YYYY') ?: '-' }}</div></div>
                <div>
                    <div class="info-label">Fecha de Pago</div>
                    <div class="info-value">{{ $payment->paid_at?->isoFormat('D [de] MMMM, YYYY') ?: 'Pendiente' }}</div>
                </div>
                <div><div class="info-label">Método</div><div class="info-value">{{ $payment->payment_method ?: '—' }}</div></div>
                <div><div class="info-label">Referencia</div><div class="info-value" style="font-family:monospace;">{{ $payment->reference ?: 'Sin referencia' }}</div></div>
            </div>
            @if($payment->notes)
                <div style="margin-top:1.2rem;padding-top:1.2rem;border-top:1px solid #f1f5f9;">
                    <div class="info-label">Notas</div>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:0.75rem;font-size:0.9rem;line-height:1.6;">{{ $payment->notes }}</div>
                </div>
            @endif
        </div>

        {{-- Tenant & contract --}}
        <div class="card">
            <h3 style="margin-bottom:1.2rem;color:#1e293b;">👤 Inquilino y Contrato</h3>
            <div class="grid grid-2" style="gap:1.2rem;">
                <div>
                    <div class="info-label">Inquilino</div>
                    <a href="{{ route('tenants.show', $payment->lease->tenant_id) }}" style="color:var(--primary);font-weight:700;text-decoration:none;">
                        {{ $payment->lease->tenant->full_name ?? '-' }} ↗
                    </a>
                </div>
                <div><div class="info-label">Unidad / Propiedad</div><div class="info-value">{{ $payment->lease->unit->code ?? '-' }} / {{ $payment->lease->unit->property->name ?? '-' }}</div></div>
                <div>
                    <div class="info-label">Contrato</div>
                    <a href="{{ route('leases.show', $payment->lease_id) }}" style="color:var(--primary);font-weight:700;text-decoration:none;">
                        #{{ $payment->lease->contract_number ?: $payment->lease_id }} ↗
                    </a>
                </div>
                <div><div class="info-label">Renta Mensual</div><div class="info-value">${{ number_format((float)$payment->lease->monthly_amount, 2) }}</div></div>
                @if($payment->period_start && $payment->period_end)
                    <div class="span-2">
                        <div class="info-label">Periodo de Cobertura</div>
                        <div class="info-value">
                            {{ $payment->period_start->locale('es')->isoFormat('D [de] MMMM') }} &ndash;
                            {{ $payment->period_end->locale('es')->isoFormat('D [de] MMMM, YYYY') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div class="no-print" style="display:flex;flex-direction:column;gap:1.2rem;">

        {{-- Comprobante de pago --}}
        <div class="card" style="padding:1.25rem;">
            <h3 style="font-size:1rem;margin-bottom:1rem;">🧾 Comprobante de Pago</h3>

            {{-- Lista de comprobantes existentes --}}
            @if(count($receipts))
                <div style="margin-bottom:0.75rem;">
                    @foreach($receipts as $f)
                        @php $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION)); $url = route('secure.download', ['file' => encrypt($f)]); @endphp
                        @if(in_array($ext, ['jpg','jpeg','png','webp','gif']))
                            <a href="{{ $url }}" target="_blank" style="display:block;margin-bottom:0.5rem;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;">
                                <img src="{{ $url }}" alt="Comprobante" style="width:100%;height:auto;display:block;">
                            </a>
                        @else
                            <a href="{{ $url }}" target="_blank" class="file-chip" style="display:flex;">
                                📄 {{ basename($f) }}
                            </a>
                        @endif
                    @endforeach
                </div>
            @else
                <div style="text-align:center;padding:1.2rem;color:#a0aec0;background:#f9fbfd;border:2px dashed #e2e8f0;border-radius:10px;margin-bottom:0.75rem;">
                    <div style="font-size:2rem;margin-bottom:0.4rem;">🧾</div>
                    <div style="font-size:0.82rem;">Sin comprobante cargado</div>
                </div>
            @endif

            {{-- Upload zone receipt --}}
            <form id="form-receipt" action="{{ route('payments.uploadReceipt', $payment) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;margin-bottom:0.6rem;">
                    <div>
                        <div class="info-label" style="margin-bottom:0.25rem;">Fecha de Pago</div>
                        <input type="date" name="paid_at" value="{{ $payment->paid_at?->format('Y-m-d') ?? now()->format('Y-m-d') }}" style="width:100%;padding:0.45rem 0.6rem;border:1px solid #e2e8f0;border-radius:8px;font-size:0.82rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <div class="info-label" style="margin-bottom:0.25rem;">Monto Pagado</div>
                        <input type="number" name="paid_amount" step="0.01" value="{{ number_format((float)$payment->paid_amount ?: (float)$payment->amount, 2, '.', '') }}" style="width:100%;padding:0.45rem 0.6rem;border:1px solid #e2e8f0;border-radius:8px;font-size:0.82rem;box-sizing:border-box;">
                    </div>
                </div>
                <div class="dz" id="dz-receipt">
                    <input type="file" name="receipt[]" id="file-receipt" accept="image/*,.pdf" multiple>
                    <div class="dz-icon">⬆️</div>
                    <div class="dz-text">Arrastra comprobantes (PDF/Foto)<br><small style="color:#94a3b8;">Puedes subir varios a la vez</small></div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem;font-size:0.85rem;" id="btn-receipt" disabled>
                    ✓ Subir Comprobante(s)
                </button>
            </form>
        </div>

        {{-- Factura Fiscal --}}
        <div class="card" style="padding:1.25rem;">
            <h3 style="font-size:1rem;margin-bottom:1rem;">📋 Factura Fiscal</h3>

            {{-- Folio actual --}}
            @if($payment->invoice_folio)
                <div style="background:#dbeafe;border:1px solid #93c5fd;border-radius:8px;padding:0.6rem 0.9rem;margin-bottom:0.75rem;font-size:0.85rem;color:#1e40af;font-weight:700;">
                    Folio: {{ $payment->invoice_folio }}
                </div>
            @endif

            {{-- Lista PDFs --}}
            @if(count($invoicePdfs))
                <div style="margin-bottom:0.5rem;">
                    <div class="info-label" style="margin-bottom:0.3rem;">PDFs</div>
                    @foreach($invoicePdfs as $f)
                        <a href="{{ route('secure.download', ['file' => encrypt($f)]) }}" target="_blank" class="file-chip">
                            📄 {{ basename($f) }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Lista XMLs --}}
            @if(count($invoiceXmls))
                <div style="margin-bottom:0.75rem;">
                    <div class="info-label" style="margin-bottom:0.3rem;">XMLs CFDI</div>
                    @foreach($invoiceXmls as $f)
                        <a href="{{ route('secure.download', ['file' => encrypt($f)]) }}" target="_blank" class="file-chip">
                            🧩 {{ basename($f) }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Upload form --}}
            <form id="form-invoice" action="{{ route('payments.uploadInvoice', $payment) }}" method="POST" enctype="multipart/form-data">
                @csrf
                {{-- Folio + Fecha facturación --}}
                <div style="margin-bottom:0.6rem;">
                    <div class="info-label" style="margin-bottom:0.25rem;">Folio de Factura (UUID / Serie)</div>
                    <input type="text" name="invoice_folio" value="{{ $payment->invoice_folio ?? '' }}" placeholder="Ej: A-00123 o UUID del CFDI" style="width:100%;padding:0.45rem 0.7rem;border:1px solid #e2e8f0;border-radius:8px;font-size:0.83rem;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:0.75rem;">
                    <div class="info-label" style="margin-bottom:0.25rem;">Fecha de Facturación</div>
                    <input type="date" name="invoiced_at" value="{{ $payment->invoiced_at?->format('Y-m-d') ?? now()->format('Y-m-d') }}" style="width:100%;padding:0.45rem 0.7rem;border:1px solid #e2e8f0;border-radius:8px;font-size:0.83rem;box-sizing:border-box;">
                </div>
                {{-- PDFs --}}
                <div class="dz" id="dz-pdf" style="margin-bottom:0.5rem;">
                    <input type="file" name="invoice_pdf[]" id="file-pdf" accept=".pdf" multiple>
                    <div class="dz-icon">📄</div>
                    <div class="dz-text">PDF(s) de Factura<br><small style="color:#94a3b8;">Hasta 4 archivos</small></div>
                </div>
                {{-- XMLs --}}
                <div class="dz" id="dz-xml" style="margin-bottom:0.75rem;">
                    <input type="file" name="invoice_xml[]" id="file-xml" accept=".xml" multiple>
                    <div class="dz-icon">🧩</div>
                    <div class="dz-text">XML(s) CFDI<br><small style="color:#94a3b8;">Hasta 4 archivos</small></div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;font-size:0.85rem;background:#1e40af;">
                    📋 Guardar Factura
                </button>
            </form>
        </div>

        {{-- Info note --}}
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:0.9rem 1rem;display:flex;gap:0.6rem;">
            <span style="font-size:1rem;">ℹ️</span>
            <p style="font-size:0.8rem;margin:0;color:#1e40af;line-height:1.5;">
                Al subir factura → estado pasa a <strong>Facturado</strong>.<br>
                Al subir comprobante → estado pasa a <strong>Pagado</strong>.
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Dropzone click & drag setup
    const setupDz = (dzId, inputId) => {
        const dz    = document.getElementById(dzId);
        const input = document.getElementById(inputId);
        if (!dz || !input) return;

        dz.addEventListener('click', () => input.click());
        dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('over'); });
        dz.addEventListener('dragleave', () => dz.classList.remove('over'));
        dz.addEventListener('drop', e => {
            e.preventDefault();
            dz.classList.remove('over');
            const dt = new DataTransfer();
            [...e.dataTransfer.files].forEach(f => dt.items.add(f));
            input.files = dt.files;
            dz.querySelector('.dz-text').innerHTML = `<strong>${dt.files.length} archivo(s) seleccionado(s)</strong>`;
        });

        input.addEventListener('change', () => {
            if (input.files.length) {
                dz.querySelector('.dz-text').innerHTML = `<strong>${input.files.length} archivo(s) seleccionado(s)</strong>`;
            }
        });
    };

    setupDz('dz-receipt', 'file-receipt');
    setupDz('dz-pdf',     'file-pdf');
    setupDz('dz-xml',     'file-xml');

    // Enable receipt button when files selected
    const fileReceipt = document.getElementById('file-receipt');
    const btnReceipt  = document.getElementById('btn-receipt');
    if (fileReceipt && btnReceipt) {
        fileReceipt.addEventListener('change', () => {
            btnReceipt.disabled = fileReceipt.files.length === 0;
        });
    }

    // AJAX form submit with reload on success
    const ajaxForm = (formId) => {
        const form = document.getElementById(formId);
        if (!form) return;
        form.addEventListener('submit', async e => {
            e.preventDefault();
            const btn = form.querySelector('button[type=submit]');
            btn.disabled = true;
            btn.textContent = 'Subiendo…';
            try {
                const res  = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al guardar.');
                    btn.disabled = false;
                    btn.textContent = '✓ Reintentar';
                }
            } catch {
                alert('Error de red.');
                btn.disabled = false;
            }
        });
    };

    ajaxForm('form-receipt');
    ajaxForm('form-invoice');
});
</script>
@endpush
