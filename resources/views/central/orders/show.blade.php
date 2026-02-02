<x-app-layout>
    <div class="flex flex-1 flex-col p-8 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold tracking-tight text-foreground">Order #{{ $order->order_number }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary border border-primary/20">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <p class="text-muted-foreground text-sm">
                    Placed on {{ $order->placed_at->format('F d, Y \a\t h:i A') }}
                </p>
            </div>
            <div class="flex gap-3">
                @if($order->status === 'pending' || $order->status === 'draft')
                    <form action="{{ route('central.orders.update-status', $order) }}" method="POST">
                        @csrf 
                        <input type="hidden" name="action" value="confirm">
                        <x-ui.button type="submit">Confirm Order</x-ui.button>
                    </form>
                @endif

                @if($order->status === 'processing')
                    <x-ui.button onclick="document.getElementById('ship-dialog').showModal()">
                        Ship Order
                    </x-ui.button>
                @endif

                @if($order->shipping_status === 'shipped')
                    <form action="{{ route('central.orders.update-status', $order) }}" method="POST">
                        @csrf 
                        <input type="hidden" name="action" value="deliver">
                        <x-ui.button type="submit" variant="outline">Mark Delivered</x-ui.button>
                    </form>
                @endif

                @if(!in_array($order->status, ['shipped', 'completed', 'cancelled']))
                    <form action="{{ route('central.orders.update-status', $order) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                        @csrf 
                        <input type="hidden" name="action" value="cancel">
                        <x-ui.button type="submit" variant="destructive">Cancel Order</x-ui.button>
                    </form>
                @endif

                <x-ui.button variant="outline" href="{{ route('central.orders.edit', $order) }}">
                    Edit Order
                </x-ui.button>
            </div>
        </div>

        <!-- Ship Dialog -->
        <dialog id="ship-dialog" class="p-6 rounded-lg shadow-xl backdrop:bg-black/50 w-full max-w-md">
            <form action="{{ route('central.orders.update-status', $order) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="action" value="ship">
                <h3 class="text-lg font-bold">Ship Order</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tracking Number</label>
                    <input type="text" name="tracking_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('ship-dialog').close()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Ship It</button>
                </div>
            </form>
        </dialog>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Items & Timeline -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Order Items -->
                <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-border">
                        <h3 class="text-lg font-semibold">Order Items</h3>
                    </div>
                    <div class="relative w-full overflow-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border/50 bg-muted/40">
                                    <th class="h-10 px-4 text-left font-medium text-muted-foreground">Product</th>
                                    <th class="h-10 px-4 text-left font-medium text-muted-foreground">SKU</th>
                                    <th class="h-10 px-4 text-right font-medium text-muted-foreground">Price</th>
                                    <th class="h-10 px-4 text-right font-medium text-muted-foreground">Qty</th>
                                    <th class="h-10 px-4 text-right font-medium text-muted-foreground">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr class="border-b border-border/40 last:border-0 hover:bg-muted/10">
                                    <td class="p-4 align-middle font-medium">{{ $item->product_name }}</td>
                                    <td class="p-4 align-middle text-muted-foreground">{{ $item->sku }}</td>
                                    <td class="p-4 align-middle text-right">Rs {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="p-4 align-middle text-right">{{ $item->quantity }}</td>
                                    <td class="p-4 align-middle text-right font-semibold">
                                        <div class="flex flex-col items-end">
                                            @if($item->discount_amount > 0)
                                                <span class="text-[10px] text-muted-foreground line-through">Rs {{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                                                <span class="text-primary">Rs {{ number_format(($item->unit_price * $item->quantity) - $item->discount_amount, 2) }}</span>
                                            @else
                                                <span>Rs {{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Financial Summary (Mobile only, desktop is sidebar) -->
                <div class="lg:hidden rounded-xl border border-border bg-card text-card-foreground shadow-sm p-6">
                   <!-- ... responsive content ... -->
                </div>
            </div>

            <!-- Right Column: Sidebar -->
            <div class="space-y-6">
                <!-- Customer Details -->
                <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm p-6">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Customer Details
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Name</span>
                            <span class="font-medium text-right">{{ $order->customer->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Email</span>
                            <a href="mailto:{{ $order->customer->email }}" class="text-primary hover:underline text-right truncate max-w-[150px]">{{ $order->customer->email }}</a>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Phone</span>
                            <span class="text-right">{{ $order->customer->phone ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Shipping & Billing -->
                <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm p-6">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Addresses
                    </h3>
                    <div class="space-y-4 text-sm">
                        <div>
                            <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Billing Address</span>
                            <div class="mt-1 text-foreground/80 leading-relaxed">
                                @if($order->billingAddress)
                                    {{ $order->billingAddress->address_line1 }}<br>
                                    @if($order->billingAddress->address_line2) {{ $order->billingAddress->address_line2 }}<br> @endif
                                    {{ $order->billingAddress->village }}, {{ $order->billingAddress->state }} - {{ $order->billingAddress->pincode }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="h-px bg-border/50"></div>
                        <div>
                            <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Shipping Address</span>
                            <div class="mt-1 text-foreground/80 leading-relaxed">
                                @if($order->shippingAddress)
                                    {{ $order->shippingAddress->address_line1 }}<br>
                                    @if($order->shippingAddress->address_line2) {{ $order->shippingAddress->address_line2 }}<br> @endif
                                    {{ $order->shippingAddress->village }}, {{ $order->shippingAddress->state }} - {{ $order->shippingAddress->pincode }}
                                @else
                                    {{ $order->warehouse->name }} (Warehouse Pick)
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm p-6 bg-muted/20">
                    <h3 class="font-semibold mb-4">Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>Rs {{ number_format($order->total_amount, 2) }}</span>
                        </div>
                        @php 
                            $itemDiscounts = $order->items->sum('discount_amount');
                            $orderDiscount = $order->discount_amount - $itemDiscounts;
                        @endphp
                        @if($itemDiscounts > 0)
                        <div class="flex justify-between text-primary">
                            <span class="">Item Discounts</span>
                            <span>- Rs {{ number_format($itemDiscounts, 2) }}</span>
                        </div>
                        @endif
                        @if($orderDiscount > 0)
                        <div class="flex justify-between text-primary">
                            <span class="">Order Discount</span>
                            <span>- Rs {{ number_format($orderDiscount, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Shipping</span>
                            <span>Rs {{ number_format($order->shipping_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Tax</span>
                            <span>Rs {{ number_format($order->tax_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="h-px bg-border my-2"></div>
                        <div class="flex justify-between font-bold text-lg text-primary">
                            <span>Total</span>
                            <span>Rs {{ number_format($order->grand_total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Order History Tracking -->
                <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm p-6">
                    <h3 class="font-semibold mb-4 flex items-center gap-2 text-sm uppercase tracking-wider text-muted-foreground">
                        Order Heritage
                    </h3>
                    <div class="space-y-4 text-xs">
                        <div class="flex items-start gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-green-500 mt-1.5"></div>
                            <div>
                                <p class="font-bold">Created</p>
                                <p class="text-muted-foreground">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                                <p class="mt-0.5"><span class="text-muted-foreground">By:</span> <span class="font-medium text-foreground">{{ $order->creator->name ?? 'System' }}</span></p>
                            </div>
                        </div>

                        @if($order->updated_at > $order->created_at && $order->updated_by)
                        <div class="flex items-start gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5"></div>
                            <div>
                                <p class="font-bold">Last Updated</p>
                                <p class="text-muted-foreground">{{ $order->updated_at->format('M d, Y h:i A') }}</p>
                                <p class="mt-0.5"><span class="text-muted-foreground">By:</span> <span class="font-medium text-foreground">{{ $order->updater->name ?? 'System' }}</span></p>
                            </div>
                        </div>
                        @endif

                        @if($order->status === 'cancelled' && $order->cancelled_by)
                        <div class="flex items-start gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-red-500 mt-1.5"></div>
                            <div>
                                <p class="font-bold text-red-600">Cancelled</p>
                                <p class="text-muted-foreground">{{ $order->cancelled_at ? $order->cancelled_at->format('M d, Y h:i A') : $order->updated_at->format('M d, Y h:i A') }}</p>
                                <p class="mt-0.5"><span class="text-muted-foreground">By:</span> <span class="font-medium text-foreground">{{ $order->canceller->name ?? 'System' }}</span></p>
                            </div>
                        </div>
                        @endif

                        @if($order->status === 'completed' && $order->completed_by)
                        <div class="flex items-start gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-primary mt-1.5"></div>
                            <div>
                                <p class="font-bold text-primary">Completed</p>
                                <p class="text-muted-foreground">{{ $order->updated_at->format('M d, Y h:i A') }}</p>
                                <p class="mt-0.5"><span class="text-muted-foreground">By:</span> <span class="font-medium text-foreground">{{ $order->completer->name ?? 'System' }}</span></p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
