<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Unit;
use App\Traits\FiltersByUserAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use FiltersByUserAccess;

    public function __invoke()
    {
        $today         = Carbon::today();
        $nextSevenDays = $today->copy()->addDays(7);
        $next30Days    = $today->copy()->addDays(30);

        $propertyIds = auth()->user()->allowedPropertyIds();

        // ── Stats filtradas por propiedad ──
        $unitsQuery = Unit::query();
        if ($propertyIds !== null) {
            $unitsQuery->whereIn('property_id', $propertyIds);
        }

        $totalUnits = (clone $unitsQuery)->count();
        $occupiedUnits = (clone $unitsQuery)->where('status', 'rented')->count();
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100) : 0;

        $propertiesQuery = Property::query();
        if ($propertyIds !== null) {
            $propertiesQuery->whereIn('id', $propertyIds);
        }

        $leasesQuery = Lease::where('status', 'active');
        if ($propertyIds !== null) {
            $leasesQuery->whereHas('unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }

        $pendingQuery = Payment::whereIn('status', ['pending', 'overdue']);
        if ($propertyIds !== null) {
            $pendingQuery->whereHas('lease.unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }

        $stats = [
            'properties'        => $propertiesQuery->count(),
            'units'             => $totalUnits,
            'occupied_units'    => $occupiedUnits,
            'occupancy_rate'    => $occupancyRate,
            'active_leases'     => $leasesQuery->count(),
            'pending_or_overdue'=> $pendingQuery->count(),
        ];

        // ── Pagos vencidos ──
        $overdueQuery = Payment::with(['lease.unit.property', 'lease.tenant'])
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date')
            ->limit(10);
        if ($propertyIds !== null) {
            $overdueQuery->whereHas('lease.unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }
        $overduePayments = $overdueQuery->get();

        // ── Pagos próximos ──
        $upcomingQuery = Payment::with(['lease.unit.property', 'lease.tenant'])
            ->where('status', '!=', 'paid')
            ->whereBetween('due_date', [$today, $nextSevenDays])
            ->orderBy('due_date')
            ->limit(10);
        if ($propertyIds !== null) {
            $upcomingQuery->whereHas('lease.unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }
        $upcomingPayments = $upcomingQuery->get();

        // ── Contratos por vencer ──
        $expiringQuery = Lease::with(['unit.property', 'tenant'])
            ->where('status', 'active')
            ->whereBetween('end_date', [$today, $next30Days])
            ->orderBy('end_date');
        if ($propertyIds !== null) {
            $expiringQuery->whereHas('unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }
        $expiringLeases = $expiringQuery->get();

        return view('dashboard', compact('stats', 'overduePayments', 'upcomingPayments', 'expiringLeases'));
    }

    public function calendarEvents(Request $request)
    {
        $start = $request->query('start');
        $end   = $request->query('end');

        $query = Payment::with(['lease.tenant', 'lease.unit.property']);

        // ── Filtro de acceso por propiedad ──
        $propertyIds = auth()->user()->allowedPropertyIds();
        if ($propertyIds !== null) {
            $query->whereHas('lease.unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }

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
