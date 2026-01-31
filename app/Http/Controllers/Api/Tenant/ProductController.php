<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::with(['category', 'brand'])->paginate(20));
    }

    public function show(Product $product)
    {
        return response()->json($product->load(['category', 'brand', 'images']));
    }
}
