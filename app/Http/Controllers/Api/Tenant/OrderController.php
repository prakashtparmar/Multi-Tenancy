<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrderController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the orders.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('orders view');

        $query = Order::with(['customer', 'items']);
        
        if ($request->filled('status')) {
            $query->where('status', (string) $request->status);
        }

        return response()->json($query->latest()->paginate(20));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('orders view');

        return response()->json($order->load(['customer', 'items', 'invoices', 'shipments']));
    }
}
