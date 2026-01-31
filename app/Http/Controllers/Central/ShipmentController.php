<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Order;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Shipment::with(['order.customer', 'warehouse']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('tracking_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function($q) use ($search) {
                      $q->where('order_number', 'like', "%{$search}%");
                  });
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 10);
        $shipments = $query->latest()->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('central.shipments.index', compact('shipments'))->render();
        }

        return view('central.shipments.index', compact('shipments'));
    }

    public function create()
    {
        // Orders that are confirmed or processing, but not yet fully shipped
        $orders = Order::whereIn('status', ['confirmed', 'processing'])
            ->where('shipping_status', '!=', 'shipped')
            ->latest()
            ->get();
            
        $warehouses = Warehouse::where('is_active', true)->get();
        
        return view('central.shipments.create', compact('orders', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'carrier' => 'required|string',
            'tracking_number' => 'nullable|string',
            'weight' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($request) {
            $shipment = Shipment::create([
                'order_id' => $request->order_id,
                'warehouse_id' => $request->warehouse_id,
                'carrier' => $request->carrier,
                'tracking_number' => $request->tracking_number,
                'weight' => $request->weight,
                'status' => 'shipped',
                'shipped_at' => now(),
            ]);

            // Update Order Status
            $order = Order::find($request->order_id);
            $order->update([
                'shipping_status' => 'shipped',
                'status' => 'shipped' // Assuming full shipment for simplicity
            ]);
            
            // In a full WMS, we would decrement stock here based on order items
            // foreach($order->items as $item) { ... decrement stock ... }
        });

        return redirect()->route('central.shipments.index')->with('success', 'Shipment created and Order updated.');
    }

    public function show(Shipment $shipment)
    {
        $shipment->load(['order.items', 'order.customer', 'warehouse']);
        return view('central.shipments.show', compact('shipment'));
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $request->validate(['status' => 'required|string']);
        
        $shipment->update(['status' => $request->status]);
        
        if ($request->status === 'delivered') {
            $shipment->update(['delivered_at' => now()]);
            $shipment->order->update([
                'shipping_status' => 'delivered',
                'status' => 'delivered'
            ]);
        }

        return back()->with('success', 'Shipment status updated.');
    }
}
