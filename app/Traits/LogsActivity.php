<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    /**
     * Registra una entrada en la bitácora de actividad.
     *
     * @param string   $action      Acción: created, updated, deleted, paid, invoiced, viewed, exported, login, bulk
     * @param string   $module      Módulo: lease, payment, tenant, property, unit, expense, user, report, auth
     * @param int|null $modelId     ID del registro afectado (nullable para acciones globales)
     * @param string   $description Mensaje legible: "Creó contrato #45 para Juan Pérez"
     */
    protected function logActivity(string $action, string $module, ?int $modelId, string $description): void
    {
        try {
            $user = auth()->user();

            ActivityLog::create([
                'user_id'     => $user?->id,
                'user_name'   => $user?->name ?? 'Sistema',
                'user_role'   => $user?->role ?? 'system',
                'action'      => $action,
                'module'      => $module,
                'model_id'    => $modelId,
                'description' => $description,
                'ip_address'  => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            // Nunca interrumpir el flujo principal por falla del log
            \Illuminate\Support\Facades\Log::error('ActivityLog failed: ' . $e->getMessage());
        }
    }
}
