<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Shipment Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
             <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6 border-b pb-4">
                        <div>
                            <h3 class="text-xl font-bold">Tracking: {{ $shipment->tracking_number ?? 'N/A' }}</h3>
                            <p class="text-gray-600">Carrier: {{ $shipment->carrier }}</p>
                            @if($shipment->shipped_at)
                                <p class="text-sm text-gray-500">Shipped: {{ $shipment->shipped_at->format('M d, Y H:i') }}</p>
                            @endif
                            @if($shipment->delivered_at)
                                <p class="text-sm text-green-600 font-bold">Delivered: {{ $shipment->delivered_at->format('M d, Y H:i') }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                             <h4 class="font-bold">Order #{{ $shipment->order->order_number }}</h4>
                             <p>{{ $shipment->order->customer->first_name ?? '' }} {{ $shipment->order->customer->last_name ?? '' }}</p>
                              <span class="mt-2 inline-flex px-3 py-1 rounded-full text-sm font-bold 
                                {{ $shipment->status === 'delivered' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ strtoupper($shipment->status) }}
                            </span>
                        </div>
                    </div>

                    <h4 class="font-bold mb-2">Items in Shipment</h4>
                    <table class="min-w-full divide-y divide-gray-200 border mb-6">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">Product</th>
                                <th class="px-6 py-3 text-left">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shipment->order->items as $item)
                                <tr>
                                    <td class="px-6 py-4">{{ $item->product_name ?? $item->sku }}</td>
                                    <td class="px-6 py-4">{{ $item->quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <div class="border-t pt-4 flex justify-between items-center">
                        <a href="{{ route('central.shipments.index') }}" class="text-gray-500 hover:underline">&larr; Back to Shipments</a>

                        @if($shipment->status !== 'delivered')
                            <form action="{{ route('central.shipments.update-status', $shipment) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="delivered">
                                <button type="submit" class="bg-green-600 text-white font-bold py-2 px-4 rounded hover:bg-green-700" 
                                    onclick="return confirm('Mark as Delivered?')">
                                    Mark Delivered
                                </button>
                            </form>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
