<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class ProductController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the central products.
     */
    public function index(Request $request): View
    {
        $this->authorize('products view');

        $query = Product::with(['category', 'brand', 'images']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        }

        if ($request->input('stock') === 'low') {
            $query->where('stock_on_hand', '<=', 10);
        }

        $perPage = (int) $request->input('per_page', 10);
        $products = $query->latest()->paginate($perPage)->withQueryString();

        return view('central.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $this->authorize('products create');

        $categories = Category::all();
        $brands = Brand::all();
        return view('central.products.create', compact('categories', 'brands'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('products create');

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
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        try {
            return DB::transaction(function () use ($request, $validated) {
                $product = Product::create($validated);

                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $index => $image) {
                        $path = $image->store('products', 'public');
                        $product->images()->create([
                            'image_path' => $path,
                            'is_primary' => $index === 0,
                            'sort_order' => $index
                        ]);
                    }
                }

                return redirect()->route('central.products.index')->with('success', 'Product created successfully.');
            });
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
        return view('central.products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('products edit');

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
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'delete_images.*' => 'exists:product_images,id'
        ]);

        try {
            DB::transaction(function () use ($request, $product, $validated) {
                $product->update($validated);

                // Handle Image Deletion
                if ($request->has('delete_images')) {
                    $imagesToDelete = ProductImage::whereIn('id', $request->delete_images)
                        ->where('product_id', $product->id)
                        ->get();
                    
                    foreach ($imagesToDelete as $img) {
                        Storage::disk('public')->delete($img->image_path);
                        $img->delete();
                    }
                }

                // Handle New Images
                if ($request->hasFile('images')) {
                    $currentCount = $product->images()->count();
                    foreach ($request->file('images') as $index => $image) {
                        $path = $image->store('products', 'public');
                        $product->images()->create([
                            'image_path' => $path,
                            'is_primary' => ($currentCount + $index) === 0,
                            'sort_order' => $currentCount + $index
                        ]);
                    }
                }

                // Ensure at least one image is primary if any exist
                if ($product->images()->exists() && !$product->images()->where('is_primary', true)->exists()) {
                    $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
                }
            });

            return redirect()->route('central.products.index')->with('success', 'Product updated successfully.');
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
        return redirect()->route('central.products.index')->with('success', 'Product deleted successfully.');
    }
}
