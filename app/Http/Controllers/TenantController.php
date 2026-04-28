<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Traits\FiltersByUserAccess;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    use FiltersByUserAccess;

    public function index(Request $request)
    {
        $this->authorizePermission('tenants.view');

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'sort' => (string) $request->query('sort', 'created_at'),
            'direction' => (string) $request->query('direction', 'desc'),
            'per_page' => (int) $request->query('per_page', 15),
        ];

        $perPageOptions = [15, 30, 50];
        if (! in_array($filters['per_page'], $perPageOptions, true)) {
            $filters['per_page'] = 15;
        }

        $sortable = [
            'created_at' => 'created_at',
            'full_name' => 'full_name',
            'email' => 'email',
        ];
        if (! array_key_exists($filters['sort'], $sortable)) {
            $filters['sort'] = 'created_at';
        }

        if (! in_array($filters['direction'], ['asc', 'desc'], true)) {
            $filters['direction'] = 'desc';
        }

        $query = Tenant::query();

        // ── Filtro de acceso por propiedad (en cascada vía contratos) ──
        $this->applyTenantPropertyFilter($query);

        $tenants = $query
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $like = '%' . $filters['q'] . '%';
                $query->where(function ($where) use ($like) {
                    $where
                        ->where('full_name', 'like', $like)
                        ->orWhere('document_id', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->orderBy($sortable[$filters['sort']], $filters['direction'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('tenants.index', compact('tenants', 'filters', 'perPageOptions'));
    }

    public function create()
    {
        $this->authorizePermission('tenants.create');

        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $this->authorizePermission('tenants.create');

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'document_id' => ['nullable', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('tenants', 'public');
        }

        Tenant::create($data);

        return redirect()->route('tenants.index')->with('success', 'Inquilino registrado.');
    }

    public function show(Tenant $tenant)
    {
        $this->authorizePermission('tenants.view');

        $tenant->load([
            'leases.unit.property', 
            'leases.payments' => function($q) {
                $q->orderBy('due_date', 'desc');
            },
            'leases.payments.lease.unit'
        ]);

        return view('tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        $this->authorizePermission('tenants.edit');

        return view('tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $this->authorizePermission('tenants.edit');

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'document_id' => ['nullable', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('tenants', 'public');
        }

        $tenant->update($data);

        return redirect()->route('tenants.index')->with('success', 'Inquilino actualizado.');
    }

    public function destroy(Tenant $tenant)
    {
        $this->authorizePermission('tenants.delete');

        $tenant->delete();

        return redirect()->route('tenants.index')->with('success', 'Inquilino eliminado.');
    }
}
