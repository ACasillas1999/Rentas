<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Solo admins
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Acceso restringido.');
        }

        $query = ActivityLog::query()->orderByDesc('created_at');

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('q')) {
            $query->where('description', 'like', '%' . $request->q . '%');
        }

        $logs = $query->paginate(50)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);

        $modules = [
            'lease'    => 'Contratos',
            'payment'  => 'Pagos',
            'tenant'   => 'Inquilinos',
            'property' => 'Propiedades',
            'unit'     => 'Unidades',
            'expense'  => 'Gastos',
            'user'     => 'Usuarios',
            'report'   => 'Reportes',
            'auth'     => 'Autenticación',
        ];

        $actions = [
            'created'  => 'Creado',
            'updated'  => 'Editado',
            'deleted'  => 'Eliminado',
            'paid'     => 'Cobrado',
            'invoiced' => 'Facturado',
            'receipt'  => 'Comprobante',
            'renewed'  => 'Renovado',
            'exported' => 'Exportado',
            'viewed'   => 'Consultado',
            'login'    => 'Acceso',
            'bulk'     => 'Masivo',
        ];

        return view('activity.index', compact('logs', 'users', 'modules', 'actions'));
    }
}
