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

        $now   = Carbon::now();
        $month = (int) $request->get('month', $now->copy()->subMonthNoOverflow()->month);
        $year  = (int) $request->get('year',  $now->copy()->subMonthNoOverflow()->year);

        // Calcular stats para la vista previa
        $stats = $this->calcularStats($month, $year);

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
        $firstYear = Payment::whereNotNull('due_date')
            ->selectRaw('MIN(YEAR(due_date)) as min_year')
            ->value('min_year') ?? $now->year;

        return view('reports.monthly_config', compact(
            'stats', 'periodLabel', 'month', 'year', 'config', 'firstYear'
        ));
    }

    /**
     * Guardar configuración de correo / WhatsApp en .env
     */
    public function saveConfig(Request $request)
    {
        $this->authorizePermission('reports.view');

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
        $filterDate = Carbon::create($year, $month, 1);

        $payments = Payment::with(['lease.unit.property', 'lease.tenant'])
            ->whereNotNull('due_date')
            ->whereYear('due_date', $year)
            ->whereMonth('due_date', $month)
            ->get();

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

        $gastos      = Expense::whereYear('expense_date', $year)->whereMonth('expense_date', $month)->get();
        $totalGastos = (float) $gastos->sum('amount');
        $utilidadNeta = $totalCobrado - $totalGastos;

        $totalUnidades  = Unit::count();
        $unidadesOcupadas = Lease::where(function ($q) use ($filterDate) {
            $q->where('start_date', '<=', $filterDate->copy()->endOfMonth())
              ->where(fn($sq) => $sq->whereNull('end_date')
                  ->orWhere('end_date', '>=', $filterDate->copy()->startOfMonth()))
              ->where('status', 'active');
        })->distinct('unit_id')->count('unit_id');

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
