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

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SearchController extends Controller
{
    use AuthorizesRequests;

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
            ->withCount('orders')
            ->get();

        $data = $customers->map(fn($customer) => [
            'id' => $customer->id,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'mobile' => $customer->mobile,
            'phone_number_2' => $customer->phone_number_2,
            'email' => $customer->email,
            'customer_code' => $customer->customer_code,
            'outstanding_balance' => (float) ($customer->outstanding_balance ?? 0.00),
            'credit_limit' => (float) ($customer->credit_limit ?? 0.00),
            'orders_count' => (int) ($customer->orders_count ?? 0),
            'addresses' => $customer->addresses,
            'company_name' => $customer->company_name,
            'gst_number' => $customer->gst_number,
            'pan_number' => $customer->pan_number,
            'type' => $customer->type,
            'category' => $customer->category,
            'aadhaar_last4' => $customer->aadhaar_last4,
            'kyc_completed' => (bool) $customer->kyc_completed,
            'kyc_verified_at' => $customer->kyc_verified_at ? $customer->kyc_verified_at->format('d M Y') : null,
            'credit_valid_till' => $customer->credit_valid_till,
            'internal_notes' => $customer->internal_notes,
            'tags' => $customer->tags,
            'crops' => $customer->crops,
            'land_area' => $customer->land_area,
            'land_unit' => $customer->land_unit,
            'irrigation_type' => $customer->irrigation_type,
            'created_at' => $customer->created_at->format('M Y'),
            'is_active' => (bool) $customer->is_active,
            'is_blacklisted' => (bool) $customer->is_blacklisted,
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
            ->with(['category', 'brand', 'images', 'stocks', 'taxClass.rates']);

        if (empty($term)) {
            $products = $query->limit(20)->get();
        } else {
            $products = $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%");
            })
                ->orWhereHas('category', function ($q) use ($term) {
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
                'category' => $product->category->name ?? 'Uncategorized',
                'default_discount_type' => $product->default_discount_type,
                'default_discount_value' => (float) $product->default_discount_value,
                'tax_rate' => $product->tax_rate,
                'tax_class_id' => $product->tax_class_id,
                'tax_class' => $product->taxClass ? [
                    'id' => $product->taxClass->id,
                    'name' => $product->taxClass->name,
                    'rates' => $product->taxClass->rates->map(fn($r) => [
                        'rate' => $r->rate,
                        'name' => $r->name
                    ])
                ] : null,
            ];
        });

        return response()->json($data);
    }

    /**
     * Store or update a customer via AJAX.
     */
    public function storeCustomer(Request $request): JsonResponse
    {
        $this->authorize('customers manage');

        try {
            \Illuminate\Support\Facades\Log::info('Store Customer Request:', $request->all());

            $id = $request->input('id');

            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'display_name' => 'nullable|string|max:255',
                'mobile' => 'required|string|max:20|unique:customers,mobile' . ($id ? ",$id" : ''),
                'phone_number_2' => 'nullable|string|max:20',
                'relative_phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|unique:customers,email' . ($id ? ",$id" : ''),
                'source' => 'nullable|string|max:50', // Fixed max length to match schema
                'category' => 'nullable|string|in:individual,business',
                'type' => 'nullable|in:farmer,buyer,vendor,dealer',
                // Business
                'company_name' => 'nullable|string|max:255',
                'gst_number' => 'nullable|string|max:50',
                'pan_number' => 'nullable|string|max:50',
                // Agriculture
                'land_area' => 'nullable|numeric|min:0',
                'land_unit' => 'nullable|string|in:acre,hectare,guntha',
                'irrigation_type' => 'nullable|string|max:50',
                'crops' => 'nullable|array',
                'crops.*' => 'string|max:50',
                // Financial & KYC
                'credit_limit' => 'nullable|numeric|min:0',
                'credit_valid_till' => 'nullable|date',
                'aadhaar_last4' => 'nullable|digits:4',
                'kyc_completed' => 'boolean',
                'internal_notes' => 'nullable|string',
                'is_active' => 'boolean',
                'is_blacklisted' => 'boolean',
            ]);

            // Fix for DB Constraints where columns are NOT NULL but have defaults
            $validated['credit_limit'] = $validated['credit_limit'] ?? 0;

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
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; // Let Laravel handle validation errors (returns 422 automatically)
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Store Customer Error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422); // Return 422 with message to be visible in frontend
        }
    }

    /**
     * Store or update a customer address via AJAX.
     */
    public function storeAddress(Request $request): JsonResponse
    {
        $this->authorize('customers manage');

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'id' => 'nullable|exists:customer_addresses,id',
            'label' => 'nullable|string|max:50',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'village' => 'nullable|string|max:100',
            'taluka' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'post_office' => 'nullable|string|max:100',
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
    /**
     * Search for ALL orders via AJAX (for returns/create search facility).
     */
    public function allOrders(Request $request): JsonResponse
    {
        $term = (string) $request->input('q', '');

        $query = \App\Models\Order::query()
            ->with(['customer', 'items.product']) // Eager load for display
            ->latest();

        if (!empty($term)) {
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', "%{$term}%")
                    ->orWhereHas('customer', function ($subQ) use ($term) {
                        $subQ->where('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('mobile', 'like', "%{$term}%");
                    });
            });
        }

        $orders = $query->limit(20)->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number ?? 'ORD-' . $order->id,
                'customer_name' => $order->customer->first_name . ' ' . $order->customer->last_name,
                'placed_at' => $order->placed_at ? $order->placed_at->format('d M Y') : $order->created_at->format('d M Y'),
                'grand_total' => (float) $order->grand_total,
                'status' => ucfirst($order->status),
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'product' => $item->product ? [
                            'name' => $item->product->name,
                            'sku' => $item->product->sku,
                            'image_url' => $item->product->image_url,
                        ] : null,
                    ];
                }),
            ];
        });

        return response()->json($orders);
    }

    /**
     * Search for customer orders via AJAX.
     */
    public function customerOrders(Request $request): JsonResponse
    {
        $customerId = $request->input('customer_id');
        if (!$customerId) {
            return response()->json([]);
        }

        $orders = \App\Models\Order::where('customer_id', $customerId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number ?? 'ORD-' . $order->id,
                    'placed_at' => $order->placed_at ? $order->placed_at->format('d M Y') : '-',
                    'grand_total' => (float) $order->grand_total,
                    'status' => ucfirst($order->status),
                    'payment_status' => ucfirst($order->payment_status ?? 'unpaid'),
                    'item_count' => $order->items->count(),
                ];
            });

        return response()->json($orders);
    }
    public function customerActivity(Request $request): JsonResponse
    {
        $customerId = $request->input('customer_id');
        if (!$customerId) {
            return response()->json(['orders' => [], 'interactions' => []]);
        }

        $orders = \App\Models\Order::where('customer_id', $customerId)
            ->with('items.product.images')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'type' => 'order',
                    'order_number' => $order->order_number ?? 'ORD-' . $order->id,
                    'placed_at' => $order->placed_at ? $order->placed_at->format('d M Y, h:i A') : '-',
                    'date' => $order->created_at->toIso8601String(), // For sorting
                    'grand_total' => (float) $order->grand_total,
                    'status' => ucfirst($order->status),
                    'payment_status' => ucfirst($order->payment_status ?? 'unpaid'),
                    'item_count' => $order->items->count(),
                    'items' => $order->items, // Return items for display
                ];
            });

        // Fetch Interactions
        $interactions = \App\Models\CustomerInteraction::where('customer_id', $customerId)
            ->with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($interaction) {
                return [
                    'id' => $interaction->id,
                    'type' => 'interaction',
                    'interaction_type' => $interaction->type, // 'type' column
                    'outcome' => $interaction->outcome,
                    'notes' => $interaction->notes,
                    'created_at' => $interaction->created_at->format('d M Y, h:i A'),
                    'date' => $interaction->created_at->toIso8601String(),
                    'user_name' => $interaction->user->name ?? 'System',
                ];
            });

        return response()->json([
            'orders' => $orders,
            'interactions' => $interactions
        ]);
    }
}
