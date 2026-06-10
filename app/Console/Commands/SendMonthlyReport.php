<?php

namespace App\Console\Commands;

use App\Mail\MonthlyReportMail;
use App\Models\Expense;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMonthlyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan reports:monthly
     * php artisan reports:monthly --month=5 --year=2026   (para probar un mes específico)
     */
    protected $signature = 'reports:monthly
                            {--month= : Mes a reportar (default: mes anterior)}
                            {--year=  : Año a reportar (default: año actual/anterior)}
                            {--dry-run : Muestra el resumen en consola sin enviar nada}';

    protected $description = 'Envía el resumen mensual de cobranza por correo y WhatsApp.';

    public function handle(): int
    {
        // ── Determinar el período a reportar ──────────────────────────────
        $now = Carbon::now();

        if ($this->option('month') && $this->option('year')) {
            $month = (int) $this->option('month');
            $year  = (int) $this->option('year');
        } else {
            // Por defecto: el mes anterior
            $reportDate = $now->copy()->subMonthNoOverflow();
            $month = $reportDate->month;
            $year  = $reportDate->year;
        }

        $periodLabel = ucfirst(Carbon::create($year, $month, 1)
            ->locale('es')
            ->isoFormat('MMMM YYYY'));

        $this->info("📊 Generando resumen mensual: {$periodLabel}...");

        // ── Calcular estadísticas ─────────────────────────────────────────
        $stats = $this->calcularEstadisticas($month, $year);

        if ($this->option('dry-run')) {
            $this->mostrarEnConsola($stats, $periodLabel);
            return Command::SUCCESS;
        }

        // ── Enviar correo ─────────────────────────────────────────────────
        $this->enviarCorreo($stats, $periodLabel, $month, $year);

        // ── Enviar WhatsApp ───────────────────────────────────────────────
        $this->enviarWhatsApp($stats, $periodLabel);

        $this->info("✅ Resumen mensual enviado correctamente.");

        return Command::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Cálculo de estadísticas (reutiliza la misma lógica del ReportController)
    // ─────────────────────────────────────────────────────────────────────
    private function calcularEstadisticas(int $month, int $year): array
    {
        $filterDate = Carbon::create($year, $month, 1);

        // ── Pagos del mes (por fecha de vencimiento - modo devengado) ──
        $payments = Payment::with(['lease.unit.property', 'lease.tenant'])
            ->whereNotNull('due_date')
            ->whereYear('due_date', $year)
            ->whereMonth('due_date', $month)
            ->get();

        // Conteos por estado
        $pagadosTotal      = $payments->where('status', 'paid')->count();
        $facturadosCount   = $payments->where('status', 'invoiced')->count();
        $pendientesCount   = $payments->where('status', 'pending')->count();
        $vencidosCount     = $payments->where('status', 'overdue')->count();
        $parcialesCount    = $payments->where('status', 'partial')->count();

        // Pagados a tiempo vs con retraso
        $pagadosATiempo = $payments->filter(function ($p) {
            return $p->status === 'paid'
                && $p->paid_at !== null
                && $p->due_date !== null
                && Carbon::parse($p->paid_at)->lte(Carbon::parse($p->due_date));
        })->count();

        $pagadosConRetraso = $payments->filter(function ($p) {
            return $p->status === 'paid'
                && $p->paid_at !== null
                && $p->due_date !== null
                && Carbon::parse($p->paid_at)->gt(Carbon::parse($p->due_date));
        })->count();

        // Montos
        $totalCobrado  = (float) $payments->sum('paid_amount');
        $totalPendiente = $payments
            ->whereIn('status', ['pending', 'overdue', 'invoiced', 'partial'])
            ->sum(fn($p) => (float)($p->amount + ($p->late_fee ?? 0)) - (float)($p->paid_amount ?? 0));

        $totalEsperado = $payments->sum(fn($p) => (float)$p->amount + (float)($p->late_fee ?? 0));

        $tasaCobranza = ($totalCobrado + $totalPendiente) > 0
            ? round(($totalCobrado / ($totalCobrado + $totalPendiente)) * 100, 1)
            : 0;

        // Gastos
        $gastos     = Expense::whereYear('expense_date', $year)->whereMonth('expense_date', $month)->get();
        $totalGastos = (float) $gastos->sum('amount');

        // Utilidad neta
        $utilidadNeta = $totalCobrado - $totalGastos;

        // Ocupación
        $totalUnidades = Unit::count();
        $unidadesOcupadas = Lease::where(function ($q) use ($filterDate) {
            $q->where('start_date', '<=', $filterDate->copy()->endOfMonth())
              ->where(function ($sq) use ($filterDate) {
                  $sq->whereNull('end_date')
                     ->orWhere('end_date', '>=', $filterDate->copy()->startOfMonth());
              })
              ->where('status', 'active');
        })->distinct('unit_id')->count('unit_id');

        $tasaOcupacion = $totalUnidades > 0
            ? round(($unidadesOcupadas / $totalUnidades) * 100)
            : 0;

        // Desglose por propiedad
        $porPropiedad = $payments
            ->groupBy(fn($p) => $p->lease->unit->property->name ?? 'Sin propiedad')
            ->map(fn($group) => [
                'cobrado'   => (float) $group->sum('paid_amount'),
                'pendiente' => $group->whereIn('status', ['pending', 'overdue', 'invoiced', 'partial'])
                                     ->sum(fn($p) => (float)($p->amount + ($p->late_fee ?? 0)) - (float)($p->paid_amount ?? 0)),
                'pagados'   => $group->where('status', 'paid')->count(),
                'total'     => $group->count(),
            ])
            ->sortByDesc('cobrado');

        // Inquilinos con pagos vencidos (para la lista de alertas)
        $inquilinosVencidos = $payments
            ->where('status', 'overdue')
            ->map(fn($p) => [
                'nombre'    => $p->lease->tenant->full_name ?? 'N/A',
                'propiedad' => $p->lease->unit->property->name ?? 'N/A',
                'unidad'    => $p->lease->unit->code ?? 'N/A',
                'monto'     => (float)($p->amount + ($p->late_fee ?? 0)),
            ]);

        return compact(
            'pagadosTotal', 'pagadosATiempo', 'pagadosConRetraso',
            'facturadosCount', 'pendientesCount', 'vencidosCount', 'parcialesCount',
            'totalCobrado', 'totalPendiente', 'totalEsperado', 'tasaCobranza',
            'totalGastos', 'utilidadNeta',
            'totalUnidades', 'unidadesOcupadas', 'tasaOcupacion',
            'porPropiedad', 'inquilinosVencidos'
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Envío de correo
    // ─────────────────────────────────────────────────────────────────────
    private function enviarCorreo(array $stats, string $periodLabel, int $month, int $year): void
    {
        $destinatarios = array_map('trim', explode(',', env('REPORT_EMAIL', '')));
        $destinatarios = array_filter($destinatarios);

        if (empty($destinatarios)) {
            $this->warn("⚠️  No hay correo configurado en REPORT_EMAIL. Saltando correo.");
            return;
        }

        try {
            foreach ($destinatarios as $email) {
                Mail::to($email)->send(new MonthlyReportMail($stats, $periodLabel, $month, $year));
                $this->info("  📧 Correo enviado a: {$email}");
            }
        } catch (\Throwable $e) {
            $this->error("  ❌ Error enviando correo: " . $e->getMessage());
            Log::error("[MonthlyReport] Error correo: " . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Envío de WhatsApp via WABA (Meta Cloud API)
    // ─────────────────────────────────────────────────────────────────────
    private function enviarWhatsApp(array $stats, string $periodLabel): void
    {
        $numeros = array_map('trim', explode(',', env('REPORT_WHATSAPP', '')));
        $numeros = array_filter($numeros, fn($n) => strlen($n) > 8);

        if (empty($numeros)) {
            $this->warn("⚠️  No hay número configurado en REPORT_WHATSAPP. Saltando WhatsApp.");
            return;
        }

        $wabaToken   = env('WABA_TOKEN');
        $phoneId     = env('WABA_PHONE_NUMBER_ID');
        $wabaVersion = env('WABA_VERSION', 'v20.0');

        if (!$wabaToken || !$phoneId) {
            $this->warn("⚠️  Faltan WABA_TOKEN o WABA_PHONE_NUMBER_ID en .env. Saltando WhatsApp.");
            return;
        }

        // Formatear el mensaje de texto del resumen
        $emoji_tasa = $stats['tasaCobranza'] >= 80 ? '🟢' : ($stats['tasaCobranza'] >= 50 ? '🟡' : '🔴');
        $fmt = fn($n) => '$' . number_format($n, 2);

        $mensaje = implode("\n", [
            "📊 *Resumen Mensual — {$periodLabel}*",
            "━━━━━━━━━━━━━━━━━━━━",
            "",
            "💰 *Cobranza*",
            "  ✅ Pagados: {$stats['pagadosTotal']} ({$stats['pagadosATiempo']} a tiempo, {$stats['pagadosConRetraso']} con retraso)",
            "  ⏳ Pendientes: {$stats['pendientesCount']}",
            "  ❌ Vencidos: {$stats['vencidosCount']}",
            "  📑 Facturados: {$stats['facturadosCount']}",
            "  🔸 Parciales: {$stats['parcialesCount']}",
            "",
            "  💵 Cobrado: {$fmt($stats['totalCobrado'])}",
            "  📋 Pendiente: {$fmt($stats['totalPendiente'])}",
            "  {$emoji_tasa} Tasa cobranza: {$stats['tasaCobranza']}%",
            "",
            "🏠 *Ocupación*",
            "  {$stats['unidadesOcupadas']} / {$stats['totalUnidades']} unidades ({$stats['tasaOcupacion']}%)",
            "",
            "📉 *Finanzas*",
            "  Gastos: {$fmt($stats['totalGastos'])}",
            "  Utilidad neta: {$fmt($stats['utilidadNeta'])}",
            "",
            "🔗 Ver reporte completo:",
            env('APP_URL', '') . "/reports/income",
        ]);

        foreach ($numeros as $numero) {
            try {
                $url = "https://graph.facebook.com/{$wabaVersion}/{$phoneId}/messages";

                $body = [
                    'messaging_product' => 'whatsapp',
                    'to'                => $numero,
                    'type'              => 'text',
                    'text'              => ['body' => $mensaje, 'preview_url' => false],
                ];

                $response = Http::withToken($wabaToken)
                    ->timeout(15)
                    ->post($url, $body);

                if ($response->successful()) {
                    $this->info("  💬 WhatsApp enviado a: +{$numero}");
                } else {
                    $errMsg = $response->json('error.message', $response->body());
                    $this->warn("  ⚠️  WhatsApp a +{$numero} falló: {$errMsg}");
                    Log::warning("[MonthlyReport] WhatsApp falló para {$numero}: {$errMsg}");
                }
            } catch (\Throwable $e) {
                $this->error("  ❌ Error WhatsApp +{$numero}: " . $e->getMessage());
                Log::error("[MonthlyReport] Error WhatsApp {$numero}: " . $e->getMessage());
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Dry-run: mostrar en consola
    // ─────────────────────────────────────────────────────────────────────
    private function mostrarEnConsola(array $stats, string $periodLabel): void
    {
        $fmt = fn($n) => '$' . number_format($n, 2);

        $this->line('');
        $this->line("  ══════════════════════════════════════");
        $this->info("  📊 RESUMEN MENSUAL: {$periodLabel}");
        $this->line("  ══════════════════════════════════════");
        $this->line("  COBRANZA:");
        $this->line("    Pagados:     {$stats['pagadosTotal']} ({$stats['pagadosATiempo']} a tiempo / {$stats['pagadosConRetraso']} con retraso)");
        $this->line("    Pendientes:  {$stats['pendientesCount']}");
        $this->line("    Vencidos:    {$stats['vencidosCount']}");
        $this->line("    Parciales:   {$stats['parcialesCount']}");
        $this->line("    Total cobrado:   {$fmt($stats['totalCobrado'])}");
        $this->line("    Total pendiente: {$fmt($stats['totalPendiente'])}");
        $this->line("    Tasa cobranza:   {$stats['tasaCobranza']}%");
        $this->line("  OCUPACIÓN:");
        $this->line("    {$stats['unidadesOcupadas']} / {$stats['totalUnidades']} unidades ({$stats['tasaOcupacion']}%)");
        $this->line("  FINANZAS:");
        $this->line("    Gastos:        {$fmt($stats['totalGastos'])}");
        $this->line("    Utilidad neta: {$fmt($stats['utilidadNeta'])}");
        $this->line("  ══════════════════════════════════════");
        $this->line('');
    }
}
