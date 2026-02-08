<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-heading font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Create Return Request') }}
            </h2>
            <p class="text-sm text-gray-500">Search for an order and select items to process a return.</p>
        </div>
    </x-slot>

    <div class="py-12" x-data="rmaForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 gap-6">
                <!-- Search Section -->
                <div class="bg-white overflow-visible shadow-sm sm:rounded-xl border border-gray-100 p-6 relative z-50">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Find Order</h3>
                    <p class="text-sm text-gray-500 mb-4">Search by Order ID, Customer Name, or Mobile Number.</p>

                    <div class="relative">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="searchOrders()"
                                @focus="showResults = true" @click.away="showResults = false"
                                placeholder="Type to search orders..."
                                class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 focus:ring-black focus:border-black sm:text-sm shadow-sm transition-colors duration-200 ease-in-out py-3">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center" x-show="loading">
                                <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </div>

                        <!-- Dropdown Results -->
                        <div x-show="showResults && results.length > 0"
                            class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-lg py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95" style="display: none;">

                            <template x-for="order in results" :key="order.id">
                                <div @click="selectOrder(order)"
                                    class="cursor-pointer select-none relative py-3 pl-3 pr-9 hover:bg-gray-50 group border-b border-gray-100 last:border-0">
                                    <div class="flex justify-between items-center">
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-gray-900"
                                                x-text="order.order_number"></span>
                                            <span class="text-xs text-gray-500">
                                                <span x-text="order.customer_name"></span> • <span
                                                    x-text="order.placed_at"></span>
                                            </span>
                                        </div>
                                        <div class="text-right pr-4">
                                            <div class="text-sm font-medium text-gray-900"
                                                x-text="'₹' + order.grand_total.toFixed(2)"></div>
                                            <div class="text-xs px-2 py-0.5 rounded-full inline-block mt-1" :class="{
                                                    'bg-green-100 text-green-800': order.status === 'Completed',
                                                    'bg-blue-100 text-blue-800': order.status === 'Processing',
                                                    'bg-yellow-100 text-yellow-800': order.status === 'Pending',
                                                    'bg-gray-100 text-gray-800': true
                                                 }" x-text="order.status"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div x-show="showResults && searchQuery.length > 2 && results.length === 0 && !loading"
                            class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-lg py-4 text-center text-sm text-gray-500 border border-gray-100">
                            No orders found.
                        </div>
                    </div>
                </div>

                <!-- Selected Order Details & Form -->
                <div x-show="selectedOrder" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100"
                    style="display: none;">

                    <form action="{{ route('central.returns.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_id" :value="selectedOrder?.id">

                        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Return Items</h3>
                                <p class="text-sm text-gray-500">Select items and condition for <span
                                        class="font-semibold text-gray-900" x-text="selectedOrder?.order_number"></span>
                                </p>
                            </div>
                            <button type="button" @click="resetSelection()"
                                class="text-sm text-red-600 hover:text-red-800 font-medium">
                                Clear Selection
                            </button>
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
                                        <template x-for="(item, index) in orderItems" :key="item.id || index">
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
                                                            <template x-if="item.product?.image_url">
                                                                <img :src="item.product.image_url"
                                                                    class="h-full w-full object-cover">
                                                            </template>
                                                            <template x-if="!item.product?.image_url">
                                                                <div
                                                                    class="h-full w-full flex items-center justify-center text-gray-400">
                                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                                        stroke="currentColor">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                    </svg>
                                                                </div>
                                                            </template>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900"
                                                                x-text="item.product?.name || 'Unknown Product'"></div>
                                                            <div class="text-xs text-gray-500"
                                                                x-text="'SKU: ' + (item.product?.sku || '-')"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                                    <span x-text="item.quantity"></span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex justify-center" x-show="item.selected">
                                                        <input type="number" x-model="item.return_qty" min="1"
                                                            :max="item.quantity"
                                                            class="block w-20 rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm text-center">
                                                    </div>
                                                    <div class="text-center text-sm text-gray-400"
                                                        x-show="!item.selected">
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

                                                    <!-- Hidden Inputs for Form Submission (ONLY if selected) -->
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
                                    placeholder="Please describe why the customer is returning these items..."
                                    required></textarea>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-gray-100">
                                <button type="submit"
                                    class="inline-flex justify-center rounded-lg border border-transparent bg-black py-2.5 px-6 text-sm font-medium text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="!hasSelectedItems">
                                    Submit Return Request
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function rmaForm() {
            return {
                searchQuery: '',
                loading: false,
                results: [],
                showResults: false,
                selectedOrder: null,
                orderItems: [],

                init() {
                    // Pre-load if query param exists (legacy support)
                    const preSelectedOrderId = '{{ $preSelectedOrderId ?? "" }}';
                    if (preSelectedOrderId) {
                        // We would ideally fetch this via API too to get the full structure
                        // For now, we rely on the user searching or the legacy blade injection if absolutely needed
                        // But since we are overhauling, let's rely on the search.
                        // Actually, let's support it via the same API to be clean
                        this.searchQuery = '{{ $preSelectedOrder->order_number ?? "" }}';
                        if (this.searchQuery) {
                            this.searchOrders();
                        }
                    }
                },

                async searchOrders() {
                    if (this.searchQuery.length < 2) {
                        this.results = [];
                        return;
                    }

                    this.loading = true;
                    try {
                        const response = await fetch(`{{ route('central.api.search.all-orders') }}?q=${encodeURIComponent(this.searchQuery)}`);
                        if (response.ok) {
                            this.results = await response.json();
                            this.showResults = true;
                        }
                    } catch (error) {
                        console.error('Search failed:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                selectOrder(order) {
                    this.selectedOrder = order;
                    // Map items to include local state for the form
                    this.orderItems = order.items.map(item => ({
                        ...item,
                        selected: false,
                        return_qty: 1, // Default return qty
                        condition: 'sellable' // Default condition
                    }));

                    this.showResults = false;
                    this.searchQuery = order.order_number;
                },

                resetSelection() {
                    this.selectedOrder = null;
                    this.orderItems = [];
                    this.searchQuery = '';
                },

                get hasSelectedItems() {
                    return this.orderItems.some(item => item.selected);
                }
            }
        }
    </script>
</x-app-layout>