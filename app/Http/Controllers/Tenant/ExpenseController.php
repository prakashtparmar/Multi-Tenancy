<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::latest()->paginate(10);
        return view('tenant.expenses.index', compact('expenses'));
    }

    public function create()
    {
        return view('tenant.expenses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = auth()->id();

        Expense::create($validated);

        return redirect()->route('tenant.expenses.index')->with('success', 'Expense added successfully.');
    }

    public function edit(Expense $expense)
    {
        return view('tenant.expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category' => 'nullable|string|max:255',
        ]);

        $expense->update($validated);

        return redirect()->route('tenant.expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('tenant.expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
