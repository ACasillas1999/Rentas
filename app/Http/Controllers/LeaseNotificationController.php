<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\LeaseNotification;
use App\Traits\FiltersByUserAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class LeaseNotificationController extends Controller
{
    use FiltersByUserAccess;

    public function index()
    {
        $this->authorizePermission('notifications.view');

        $propertyIds = auth()->user()->allowedPropertyIds();

        // Traemos los contratos activos para configurar sus notificaciones
        $leasesQuery = Lease::with(['tenant', 'unit', 'notifications'])
            ->where('status', 'active')
            ->orderBy('end_date', 'asc');

        // ── Filtro de acceso por propiedad ──
        if ($propertyIds !== null) {
            $leasesQuery->whereHas('unit', fn($q) => $q->whereIn('property_id', $propertyIds));
        }

        $leases = $leasesQuery->get();

        return view('notifications.index', compact('leases'));
    }

    public function runManual()
    {
        $this->authorizePermission('notifications.manage');

        try {
            Artisan::call('leases:notify');
            $output = Artisan::output();
            
            return redirect()->back()->with('success', '🔔 Proceso de notificaciones ejecutado. Resultado: ' . $output);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al ejecutar: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->authorizePermission('notifications.manage');

        $data = $request->validate([
            'lease_id' => 'required|exists:leases,id',
            'email'    => 'required|email',
            'notify_30_days'  => 'boolean',
            'notify_15_days'  => 'boolean',
            'notify_end_date' => 'boolean',
        ]);

        // Verificar que el lease pertenece a una propiedad permitida
        $propertyIds = auth()->user()->allowedPropertyIds();
        if ($propertyIds !== null) {
            $lease = Lease::whereHas('unit', fn($q) => $q->whereIn('property_id', $propertyIds))
                          ->findOrFail($data['lease_id']);
        }

        // Aseguramos que los toggles tengan valor si no vienen en el request
        $data['notify_30_days']  = $request->has('notify_30_days');
        $data['notify_15_days']  = $request->has('notify_15_days');
        $data['notify_end_date'] = $request->has('notify_end_date');

        LeaseNotification::create($data);

        return redirect()->back()->with('success', 'Regla de notificación añadida correctamente.');
    }

    public function destroy(LeaseNotification $notification)
    {
        $this->authorizePermission('notifications.manage');

        // Verificar que la notificación pertenece a un lease de propiedad permitida
        $propertyIds = auth()->user()->allowedPropertyIds();
        if ($propertyIds !== null) {
            $allowed = Lease::whereHas('unit', fn($q) => $q->whereIn('property_id', $propertyIds))
                            ->where('id', $notification->lease_id)
                            ->exists();
            abort_unless($allowed, 403);
        }

        $notification->delete();
        return redirect()->back()->with('success', 'Notificación eliminada.');
    }
}
