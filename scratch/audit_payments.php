<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;
use Carbon\Carbon;

$year = 2026;
$month = 4;

$payments = Payment::whereYear('due_date', $year)
    ->whereMonth('due_date', $month)
    ->get();

echo "Auditoría de Pagos - Abril 2026\n";
echo str_repeat("-", 80) . "\n";
echo sprintf("%-20s | %-15s | %-12s | %-12s | %-10s\n", "Inquilino", "Unidad", "Monto", "Pagado", "Estatus");
echo str_repeat("-", 80) . "\n";

$calculatedTotalPaid = 0;

foreach ($payments as $p) {
    $tenant = $p->lease->tenant->full_name ?? 'N/A';
    $unit = $p->lease->unit->code ?? 'N/A';
    $amount = (float)$p->amount;
    $paid = (float)$p->paid_amount;
    $status = $p->status;

    if ($status === 'paid') {
        $calculatedTotalPaid += $paid;
    }

    echo sprintf("%-20s | %-15s | %12.2f | %12.2f | %-10s\n", 
        substr($tenant, 0, 20), 
        $unit, 
        $amount, 
        $paid, 
        $status
    );
}

echo str_repeat("-", 80) . "\n";
echo "Total Pagado Sumando (paid_amount): " . number_format($calculatedTotalPaid, 2) . "\n";
echo "Total Monto de los Pagados (amount):  " . number_format($payments->where('status', 'paid')->sum('amount'), 2) . "\n";
