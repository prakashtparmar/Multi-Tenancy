<?php

use App\Models\Order;
use App\Models\User;
use App\Models\OrderVerification;
use Illuminate\Support\Facades\Auth;

// 1. Setup Context
$user = User::first();
Auth::login($user);
echo "Logged in as: " . $user->name . "\n";

// 2. Create a Test Order
$order = Order::create([
    'order_number' => 'ORD-TEST-' . time(),
    'customer_id' => 1, // Assuming customer 1 exists, or nullable
    'total_amount' => 1000,
    'grand_total' => 1000,
    'status' => 'pending',
    'payment_status' => 'unpaid',
    'shipping_status' => 'pending',
    'verification_status' => 'unverified', // Default
]);

echo "Created Order: " . $order->order_number . " with status: " . $order->status . " and verification: " . $order->verification_status . "\n";

// 3. Simulate "Pending Follow-up"
echo "\n--- Test 1: Pending Follow-up ---\n";
// Resolve controller from container to inject dependencies
$controller = app(\App\Http\Controllers\Central\OrderVerificationController::class);
$request = new \Illuminate\Http\Request();
$request->merge([
    'status' => 'pending_followup',
    'remarks' => 'Customer busy, call later.',
    'next_followup_at' => now()->addDay()->toDateTimeString(),
]);

$controller->store($request, $order);

$order->refresh();
echo "Order Verification Status: " . $order->verification_status . "\n";
echo "Latest Verification Remark: " . $order->verifications()->latest()->first()->remarks . "\n";

if ($order->verification_status === 'pending_followup') {
    echo "PASS: Status updated to pending_followup.\n";
} else {
    echo "FAIL: Status not updated.\n";
}

// 4. Simulate "Verified" -> Should Confirm Order
echo "\n--- Test 2: Verify & Confirm ---\n";
$request = new \Illuminate\Http\Request();
$request->merge([
    'status' => 'verified',
    'remarks' => 'Customer confirmed details.',
]);

$controller->store($request, $order);

$order->refresh();
echo "Order Verification Status: " . $order->verification_status . "\n";
echo "Order Status: " . $order->status . "\n";

if ($order->verification_status === 'verified' && $order->status === 'confirmed') {
    echo "PASS: Order verified and status updated to confirmed.\n";
} else {
    echo "FAIL: Order not verified or status not confirmed.\n";
}

// 5. Cleanup
$order->delete();
echo "\nTest cleanup done.\n";
