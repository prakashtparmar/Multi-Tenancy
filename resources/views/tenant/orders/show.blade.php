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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="px-6 py-4">{{ $item->product->name ?? 'Product #'.$item->product_id }}</td>
                                    <td class="px-6 py-4">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-6 py-4">${{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-bold">
                                <td colspan="3" class="px-6 py-4 text-right">Total:</td>
                                <td class="px-6 py-4">${{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="flex gap-4">
                        <a href="{{ route('tenant.orders.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
