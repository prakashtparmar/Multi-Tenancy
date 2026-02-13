<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class WarehouseController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of central warehouses.
     */
    public function index(): View
    {
        $this->authorize('inventory manage');

        $warehouses = Warehouse::all();
        return view('central.warehouses.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new warehouse.
     */
    public function create(): View
    {
        $this->authorize('inventory manage');
        return view('central.warehouses.create');
    }

    /**
     * Store a newly created warehouse in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('inventory manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:warehouses,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                Warehouse::create($validated);
            });

            return redirect()->route('central.warehouses.index')->with('success', 'Warehouse created successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to create warehouse: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse): View
    {
        $this->authorize('inventory manage');

        $stocks = $warehouse->stocks()->with('product')->get();
        return view('central.warehouses.show', compact('warehouse', 'stocks'));
    }

    /**
     * Show the form for editing the specified warehouse.
     */
    public function edit(Warehouse $warehouse): View
    {
        $this->authorize('inventory manage');
        return view('central.warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the specified warehouse in storage.
     */
    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('inventory manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:warehouses,code,' . $warehouse->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($warehouse, $validated) {
                $warehouse->update($validated);
            });

            return redirect()->route('central.warehouses.index')->with('success', 'Warehouse updated successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to update warehouse: ' . $e->getMessage());
        }
    }
}
