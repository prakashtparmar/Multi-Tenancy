<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class BrandController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of central brands.
     */
    public function index(Request $request): View
    {
        $this->authorize('products view'); // Reusing products view for catalog components

        $query = Brand::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $perPage = (int) $request->input('per_page', 10);
        $brands = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return view('central.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new brand.
     */
    public function create(): View
    {
        $this->authorize('products create');
        return view('central.brands.create');
    }

    /**
     * Store a newly created brand in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('products create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug',
            'logo' => 'nullable|string', // URL or path
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                Brand::create($validated);
            });

            return redirect()->route('central.brands.index')
                ->with('success', 'Brand created successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to create brand: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified brand.
     */
    public function edit(Brand $brand): View
    {
        $this->authorize('products edit');
        return view('central.brands.edit', compact('brand'));
    }

    /**
     * Update the specified brand in storage.
     */
    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $this->authorize('products edit');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands')->ignore($brand->id),
            ],
            'logo' => 'nullable|string',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $brand) {
                $brand->update($validated);
            });

            return redirect()->route('central.brands.index')
                ->with('success', 'Brand updated successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to update brand: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified brand from storage.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        $this->authorize('products delete');

        // Check for products using this brand
        if (DB::table('products')->where('brand_id', $brand->id)->exists()) {
            return back()->with('error', 'Cannot delete brand with associated products.');
        }

        try {
            $brand->delete();
            return redirect()->route('central.brands.index')
                ->with('success', 'Brand deleted successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to delete brand: ' . $e->getMessage());
        }
    }
}
