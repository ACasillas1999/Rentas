<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $today         = Carbon::today();
        $nextSevenDays = $today->copy()->addDays(7);
        $next30Days    = $today->copy()->addDays(30);

        $totalUnits = Unit::count();
        $occupiedUnits = Unit::where('status', 'rented')->count();
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100) : 0;

        $stats = [
            'properties'        => Property::count(),
            'units'             => $totalUnits,
            'occupied_units'    => $occupiedUnits,
            'occupancy_rate'    => $occupancyRate,
            'active_leases'     => Lease::where('status', 'active')->count(),
            'pending_or_overdue'=> Payment::whereIn('status', ['pending', 'overdue'])->count(),
        ];

        $overduePayments = Payment::with(['lease.unit.property', 'lease.tenant'])
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $upcomingPayments = Payment::with(['lease.unit.property', 'lease.tenant'])
            ->where('status', '!=', 'paid')
            ->whereBetween('due_date', [$today, $nextSevenDays])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Contratos que vencen en los próximos 30 días
        $expiringLeases = Lease::with(['unit.property', 'tenant'])
            ->where('status', 'active')
            ->whereBetween('end_date', [$today, $next30Days])
            ->orderBy('end_date')
            ->get();

        return view('dashboard', compact('stats', 'overduePayments', 'upcomingPayments', 'expiringLeases'));
    }

    public function calendarEvents(Request $request)
    {
        $start = $request->query('start');
        $end   = $request->query('end');

        $query = Payment::with(['lease.tenant', 'lease.unit.property']);

        if ($start) {
            $query->whereDate('due_date', '>=', $start);
        }
        if ($end) {
            $query->whereDate('due_date', '<=', $end);
        }

        $colorMap = [
            'paid'     => ['bg' => '#1a7f3c', 'border' => '#15692f'],  // verde
            'invoiced' => ['bg' => '#1e40af', 'border' => '#1e3a8a'],  // azul
            'pending'  => ['bg' => '#c47a0a', 'border' => '#a36509'],  // naranja
            'overdue'  => ['bg' => '#b82020', 'border' => '#971a1a'],  // rojo
            'partial'  => ['bg' => '#7c3aed', 'border' => '#6d28d9'],  // violeta
        ];

        $events = $query->get()->map(function (Payment $payment) use ($colorMap) {
            $tenant   = $payment->lease->tenant->full_name   ?? 'Sin inquilino';
            $property = $payment->lease->unit->property->name ?? '';
            $unit     = $payment->lease->unit->code           ?? '';
            $status   = $payment->status;
            $colors   = $colorMap[$status] ?? ['bg' => '#5b7fa6', 'border' => '#4a6a8e'];

            $label = $payment->period_label
                ? "{$tenant} — {$payment->period_label}"
                : "{$tenant} — " . ($payment->due_date ? $payment->due_date->format('M Y') : '');

            return [
                'id'              => $payment->id,
                'title'           => $label,
                'start'           => $payment->due_date?->toDateString(),
                'url'             => route('payments.show', $payment),
                'backgroundColor' => $colors['bg'],
                'borderColor'     => $colors['border'],
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'status'    => $status,
                    'amount'    => '$' . number_format((float) $payment->amount, 2),
                    'rawAmount' => number_format((float) $payment->amount, 2, '.', ''),
                    'lateFee'   => number_format((float) $payment->late_fee, 2, '.', ''),
                    'property'  => "{$property} / {$unit}",
                ],
            ];
        });

        return response()->json($events);
    }
}
