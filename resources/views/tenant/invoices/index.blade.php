<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Invoices') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-lg font-bold">Invoice List</h3>
                        <!-- Optional: Bulk Create or Filter -->
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($invoices as $inv)
                                    <tr>
                                        <td class="px-6 py-4 font-bold">{{ $inv->invoice_number }}</td>
                                        <td class="px-6 py-4">#{{ $inv->order->order_number ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ $inv->issue_date->format('Y-m-d') }}</td>
                                        <td class="px-6 py-4">{{ $inv->order->customer->first_name ?? '-' }}</td>
                                        <td class="px-6 py-4 font-mono">${{ number_format($inv->total_amount, 2) }}</td>
                                        <td class="px-6 py-4 font-mono">${{ number_format($inv->paid_amount, 2) }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $inv->status === 'paid' ? 'bg-green-100 text-green-800' : ($inv->status === 'partial' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($inv->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('tenant.invoices.show', $inv) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="px-6 py-4 text-center">No invoices found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4">{{ $invoices->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
