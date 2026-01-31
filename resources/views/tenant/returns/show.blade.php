<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('RMA Details') }} - {{ $orderReturn->rma_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-bold">Return Request</h3>
                            <p class="text-gray-600">Order: #{{ $orderReturn->order->order_number }}</p>
                            <p class="text-gray-600">Customer: {{ $orderReturn->customer->first_name ?? 'Guest' }}</p>
                             <p class="mt-2 text-gray-800"><span class="font-bold">Reason:</span> {{ $orderReturn->reason }}</p>
                        </div>
                        <div class="text-right">
                             <span class="px-3 py-1 rounded-full text-sm font-bold 
                                {{ $orderReturn->status === 'requested' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($orderReturn->status === 'approved' ? 'bg-blue-100 text-blue-800' : 
                                    ($orderReturn->status === 'received' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ strtoupper($orderReturn->status) }}
                            </span>
                        </div>
                    </div>

                    <h4 class="font-bold mb-2">Items to Return</h4>
                    <table class="min-w-full divide-y divide-gray-200 border mb-6">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">Product</th>
                                <th class="px-6 py-3 text-left">Qty</th>
                                <th class="px-6 py-3 text-left">Condition</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orderReturn->items as $item)
                                <tr>
                                    <td class="px-6 py-4">{{ $item->product->name ?? 'Product' }}</td>
                                    <td class="px-6 py-4">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 text-xs rounded-full {{ $item->condition === 'sellable' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                            {{ ucfirst($item->condition) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Workflow Actions -->
                    <div class="border-t pt-4 flex justify-end gap-3">
                        <a href="{{ route('tenant.returns.index') }}" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50">Back</a>

                        @if($orderReturn->status === 'requested')
                            <form action="{{ route('tenant.returns.update-status', $orderReturn) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">Approve Request</button>
                            </form>
                            <form action="{{ route('tenant.returns.update-status', $orderReturn) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="bg-red-600 text-white font-bold py-2 px-4 rounded hover:bg-red-700">Reject</button>
                            </form>
                        @endif

                        @if($orderReturn->status === 'approved')
                            <form action="{{ route('tenant.returns.update-status', $orderReturn) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="received">
                                <button type="submit" class="bg-green-600 text-white font-bold py-2 px-4 rounded hover:bg-green-700"
                                    onclick="return confirm('Confirm items received? Valid sellable items will return to stock.')">
                                    Mark Received & Restock
                                </button>
                            </form>
                        @endif
                        
                        @if($orderReturn->status === 'received')
                             <form action="{{ route('tenant.returns.update-status', $orderReturn) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="refunded">
                                <button type="submit" class="bg-purple-600 text-white font-bold py-2 px-4 rounded hover:bg-purple-700">
                                    Issue Refund
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
