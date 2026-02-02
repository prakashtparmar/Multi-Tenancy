<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Return Request') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="rmaForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('central.returns.store') }}" method="POST" class="p-6 text-gray-900">
                    @csrf

                    <!-- Order Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Select Order</label>
                        <select name="order_id" x-model="selectedOrder" @change="loadItems()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Select Order</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}" 
                                    data-items="{{ json_encode($order->items) }}"
                                    {{ (isset($preSelectedOrderId) && $preSelectedOrderId == $order->id) ? 'selected' : '' }}>
                                    Order #{{ $order->order_number }} - {{ $order->customer->first_name ?? 'Guest' }} ({{ $order->created_at->format('Y-m-d') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Reason -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Reason for Return</label>
                        <textarea name="reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></textarea>
                    </div>

                    <!-- Items Selection -->
                    <div class="mb-6" x-show="orderItems.length > 0">
                        <h3 class="text-lg font-bold mb-4">Select Items to Return</h3>
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left">Product</th>
                                    <th class="px-6 py-3 text-left">Ordered Qty</th>
                                    <th class="px-6 py-3 text-left">Return Qty</th>
                                    <th class="px-6 py-3 text-left">Condition</th>
                                    <th class="px-6 py-3 text-left">Select</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in orderItems" :key="index">
                                    <tr>
                                        <td class="px-6 py-4" x-text="item.product_name || item.sku"></td>
                                        <td class="px-6 py-4" x-text="item.quantity"></td>
                                        <td class="px-6 py-4">
                                             <input type="number" :name="'items['+index+'][quantity]'" value="1" :max="item.quantity" min="1" class="rounded border-gray-300 w-20">
                                             <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                                        </td>
                                        <td class="px-6 py-4">
                                            <select :name="'items['+index+'][condition]'" class="rounded border-gray-300">
                                                <option value="sellable">Sellable</option>
                                                <option value="damaged">Damaged</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="checkbox" :name="'items['+index+'][selected]'" value="1" class="rounded border-gray-300 transform scale-125">
                                            <!-- Note: Real impl would need better array handling for selected items -->
                                            <!-- Hack for prototype: sending all, backend validation needed, or JS filters -->
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <p class="text-sm text-gray-500 mt-2">* Check items to include in return.</p>
                    </div>

                    <div class="flex justify-end pt-4 border-t">
                        <button type="submit" class="bg-black text-white font-bold py-2 px-6 rounded hover:bg-gray-800">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function rmaForm() {
            const initialItems = @json($preSelectedOrder ? $preSelectedOrder->items : []);
            return {
                selectedOrder: '{{ $preSelectedOrderId ?? "" }}',
                orderItems: initialItems,
                init() {
                    if (this.selectedOrder) {
                        this.loadItems();
                    }
                },
                loadItems() {
                    const select = document.querySelector('select[name="order_id"]');
                    if (!select) return;
                    const option = select.options[select.selectedIndex];
                    if (option && option.dataset.items) {
                        this.orderItems = JSON.parse(option.dataset.items);
                    } else {
                        if (!this.orderItems.length) {
                             this.orderItems = [];
                        }
                    }
                }
            }
        }
    </script>
</x-app-layout>
