<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-heading font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Edit Return Request') }}
            </h2>
            <p class="text-sm text-gray-500">Modify items or reason for RMA #{{ $orderReturn->rma_number }}</p>
        </div>
    </x-slot>

    <div class="py-12" x-data="editRmaForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">

                <form action="{{ route('central.returns.update', $orderReturn) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Return Details</h3>
                            <p class="text-sm text-gray-500">Order <span
                                    class="font-semibold text-gray-900">#{{ $orderReturn->order->order_number }}</span>
                            </p>
                        </div>
                        <div class="text-sm">
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                {{ ucfirst($orderReturn->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Items Table -->
                        <div class="overflow-hidden border border-gray-200 rounded-lg mb-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                                            Select
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ordered Qty
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Return Qty
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Condition
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="(item, index) in items" :key="item.product_id">
                                        <tr :class="item.selected ? 'bg-blue-50/50' : 'hover:bg-gray-50'"
                                            class="transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center justify-center">
                                                    <input type="checkbox" x-model="item.selected"
                                                        class="h-5 w-5 text-black border-gray-300 rounded focus:ring-black transition duration-150 ease-in-out cursor-pointer">
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="h-10 w-10 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden border border-gray-200">
                                                        <template x-if="item.image_url">
                                                            <img :src="item.image_url"
                                                                class="h-full w-full object-cover">
                                                        </template>
                                                        <template x-if="!item.image_url">
                                                            <div
                                                                class="h-full w-full flex items-center justify-center text-gray-400">
                                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900"
                                                            x-text="item.name"></div>
                                                        <div class="text-xs text-gray-500"
                                                            x-text="'SKU: ' + (item.sku || '-')"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                                <span x-text="item.max_quantity"></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex justify-center" x-show="item.selected">
                                                    <input type="number" x-model="item.return_qty" min="1"
                                                        :max="item.max_quantity"
                                                        class="block w-20 rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm text-center">
                                                </div>
                                                <div class="text-center text-sm text-gray-400" x-show="!item.selected">
                                                    -
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div x-show="item.selected">
                                                    <select x-model="item.condition"
                                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm">
                                                        <option value="sellable">Sellable</option>
                                                        <option value="damaged">Damaged</option>
                                                    </select>
                                                </div>
                                                <div class="text-sm text-gray-400" x-show="!item.selected">
                                                    -
                                                </div>

                                                <!-- Hidden Inputs -->
                                                <template x-if="item.selected">
                                                    <div>
                                                        <input type="hidden" :name="'items['+index+'][product_id]'"
                                                            :value="item.product_id">
                                                        <input type="hidden" :name="'items['+index+'][quantity]'"
                                                            :value="item.return_qty">
                                                        <input type="hidden" :name="'items['+index+'][condition]'"
                                                            :value="item.condition">
                                                    </div>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Reason -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Return</label>
                            <textarea name="reason" rows="3"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm placeholder-gray-400"
                                required>{{ $orderReturn->reason }}</textarea>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-100 gap-3">
                            <a href="{{ route('central.returns.index') }}"
                                class="inline-flex justify-center rounded-lg border border-gray-300 bg-white py-2.5 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition-all">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex justify-center rounded-lg border border-transparent bg-black py-2.5 px-6 text-sm font-medium text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="!hasSelectedItems">
                                Update Return Request
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editRmaForm() {
            // Prepare data: merge Order Items with Existing Return Items
            const orderItems = @json($orderReturn->order->items);
            const returnedItems = @json($orderReturn->items);

            const mergedItems = orderItems.map(orderItem => {
                const existingReturn = returnedItems.find(r => r.product_id === orderItem.product_id);
                return {
                    product_id: orderItem.product_id,
                    name: orderItem.product?.name || 'Unknown',
                    sku: orderItem.product?.sku || '-',
                    image_url: orderItem.product?.image_url || null,
                    max_quantity: orderItem.quantity,

                    // State
                    selected: !!existingReturn,
                    return_qty: existingReturn ? existingReturn.quantity : 1,
                    condition: existingReturn ? existingReturn.condition : 'sellable'
                };
            });

            return {
                items: mergedItems,

                get hasSelectedItems() {
                    return this.items.some(item => item.selected);
                }
            }
        }
    </script>
</x-app-layout>