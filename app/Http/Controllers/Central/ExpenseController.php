<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExpenseController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('expenses manage');
        $expenses = Expense::latest('date')->paginate(10);
        return view('central.expenses.index', compact('expenses'));
    }

    public function create()
    {
        $this->authorize('expenses manage');
        return view('central.expenses.create');
    }

    public function store(Request $request)
    {
        $this->authorize('expenses manage');

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category' => 'nullable|string|max:50',
        ]);

        $request->user()->expenses()->create($validated); // Assuming User hasMany Expenses, or explicitly:
        // Expense::create($validated + ['created_by' => auth()->id()]);

        return redirect()->route('central.expenses.index')->with('success', 'Expense created successfully.');
    }

    public function edit(Expense $expense)
    {
        $this->authorize('expenses manage');
        return view('central.expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $this->authorize('expenses manage');

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category' => 'nullable|string|max:50',
        ]);

        $expense->update($validated);

        return redirect()->route('central.expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $this->authorize('expenses manage');
        $expense->delete();
        return redirect()->route('central.expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
