<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Traits\FiltersByUserAccess;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    use FiltersByUserAccess;

    public function index(Request $request)
    {
        $this->authorizePermission('properties.view');

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'type' => (string) $request->query('type', ''),
            'city' => trim((string) $request->query('city', '')),
            'sort' => (string) $request->query('sort', 'name'),
            'direction' => (string) $request->query('direction', 'asc'),
            'per_page' => (int) $request->query('per_page', 15),
        ];

        $perPageOptions = [15, 30, 50];
        if (! in_array($filters['per_page'], $perPageOptions, true)) {
            $filters['per_page'] = 15;
        }

        $sortable = [
            'name' => 'name',
            'city' => 'city',
            'created_at' => 'created_at',
            'units' => 'units_count',
        ];
        if (! array_key_exists($filters['sort'], $sortable)) {
            $filters['sort'] = 'name';
        }

        if (! in_array($filters['direction'], ['asc', 'desc'], true)) {
            $filters['direction'] = 'asc';
        }

        $baseQuery = Property::query()
            ->withCount([
                'units',
                'units as occupied_units_count' => fn ($query) => $query->where('status', 'rented'),
                'units as available_units_count' => fn ($query) => $query->where('status', 'available'),
            ]);

        // ── Filtro de acceso por propiedad ──
        $this->applyPropertyIdFilter($baseQuery);

        $baseQuery
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $like = '%' . $filters['q'] . '%';
                $query->where(function ($where) use ($like) {
                    $where
                        ->where('name', 'like', $like)
                        ->orWhere('address', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('state', 'like', $like);
                });
            })
            ->when($filters['type'] !== '', fn ($query) => $query->where('type', $filters['type']))
            ->when($filters['city'] !== '', fn ($query) => $query->where('city', 'like', '%' . $filters['city'] . '%'));

        $mapProperties = (clone $baseQuery)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->limit(400)
            ->get(['id', 'name', 'address', 'type', 'latitude', 'longitude']);

        $properties = (clone $baseQuery)
            ->orderBy($sortable[$filters['sort']], $filters['direction'])
            ->orderBy('name')
            ->paginate($filters['per_page'])
            ->withQueryString();

        $types = [
            'commercial' => 'Comercial',
            'residential' => 'Residencial',
            'mixed' => 'Mixto',
            'other' => 'Otro',
        ];

        return view('properties.index', compact('properties', 'mapProperties', 'filters', 'types', 'perPageOptions'));
    }

    public function create()
    {
        $this->authorizePermission('properties.create');

        return view('properties.create');
    }

    public function store(Request $request)
    {
        $this->authorizePermission('properties.create');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:commercial,residential,mixed,other'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('properties', 'public');
        }

        Property::create($data);

        return redirect()->route('properties.index')->with('success', 'Propiedad creada.');
    }

    public function show(Property $property)
    {
        $this->authorizePermission('properties.view');
        $this->authorizeProperty($property->id);

        $property->load('units');

        return view('properties.show', compact('property'));
    }

    public function edit(Property $property)
    {
        $this->authorizePermission('properties.edit');
        $this->authorizeProperty($property->id);

        return view('properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property)
    {
        $this->authorizePermission('properties.edit');
        $this->authorizeProperty($property->id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:commercial,residential,mixed,other'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('properties', 'public');
        }

        $property->update($data);

        return redirect()->route('properties.index')->with('success', 'Propiedad actualizada.');
    }

    public function destroy(Property $property)
    {
        $this->authorizePermission('properties.delete');
        $this->authorizeProperty($property->id);

        $property->delete();

        return redirect()->route('properties.index')->with('success', 'Propiedad eliminada.');
    }
}
