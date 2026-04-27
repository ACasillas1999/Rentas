<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicatePayments extends Command
{
    protected $signature = 'payments:clean-duplicates {--dry-run : Solo mostrar duplicados sin eliminar}';
    protected $description = 'Identifica y elimina pagos duplicados (mismo lease_id, type, period_label, due_date)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info($dryRun ? '🔍 Modo simulación (dry-run) — No se eliminará nada.' : '🧹 Limpiando pagos duplicados...');
        $this->newLine();

        // Encontrar grupos de duplicados
        $duplicateGroups = DB::table('payments')
            ->select('lease_id', 'type', 'period_label', 'due_date', DB::raw('COUNT(*) as total'))
            ->groupBy('lease_id', 'type', 'period_label', 'due_date')
            ->having('total', '>', 1)
            ->get();

        if ($duplicateGroups->isEmpty()) {
            $this->info('✅ No se encontraron pagos duplicados.');
            return self::SUCCESS;
        }

        $this->warn("Se encontraron {$duplicateGroups->count()} grupo(s) de pagos duplicados:");
        $this->newLine();

        $totalRemoved = 0;

        foreach ($duplicateGroups as $group) {
            // Obtener todos los pagos del grupo, con sus datos importantes
            $payments = DB::table('payments')
                ->where('lease_id', $group->lease_id)
                ->where('type', $group->type)
                ->where('period_label', $group->period_label)
                ->where('due_date', $group->due_date)
                ->orderByRaw("FIELD(status, 'paid', 'partial', 'overdue', 'pending') ASC") // Priorizar 'paid'
                ->orderByDesc('paid_amount') // Luego el que tenga mayor monto pagado
                ->orderByDesc('id') // Luego el más reciente
                ->get();

            // El primero es el que mantenemos (el mejor candidato)
            $keeper = $payments->first();
            $toRemove = $payments->slice(1);

            // Obtener nombre del inquilino para el log
            $leaseInfo = DB::table('leases')
                ->join('tenants', 'leases.tenant_id', '=', 'tenants.id')
                ->join('units', 'leases.unit_id', '=', 'units.id')
                ->where('leases.id', $group->lease_id)
                ->select('tenants.full_name', 'units.code')
                ->first();

            $tenantName = $leaseInfo->full_name ?? 'Desconocido';
            $unitCode = $leaseInfo->code ?? '?';

            $this->line("  📋 <comment>{$tenantName}</comment> — Unidad <comment>{$unitCode}</comment>");
            $this->line("     Tipo: {$group->type} | Período: {$group->period_label} | Vence: {$group->due_date}");
            $this->line("     Total encontrados: {$group->total} | Se mantendrá ID #{$keeper->id} (status: {$keeper->status})");
            $this->line("     Se eliminarán: " . $toRemove->pluck('id')->implode(', '));
            $this->newLine();

            if (!$dryRun) {
                $removedIds = $toRemove->pluck('id')->toArray();
                DB::table('payments')->whereIn('id', $removedIds)->delete();
                $totalRemoved += count($removedIds);
            } else {
                $totalRemoved += $toRemove->count();
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->warn("🔎 Se eliminarían {$totalRemoved} pago(s) duplicado(s). Ejecuta sin --dry-run para aplicar.");
        } else {
            $this->info("✅ Se eliminaron {$totalRemoved} pago(s) duplicado(s) exitosamente.");
        }

        return self::SUCCESS;
    }
}
