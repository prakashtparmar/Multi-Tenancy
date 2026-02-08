<x-app-layout>
    <x-slot name="header">
        <h2 class="font-heading font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Inspect Return') }} <span class="text-gray-400">#{{ $orderReturn->rma_number }}</span>
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ items: {{ $orderReturn->items }} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">

                <form action="{{ route('central.returns.inspect.store', $orderReturn) }}" method="POST">
                    @csrf

                    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Verify Received Items</h3>
                            <p class="text-sm text-gray-500">Confirm quantity and condition of items received from
                                customer.</p>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="overflow-hidden border border-gray-200 rounded-lg mb-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product</th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Requested Qty</th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Received Qty</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Received Condition</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($orderReturn->items as $index => $item)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="h-10 w-10 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden border border-gray-200">
                                                        @if($item->product && $item->product->image_url)
                                                            <img src="{{ $item->product->image_url }}" alt=""
                                                                class="h-full w-full object-cover">
                                                        @else
                                                            <div
                                                                class="h-full w-full flex items-center justify-center text-gray-400">
                                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $item->product->name ?? 'Unknown' }}</div>
                                                        <div class="text-xs text-gray-500">SKU:
                                                            {{ $item->product->sku ?? '-' }}</div>
                                                        <input type="hidden" name="items[{{ $index }}][id]"
                                                            value="{{ $item->id }}">
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex justify-center">
                                                    <input type="number" name="items[{{ $index }}][quantity_received]"
                                                        value="{{ $item->quantity }}" min="0" max="{{ $item->quantity }}"
                                                        class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm text-center">
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <select name="items[{{ $index }}][condition_received]"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm">
                                                    <option value="sellable" {{ $item->condition === 'sellable' ? 'selected' : '' }}>Sellable</option>
                                                    <option value="damaged" {{ $item->condition === 'damaged' ? 'selected' : '' }}>Damaged</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-100 gap-3">
                            <a href="{{ route('central.returns.show', $orderReturn) }}"
                                class="inline-flex justify-center rounded-lg border border-gray-300 bg-white py-2.5 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none transition-all">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex justify-center rounded-lg border border-transparent bg-emerald-600 py-2.5 px-6 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all">
                                Confirm Received & Restock
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>