<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Exception;

class ProductController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the products.
     */
    public function index(): View
    {
        $this->authorize('products view');
        
        $products = Product::with(['category', 'brand'])->paginate(10);
        return view('tenant.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $this->authorize('products create');
        
        $categories = Category::all();
        $brands = Brand::all();
        return view('tenant.products.create', compact('categories', 'brands'));
    }

    /**
     * Search for products via AJAX.
     */
    public function search(Request $request): JsonResponse
    {
        $this->authorize('products view');
        
        $query = (string) $request->get('query', '');
        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('sku', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'sku', 'price', 'default_discount_type', 'default_discount_value']);
            
        return response()->json($products);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('products create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'default_discount_type' => 'nullable|in:fixed,percent',
            'default_discount_value' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                Product::create($validated);
            });

            return redirect()->route('tenant.products.index')->with('success', 'Product created successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to create product: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $this->authorize('products edit');
        
        $categories = Category::all();
        $brands = Brand::all();
        return view('tenant.products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('products edit');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'default_discount_type' => 'nullable|in:fixed,percent',
            'default_discount_value' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($product, $validated) {
                $product->update($validated);
            });

            return redirect()->route('tenant.products.index')->with('success', 'Product updated successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to update product: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('products delete');
        
        $product->delete();
        return redirect()->route('tenant.products.index')->with('success', 'Product deleted successfully.');
    }
}
