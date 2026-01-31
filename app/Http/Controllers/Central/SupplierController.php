<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%");
        }

        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                 $query->where('is_active', false);
            }
        }

        $perPage = $request->input('per_page', 10);
        $suppliers = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('central.suppliers.index', compact('suppliers'))->render();
        }

        return view('central.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('central.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'currency' => 'required|string|max:3',
            'farm_size' => 'nullable|numeric|min:0',
            'primary_crop' => 'nullable|string|max:255',
            'verification_status' => 'required|in:unverified,verified,rejected',
            'is_active' => 'boolean',
        ]);

        Supplier::create($validated);

        return redirect()->route('central.suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return view('central.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'currency' => 'required|string|max:3',
            'farm_size' => 'nullable|numeric|min:0',
            'primary_crop' => 'nullable|string|max:255',
            'verification_status' => 'required|in:unverified,verified,rejected',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);

        return redirect()->route('central.suppliers.index')->with('success', 'Supplier updated successfully.');
    }
}
