<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer', 'warehouse'])->latest()->paginate(10);
        return view('tenant.orders.index', compact('orders'));
    }

    public function create()
    {
        $customers = Customer::all();
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('tenant.orders.create', compact('customers', 'warehouses'));
    }

    public function store(Request $request)
    {
        // Complex logic: Create Order -> Add Items -> Reservce Stock
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_number' => 'required|unique:orders,order_number',
        ]);

        $order = Order::create($validated);

        return redirect()->route('orders.show', $order)->with('success', 'Order created successfully.');
    }

    public function show(Order $order)
    {
        $order->load(['items', 'invoices', 'shipments']);
        return view('tenant.orders.show', compact('order'));
    }
}
