<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2
                    class="font-extrabold text-3xl leading-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                    {{ __('Process Return') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Search for an order to initiate a return request.</p>
            </div>
            <a href="{{ route('central.returns.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Back to Returns
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 animate-in fade-in duration-500" x-data="rmaForm()">

        <!-- Error Display -->
        @if (session('error'))
            <div class="mb-8 bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Error</h3>
                        <div class="mt-2 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-8 bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were problems with your submission</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul role="list" class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Search Section -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-visible relative z-30 mb-8">
            <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span
                        class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-900 text-white text-xs">1</span>
                    Find Order
                </h3>
            </div>
            <div class="p-6">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="searchOrders()"
                        @focus="showResults = true" @click.away="showResults = false"
                        placeholder="Search by Order ID, Customer Name, or Phone..."
                        class="block w-full rounded-xl border-gray-200 pl-11 pr-4 py-3 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-all shadow-sm">

                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center" x-show="loading"
                        style="display: none;">
                        <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>

                    <!-- Dropdown Results -->
                    <div x-show="showResults && results.length > 0"
                        class="absolute z-50 mt-2 w-full bg-white shadow-xl rounded-xl border border-gray-100 max-h-80 overflow-auto divide-y divide-gray-50"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100" style="display: none;">

                        <template x-for="order in results" :key="order.id">
                            <div @click="selectOrder(order)"
                                class="p-5 hover:bg-indigo-50/50 cursor-pointer transition-all group border-b border-gray-50 last:border-0 relative">
                                <div class="flex justify-between items-center gap-4">
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div
                                            class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                                <path d="M3 6h18" />
                                                <path d="M16 10a4 4 0 0 1-8 0" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors truncate"
                                                x-text="order.order_number"></p>
                                            <p class="text-xs text-gray-500 mt-0.5 truncate font-medium">
                                                <span x-text="order.customer_name"></span> &bull; <span
                                                    x-text="order.placed_at"></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-black text-gray-900"
                                            x-text="'₹' + Number(order.grand_total).toFixed(2)"></p>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider mt-1 border"
                                            :class="{
                                                  'bg-emerald-50 text-emerald-700 border-emerald-100': order.status === 'completed' || order.status === 'delivered',
                                                  'bg-indigo-50 text-indigo-700 border-indigo-100': order.status === 'processing',
                                                  'bg-amber-50 text-amber-700 border-amber-100': order.status === 'pending' || order.status === 'placed',
                                                  'bg-gray-50 text-gray-700 border-gray-100': true
                                              }" x-text="order.status.replace('_', ' ')">
                                        </span>
                                    </div>
                                    <div
                                        class="absolute right-2 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="text-indigo-400">
                                            <path d="m9 18 6-6-6-6" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="showResults && searchQuery.length > 2 && results.length === 0 && !loading"
                        class="absolute z-50 mt-2 w-full bg-white shadow-lg rounded-xl p-4 text-center text-sm text-gray-500 border border-gray-100"
                        style="display: none;">
                        No orders found matching "<span x-text="searchQuery" class="font-medium text-gray-900"></span>"
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Form -->
        <div x-show="selectedOrder" x-transition.opacity.duration.500ms style="display: none;">
            <form action="{{ route('central.returns.store') }}" method="POST" class="space-y-8"
                @submit="confirmSubmission">
                @csrf
                <input type="hidden" name="order_id" :value="selectedOrder?.id">

                <!-- 2. Select Items -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div
                        class="p-6 border-b border-gray-50 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-900 text-white text-xs">2</span>
                            Select Items for <span class="text-indigo-600" x-text="selectedOrder?.order_number"></span>
                        </h3>
                        <button type="button" @click="resetSelection()"
                            class="text-xs font-semibold text-red-500 hover:text-red-700 transition-colors">
                            Change Order
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6">
                        <template x-for="(item, index) in orderItems" :key="item.id || index">
                            <div class="group relative bg-white rounded-2xl border transition-all duration-300 overflow-hidden shadow-sm hover:shadow-md h-full flex flex-col"
                                :class="item.selected ? 'border-indigo-500 ring-4 ring-indigo-50' : 'border-gray-100 hover:border-gray-300'">

                                <!-- Selection Badge -->
                                <div class="absolute top-3 left-3 z-10">
                                    <input type="checkbox" x-model="item.selected"
                                        class="h-6 w-6 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer shadow-sm transition-all focus:ring-offset-2">
                                </div>

                                <div class="p-4 flex gap-4 h-full">
                                    <!-- Image -->
                                    <div
                                        class="h-20 w-20 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden flex-shrink-0">
                                        <template x-if="item.product && item.product.image_url">
                                            <img :src="item.product.image_url"
                                                class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        </template>
                                        <template x-if="!item.product || !item.product.image_url">
                                            <div class="h-full w-full flex items-center justify-center text-gray-300">
                                                <svg class="w-10 h-10" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0 flex flex-col justify-between">
                                        <div>
                                            <h4 class="text-sm font-black text-gray-900 line-clamp-2 leading-snug"
                                                x-text="item.product_name || item.sku"></h4>
                                            <div class="flex items-center gap-2 mt-1.5">
                                                <span
                                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"
                                                    x-text="'SKU: ' + (item.sku || 'N/A')"></span>
                                                <span
                                                    class="text-[10px] font-bold text-gray-500 px-1.5 py-0.5 bg-gray-100 rounded"
                                                    x-text="'Ordered: ' + item.formatted_quantity"></span>
                                            </div>
                                        </div>

                                        <div x-show="item.selected"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-2"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            class="mt-4 pt-4 border-t border-gray-50 grid grid-cols-2 gap-3">
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black uppercase text-gray-400 mb-1 px-1">Return
                                                    Qty</label>
                                                <input type="number" x-model="item.return_qty" min="1"
                                                    :max="item.quantity"
                                                    class="block w-full h-10 bg-gray-50 border-gray-100 rounded-xl text-xs font-bold text-center focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black uppercase text-gray-400 mb-1 px-1">Condition</label>
                                                <select x-model="item.condition"
                                                    class="block w-full h-10 bg-gray-50 border-gray-100 rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                                                    <option value="sellable">Sellable</option>
                                                    <option value="damaged">Damaged</option>
                                                </select>
                                            </div>
                                            <!-- Hidden Inputs for Form Submission -->
                                            <input type="hidden" :name="'items['+index+'][product_id]'"
                                                :value="item.product_id" :disabled="!item.selected">
                                            <input type="hidden" :name="'items['+index+'][quantity]'"
                                                :value="item.return_qty" :disabled="!item.selected">
                                            <input type="hidden" :name="'items['+index+'][condition]'"
                                                :value="item.condition" :disabled="!item.selected">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 3. Finalize -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                    x-show="hasSelectedItems" x-transition>
                    <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-900 text-white text-xs">3</span>
                            Reason & Submit
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Return Reason</label>
                            <textarea name="reason" rows="3"
                                class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-4 placeholder-gray-400 resize-none"
                                placeholder="Enter reason for return..." required></textarea>
                        </div>

                        <div class="flex items-center justify-end pt-2">
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-transparent bg-gray-900 py-3.5 px-8 text-sm font-bold text-white shadow-lg shadow-gray-900/20 hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 transition-all hover:-translate-y-0.5 transform">
                                <span>Process Return</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <line x1="22" y1="2" x2="11" y2="13" />
                                    <polygon points="22 2 15 22 11 13 2 9 22 2" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
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
                    const preSelectedOrder = @json($preSelectedOrder);
                    if (preSelectedOrder) {
                        this.selectOrder(preSelectedOrder);
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
                    this.orderItems = (order.items || []).map(item => ({
                        ...item,
                        selected: false,
                        return_qty: parseFloat(item.quantity) || 1,
                        condition: 'sellable',
                        product_name: item.product?.name || item.product_name || 'Unknown Item',
                        sku: item.product?.sku || item.sku || 'N/A',
                        formatted_quantity: parseFloat(item.quantity) // Helper for display
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
                    return this.orderItems && this.orderItems.length > 0 && this.orderItems.some(item => item.selected);
                },

                confirmSubmission(e) {
                    if (!confirm('Are you sure you want to process this return?')) {
                        e.preventDefault();
                    }
                }
            }
        }
    </script>
</x-app-layout>