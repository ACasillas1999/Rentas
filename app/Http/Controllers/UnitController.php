<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'property_id' => (string) $request->query('property_id', ''),
            'status' => (string) $request->query('status', ''),
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
            'code'       => 'code',
            'status'     => 'status',
        ];
        if (! array_key_exists($filters['sort'], $sortable)) {
            $filters['sort'] = 'created_at';
        }

        if (! in_array($filters['direction'], ['asc', 'desc'], true)) {
            $filters['direction'] = 'desc';
        }

        $query = Unit::query()->with(['property', 'beneficiary']);

        $query
            ->when($filters['q'] !== '', function ($builder) use ($filters) {
                $like = '%' . $filters['q'] . '%';
                $builder->where(function ($where) use ($like) {
                    $where
                        ->where('code', 'like', $like)
                        ->orWhere('floor', 'like', $like)
                        ->orWhereHas('property', fn ($property) => $property->where('name', 'like', $like));
                });
            })
            ->when($filters['property_id'] !== '', fn ($builder) => $builder->where('property_id', $filters['property_id']))
            ->when($filters['status'] !== '', fn ($builder) => $builder->where('status', $filters['status']));

        $units = $query
            ->orderBy($sortable[$filters['sort']], $filters['direction'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        $properties = Property::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('units.index', compact('units', 'properties', 'users', 'filters', 'perPageOptions'));
    }

    public function create()
    {
        $properties = Property::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('units.create', compact('properties', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'beneficiary_id' => ['nullable', 'exists:users,id'],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('units')->where(fn ($query) => $query->where('property_id', $request->input('property_id'))),
            ],
            'floor'      => ['nullable', 'string', 'max:30'],
            'area_m2'    => ['nullable', 'numeric', 'min:0'],
            'status'     => ['required', 'in:available,rented,maintenance'],
            'notes'      => ['nullable', 'string'],
            'photo'      => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('units', 'public');
        }

        Unit::create($data);

        return redirect()->route('units.index')->with('success', 'Local/unidad creada.');
    }

    public function show(Unit $unit)
    {
        $unit->load('property', 'leases.tenant');

        return view('units.show', compact('unit'));
    }

    public function edit(Unit $unit)
    {
        $properties = Property::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('units.edit', compact('unit', 'properties', 'users'));
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'beneficiary_id' => ['nullable', 'exists:users,id'],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('units')
                    ->where(fn ($query) => $query->where('property_id', $request->input('property_id')))
                    ->ignore($unit->id),
            ],
            'floor'      => ['nullable', 'string', 'max:30'],
            'area_m2'    => ['nullable', 'numeric', 'min:0'],
            'status'     => ['required', 'in:available,rented,maintenance'],
            'notes'      => ['nullable', 'string'],
            'photo'      => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('units', 'public');
        }

        $unit->update($data);

        return redirect()->route('units.index')->with('success', 'Local/unidad actualizada.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Local/unidad eliminada.');
    }
}
