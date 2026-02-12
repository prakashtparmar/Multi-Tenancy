<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order Details') }} - {{ $order->order_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h3 class="text-lg font-bold mb-2">Customer Info</h3>
                            <p><strong>Name:</strong> {{ $order->customer->first_name }} {{ $order->customer->last_name }}</p>
                            <p><strong>Email:</strong> {{ $order->customer->email }}</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold mb-2">Order Info</h3>
                            <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>
                            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                            <p><strong>Warehouse:</strong> {{ $order->warehouse->name }}</p>
                        </div>
                    </div>

                    <!-- Order Progress Stepper -->
                    <div class="mb-10 p-6 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="relative flex justify-between items-center w-full">
                            <div class="absolute left-0 top-5 w-full h-1 bg-gray-200 z-0"></div>
                            <div class="absolute left-0 top-5 h-1 bg-blue-600 z-0 transition-all duration-1000" style="width: {{ $order->status === 'delivered' ? '100%' : ($order->status === 'shipped' ? '66%' : ($order->status === 'confirmed' || $order->status === 'processing' ? '33%' : '0%')) }}"></div>

                            <!-- Step 1: Placed -->
                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-600 text-white shadow-md ring-4 ring-white">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="mt-2 text-xs font-bold text-gray-900">Placed</span>
                                <span class="text-[10px] text-gray-500">{{ $order->created_at->format('M d') }}</span>
                            </div>

                            <!-- Step 2: Confirmed -->
                            @php $isConfirmed = in_array($order->status, ['confirmed', 'processing', 'shipped', 'delivered', 'completed']); @endphp
                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $isConfirmed ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-400' }} shadow-md ring-4 ring-white transition-colors duration-500">
                                    @if($isConfirmed)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        <span class="text-sm font-bold">2</span>
                                    @endif
                                </div>
                                <span class="mt-2 text-xs font-bold {{ $isConfirmed ? 'text-gray-900' : 'text-gray-400' }}">Confirmed</span>
                            </div>

                            <!-- Step 3: Shipped -->
                            @php $isShipped = in_array($order->status, ['shipped', 'delivered', 'completed']); @endphp
                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $isShipped ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-400' }} shadow-md ring-4 ring-white transition-colors duration-500">
                                    @if($isShipped)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        <span class="text-sm font-bold">3</span>
                                    @endif
                                </div>
                                <span class="mt-2 text-xs font-bold {{ $isShipped ? 'text-gray-900' : 'text-gray-400' }}">Shipped</span>
                                @if($isShipped && $order->shipments->isNotEmpty())
                                    <span class="text-[10px] text-gray-500">{{ $order->shipments->first()->shipped_at ? $order->shipments->first()->shipped_at->format('M d') : '' }}</span>
                                @endif
                            </div>

                            <!-- Step 4: Delivered -->
                            @php $isDelivered = in_array($order->status, ['delivered', 'completed', 'delivered']); @endphp
                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $isDelivered ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-400' }} shadow-md ring-4 ring-white transition-colors duration-500">
                                    @if($isDelivered)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        <span class="text-sm font-bold">4</span>
                                    @endif
                                </div>
                                <span class="mt-2 text-xs font-bold {{ $isDelivered ? 'text-gray-900' : 'text-gray-400' }}">Delivered</span>
                            </div>
                        </div>

                        <!-- Tracking Info Box -->
                        @if($order->shipments->isNotEmpty())
                            <div class="mt-10 p-4 bg-white rounded-lg border border-gray-200 flex flex-wrap gap-8 items-center shadow-sm">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Carrier</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $order->shipments->first()->carrier ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tracking ID</p>
                                    <p class="text-sm font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">{{ $order->shipments->first()->tracking_number ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Shipped At</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $order->shipments->first()->shipped_at ? $order->shipments->first()->shipped_at->format('M d, Y h:i A') : 'N/A' }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 border mb-8">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="px-6 py-4">{{ $item->product->name ?? 'Product #'.$item->product_id }}</td>
                                    <td class="px-6 py-4">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4">Rs {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-6 py-4">
                                        @if($item->discount_amount > 0)
                                            <span class="text-green-600">
                                                - Rs {{ number_format($item->discount_amount, 2) }}
                                                @if($item->discount_type == 'percent')
                                                    <span class="text-xs text-gray-500">({{ $item->discount_value }}%)</span>
                                                @endif
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">Rs {{ number_format(($item->quantity * $item->unit_price) - $item->discount_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-medium">
                                <td colspan="4" class="px-6 py-2 text-right text-gray-500">Subtotal:</td>
                                <td class="px-6 py-2">Rs {{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                            @php 
                                $itemDiscounts = $order->items->sum('discount_amount');
                                $orderDiscount = $order->discount_amount - $itemDiscounts;
                            @endphp
                            @if($itemDiscounts > 0)
                            <tr class="bg-gray-50 font-medium">
                                <td colspan="4" class="px-6 py-2 text-right text-green-600">Item Discounts:</td>
                                <td class="px-6 py-2 text-green-600">- Rs {{ number_format($itemDiscounts, 2) }}</td>
                            </tr>
                            @endif
                            @if($orderDiscount > 0)
                            <tr class="bg-gray-50 font-medium">
                                <td colspan="4" class="px-6 py-2 text-right text-green-600">
                                    Order Discount 
                                    @if($order->discount_type == 'percent')
                                        <span class="text-xs">({{ $order->discount_value }}%)</span>
                                    @endif
                                :</td>
                                <td class="px-6 py-2 text-green-600">- Rs {{ number_format($orderDiscount, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="bg-gray-50 font-bold text-lg">
                                <td colspan="4" class="px-6 py-4 text-right">Grand Total:</td>
                                <td class="px-6 py-4 text-blue-600">Rs {{ number_format($order->grand_total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="flex flex-wrap gap-4 mt-8" x-data="{ showShipModal: false }">
                        <a href="{{ route('tenant.orders.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded shadow hover:bg-gray-600 transition text-sm">Back to List</a>
                        
                        @can('orders edit')
                            @if(!in_array($order->status, ['completed', 'delivered', 'cancelled', 'returned']))
                                <a href="{{ route('tenant.orders.edit', $order) }}" class="bg-blue-800 text-white px-4 py-2 rounded shadow hover:bg-blue-900 transition text-sm">Edit Order</a>
                            @endif
                        @endcan

                        @if(in_array($order->status, ['completed', 'delivered']))
                             <a href="{{ route('tenant.returns.create', ['order_id' => $order->id]) }}" class="bg-orange-600 text-white px-4 py-2 rounded shadow hover:bg-orange-700 transition text-sm">
                                Request Return
                            </a>
                        @endif
                        
                        <!-- Print Actions -->
                        <a href="{{ route('tenant.orders.invoice', $order) }}" target="_blank" class="bg-gray-800 text-white px-4 py-2 rounded shadow hover:bg-gray-900 transition flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Invoice
                        </a>
                        <a href="{{ route('tenant.orders.receipt', $order) }}" target="_blank" class="bg-gray-800 text-white px-4 py-2 rounded shadow hover:bg-gray-900 transition flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            COD Receipt
                        </a>

                        <!-- State Transitions -->
                        @if($order->status === 'pending')
                            @can('orders approve')
                                <form action="{{ route('tenant.orders.status', $order) }}" method="POST" onsubmit="return confirm('Confirm this order?')">
                                    @csrf
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">Mark Confirmed</button>
                                </form>
                            @endcan
                        @endif

                        @if($order->status === 'confirmed' || $order->status === 'processing')
                            @can('orders process')
                                <button @click="showShipModal = true" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 transition">Ship Order</button>
                            @endcan
                        @endif

                        @if($order->status === 'shipped')
                            @can('orders deliver')
                                <form action="{{ route('tenant.orders.status', $order) }}" method="POST" onsubmit="return confirm('Mark as Delivered?')">
                                    @csrf
                                    <input type="hidden" name="status" value="delivered">
                                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700 transition">Mark Delivered</button>
                                </form>
                            @endcan
                        @endif

                        @if(!in_array($order->status, ['cancelled', 'delivered', 'returned']))
                            @can('orders cancel')
                                <form action="{{ route('tenant.orders.status', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                    @csrf
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded shadow hover:bg-red-700 transition">Cancel Order</button>
                                </form>
                            @endcan
                        @endif

                        <!-- Shipping Modal -->
                        <div x-show="showShipModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showShipModal = false"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <form action="{{ route('tenant.orders.status', $order) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="shipped">
                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Ship Order</h3>
                                            <div class="mt-4 space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Courier / Carrier</label>
                                                    <input type="text" name="carrier" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. FedEx, Local">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Tracking Number</label>
                                                    <input type="text" name="tracking_number" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                                Confirm Shipment
                                            </button>
                                            <button type="button" @click="showShipModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
