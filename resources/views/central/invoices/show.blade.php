<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Invoice #{{ $invoice->invoice_number }}
            </h2>

            <div class="flex gap-2">
                <a href="{{ route('central.invoices.pdf', $invoice) }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-semibold">
                    ⬇ Download PDF
                </a>

                <a href="{{ route('central.invoices.pdf', [$invoice, 'action' => 'print']) }}" target="_blank"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm font-semibold">
                    🖨 Print
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 print:p-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:px-0">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 print:grid-cols-1">

                <!-- ================= INVOICE DETAILS ================= -->
                <div class="col-span-2 bg-white shadow rounded-lg print:shadow-none print:rounded-none">
                    <div class="p-6 print:p-4 text-gray-900">

                        <!-- ===== Invoice Header ===== -->
                        <div class="flex justify-between items-start border-b pb-4 mb-6">
                            <div>
                                <h3 class="text-2xl font-bold tracking-wide">INVOICE</h3>
                                <p class="text-sm text-gray-500">Invoice No: {{ $invoice->invoice_number }}</p>
                                <p class="text-sm text-gray-500">
                                    Issued: {{ $invoice->issue_date->format('d M Y') }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Due: {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'On Receipt' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="font-bold text-lg">
                                    {{ $invoice->order->customer->first_name ?? 'Guest' }}
                                    {{ $invoice->order->customer->last_name ?? '' }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ $invoice->order->customer->email ?? '' }}
                                </p>

                                <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-bold
                                    {{ $invoice->status === 'paid'
    ? 'bg-green-100 text-green-800'
    : ($invoice->status === 'partial'
        ? 'bg-blue-100 text-blue-800'
        : 'bg-red-100 text-red-800') }}">
                                    {{ strtoupper($invoice->status) }}
                                </span>
                            </div>
                        </div>

                        <!-- ===== Addresses ===== -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="border rounded p-4">
                                <h4 class="font-bold mb-2">Billing Address</h4>
                                <p class="text-sm">
                                    <strong>Customer:</strong>
                                    {{ $invoice->order->customer->first_name ?? '' }}
                                    {{ $invoice->order->customer->last_name ?? '' }}<br>

                                    <strong>Mobile:</strong>
                                    {{ $invoice->order->customer->mobile ?? 'N/A' }}<br>

                                    @if($invoice->order->billingAddress)
                                        {{ $invoice->order->billingAddress->address_line1 }}<br>
                                        @if($invoice->order->billingAddress->address_line2)
                                            {{ $invoice->order->billingAddress->address_line2 }}<br>
                                        @endif
                                        {{ $invoice->order->billingAddress->village }},
                                        {{ $invoice->order->billingAddress->state }} -
                                        {{ $invoice->order->billingAddress->pincode }}
                                    @endif
                                </p>
                            </div>

                            <div class="border rounded p-4">
                                <h4 class="font-bold mb-2">Shipping Address</h4>
                                <p class="text-sm">
                                    <strong>Customer:</strong>
                                    {{ $invoice->order->customer->first_name ?? '' }}
                                    {{ $invoice->order->customer->last_name ?? '' }}<br>

                                    <strong>Mobile:</strong>
                                    {{ $invoice->order->customer->mobile ?? 'N/A' }}<br>

                                    @if($invoice->order->shippingAddress)
                                        {{ $invoice->order->shippingAddress->address_line1 }}<br>
                                        @if($invoice->order->shippingAddress->address_line2)
                                            {{ $invoice->order->shippingAddress->address_line2 }}<br>
                                        @endif
                                        {{ $invoice->order->shippingAddress->village }},
                                        {{ $invoice->order->shippingAddress->state }} -
                                        {{ $invoice->order->shippingAddress->pincode }}
                                    @else
                                        Same as Billing
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- ===== Line Items ===== -->
                        <table class="w-full text-sm border-collapse mb-6">
                            <thead class="border-b bg-gray-100">
                                <tr>
                                    <th class="py-2 text-left">Item</th>
                                    <th class="py-2 text-right">Qty</th>
                                    <th class="py-2 text-right">Price</th>
                                    <th class="py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->order->items as $item)
                                    <tr class="border-b">
                                        <td class="py-2">
                                            {{ $item->product_name }}
                                            <span class="block text-xs text-gray-500">{{ $item->sku }}</span>
                                        </td>
                                        <td class="py-2 text-right">{{ $item->quantity }}</td>
                                        <td class="py-2 text-right">
                                            ₹ {{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="py-2 text-right">
                                            ₹ {{ number_format($item->total_price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- ===== Totals ===== -->
                        <div class="flex justify-end">
                            <table class="w-full max-w-sm text-sm">
                                <tr>
                                    <td class="py-1 text-right font-semibold">Subtotal</td>
                                    <td class="py-1 text-right">
                                        ₹ {{ number_format($invoice->total_amount, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-1 text-right font-semibold">Paid</td>
                                    <td class="py-1 text-right text-green-600">
                                        -₹ {{ number_format($invoice->paid_amount, 2) }}
                                    </td>
                                </tr>
                                <tr class="text-lg font-bold text-red-600">
                                    <td class="py-2 text-right">Balance Due</td>
                                    <td class="py-2 text-right">
                                        ₹ {{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <a href="{{ route('central.invoices.index') }}"
                            class="inline-block mt-6 text-gray-500 hover:underline print:hidden">
                            ← Back to Invoices
                        </a>
                    </div>
                </div>

                <!-- ================= PAYMENT SIDEBAR ================= -->
                <div class="bg-white shadow rounded-lg h-fit print:hidden">
                    <div class="p-6">

                        <h3 class="font-bold text-lg mb-4">Record Payment</h3>

                        @if($invoice->status !== 'paid')
                            <form action="{{ route('central.invoices.add-payment', $invoice) }}" method="POST">
                                @csrf

                                <div class="mb-4">
                                    <label class="block text-sm font-medium">Amount</label>
                                    <input type="number" step="0.01" name="amount"
                                        value="{{ $invoice->total_amount - $invoice->paid_amount }}"
                                        max="{{ $invoice->total_amount - $invoice->paid_amount }}"
                                        class="mt-1 w-full rounded border-gray-300" required>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium">Method</label>
                                    <select name="method" class="mt-1 w-full rounded border-gray-300">
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="online">Online</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium">Transaction ID / Reference</label>
                                    <input type="text" name="transaction_id" class="mt-1 w-full rounded border-gray-300"
                                        placeholder="Optional">
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium">Notes</label>
                                    <input type="text" name="notes" class="mt-1 w-full rounded border-gray-300">
                                </div>

                                <button type="submit"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded font-bold">
                                    Record Payment
                                </button>
                            </form>
                        @else
                            <div class="text-center bg-green-50 p-4 rounded text-green-800 font-bold">
                                Fully Paid
                            </div>
                        @endif

                        <!-- ===== Payment History ===== -->
                        <div class="mt-6 border-t pt-4">
                            <div class="text-xl font-bold text-gray-900 dark:text-gray-100">Krushify Agro Pvt Ltd.</div>
                            <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">
                                Payment History
                            </h4>
                            <ul class="space-y-2 text-sm">
                                @forelse($invoice->payments as $payment)
                                    <li class="flex justify-between">
                                        <span>
                                            {{ $payment->paid_at->format('d M Y') }}
                                            <span class="text-gray-500">({{ $payment->method }})</span>
                                        </span>
                                        <span class="font-bold">
                                            ₹ {{ number_format($payment->amount, 2) }}
                                        </span>
                                    </li>
                                @empty
                                    <li class="italic text-gray-400">No payments yet.</li>
                                @endforelse
                            </ul>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>