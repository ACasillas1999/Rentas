<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Property;
use App\Models\Unit;
use App\Traits\FiltersByUserAccess;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    use FiltersByUserAccess, LogsActivity;

    public function index(Request $request)
    {
        $this->authorizePermission('expenses.view');

        $query = Expense::with(['property', 'unit'])->latest('expense_date');

        // ── Filtro de acceso por propiedad ──
        $this->applyPropertyFilter($query);

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('expense_date', $request->month)
                  ->whereYear('expense_date', $request->year);
        }

        $expenses   = $query->paginate(20)->withQueryString();

        // Solo propiedades accesibles en el filtro
        $propertiesQuery = Property::orderBy('name');
        $this->applyPropertyIdFilter($propertiesQuery);
        $properties = $propertiesQuery->get();

        $categories = Expense::categories();
        $totalAmount = $query->sum('amount');

        return view('expenses.index', compact('expenses', 'properties', 'categories', 'totalAmount'));
    }

    public function create()
    {
        $this->authorizePermission('expenses.create');

        $propertiesQuery = Property::orderBy('name');
        $this->applyPropertyIdFilter($propertiesQuery);
        $properties = $propertiesQuery->get();

        $units      = collect();
        $categories = Expense::categories();
        return view('expenses.create', compact('properties', 'units', 'categories'));
    }

    public function store(Request $request)
    {
        $this->authorizePermission('expenses.create');

        $data = $request->validate([
            'property_id'  => 'required|exists:properties,id',
            'unit_id'      => 'nullable|exists:units,id',
            'category'     => 'required|string|max:100',
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'paid_to'      => 'nullable|string|max:255',
            'notes'        => 'nullable|string',
            'receipt'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Verificar acceso a la propiedad
        $this->authorizeProperty((int) $data['property_id']);

        if ($request->hasFile('receipt')) {
            $data['receipt'] = $request->file('receipt')->store('expenses');
        }

        Expense::create($data);

        $this->logActivity('created', 'expense', null, "Creó gasto: {$data['description']} (\${$data['amount']})");

        return redirect()->route('expenses.index')->with('success', 'Gasto registrado correctamente.');
    }

    public function show(Expense $expense)
    {
        $this->authorizePermission('expenses.view');
        $this->authorizeProperty($expense->property_id);

        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $this->authorizePermission('expenses.edit');
        $this->authorizeProperty($expense->property_id);

        $propertiesQuery = Property::orderBy('name');
        $this->applyPropertyIdFilter($propertiesQuery);
        $properties = $propertiesQuery->get();

        $units      = Unit::where('property_id', $expense->property_id)->orderBy('code')->get();
        $categories = Expense::categories();
        return view('expenses.edit', compact('expense', 'properties', 'units', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $this->authorizePermission('expenses.edit');
        $this->authorizeProperty($expense->property_id);

        $data = $request->validate([
            'property_id'  => 'required|exists:properties,id',
            'unit_id'      => 'nullable|exists:units,id',
            'category'     => 'required|string|max:100',
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'paid_to'      => 'nullable|string|max:255',
            'notes'        => 'nullable|string',
            'receipt'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('receipt')) {
            if ($expense->receipt) {
                Storage::delete($expense->receipt);
                Storage::disk('public')->delete($expense->receipt);
            }
            $data['receipt'] = $request->file('receipt')->store('expenses');
        }

        $expense->update($data);

        $this->logActivity('updated', 'expense', $expense->id, "Editó gasto: {$expense->description} (\${$expense->amount})");

        return redirect()->route('expenses.index')->with('success', 'Gasto actualizado correctamente.');
    }

    public function destroy(Expense $expense)
    {
        $this->authorizePermission('expenses.delete');
        $this->authorizeProperty($expense->property_id);

        $desc = $expense->description;
        $id = $expense->id;
        if ($expense->receipt) {
            Storage::delete($expense->receipt);
            Storage::disk('public')->delete($expense->receipt);
        }
        $expense->delete();

        $this->logActivity('deleted', 'expense', $id, "Eliminó gasto: {$desc}");

        return redirect()->route('expenses.index')->with('success', 'Gasto eliminado.');
    }

    // AJAX: unidades por propiedad
    public function unitsByProperty(Property $property)
    {
        return response()->json(
            $property->units()->orderBy('code')->get(['id', 'code'])
        );
    }
}
