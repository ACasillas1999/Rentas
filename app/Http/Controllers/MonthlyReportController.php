<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Unit;
use App\Traits\FiltersByUserAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class MonthlyReportController extends Controller
{
    use FiltersByUserAccess;

    /**
     * Panel de configuración + historial + vista previa del reporte mensual.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('reports.view');

        if (auth()->user()->isViewer()) {
            abort(403, 'No tienes permiso para acceder al resumen mensual.');
        }

        $now   = Carbon::now();
        $month = (int) $request->get('month', $now->copy()->subMonthNoOverflow()->month);
        $year  = (int) $request->get('year',  $now->copy()->subMonthNoOverflow()->year);

        // Calcular stats y matriz para la vista previa
        $stats = $this->calcularStats($month, $year);
        $matrixData = self::obtenerMatrizMensual($month, $year, auth()->user()->allowedPropertyIds());

        $periodLabel = ucfirst(Carbon::create($year, $month, 1)
            ->locale('es')->isoFormat('MMMM YYYY'));

        // Leer configuración actual del .env
        $config = [
            'report_email'    => env('REPORT_EMAIL', ''),
            'report_whatsapp' => env('REPORT_WHATSAPP', ''),
            'waba_token'      => env('WABA_TOKEN') ? '••••••••' . substr(env('WABA_TOKEN'), -6) : '',
            'waba_phone_id'   => env('WABA_PHONE_NUMBER_ID', ''),
        ];

        // Años disponibles
        $minDueDate = Payment::whereNotNull('due_date')->min('due_date');
        $firstYear  = $minDueDate ? (int) Carbon::parse($minDueDate)->year : $now->year;

        return view('reports.monthly_config', compact(
            'stats', 'matrixData', 'periodLabel', 'month', 'year', 'config', 'firstYear'
        ));
    }

    /**
     * Construye la matriz contable mensual (Reporte Pormenorizado del Mes)
     * idéntica a la vista reports.matrix con mode='monthly'.
     */
    public static function obtenerMatrizMensual(int $month, int $year, ?array $propertyIds = null): array
    {
        $unitsQuery = Unit::with([
            'property',
            'leases' => function ($q) {
                $q->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'finished' THEN 2 WHEN 'cancelled' THEN 3 ELSE 4 END ASC, start_date DESC");
            },
            'leases.tenant'
        ]);

        if ($propertyIds !== null) {
            $unitsQuery->whereIn('property_id', $propertyIds);
        }

        $units = $unitsQuery->orderBy('code')->get();

        $paymentsQuery = Payment::with(['lease.unit', 'lease.tenant'])
            ->whereHas('lease.unit', function ($q) use ($propertyIds) {
                if ($propertyIds !== null) {
                    $q->whereIn('property_id', $propertyIds);
                }
            })
            ->where(function ($q) use ($year) {
                $q->whereYear('due_date', $year)
                  ->orWhere(function ($sq) use ($year) {
                      $sq->whereNotNull('period_start')->whereYear('period_start', $year);
                  });
            });

        $payments = $paymentsQuery->get();

        $selectedPeriod = ucfirst(Carbon::create($year, $month, 1)->locale('es')->isoFormat('MMMM YYYY'));

        $matrix = [];
        foreach ($units as $unit) {
            $matrix[$unit->code] = [
                'rent'        => null,
                'maintenance' => null,
            ];
        }

        foreach ($payments as $payment) {
            $paymentDate = $payment->period_start ?? $payment->due_date;
            if (!$paymentDate) continue;

            $pMonth = (int) $paymentDate->month;
            $pYear  = (int) $paymentDate->year;
            if ($pYear !== $year || $pMonth !== $month) continue;

            $unitCode = $payment->lease->unit->code ?? null;
            $type     = $payment->type ?? 'rent';

            if ($unitCode && array_key_exists($unitCode, $matrix)) {
                $matrix[$unitCode][$type] = $payment;
            }
        }

        $statusLabels = [
            'pending'  => 'Por Facturar',
            'overdue'  => 'Vencido',
            'invoiced' => 'Facturado',
            'paid'     => 'Pagado',
            'partial'  => 'Parcial',
        ];

        $rows = [];
        $gTotalRentSubtotal  = 0;
        $gTotalRentTax       = 0;
        $gTotalRentAmount    = 0;
        $gTotalMaintSubtotal = 0;
        $gTotalMaintTax      = 0;
        $gTotalMaintAmount   = 0;

        foreach ($units as $idx => $unit) {
            $lease = $unit->leases->first();
            $rentP = $matrix[$unit->code]['rent'] ?? null;
            $mainP = $matrix[$unit->code]['maintenance'] ?? null;

            // Renta
            $rSub = $rentP ? (float) $rentP->subtotal : 0.0;
            $rTax = $rentP ? (float) $rentP->tax_amount : 0.0;
            $rAmt = $rentP ? (float) $rentP->amount : 0.0;

            // Mantenimiento
            $mSub = $mainP ? (float) $mainP->subtotal : 0.0;
            $mTax = $mainP ? (float) $mainP->tax_amount : 0.0;
            $mAmt = $mainP ? (float) $mainP->amount : 0.0;

            $gTotalRentSubtotal  += $rSub;
            $gTotalRentTax       += $rTax;
            $gTotalRentAmount    += $rAmt;
            $gTotalMaintSubtotal += $mSub;
            $gTotalMaintTax      += $mTax;
            $gTotalMaintAmount   += $mAmt;

            $vigencia = '-';
            if ($lease && $lease->start_date && $lease->end_date) {
                $vigencia = $lease->start_date->format('d/m/y') . ' - ' . $lease->end_date->format('d/m/y');
            }

            $rows[] = [
                'no'           => $idx + 1,
                'unit_code'    => $unit->code,
                'tenant_name'  => $lease->tenant->full_name ?? 'VACANTE',
                'vigencia'     => $vigencia,
                'rent'         => [
                    'payment'       => $rentP,
                    'status'        => $rentP->status ?? null,
                    'status_label'  => $rentP ? ($statusLabels[$rentP->status] ?? $rentP->status) : '-',
                    'subtotal'      => $rSub,
                    'tax'           => $rTax,
                    'total'         => $rAmt,
                    'invoice_folio' => $rentP->invoice_folio ?? null,
                    'period_label'  => $rentP->period_label ?? null,
                ],
                'maintenance'  => [
                    'payment'       => $mainP,
                    'status'        => $mainP->status ?? null,
                    'status_label'  => $mainP ? ($statusLabels[$mainP->status] ?? $mainP->status) : '-',
                    'subtotal'      => $mSub,
                    'tax'           => $mTax,
                    'total'         => $mAmt,
                    'invoice_folio' => $mainP->invoice_folio ?? null,
                    'period_label'  => $mainP->period_label ?? null,
                ],
                'grand_total'  => $rAmt + $mAmt,
            ];
        }

        return [
            'rows'           => $rows,
            'selectedPeriod' => $selectedPeriod,
            'totals'         => [
                'rent_subtotal'        => $gTotalRentSubtotal,
                'rent_tax'             => $gTotalRentTax,
                'rent_total'           => $gTotalRentAmount,
                'maintenance_subtotal' => $gTotalMaintSubtotal,
                'maintenance_tax'      => $gTotalMaintTax,
                'maintenance_total'    => $gTotalMaintAmount,
                'grand_total'          => $gTotalRentAmount + $gTotalMaintAmount,
            ],
        ];
    }

    /**
     * Guardar configuración de correo / WhatsApp en .env
     */
    public function saveConfig(Request $request)
    {
        $this->authorizePermission('reports.view');

        if (auth()->user()->isViewer()) {
            abort(403, 'No tienes permiso para modificar la configuración.');
        }

        $email    = trim($request->input('report_email', ''));
        $whatsapp = trim($request->input('report_whatsapp', ''));

        // Actualizar .env en disco
        $this->updateEnv([
            'REPORT_EMAIL'    => $email,
            'REPORT_WHATSAPP' => $whatsapp,
        ]);

        return redirect()
            ->route('reports.monthly.index')
            ->with('success', '✅ Configuración guardada. Se aplicará en el próximo reporte mensual.');
    }

    /**
     * Enviar el reporte manualmente (dispara el Artisan command en background)
     */
    public function sendNow(Request $request)
    {
        $this->authorizePermission('reports.view');

        if (auth()->user()->isViewer()) {
            abort(403, 'No tienes permiso para enviar reportes.');
        }

        $month = (int) $request->input('month', Carbon::now()->subMonthNoOverflow()->month);
        $year  = (int) $request->input('year',  Carbon::now()->subMonthNoOverflow()->year);

        try {
            Artisan::call('reports:monthly', [
                '--month' => $month,
                '--year'  => $year,
            ]);

            $output = Artisan::output();

            return redirect()
                ->route('reports.monthly.index', compact('month', 'year'))
                ->with('success', "📤 Reporte enviado correctamente. Período: {$month}/{$year}")
                ->with('artisan_output', $output);
        } catch (\Throwable $e) {
            Log::error('[MonthlyReport] Error al enviar manualmente: ' . $e->getMessage());

            return redirect()
                ->route('reports.monthly.index', compact('month', 'year'))
                ->with('error', '❌ Error al enviar: ' . $e->getMessage());
        }
    }

    /**
     * Mismo cálculo de stats que el command (centralizado aquí para evitar duplicación)
     */
    public function calcularStats(int $month, int $year): array
    {
        $filterDate  = Carbon::create($year, $month, 1);
        $propertyIds = auth()->check() ? auth()->user()->allowedPropertyIds() : null;

        $paymentsQuery = Payment::with(['lease.unit.property', 'lease.tenant'])
            ->whereNotNull('due_date')
            ->whereYear('due_date', $year)
            ->whereMonth('due_date', $month);

        if ($propertyIds !== null) {
            $paymentsQuery->whereHas('lease.unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }

        $payments = $paymentsQuery->get();

        $pagadosTotal    = $payments->where('status', 'paid')->count();
        $facturadosCount = $payments->where('status', 'invoiced')->count();
        $pendientesCount = $payments->where('status', 'pending')->count();
        $vencidosCount   = $payments->where('status', 'overdue')->count();
        $parcialesCount  = $payments->where('status', 'partial')->count();

        $pagadosATiempo = $payments->filter(fn($p) =>
            $p->status === 'paid' && $p->paid_at && $p->due_date &&
            Carbon::parse($p->paid_at)->lte(Carbon::parse($p->due_date))
        )->count();

        $pagadosConRetraso = $payments->filter(fn($p) =>
            $p->status === 'paid' && $p->paid_at && $p->due_date &&
            Carbon::parse($p->paid_at)->gt(Carbon::parse($p->due_date))
        )->count();

        $totalCobrado   = (float) $payments->sum('paid_amount');
        $totalPendiente = $payments
            ->whereIn('status', ['pending', 'overdue', 'invoiced', 'partial'])
            ->sum(fn($p) => (float)($p->amount + ($p->late_fee ?? 0)) - (float)($p->paid_amount ?? 0));

        $tasaCobranza = ($totalCobrado + $totalPendiente) > 0
            ? round(($totalCobrado / ($totalCobrado + $totalPendiente)) * 100, 1)
            : 0;

        $gastosQuery = Expense::whereYear('expense_date', $year)->whereMonth('expense_date', $month);
        if ($propertyIds !== null) {
            $gastosQuery->whereIn('property_id', $propertyIds);
        }
        $gastos      = $gastosQuery->get();
        $totalGastos = (float) $gastos->sum('amount');
        $utilidadNeta = $totalCobrado - $totalGastos;

        $totalUnidadesQuery = Unit::query();
        if ($propertyIds !== null) {
            $totalUnidadesQuery->whereIn('property_id', $propertyIds);
        }
        $totalUnidades  = $totalUnidadesQuery->count();

        $unidadesOcupadasQuery = Lease::where(function ($q) use ($filterDate) {
            $q->where('start_date', '<=', $filterDate->copy()->endOfMonth())
              ->where(fn($sq) => $sq->whereNull('end_date')
                  ->orWhere('end_date', '>=', $filterDate->copy()->startOfMonth()))
              ->where('status', 'active');
        });
        if ($propertyIds !== null) {
            $unidadesOcupadasQuery->whereHas('unit', fn($q) => $q->whereIn('property_id', $propertyIds));
        }
        $unidadesOcupadas = $unidadesOcupadasQuery->distinct('unit_id')->count('unit_id');

        $tasaOcupacion = $totalUnidades > 0
            ? round(($unidadesOcupadas / $totalUnidades) * 100)
            : 0;

        $porPropiedad = $payments
            ->groupBy(fn($p) => $p->lease->unit->property->name ?? 'Sin propiedad')
            ->map(fn($g) => [
                'cobrado'   => (float) $g->sum('paid_amount'),
                'pendiente' => $g->whereIn('status', ['pending', 'overdue', 'invoiced', 'partial'])
                                  ->sum(fn($p) => (float)($p->amount + ($p->late_fee ?? 0)) - (float)($p->paid_amount ?? 0)),
                'pagados'   => $g->where('status', 'paid')->count(),
                'total'     => $g->count(),
            ])->sortByDesc('cobrado');

        $inquilinosVencidos = $payments->where('status', 'overdue')
            ->map(fn($p) => [
                'nombre'    => $p->lease->tenant->full_name ?? 'N/A',
                'propiedad' => $p->lease->unit->property->name ?? 'N/A',
                'unidad'    => $p->lease->unit->code ?? 'N/A',
                'monto'     => (float)($p->amount + ($p->late_fee ?? 0)),
            ]);

        return compact(
            'pagadosTotal', 'pagadosATiempo', 'pagadosConRetraso',
            'facturadosCount', 'pendientesCount', 'vencidosCount', 'parcialesCount',
            'totalCobrado', 'totalPendiente', 'tasaCobranza',
            'totalGastos', 'utilidadNeta',
            'totalUnidades', 'unidadesOcupadas', 'tasaOcupacion',
            'porPropiedad', 'inquilinosVencidos'
        );
    }

    /**
     * Actualizar variables en el archivo .env
     */
    private function updateEnv(array $data): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            // Escapar el valor si tiene espacios
            $escaped = str_contains($value, ' ') ? "\"{$value}\"" : $value;

            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$escaped}", $content);
            } else {
                $content .= "\n{$key}={$escaped}";
            }
        }

        file_put_contents($envPath, $content);
    }
}
