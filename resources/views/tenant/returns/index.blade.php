<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Returns (RMA)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-lg font-bold">RMA Requests</h3>
                        <a href="{{ route('central.returns.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create Return
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">RMA #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($returns as $rma)
                                    <tr>
                                        <td class="px-6 py-4 font-bold">{{ $rma->rma_number }}</td>
                                        <td class="px-6 py-4">#{{ $rma->order->order_number ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ $rma->customer->first_name ?? 'Guest' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $rma->status === 'requested' ? 'bg-yellow-100 text-yellow-800' : 
                                                  ($rma->status === 'approved' ? 'bg-blue-100 text-blue-800' : 
                                                  ($rma->status === 'received' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                                {{ ucfirst($rma->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('tenant.returns.show', $rma) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-4 text-center">No returns found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4">{{ $returns->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
