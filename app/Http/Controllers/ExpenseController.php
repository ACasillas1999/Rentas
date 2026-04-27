<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['property', 'unit'])->latest('expense_date');

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
        $properties = Property::orderBy('name')->get();
        $categories = Expense::categories();
        $totalAmount = $query->sum('amount');

        return view('expenses.index', compact('expenses', 'properties', 'categories', 'totalAmount'));
    }

    public function create()
    {
        $properties = Property::orderBy('name')->get();
        $units      = collect();
        $categories = Expense::categories();
        return view('expenses.create', compact('properties', 'units', 'categories'));
    }

    public function store(Request $request)
    {
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
            $data['receipt'] = $request->file('receipt')->store('expenses', 'public');
        }

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Gasto registrado correctamente.');
    }

    public function show(Expense $expense)
    {
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $properties = Property::orderBy('name')->get();
        $units      = Unit::where('property_id', $expense->property_id)->orderBy('code')->get();
        $categories = Expense::categories();
        return view('expenses.edit', compact('expense', 'properties', 'units', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
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
            if ($expense->receipt) Storage::disk('public')->delete($expense->receipt);
            $data['receipt'] = $request->file('receipt')->store('expenses', 'public');
        }

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Gasto actualizado correctamente.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt) Storage::disk('public')->delete($expense->receipt);
        $expense->delete();
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
