<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrderReturnController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the order returns.
     */
    public function index(): JsonResponse
    {
        $this->authorize('orders view');

        $returns = OrderReturn::with(['order.customer'])->latest()->paginate(20);
        
        return response()->json($returns);
    }

    /**
     * Display the specified order return.
     */
    public function show(OrderReturn $orderReturn): JsonResponse
    {
        $this->authorize('orders view');

        return response()->json($orderReturn->load(['items', 'order']));
    }
}
