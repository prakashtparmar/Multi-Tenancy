<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Invoice') }} {{ $invoice->invoice_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Invoice Details -->
                <div class="col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                         <div class="flex justify-between items-start border-b pb-4 mb-4">
                            <div>
                                <h3 class="text-2xl font-bold mb-1">INVOICE</h3>
                                <p class="text-gray-500">#{{ $invoice->invoice_number }}</p>
                                <p class="text-sm text-gray-500">Issued: {{ $invoice->issue_date->format('Y-m-d') }}</p>
                                <p class="text-sm text-gray-500">Due: {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'On Receipt' }}</p>
                            </div>
                            <div class="text-right">
                                <h4 class="font-bold text-lg">{{ $invoice->order->customer->first_name ?? 'Guest' }} {{ $invoice->order->customer->last_name ?? '' }}</h4>
                                <p class="text-sm text-gray-600">{{ $invoice->order->customer->email ?? '' }}</p>
                                <div class="mt-2">
                                     <span class="px-3 py-1 rounded-full text-sm font-bold 
                                        {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->status === 'partial' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">
                                        {{ strtoupper($invoice->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Line Items from Order -->
                        <table class="min-w-full divide-y divide-gray-200 mb-6">
                            <thead>
                                <tr>
                                    <th class="text-left py-2">Item</th>
                                    <th class="text-right py-2">Qty</th>
                                    <th class="text-right py-2">Price</th>
                                    <th class="text-right py-2">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->order->items as $item)
                                    <tr>
                                        <td class="py-2">{{ $item->product_name ?? 'Product' }} <span class="text-xs text-gray-500 block">{{ $item->sku }}</span></td>
                                        <td class="text-right py-2">{{ $item->quantity }}</td>
                                        <td class="text-right py-2">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-right py-2">${{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t font-bold">
                                    <td colspan="3" class="text-right py-2">Total:</td>
                                    <td class="text-right py-2">${{ number_format($invoice->total_amount, 2) }}</td>
                                </tr>
                                 <tr class="text-green-600">
                                    <td colspan="3" class="text-right py-2">Paid:</td>
                                    <td class="text-right py-2">-${{ number_format($invoice->paid_amount, 2) }}</td>
                                </tr>
                                <tr class="text-red-600 font-bold text-lg">
                                    <td colspan="3" class="text-right py-2">Balance Due:</td>
                                    <td class="text-right py-2">${{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <a href="{{ route('central.invoices.index') }}" class="text-gray-500 hover:underline">&larr; Back to Invoices</a>
                    </div>
                </div>

                <!-- Payment Sidebar -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-fit">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-bold text-lg mb-4">Record Payment</h3>
                        
                        @if($invoice->status !== 'paid')
                            <form action="{{ route('central.invoices.add-payment', $invoice) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Amount</label>
                                    <input type="number" step="0.01" name="amount" value="{{ $invoice->total_amount - $invoice->paid_amount }}" max="{{ $invoice->total_amount - $invoice->paid_amount }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Method</label>
                                    <select name="method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="check">Check</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Reference / Notes</label>
                                    <input type="text" name="notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Record Payment
                                </button>
                            </form>
                        @else
                            <div class="text-center p-4 bg-green-50 rounded text-green-800">
                                <p class="font-bold">Fully Paid</p>
                            </div>
                        @endif

                        <!-- Payment History -->
                        <div class="mt-6 border-t pt-4">
                            <h4 class="font-bold text-sm mb-2 text-gray-500 uppercase">History</h4>
                            <ul class="space-y-2 text-sm">
                                @forelse($invoice->payments as $payment)
                                    <li class="flex justify-between">
                                        <span>{{ $payment->paid_at->format('M d') }} <span class="text-gray-500">({{ $payment->method }})</span></span>
                                        <span class="font-bold">${{ number_format($payment->amount, 2) }}</span>
                                    </li>
                                @empty
                                    <li class="text-gray-400 italic">No payments yet.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
