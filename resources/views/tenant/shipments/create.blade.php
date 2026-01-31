<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Shipment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('tenant.shipments.store') }}" method="POST" class="p-6 text-gray-900">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Order to Fulfill (Confirmed/Processing)</label>
                        <select name="order_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Select Order</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}">
                                    Order #{{ $order->order_number }} - {{ $order->customer->first_name ?? 'Guest' }} ({{ $order->created_at->format('Y-m-d') }})
                                </option>
                            @endforeach
                        </select>
                         <p class="text-xs text-gray-500 mt-1">Only orders pending shipment are shown.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Warehouse</label>
                            <select name="warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Carrier</label>
                             <select name="carrier" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="FedEx">FedEx</option>
                                <option value="UPS">UPS</option>
                                <option value="USPS">USPS</option>
                                <option value="DHL">DHL</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                         <div>
                            <label class="block text-sm font-medium text-gray-700">Tracking Number</label>
                            <input type="text" name="tracking_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                            <input type="number" step="0.01" name="weight" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t">
                        <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-700">Create Shipment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
