<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-3xl leading-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                    {{ __('New Return Request') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Select an order and choose items to return.</p>
            </div>
            <a href="{{ route('tenant.returns.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
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
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
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
        
        <!-- Error Display -->
        @if ($errors->any())
            <div class="mb-8 bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
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
        
        <form action="{{ route('tenant.returns.store') }}" method="POST" class="space-y-8">
            @csrf

            <!-- 1. Select Order -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-900 text-white text-xs">1</span>
                        Select Order
                    </h3>
                </div>
                <div class="p-4 sm:p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Choose an Order</label>
                    <div class="relative">
                        <select name="order_id" x-model="selectedOrder" @change="loadItems()" 
                                class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 pl-4 pr-10 bg-gray-50 hover:bg-white transition-colors cursor-pointer appearance-none truncate" required>
                            <option value="">Select an order...</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}" 
                                    data-items="{{ json_encode($order->items) }}"
                                    {{ (isset($preSelectedOrderId) && $preSelectedOrderId == $order->id) ? 'selected' : '' }}>
                                    Order #{{ $order->order_number }} - {{ $order->customer->first_name ?? 'Guest' }} ({{ $order->created_at->format('M d, Y') }})
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Only orders from the last 30 days are shown.</p>
                </div>
            </div>

            <!-- 2. Select Items -->
            <div x-show="selectedOrder" x-transition.opacity.duration.300ms class="group bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden relative">
                <div class="p-4 sm:p-6 border-b border-gray-50 bg-gray-50/50 flex flex-wrap gap-2 justify-between items-center">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-900 text-white text-xs">2</span>
                        Select Items
                    </h3>
                    <div x-show="orderItems.length > 0" class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg">
                        <span x-text="orderItems.filter(i => i.selected).length"></span> items selected
                    </div>
                </div>

                <div class="p-0">
                    <!-- Items List -->
                    <div class="divide-y divide-gray-100">
                        <template x-for="(item, index) in orderItems" :key="index">
                            <div class="p-4 sm:p-6 hover:bg-gray-50 transition-colors" :class="item.selected ? 'bg-indigo-50/30' : ''">
                                <div class="flex flex-col sm:flex-row items-start gap-4">
                                    <div class="flex items-start gap-4 w-full sm:w-auto">
                                        <!-- Checkbox -->
                                        <div class="pt-1">
                                            <input type="checkbox" x-model="item.selected" :name="'items['+index+'][selected]'" value="1" 
                                                   class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition-all">
                                        </div>

                                        <!-- Image -->
                                        <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-xl bg-white border border-gray-200 overflow-hidden flex-shrink-0">
                                            <template x-if="item.product && item.product.image_url">
                                                <img :src="item.product.image_url" class="h-full w-full object-cover">
                                            </template>
                                            <template x-if="!item.product || !item.product.image_url">
                                                <div class="h-full w-full flex items-center justify-center text-gray-300 bg-gray-100">
                                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                                </div>
                                            </template>
                                        </div>
                                        
                                        <!-- Mobile Details (Visible only on small screens if needed, but here we keep layout fluid) -->
                                        <div class="sm:hidden flex-1">
                                             <h4 class="text-sm font-bold text-gray-900 line-clamp-2" x-text="item.product_name || item.sku"></h4>
                                             <p class="text-xs text-gray-500 mt-0.5" x-text="'SKU: ' + (item.sku || 'N/A')"></p>
                                             <p class="text-xs font-medium text-gray-700 mt-1">Ordered: <span x-text="item.formatted_quantity"></span></p>
                                        </div>
                                    </div>

                                    <!-- Details & Controls -->
                                    <div class="flex-1 w-full sm:min-w-0 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="hidden sm:block">
                                            <h4 class="text-sm font-bold text-gray-900" x-text="item.product_name || item.sku"></h4>
                                            <p class="text-xs text-gray-500 mt-0.5" x-text="'SKU: ' + (item.sku || 'N/A')"></p>
                                            <p class="text-xs font-medium text-gray-700 mt-2">Ordered: <span x-text="item.formatted_quantity"></span></p>
                                        </div>

                                        <!-- Controls (Only visible if selected) -->
                                        <div x-show="item.selected" x-transition class="flex flex-row gap-3 items-center justify-between sm:justify-end w-full bg-gray-50/50 sm:bg-transparent p-3 sm:p-0 rounded-lg sm:rounded-none">
                                            <!-- Hidden Inputs -->
                                            <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                                            
                                            <div class="flex-1 sm:flex-none sm:w-auto">
                                                <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Return Qty</label>
                                                <input type="number" :name="'items['+index+'][quantity]'" x-model="item.return_qty" min="1" :max="item.quantity" 
                                                       class="block w-full sm:w-24 rounded-lg border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center font-bold">
                                            </div>

                                            <div class="flex-1 sm:flex-none sm:w-auto">
                                                <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Condition</label>
                                                <select :name="'items['+index+'][condition]'" x-model="item.condition" 
                                                        class="block w-full sm:w-32 rounded-lg border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    <option value="sellable">Sellable</option>
                                                    <option value="damaged">Damaged</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <div x-show="orderItems.length === 0" class="p-8 text-center text-gray-500">
                        Select an order to view items.
                    </div>
                </div>
            </div>

            <!-- 3. Finalize -->
            <div x-show="hasSelectedItems" x-transition class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-900 text-white text-xs">3</span>
                        Reason & Submit
                    </h3>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Why is the customer returning these items?</label>
                        <textarea name="reason" rows="3" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-4 placeholder-gray-400 resize-none" placeholder="e.g. Items were damaged in transit, ordered wrong size..." required></textarea>
                    </div>

                    <div class="flex items-center justify-end pt-2">
                         <button type="submit" 
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-transparent bg-gray-900 py-3.5 px-8 text-sm font-bold text-white shadow-lg shadow-gray-900/20 hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 transition-all hover:-translate-y-0.5 transform">
                            <span>Submit Return Request</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script>
        function rmaForm() {
            const initialItems = @json($preSelectedOrder ? $preSelectedOrder->items : []);
            // Normalize initial items if any
            const normalizedInitial = initialItems.map(item => ({
                ...item,
                selected: false,
                return_qty: 1, 
                condition: 'sellable'
            }));

            return {
                selectedOrder: '{{ $preSelectedOrderId ?? "" }}',
                orderItems: normalizedInitial,
                
                init() {
                    if (this.selectedOrder && this.orderItems.length === 0) {
                        this.loadItems();
                    }
                },

                loadItems() {
                    const select = document.querySelector('select[name="order_id"]');
                    if (!select) return;
                    const option = select.options[select.selectedIndex];
                    
                    if (option && option.dataset.items) {
                        const items = JSON.parse(option.dataset.items);
                        // Map items to include local state
                        this.orderItems = items.map(item => ({
                            ...item,
                            selected: false,
                            return_qty: 1, 
                            condition: 'sellable',
                            formatted_quantity: parseFloat(item.quantity)
                        }));
                    } else {
                        this.orderItems = [];
                    }
                },

                get hasSelectedItems() {
                    return this.orderItems && this.orderItems.some(i => i.selected);
                }
            }
        }
    </script>
</x-app-layout>
