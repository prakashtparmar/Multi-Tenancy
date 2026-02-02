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

                    <h3 class="text-lg font-bold mb-4">Items</h3>
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

                    <div class="flex gap-4">
                        <a href="{{ route('tenant.orders.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded shadow hover:bg-gray-600 transition">Back to List</a>
                        @if($order->status !== 'completed' && $order->status !== 'cancelled')
                            <a href="{{ route('tenant.orders.edit', $order) }}" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 transition">Edit Order</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
