@extends('layouts.app')

@section('title', 'Gastos')

@section('content')
    <div class="page-head">
        
        <button type="button" class="btn btn-primary" id="btn-new-expense" onclick="document.getElementById('modal-new-expense').classList.add('is-open')">
            <svg viewBox="0 0 24 24" width="16" height="16" style="margin-right:0.3rem;" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo Gasto
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Filtros --}}
    <form method="GET" class="card" style="margin-bottom:1.5rem;">
        <div class="form-grid" style="margin-bottom:0;">
            <div>
                <label>Propiedad</label>
                <select name="property_id">
                    <option value="">— Todas —</option>
                    @foreach ($properties as $prop)
                        <option value="{{ $prop->id }}" {{ request('property_id') == $prop->id ? 'selected' : '' }}>{{ $prop->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Categoría</label>
                <select name="category">
                    <option value="">— Todas —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Mes</label>
                <select name="month">
                    <option value="">— Todos —</option>
                    @foreach (range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Año</label>
                <select name="year">
                    @foreach (range(now()->year, 2020) as $y)
                        <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-actions" style="margin-top:1rem;padding-top:1rem;border-top:1px solid #e8edf3;">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-light">Limpiar</a>
            <span class="muted" style="margin-left:auto;font-size:0.88rem;">
                Total filtrado: <strong style="color:#b82020;">${{ number_format($totalAmount, 2) }}</strong>
            </span>
        </div>
    </form>

    <div class="card">
        @if ($expenses->isEmpty())
            <p class="muted" style="text-align:center;padding:2rem 0;">
                No hay gastos registrados.
                <a href="#" onclick="document.getElementById('modal-new-expense').classList.add('is-open');return false;">Registrar el primero</a>.
            </p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Propiedad / Local</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Pagado a</th>
                        <th style="text-align:right;">Monto</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date?->format('d/m/Y') }}</td>
                            <td>
                                {{ $expense->property->name ?? '-' }}
                                @if ($expense->unit)
                                    <span class="muted"> / {{ $expense->unit->code }}</span>
                                @endif
                            </td>
                            <td><span class="badge">{{ $expense->category }}</span></td>
                            <td>{{ $expense->description }}</td>
                            <td class="muted">{{ $expense->paid_to ?? '-' }}</td>
                            <td style="text-align:right;font-weight:700;color:#b82020;">
                                ${{ number_format((float)$expense->amount, 2) }}
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('expenses.show', $expense) }}" class="btn btn-light">Ver</a>
                                    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-light">Editar</a>
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('¿Eliminar este gasto?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:1.5rem;">{{ $expenses->links() }}</div>
        @endif
    </div>
@endsection

@push('modals')
    {{-- Modal: Nuevo Gasto --}}
    <div class="modal-overlay" id="modal-new-expense">
        <div class="modal-dialog" style="max-width:780px;">
            <div class="modal-head">
                <h2 class="modal-title">Registrar Gasto</h2>
                <button class="modal-close" type="button" onclick="document.getElementById('modal-new-expense').classList.remove('is-open')">✕</button>
            </div>
            <div class="modal-body">
                <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-grid">
                        <div>
                            <label for="m_property_id">Propiedad *</label>
                            <select name="property_id" id="m_property_id" required>
                                <option value="">— Seleccionar —</option>
                                @foreach ($properties as $prop)
                                    <option value="{{ $prop->id }}" {{ old('property_id') == $prop->id ? 'selected' : '' }}>{{ $prop->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="m_unit_id">Local / Unidad (opcional)</label>
                            <select name="unit_id" id="m_unit_id">
                                <option value="">— General de la propiedad —</option>
                            </select>
                        </div>
                        <div>
                            <label for="m_category">Categoría *</label>
                            <select name="category" id="m_category" required>
                                <option value="">— Seleccionar —</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="m_expense_date">Fecha del Gasto *</label>
                            <input type="date" id="m_expense_date" name="expense_date" value="{{ old('expense_date', now()->toDateString()) }}" required>
                        </div>
                        <div>
                            <label for="m_description">Descripción *</label>
                            <input type="text" id="m_description" name="description" value="{{ old('description') }}" required placeholder="Ej. Reparación de fuga en tubería">
                        </div>
                        <div>
                            <label for="m_amount">Monto ($) *</label>
                            <input type="number" id="m_amount" name="amount" min="0" step="0.01" value="{{ old('amount') }}" required placeholder="0.00">
                        </div>
                        <div>
                            <label for="m_paid_to">Pagado a (Proveedor / Técnico)</label>
                            <input type="text" id="m_paid_to" name="paid_to" value="{{ old('paid_to') }}" placeholder="Nombre del proveedor">
                        </div>
                        <div>
                            <label for="m_receipt">Comprobante (Factura / Foto)</label>
                            <input type="file" id="m_receipt" name="receipt" accept="image/*,.pdf">
                        </div>
                        <div class="span-2">
                            <label for="m_notes">Notas adicionales</label>
                            <textarea id="m_notes" name="notes" rows="2" placeholder="Información adicional...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top:1.5rem;border-top:1px solid #e8edf3;padding-top:1rem;">
                        <button type="submit" class="btn btn-primary">Registrar Gasto</button>
                        <button type="button" class="btn btn-light" onclick="document.getElementById('modal-new-expense').classList.remove('is-open')">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    {{-- Cerrar modal al hacer click fuera --}}
    <script>
    document.getElementById('modal-new-expense').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('is-open');
    });

    document.getElementById('m_property_id').addEventListener('change', function () {
        const propertyId = this.value;
        const unitSelect = document.getElementById('m_unit_id');
        unitSelect.innerHTML = '<option value="">— General de la propiedad —</option>';
        if (!propertyId) return;
        fetch(`/expenses/units/${propertyId}`)
            .then(r => r.json())
            .then(units => {
                units.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.code;
                    unitSelect.appendChild(opt);
                });
            });
    });

    // Abrir modal si hay errores de validación
    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modal-new-expense').classList.add('is-open');
        });
    @endif
    </script>
@endpush
