<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\LeaseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class LeaseNotificationController extends Controller
{
    public function index()
    {
        // Traemos los contratos activos para configurar sus notificaciones
        $leases = Lease::with(['tenant', 'unit', 'notifications'])
            ->where('status', 'active')
            ->orderBy('end_date', 'asc')
            ->get();

        return view('notifications.index', compact('leases'));
    }

    public function runManual()
    {
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
        $data = $request->validate([
            'lease_id' => 'required|exists:leases,id',
            'email'    => 'required|email',
            'notify_30_days'  => 'boolean',
            'notify_15_days'  => 'boolean',
            'notify_end_date' => 'boolean',
        ]);

        // Aseguramos que los toggles tengan valor si no vienen en el request
        $data['notify_30_days']  = $request->has('notify_30_days');
        $data['notify_15_days']  = $request->has('notify_15_days');
        $data['notify_end_date'] = $request->has('notify_end_date');

        LeaseNotification::create($data);

        return redirect()->back()->with('success', 'Regla de notificación añadida correctamente.');
    }

    public function destroy(LeaseNotification $notification)
    {
        $notification->delete();
        return redirect()->back()->with('success', 'Notificación eliminada.');
    }
}
