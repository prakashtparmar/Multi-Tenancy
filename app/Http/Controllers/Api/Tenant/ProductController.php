<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the products.
     */
    public function index(): JsonResponse
    {
        $this->authorize('products view');

        $products = Product::with(['category', 'brand'])->paginate(20);
        
        return response()->json($products);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): JsonResponse
    {
        $this->authorize('products view');

        return response()->json($product->load(['category', 'brand']));
    }
}
