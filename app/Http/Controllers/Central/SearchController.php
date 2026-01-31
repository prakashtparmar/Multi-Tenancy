<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function customers(Request $request)
    {
        $term = $request->input('q');
        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        $customers = Customer::where('mobile', 'like', "%{$term}%")
            ->orWhere('first_name', 'like', "%{$term}%")
            ->orWhere('last_name', 'like', "%{$term}%")
            ->orWhere('customer_code', 'like', "%{$term}%")
            ->limit(10)
            ->limit(10)
            ->with('addresses')
            ->get();

        $customers->transform(function ($customer) {
            return [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'mobile' => $customer->mobile,
                'email' => $customer->email,
                'customer_code' => $customer->customer_code,
                'outstanding_balance' => $customer->outstanding_balance ?? 0.00,
                'addresses' => $customer->addresses,
                'company_name' => $customer->company_name,
                'gst_number' => $customer->gst_number,
                'pan_number' => $customer->pan_number,
                'type' => $customer->type,
                'land_area' => $customer->land_area,
                'land_unit' => $customer->land_unit,
            ];
        });

        return response()->json($customers);
    }

    public function products(Request $request)
    {
        $term = $request->input('q');
        if (empty($term)) {
            $products = Product::where('is_active', true)
                ->with(['category', 'brand'])
                ->limit(20)
                ->get(); // Default recent list
        } else {
            $products = Product::where('is_active', true)
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                      ->orWhere('sku', 'like', "%{$term}%")
                      ->orWhere('slug', 'like', "%{$term}%");
                })
                ->orWhereHas('category', function($q) use ($term){
                    $q->where('name', 'like', "%{$term}%");
                })
                ->with(['category', 'brand'])
                ->limit(20)
                ->get();
        }
        
        // Append stocks and image
        $products->map(function ($product) {
            $product->stock_on_hand = $product->stock_on_hand; // Triggers accessor
            
            $primaryImage = $product->images()->where('is_primary', true)->value('image_path');
            $product->image_url = $primaryImage 
                ? asset('storage/' . $primaryImage) 
                : 'https://placehold.co/400x400?text=No+Image';
                
            return $product;
        });

        return response()->json($products);
    }

    public function storeCustomer(Request $request)
    {
        $id = $request->input('id');

        $rules = [
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
        ];

        $validated = $request->validate($rules);

        if ($id) {
            $customer = Customer::findOrFail($id);
            $customer->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'mobile' => $validated['mobile'],
                'email' => $validated['email'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'gst_number' => $validated['gst_number'] ?? null,
                'pan_number' => $validated['pan_number'] ?? null,
                'type' => $validated['type'] ?? 'farmer',
                'land_area' => $validated['land_area'] ?? null,
                'land_unit' => $validated['land_unit'] ?? 'acre',
            ]);
        } else {
            $customer = Customer::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'mobile' => $validated['mobile'],
                'email' => $validated['email'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'gst_number' => $validated['gst_number'] ?? null,
                'pan_number' => $validated['pan_number'] ?? null,
                'type' => $validated['type'] ?? 'farmer',
                'land_area' => $validated['land_area'] ?? null,
                'land_unit' => $validated['land_unit'] ?? 'acre',
                'customer_code' => 'CUST-' . strtoupper(\Str::random(6)), 
                'is_active' => true,
            ]);
        }
        
        // Return loaded addresses
        $customer->load('addresses');

        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    public function storeAddress(Request $request)
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

        // Handle Default assignment (reset others)
        if (!empty($validated['is_default']) && $validated['is_default']) {
            \App\Models\CustomerAddress::where('customer_id', $validated['customer_id'])
                ->update(['is_default' => false]);
        }

        if (!empty($validated['id'])) {
            // Update
            $address = \App\Models\CustomerAddress::where('customer_id', $validated['customer_id'])
                        ->where('id', $validated['id'])
                        ->firstOrFail();
            
            $address->update($validated);
        } else {
            // Create
            $address = \App\Models\CustomerAddress::create($validated);
        }

        return response()->json([
            'success' => true,
            'address' => $address,
            'all_addresses' => \App\Models\CustomerAddress::where('customer_id', $validated['customer_id'])->get()
        ]);
    }
}
