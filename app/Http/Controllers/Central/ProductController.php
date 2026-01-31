<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            }
        }

        if ($request->has('stock')) {
            if ($request->input('stock') === 'low') {
                $query->where('stock_on_hand', '<=', 10);
            }
        }

        $perPage = $request->input('per_page', 10);
        $products = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('central.products.index', compact('products'))->render();
        }

        return view('central.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        return view('central.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku',
            'type' => 'required|in:simple,variable',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'harvest_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:harvest_date',
            'origin' => 'nullable|string|max:255',
            'is_organic' => 'boolean',
            'certification_number' => 'nullable|string|max:255',
            'unit_type' => 'required|string|max:50',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        Product::create($validated);

        return redirect()->route('central.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        return view('central.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'type' => 'required|in:simple,variable',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'harvest_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:harvest_date',
            'origin' => 'nullable|string|max:255',
            'is_organic' => 'boolean',
            'certification_number' => 'nullable|string|max:255',
            'unit_type' => 'required|string|max:50',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $product->update($validated);

        return redirect()->route('central.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('central.products.index')->with('success', 'Product deleted successfully.');
    }
}
