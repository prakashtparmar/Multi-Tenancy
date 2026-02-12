<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderVerification;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderVerificationController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): View
    {
        $this->authorize('orders view');

        // Default to 'unverified' if no status is provided
        $status = $request->input('status', 'unverified');

        $query = Order::with(['customer', 'items', 'verifications.user', 'billingAddress', 'shippingAddress'])
            ->latest();

        // Apply Status Filter
        if ($status === 'unverified') {
            $query->where(function ($q) {
                $q->where('verification_status', 'unverified')
                    ->orWhereNull('verification_status');
            });
        } elseif ($status === 'pending_followup') {
            $query->where('verification_status', 'pending_followup');
        } elseif ($status === 'verified') {
            $query->where('verification_status', 'verified');
        } elseif ($status === 'cancelled') {
            $query->where('status', 'cancelled');
        } elseif ($status === 'all') {
            // No filter applied for 'all'
        }

        if (!auth()->user()->hasRole('Super Admin')) {
            $query->where('created_by', auth()->id());
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('grand_total', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($i) use ($search) {
                        $i->where('product_name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->paginate($request->get('per_page', 10))->withQueryString();

        return view('tenant.orders.verification.index', compact('orders'));
    }

    public function store(Request $request, Order $order)
    {
        $this->authorize('orders edit');

        $validated = $request->validate([
            'status' => 'required|in:verified,pending_followup,rejected',
            'remarks' => 'required|string',
            'next_followup_at' => 'nullable|date|after:now',
        ]);

        try {
            DB::transaction(function () use ($validated, $order) {
                // Create verification entry
                OrderVerification::create([
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'status' => $validated['status'],
                    'remarks' => $validated['remarks'],
                    'next_followup_at' => $validated['next_followup_at'] ?? null,
                ]);

                // Update order verification status
                $order->update([
                    'verification_status' => $validated['status']
                ]);

                // If verified, we might want to automatically confirm the order if it's pending
                if ($validated['status'] === 'verified' && $order->status === 'pending') {
                    $this->orderService->confirmOrder($order);
                }

                // If rejected, we might want to cancel the order
                if ($validated['status'] === 'rejected' && $order->status !== 'cancelled') {
                    $this->orderService->cancelOrder($order);
                }
            });

            return redirect()->route('tenant.orders.verification.index')
                ->with('success', 'Order verification updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error updating verification: ' . $e->getMessage());
        }
    }
}
