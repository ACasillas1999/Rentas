<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'module',
        'model_id',
        'description',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Colores por acción para la vista ──────────────────────────────────

    public static function actionBadge(string $action): array
    {
        return match($action) {
            'created'  => ['bg' => '#d1fae5', 'color' => '#065f46', 'label' => 'Creado'],
            'updated'  => ['bg' => '#dbeafe', 'color' => '#1e40af', 'label' => 'Editado'],
            'deleted'  => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'Eliminado'],
            'paid'     => ['bg' => '#d1fae5', 'color' => '#065f46', 'label' => 'Cobrado'],
            'invoiced' => ['bg' => '#ede9fe', 'color' => '#5b21b6', 'label' => 'Facturado'],
            'receipt'  => ['bg' => '#fef9c3', 'color' => '#854d0e', 'label' => 'Comprobante'],
            'renewed'  => ['bg' => '#cffafe', 'color' => '#0e7490', 'label' => 'Renovado'],
            'exported' => ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => 'Exportado'],
            'viewed'   => ['bg' => '#f3f4f6', 'color' => '#374151', 'label' => 'Consultado'],
            'login'    => ['bg' => '#f0fdf4', 'color' => '#14532d', 'label' => 'Acceso'],
            'bulk'     => ['bg' => '#e0f2fe', 'color' => '#0369a1', 'label' => 'Masivo'],
            default    => ['bg' => '#f3f4f6', 'color' => '#374151', 'label' => ucfirst($action)],
        };
    }

    public static function moduleIcon(string $module): string
    {
        return match($module) {
            'lease'    => '📄',
            'payment'  => '💳',
            'tenant'   => '👤',
            'property' => '🏢',
            'unit'     => '🔲',
            'expense'  => '💸',
            'user'     => '🔑',
            'report'   => '📊',
            'auth'     => '🔐',
            default    => '📌',
        };
    }
}
