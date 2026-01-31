<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::all();
        return view('central.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('central.warehouses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:warehouses,code',
            'email' => 'nullable|email',
        ]);

        Warehouse::create($validated);

        return redirect()->route('central.warehouses.index')->with('success', 'Warehouse created successfully.');
    }

    public function show(Warehouse $warehouse)
    {
        $stocks = $warehouse->stocks()->with('product')->get();
        return view('central.warehouses.show', compact('warehouse', 'stocks'));
    }

    public function edit(Warehouse $warehouse)
    {
        return view('central.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:warehouses,code,' . $warehouse->id,
            'email' => 'nullable|email',
        ]);

        $warehouse->update($validated);

        return redirect()->route('central.warehouses.index')->with('success', 'Warehouse updated successfully.');
    }
}
