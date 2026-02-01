<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShipmentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the shipments.
     */
    public function index(): JsonResponse
    {
        $this->authorize('orders view');

        $shipments = Shipment::with(['order.customer', 'warehouse'])->latest()->paginate(20);
        
        return response()->json($shipments);
    }

    /**
     * Display the specified shipment.
     */
    public function show(Shipment $shipment): JsonResponse
    {
        $this->authorize('orders view');

        return response()->json($shipment->load(['order.items', 'warehouse']));
    }
}
