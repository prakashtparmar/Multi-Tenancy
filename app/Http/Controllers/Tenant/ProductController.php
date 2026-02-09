<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\TaxClass;
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
        $taxClasses = TaxClass::with('rates')->get();
        return view('tenant.products.create', compact('categories', 'brands', 'taxClasses'));
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
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'default_discount_type' => 'nullable|in:fixed,percent',
            'default_discount_value' => 'nullable|numeric|min:0',
            'tax_class_id' => 'nullable|exists:tax_classes,id',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'hsn_code' => 'nullable|string|max:20',
            // General
            'barcode' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:simple,variable',
            'description' => 'nullable|string',
            'unit_type' => 'required|string|max:50',
            'packing_size' => 'nullable|string|max:100',
            // Agri
            'harvest_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'technical_name' => 'nullable|string|max:255',
            'application_method' => 'nullable|string|max:255',
            'usage_instructions' => 'nullable|string',
            'target_crops' => 'nullable|string', // Comma separated
            'target_pests' => 'nullable|string', // Comma separated
            'pre_harvest_interval' => 'nullable|string|max:255',
            'shelf_life' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'is_organic' => 'boolean',
            'certification_number' => 'nullable|string|max:255',
            'certificate_url' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            // WMS
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'manage_stock' => 'boolean',
            'stock_on_hand' => 'nullable|numeric|min:0',
            'min_order_qty' => 'nullable|integer|min:1',
            'reorder_level' => 'nullable|integer|min:0',
            // Status & SEO
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_taxable' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            // Media
            'image' => 'nullable|image|max:2048', // 2MB
            'gallery.*' => 'nullable|image|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request, $validated) {
                // Process Tags
                if (isset($validated['target_crops'])) {
                    $validated['target_crops'] = array_map('trim', explode(',', $validated['target_crops']));
                }
                if (isset($validated['target_pests'])) {
                    $validated['target_pests'] = array_map('trim', explode(',', $validated['target_pests']));
                }

                // Handle Certificate Upload
                if ($request->hasFile('certificate_url')) {
                    $validated['certificate_url'] = $request->file('certificate_url')->store('certificates', 'public');
                }

                $product = Product::create($validated);

                // Handle Main Image
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('products', 'public');
                    // Save to product_images table
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => true,
                        'sort_order' => 0
                    ]);
                    // Also update flat column for read performance/compatibility if needed
                    $product->update(['image' => $path]);
                }

                // Handle Gallery
                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $index => $file) {
                        $path = $file->store('products', 'public');
                        $product->images()->create([
                            'image_path' => $path,
                            'is_primary' => false,
                            'sort_order' => $index + 1
                        ]);
                    }
                }
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
        $taxClasses = TaxClass::with('rates')->get();
        return view('tenant.products.edit', compact('product', 'categories', 'brands', 'taxClasses'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('products edit');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'default_discount_type' => 'nullable|in:fixed,percent',
            'default_discount_value' => 'nullable|numeric|min:0',
            'tax_class_id' => 'nullable|exists:tax_classes,id',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'hsn_code' => 'nullable|string|max:20',
            // General
            'barcode' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:simple,variable',
            'description' => 'nullable|string',
            'unit_type' => 'required|string|max:50',
            'packing_size' => 'nullable|string|max:100',
            // Agri
            'harvest_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'technical_name' => 'nullable|string|max:255',
            'application_method' => 'nullable|string|max:255',
            'usage_instructions' => 'nullable|string',
            'target_crops' => 'nullable|string', // Comma separated
            'target_pests' => 'nullable|string', // Comma separated
            'pre_harvest_interval' => 'nullable|string|max:255',
            'shelf_life' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'is_organic' => 'boolean',
            'certification_number' => 'nullable|string|max:255',
            'certificate_url' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            // WMS
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'manage_stock' => 'boolean',
            'stock_on_hand' => 'nullable|numeric|min:0',
            'min_order_qty' => 'nullable|integer|min:1',
            'reorder_level' => 'nullable|integer|min:0',
            // Status & SEO
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_taxable' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            // Media
            'image' => 'nullable|image|max:2048',
            'gallery.*' => 'nullable|image|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request, $product, $validated) {
                // Process Tags
                if (isset($validated['target_crops'])) {
                    $validated['target_crops'] = array_map('trim', explode(',', $validated['target_crops']));
                }
                if (isset($validated['target_pests'])) {
                    $validated['target_pests'] = array_map('trim', explode(',', $validated['target_pests']));
                }

                // Handle Certificate Upload
                if ($request->hasFile('certificate_url')) {
                    // Delete old certificate if exists
                    if ($product->certificate_url) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($product->certificate_url);
                    }
                    $validated['certificate_url'] = $request->file('certificate_url')->store('certificates', 'public');
                }

                $product->update($validated);

                // Handle Main Image Update
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('products', 'public');

                    // Unset old primary
                    $product->images()->where('is_primary', true)->update(['is_primary' => false]);

                    // Add new primary
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => true,
                        'sort_order' => 0
                    ]);
                    // Update flat column
                    $product->update(['image' => $path]);
                }

                // Handle Gallery Append
                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $index => $file) {
                        $path = $file->store('products', 'public');
                        $product->images()->create([
                            'image_path' => $path,
                            'is_primary' => false,
                            'sort_order' => 10 + $index // arbitrary higher sort order
                        ]);
                    }
                }
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
