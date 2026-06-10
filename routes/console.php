<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('leases:notify')->dailyAt('08:00');

// Resumen mensual: se ejecuta el día 1 de cada mes a las 8:00am
// Reporta las estadísticas del mes anterior (cobros, vencidos, ocupación, finanzas)
// Para probar manualmente: php artisan reports:monthly --dry-run
Schedule::command('reports:monthly')->monthlyOn(1, '08:00');
