<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('PO Details') }} - {{ $purchaseOrder->po_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-2xl font-bold">{{ $purchaseOrder->supplier->company_name }}</h3>
                            <p class="text-gray-600">Expected: {{ $purchaseOrder->expected_date ? $purchaseOrder->expected_date->format('Y-m-d') : 'N/A' }}</p>
                            <p class="text-gray-600">Destination: {{ $purchaseOrder->warehouse->name }}</p>
                        </div>
                        <div class="text-right">
                            <span class="block px-3 py-1 rounded-full text-sm font-bold 
                                {{ $purchaseOrder->status === 'received' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ strtoupper($purchaseOrder->status) }}
                            </span>
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200 border mb-6">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">Product</th>
                                <th class="px-6 py-3 text-left">Ordered</th>
                                <th class="px-6 py-3 text-left">Received</th>
                                <th class="px-6 py-3 text-left">Unit Cost</th>
                                <th class="px-6 py-3 text-left">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="px-6 py-4">{{ $item->product->name }}</td>
                                    <td class="px-6 py-4">{{ $item->quantity_ordered }}</td>
                                    <td class="px-6 py-4">{{ $item->quantity_received }}</td>
                                    <td class="px-6 py-4">${{ number_format($item->unit_cost, 2) }}</td>
                                    <td class="px-6 py-4">${{ number_format($item->total_cost, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-bold">
                                <td colspan="4" class="px-6 py-4 text-right">Grand Total:</td>
                                <td class="px-6 py-4">${{ number_format($purchaseOrder->total_cost, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="flex justify-end gap-4">
                        @if($purchaseOrder->status !== 'received')
                            <form action="{{ route('tenant.purchase-orders.receive', $purchaseOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-green-600 text-white font-bold py-2 px-6 rounded hover:bg-green-700"
                                    onclick="return confirm('Are you sure? This will add stock to {{ $purchaseOrder->warehouse->name }}.')">
                                    Receive Goods
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('tenant.purchase-orders.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded flex items-center">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
