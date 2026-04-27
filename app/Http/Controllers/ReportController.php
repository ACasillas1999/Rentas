<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Lease;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function income(Request $request)
    {
        $now   = Carbon::now();
        $month = (int) $request->get('month', $now->month);
        $year  = (int) $request->get('year',  $now->year);
        $mode  = $request->get('mode', 'accrual'); // 'accrual' (Vencimiento) o 'cash' (Flujo de Caja)

        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);

        // --- LÓGICA DE FILTRADO SEGÚN EL MODO ---
        $paymentsQuery = Payment::with(['lease.unit.property', 'lease.unit.beneficiary', 'lease.tenant'])
            ->whereNotNull('due_date');

        if ($mode === 'cash') {
            // Modo Flujo de Caja: Dinero que entró físicamente en este mes
            // O pagos que vencían este mes y siguen pendientes (para no perder visibilidad)
            $paymentsQuery->where(function($q) use ($year, $month) {
                $q->whereYear('paid_at', $year)->whereMonth('paid_at', $month)
                  ->orWhere(function($sq) use ($year, $month) {
                      $sq->whereYear('due_date', $year)->whereMonth('due_date', $month)
                         ->whereIn('status', ['pending', 'overdue', 'invoiced', 'partial']);
                  });
            });
        } else {
            // Modo Vencimiento (Tradicional): Pagos que corresponden a este mes
            $paymentsQuery->whereYear('due_date', $year)
                          ->whereMonth('due_date', $month);
        }

        $payments = $paymentsQuery->get();


        // Depósitos de contratos que inician en este mes (con detalle)
        $depositLeases = \App\Models\Lease::with(['unit.property', 'tenant'])
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->where('deposit_amount', '>', 0)
            ->get();

        $totalDeposits = $depositLeases->sum('deposit_amount');

        // Cálculo exacto de lo cobrado (paid_amount de TODOS los pagos en la colección)
        $totalPaid = (float) $payments->sum('paid_amount');
        
        // Lo que falta por cobrar (Monto pactado + recargos - lo ya pagado)
        $totalPending = $payments->whereIn('status', ['pending', 'overdue', 'invoiced', 'partial'])
            ->sum(fn($p) => (float)($p->amount + ($p->late_fee ?? 0)) - (float)($p->paid_amount ?? 0));

        // Total FACTURADO en el mes (por fecha de facturación, independiente del filtro modo)
        $totalInvoicedMonth = (float) Payment::whereYear('invoiced_at', $year)
            ->whereMonth('invoiced_at', $month)
            ->whereIn('status', ['invoiced', 'paid'])
            ->sum('amount');

        // Total COBRADO en el mes (dinero que entró físicamente, por fecha de pago)
        $totalCashedMonth = (float) Payment::whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->where('status', 'paid')
            ->sum('paid_amount');

        // Desglose por propiedad
        $byProperty = $payments
            ->groupBy(fn($p) => $p->lease->unit->property->name ?? 'Sin propiedad')
            ->map(fn($group) => [
                'paid'     => (float) $group->sum('paid_amount'),
                'pending'  => $group->whereIn('status', ['pending', 'overdue', 'invoiced', 'partial'])
                                     ->sum(fn($p) => (float)($p->amount + ($p->late_fee ?? 0)) - (float)($p->paid_amount ?? 0)),
                'count'    => $group->count(),
                'payments' => $group,
            ])
            ->sortByDesc('paid');

        $byBeneficiary = $payments
            ->groupBy(fn($p) => $p->lease->unit->beneficiary->name ?? 'Sin beneficiario')
            ->map(fn($group) => [
                'paid'     => (float) $group->sum('paid_amount'),
                'pending'  => $group->whereIn('status', ['pending', 'overdue', 'invoiced', 'partial'])
                                     ->sum(fn($p) => (float)($p->amount + ($p->late_fee ?? 0)) - (float)($p->paid_amount ?? 0)),
                'count'    => $group->count(),
            ])
            ->sortByDesc('paid');

        // Gastos del período
        $expenses = Expense::with('property')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->get();

        $totalExpenses = $expenses->sum('amount');
        
        // Utilidad Neta dinámica según el modo (Incluyendo depósitos)
        if ($mode === 'accrual') {
            // En devengado la utilidad es lo esperado (pagado + pendiente + depósitos) - gastos
            $netIncome = (($totalPaid + $totalPending) + $totalDeposits) - $totalExpenses;
        } else {
            // En caja es lo cobrado (pagos + depósitos) - gastos
            $netIncome = ($totalPaid + $totalDeposits) - $totalExpenses;
        }

        // --- LÓGICA DE DASHBOARD SINCRONIZADA ---
        $filterDate = Carbon::create($year, $month, 1);
        
        // 1. Ocupación (Basada en contratos activos en ese mes)
        $totalUnits = Unit::count();
        $occupiedUnits = Lease::where(function($q) use ($filterDate) {
            $q->where('start_date', '<=', $filterDate->copy()->endOfMonth())
              ->where(function($sq) use ($filterDate) {
                  $sq->whereNull('end_date')
                    ->orWhere('end_date', '>=', $filterDate->copy()->startOfMonth());
              })
              ->where('status', 'active');
        })->distinct('unit_id')->count('unit_id');
        
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100) : 0;

        // 2. Cobranza (Ya calculada arriba como totalPaid y totalPending)
        $collectionRate = ($totalPaid + $totalPending) > 0 
            ? round(($totalPaid / ($totalPaid + $totalPending)) * 100) 
            : 0;

        // 3. Datos para Gráficos (6 meses atrás desde la fecha filtrada)
        $chartData = [
            'labels' => [],
            'rent_income' => [],
            'maintenance_income' => [],
            'expenses' => [],
            'occupancy' => [
                'occupied' => $occupiedUnits,
                'vacant' => $totalUnits - $occupiedUnits
            ],
            'collection' => [
                'paid'     => $payments->where('status', 'paid')->count(),
                'invoiced' => $payments->where('status', 'invoiced')->count(),
                'pending'  => $payments->where('status', 'pending')->count(),
                'overdue'  => $payments->where('status', 'overdue')->count(),
            ]
        ];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $filterDate->copy()->subMonthsNoOverflow($i);
            
            // Usamos clones de la query base o queries frescas para evitar acumulacion de 'where'
            $rentQuery = Payment::where('type', 'rent');
            $maintQuery = Payment::where('type', 'maintenance');

            if ($mode === 'cash') {
                $rentQuery->whereYear('paid_at', $monthDate->year)->whereMonth('paid_at', $monthDate->month)->where('status', 'paid');
                $maintQuery->whereYear('paid_at', $monthDate->year)->whereMonth('paid_at', $monthDate->month)->where('status', 'paid');
                
                $rentIncome = $rentQuery->sum('paid_amount');
                $maintenanceIncome = $maintQuery->sum('paid_amount');
            } else {
                $rentQuery->whereYear('due_date', $monthDate->year)->whereMonth('due_date', $monthDate->month);
                $maintQuery->whereYear('due_date', $monthDate->year)->whereMonth('due_date', $monthDate->month);
                
                $rentIncome = $rentQuery->get()->sum(fn($p) => (float)$p->amount + (float)($p->late_fee ?? 0));
                $maintenanceIncome = $maintQuery->get()->sum(fn($p) => (float)$p->amount + (float)($p->late_fee ?? 0));
            }

            $expense = Expense::whereYear('expense_date', $monthDate->year)
                ->whereMonth('expense_date', $monthDate->month)
                ->sum('amount');

            $chartData['labels'][] = ucfirst($monthDate->translatedFormat('M Y'));
            $chartData['rent_income'][] = (float) $rentIncome;
            $chartData['maintenance_income'][] = (float) $maintenanceIncome;
            $chartData['expenses'][] = (float) $expense;
        }

        $stats = [
            'total_units'      => $totalUnits,
            'occupied_units'   => $occupiedUnits,
            'occupancy_rate'   => $occupancyRate,
            'collection_rate'  => $collectionRate,
            'total_paid'       => $totalPaid,
            'total_pending'    => $totalPending,
            'net_income'       => $netIncome,
            'total_expenses'   => $totalExpenses,
            'invoiced_month'   => $totalInvoicedMonth,
            'cashed_month'     => $totalCashedMonth,
        ];
        // --- FIN LÓGICA DASHBOARD ---

        $expensesByCategory = $expenses
            ->groupBy('category')
            ->map(fn($g) => $g->sum('amount'))
            ->sortByDesc(fn($v) => $v);

        $expensesByProperty = $expenses
            ->groupBy(fn($e) => $e->property->name ?? 'Sin propiedad')
            ->map(fn($g) => $g->sum('amount'))
            ->sortByDesc(fn($v) => $v);

        // Año mínimo con pagos
        $firstYear = Payment::whereNotNull('due_date')
            ->selectRaw('MIN(YEAR(due_date)) as min_year')
            ->value('min_year') ?? $now->year;

        return view('reports.income', compact(
            'month', 'year', 'mode', 'payments',
            'totalPaid', 'totalPending', 'totalDeposits', 'depositLeases',
            'byProperty', 'byBeneficiary', 'firstYear',
            'expenses', 'totalExpenses', 'expensesByCategory', 'expensesByProperty', 'netIncome',
            'stats', 'chartData'
        ));
    }

    public function exportIncome(Request $request)
    {
        $now   = Carbon::now();
        $month = (int) $request->get('month', $now->month);
        $year  = (int) $request->get('year',  $now->year);
        $mode  = $request->get('mode', 'accrual');

        $paymentsQuery = Payment::with(['lease.unit.property', 'lease.unit.beneficiary', 'lease.tenant'])
            ->whereNotNull('due_date');

        if ($mode === 'cash') {
            $paymentsQuery->where(function($q) use ($year, $month) {
                $q->whereYear('paid_at', $year)->whereMonth('paid_at', $month)
                  ->orWhere(function($sq) use ($year, $month) {
                      $sq->whereYear('due_date', $year)->whereMonth('due_date', $month)
                         ->whereIn('status', ['pending', 'overdue', 'invoiced', 'partial']);
                  });
            });
        } else {
            $paymentsQuery->whereYear('due_date', $year)
                          ->whereMonth('due_date', $month);
        }

        $payments = $paymentsQuery->get()->sortBy(fn($p) => $p->lease->unit->property->name ?? '');

        $fileName = sprintf('reporte_ingresos_%s_%s_%s.csv', $year, str_pad($month, 2, '0', STR_PAD_LEFT), $mode);

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Propiedad', 'Unidad', 'Inquilino', 'Tipo', 'Periodo',
            'F. Vencimiento', 'F. Factura', 'Folio Factura', 'F. Pago',
            'Monto Cobrar', 'Recargos', 'Total a Cobrar', 'Monto Pagado', 'Estatus'
        ];

        $callback = function() use($payments, $columns) {
            $file = fopen('php://output', 'w');
            // Agregar BOM para que Excel en Windows lea correctamente los acentos (UTF-8)
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            fputcsv($file, $columns);

            foreach ($payments as $payment) {
                $statusLabels = [
                    'pending'  => 'Por Facturar',
                    'invoiced' => 'Facturado',
                    'paid'     => 'Pagado',
                    'partial'  => 'Parcial',
                    'overdue'  => 'Vencido'
                ];

                $amount = (float) $payment->amount;
                $lateFee = (float) ($payment->late_fee ?? 0);

                fputcsv($file, [
                    $payment->lease->unit->property->name ?? '-',
                    $payment->lease->unit->code ?? '-',
                    $payment->lease->tenant->full_name ?? '-',
                    $payment->type === 'maintenance' ? 'Mantenimiento' : 'Renta',
                    $payment->period_label,
                    $payment->due_date ? $payment->due_date->format('Y-m-d') : '-',
                    $payment->invoiced_at ? $payment->invoiced_at->format('Y-m-d') : '-',
                    $payment->invoice_folio ?? '-',
                    $payment->paid_at ? $payment->paid_at->format('Y-m-d') : '-',
                    $amount,
                    $lateFee,
                    $amount + $lateFee,
                    (float) ($payment->paid_amount ?? 0),
                    $statusLabels[$payment->status] ?? $payment->status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function matrix(Request $request)
    {
        $now    = Carbon::now();
        $year   = (int) $request->get('year', $now->year);
        $month  = (int) $request->get('month', $now->month);
        $mode   = $request->get('mode', 'monthly'); // annual or monthly
        $propertyId = $request->get('property_id');
        $dateFilter = $request->get('date_filter', 'period'); // period or paid_at

        // Obtener todas las unidades (filtradas por propiedad si se indica)
        $unitsQuery = \App\Models\Unit::with(['property', 'leases' => function($q) {
            $q->orderByRaw("FIELD(status, 'active', 'finished', 'cancelled') ASC, start_date DESC");
        }, 'leases.tenant']);

        if ($propertyId) {
            $unitsQuery->where('property_id', $propertyId);
        }

        $units = $unitsQuery->orderBy('code')->get();

        // Obtener todos los pagos del año para esas unidades
        $paymentsQuery = Payment::whereHas('lease.unit', function($q) use ($propertyId) {
            if ($propertyId) $q->where('property_id', $propertyId);
        });

        if ($dateFilter === 'paid_at') {
            $paymentsQuery->whereYear('paid_at', $year)->whereNotNull('paid_at');
        } else {
            $paymentsQuery->whereYear('due_date', $year);
        }

        $payments = $paymentsQuery->get();

        // Agrupar pagos por mes (fila) y por código de unidad (columna)
        // Estructura: $matrix[mes][unidad_code][tipo] = payment_object
        $matrix = [];
        $periods = []; // Lista de meses que aparecen en el reporte

        // Inicializar periodos (12 meses del año)
        for ($m = 1; $m <= 12; $m++) {
            $periodKey = ucfirst(Carbon::create($year, $m, 1)->locale('es')->isoFormat('MMMM YYYY'));
            $periods[] = $periodKey;
            $matrix[$periodKey] = [];
            foreach ($units as $unit) {
                $matrix[$periodKey][$unit->code] = [
                    'rent' => null,
                    'maintenance' => null,
                ];
            }
        }

        // Llenar la matriz con los pagos reales
        foreach ($payments as $payment) {
            if ($dateFilter === 'paid_at') {
                $paymentDate = $payment->paid_at;
            } else {
                // Usar el mes de period_start si existe, si no el mes de due_date
                $paymentDate = $payment->period_start ?? $payment->due_date;
            }
            
            if (! $paymentDate) continue;

            $paymentMonth = (int) $paymentDate->month;
            $periodKey = ucfirst(Carbon::create($year, $paymentMonth, 1)->locale('es')->isoFormat('MMMM YYYY'));

            $unitCode = $payment->lease->unit->code ?? null;
            $type     = $payment->type ?? 'rent';

            if ($unitCode && isset($matrix[$periodKey][$unitCode])) {
                $matrix[$periodKey][$unitCode][$type] = $payment;
            }
        }

        $properties = Property::orderBy('name')->get();

        $isExport = $request->boolean('export', false);

        if ($isExport) {
            $fileName = sprintf('Matriz_Pagos_%s.xls', $year);
            return response(view('reports.matrix', compact('year', 'month', 'mode', 'propertyId', 'dateFilter', 'units', 'periods', 'matrix', 'properties', 'isExport')))
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        }

        return view('reports.matrix', compact('year', 'month', 'mode', 'propertyId', 'dateFilter', 'units', 'periods', 'matrix', 'properties', 'isExport'));
    }
}
