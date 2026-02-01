<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Order;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the shipments.
     */
    public function index(): View
    {
        $this->authorize('orders view');
        
        $shipments = Shipment::with(['order.customer', 'warehouse'])->latest()->paginate(10);
        return view('tenant.shipments.index', compact('shipments'));
    }

    /**
     * Show the form for creating a new shipment.
     */
    public function create(): View
    {
        $this->authorize('orders manage');
        
        $orders = Order::whereIn('status', ['confirmed', 'processing'])
            ->where('shipping_status', '!=', 'shipped')
            ->latest()
            ->get();
            
        $warehouses = Warehouse::where('is_active', true)->get();
        
        return view('tenant.shipments.create', compact('orders', 'warehouses'));
    }

    /**
     * Store a newly created shipment in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('orders manage');

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'carrier' => 'required|string',
            'tracking_number' => 'nullable|string',
            'weight' => 'nullable|numeric',
        ]);

        try {
            DB::transaction(function () use ($request) {
                Shipment::create([
                    'order_id' => $request->order_id,
                    'warehouse_id' => $request->warehouse_id,
                    'carrier' => $request->carrier,
                    'tracking_number' => $request->tracking_number,
                    'weight' => $request->weight,
                    'status' => 'shipped',
                    'shipped_at' => now(),
                ]);

                $order = Order::findOrFail($request->order_id);
                $order->update([
                    'shipping_status' => 'shipped',
                    'status' => 'shipped'
                ]);
            });

            return redirect()->route('tenant.shipments.index')->with('success', 'Shipment created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create shipment.');
        }
    }

    /**
     * Display the specified shipment.
     */
    public function show(Shipment $shipment): View
    {
        $this->authorize('orders view');
        
        $shipment->load(['order.items', 'order.customer', 'warehouse']);
        return view('tenant.shipments.show', compact('shipment'));
    }

    /**
     * Update the status of the shipment.
     */
    public function updateStatus(Request $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('orders manage');

        $request->validate(['status' => 'required|string']);
        
        try {
            DB::transaction(function () use ($request, $shipment) {
                $shipment->update(['status' => $request->status]);
                
                if ($request->status === 'delivered') {
                    $shipment->update(['delivered_at' => now()]);
                    $shipment->order->update([
                        'shipping_status' => 'delivered',
                        'status' => 'delivered'
                    ]);
                }
            });

            return back()->with('success', 'Shipment status updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update shipment status.');
        }
    }
}
