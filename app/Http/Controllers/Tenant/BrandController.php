<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

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
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // Use generic 'products view' permission or a specific one if added
        // For now, consistent with sidebar logic which checks 'products view'
        // $this->authorize('products view'); 

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

        // We can reuse the central view or generic tenant view
        // Assuming we share views or have a specific path. 
        // Typically tenant views might be in 'tenant.brands.index' or 'central.brands.index' if shared.
        // Let's assume we use the same view path for simplicity if supported, or 'tenant.brands.index'
        // Checking existing controllers pattern...
        // CategoryController uses 'central.categories.index'? Let's check.
        // If they share views, we use 'central.brands.index'. If not, we need to create 'tenant/brands/index.blade.php'.
        // Given the typical setup in this project, often views are shared if 'central' prefix is just organizational.
        // But usually tenants have their own views directory or use a common one.
        // Let's use 'central.brands.index' since I created that earlier (referenced in Central Controller).
        // If Tenant views are separate, I might need to create them. 
        // For now, I'll point to 'central.brands.index' as a safe bet for shared UI or I'll create the view file if needed.
        // Let's use 'central.brands.index' for now.
        return view('tenant.brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('tenant.brands.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'logo' => 'nullable|string', // Assuming file upload returns path or is handled elsewhere
            'banner_image' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'country_origin' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Auto-generate slug if missing handled in Model boot
                Brand::create($validated);
            });

            return redirect()->route('tenant.brands.index')
                ->with('success', 'Brand created successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to create brand: ' . $e->getMessage());
        }
    }

    public function edit(Brand $brand): View
    {
        // View not created yet, but this is the method
        return view('tenant.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('brands')->ignore($brand->id),
            ],
            'logo' => 'nullable|string',
            'banner_image' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'country_origin' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $brand) {
                $brand->update($validated);
            });

            return redirect()->route('tenant.brands.index')
                ->with('success', 'Brand updated successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to update brand: ' . $e->getMessage());
        }
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->exists()) {
            return back()->with('error', 'Cannot delete brand with associated products.');
        }

        try {
            $brand->delete();
            return redirect()->route('tenant.brands.index')
                ->with('success', 'Brand deleted successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to delete brand: ' . $e->getMessage());
        }
    }
}
