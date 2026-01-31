<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Simple filtering
        $query = Order::with(['customer', 'items']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function show(Order $order)
    {
        return response()->json($order->load(['customer', 'items', 'invoices', 'shipments']));
    }
    
    // Store method can be added if mobile app supports creating orders
}
