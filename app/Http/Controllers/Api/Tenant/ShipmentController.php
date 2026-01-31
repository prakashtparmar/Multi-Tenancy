<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index()
    {
        return response()->json(Shipment::with('order')->latest()->paginate(20));
    }

    public function show(Shipment $shipment)
    {
        return response()->json($shipment->load(['order.items', 'warehouse']));
    }
}
