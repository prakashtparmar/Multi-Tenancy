<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class SupplierController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the suppliers.
     */
    public function index(): View
    {
        $this->authorize('inventory manage');
        
        $suppliers = Supplier::paginate(15);
        return view('tenant.suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create(): View
    {
        $this->authorize('inventory manage');
        return view('tenant.suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('inventory manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email',
            'phone' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                Supplier::create($validated);
            });

            return redirect()->route('tenant.suppliers.index')->with('success', 'Supplier created successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to create supplier.');
        }
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier): View
    {
        $this->authorize('inventory manage');
        return view('tenant.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('inventory manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email,' . $supplier->id,
            'phone' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($supplier, $validated) {
                $supplier->update($validated);
            });

            return redirect()->route('tenant.suppliers.index')->with('success', 'Supplier updated successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to update supplier.');
        }
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('inventory manage');
        
        $supplier->delete();
        return redirect()->route('tenant.suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
