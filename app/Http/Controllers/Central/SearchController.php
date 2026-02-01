<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class SearchController extends Controller
{
    /**
     * Search for customers via AJAX.
     */
    public function customers(Request $request): JsonResponse
    {
        $term = (string) $request->input('q', '');
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $customers = Customer::where('mobile', 'like', "%{$term}%")
            ->orWhere('first_name', 'like', "%{$term}%")
            ->orWhere('last_name', 'like', "%{$term}%")
            ->orWhere('customer_code', 'like', "%{$term}%")
            ->limit(10)
            ->with('addresses')
            ->get();

        $data = $customers->map(fn($customer) => [
            'id' => $customer->id,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'mobile' => $customer->mobile,
            'email' => $customer->email,
            'customer_code' => $customer->customer_code,
            'outstanding_balance' => (float) ($customer->outstanding_balance ?? 0.00),
            'addresses' => $customer->addresses,
            'company_name' => $customer->company_name,
            'gst_number' => $customer->gst_number,
            'pan_number' => $customer->pan_number,
            'type' => $customer->type,
            'land_area' => $customer->land_area,
            'land_unit' => $customer->land_unit,
        ]);

        return response()->json($data);
    }

    /**
     * Search for products via AJAX.
     */
    public function products(Request $request): JsonResponse
    {
        $term = (string) $request->input('q', '');
        $query = Product::where('is_active', true)
            ->with(['category', 'brand', 'images', 'stocks']);

        if (empty($term)) {
            $products = $query->limit(20)->get();
        } else {
            $products = $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                      ->orWhere('sku', 'like', "%{$term}%")
                      ->orWhere('slug', 'like', "%{$term}%");
                })
                ->orWhereHas('category', function($q) use ($term){
                    $q->where('name', 'like', "%{$term}%");
                })
                ->limit(20)
                ->get();
        }
        
        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->price,
                'stock_on_hand' => (float) $product->stock_on_hand,
                'unit_type' => $product->unit_type,
                'brand' => $product->brand->name ?? 'N/A',
                'description' => $product->description,
                'is_organic' => $product->is_organic,
                'origin' => $product->origin,
                'image_url' => $product->image_url,
                'category' => $product->category->name ?? 'Uncategorized'
            ];
        });

        return response()->json($data);
    }

    /**
     * Store or update a customer via AJAX.
     */
    public function storeCustomer(Request $request): JsonResponse
    {
        $id = $request->input('id');

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'mobile' => 'required|string|max:20|unique:customers,mobile' . ($id ? ",$id" : ''),
            'email' => 'nullable|email|unique:customers,email' . ($id ? ",$id" : ''),
            'company_name' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:50',
            'type' => 'nullable|in:farmer,buyer,vendor,dealer',
            'land_area' => 'nullable|numeric|min:0',
            'land_unit' => 'nullable|string|in:acre,hectare,guntha',
        ]);

        try {
            return DB::transaction(function () use ($id, $validated) {
                if ($id) {
                    $customer = Customer::findOrFail($id);
                    $customer->update($validated);
                } else {
                    $customer = Customer::create($validated + [
                        'customer_code' => 'CUST-' . strtoupper(Str::random(6)), 
                        'is_active' => true,
                    ]);
                }
                
                $customer->load('addresses');

                return response()->json([
                    'success' => true,
                    'customer' => $customer
                ]);
            });
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Store or update a customer address via AJAX.
     */
    public function storeAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'id' => 'nullable|exists:customer_addresses,id',
            'label' => 'nullable|string|max:50',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'village' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:20',
            'type' => 'nullable|in:billing,shipping,both',
            'is_default' => 'boolean',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                // Handle Default assignment (reset others)
                if (!empty($validated['is_default'])) {
                    CustomerAddress::where('customer_id', $validated['customer_id'])
                        ->update(['is_default' => false]);
                }

                if (!empty($validated['id'])) {
                    $address = CustomerAddress::where('customer_id', $validated['customer_id'])
                                ->where('id', $validated['id'])
                                ->firstOrFail();
                    $address->update($validated);
                } else {
                    $address = CustomerAddress::create($validated);
                }

                return response()->json([
                    'success' => true,
                    'address' => $address,
                    'all_addresses' => CustomerAddress::where('customer_id', $validated['customer_id'])->get()
                ]);
            });
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
