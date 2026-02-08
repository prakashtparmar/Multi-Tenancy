<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profit & Loss Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('central.reports.profit-loss') }}" method="GET"
                        class="flex flex-wrap items-end gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Range</label>
                            <select name="range"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                onchange="this.form.submit()">
                                <option value="this_month" {{ $range == 'this_month' ? 'selected' : '' }}>This Month
                                </option>
                                <option value="last_month" {{ $range == 'last_month' ? 'selected' : '' }}>Last Month
                                </option>
                                <option value="custom" {{ $range == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                        @if($range == 'custom')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" name="start_date" value="{{ request('start_date') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" name="end_date" value="{{ request('end_date') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <button type="submit"
                                class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Filter
                            </button>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <!-- Revenue -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 truncate">Total Revenue</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">Rs {{ number_format($revenue, 2) }}</div>
                    </div>
                </div>

                <!-- COGS -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 truncate">Cost of Goods Sold</div>
                        <div class="mt-1 text-3xl font-semibold text-red-600">Rs {{ number_format($cogs, 2) }}</div>
                    </div>
                </div>

                <!-- Expenses -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 truncate">Operating Expenses</div>
                        <div class="mt-1 text-3xl font-semibold text-red-600">Rs {{ number_format($totalExpenses, 2) }}
                        </div>
                    </div>
                </div>

                <!-- Net Profit -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 truncate">Net Profit</div>
                        <div
                            class="mt-1 text-3xl font-semibold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rs {{ number_format($netProfit, 2) }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Gross: Rs {{ number_format($grossProfit, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Breakdown -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Expense Breakdown -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-bold mb-4">Expense Breakdown</h3>
                        @if(count($expenseBreakdown) > 0)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Category</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($expenseBreakdown as $category => $amount)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $category }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">Rs
                                                {{ number_format($amount, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-gray-500">No expenses recorded for this period.</p>
                        @endif
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-bold mb-4">Notes</h3>
                        <ul class="list-disc list-inside text-sm text-gray-600 space-y-2">
                            <li><strong>Revenue</strong> is calculated based on orders with 'Paid' payment status.</li>
                            <li><strong>COGS</strong> is calculated based on the cost price of items at the time of
                                order creation.</li>
                            <li><strong>Operating Expenses</strong> are fetched from the Expenses module.</li>
                            <li><strong>Net Profit</strong> = Revenue - COGS - Operating Expenses.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>