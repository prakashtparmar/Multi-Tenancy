<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Order (Central)') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="orderForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('tenant.orders.store') }}" method="POST" class="p-6 text-gray-900">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Customer -->
                        <div>
                            <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer</label>
                            <select name="customer_id" id="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->first_name }} {{ $customer->last_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Warehouse -->
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Warehouse</label>
                            <select name="warehouse_id" id="warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Order Number -->
                        <div>
                            <label for="order_number" class="block text-sm font-medium text-gray-700">Order Number</label>
                            <input type="text" name="order_number" id="order_number" value="ORD-{{ strtoupper(Str::random(8)) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="mb-6">
                        <h3 class="text-lg font-bold mb-4">Items</h3>
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-6 py-4">
                                            <!-- Product Select -->
                                            <input type="text" :name="'items['+index+'][product_id]'" x-model="item.product_id" class="rounded border-gray-300 w-full" placeholder="Product ID (for now)">
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" :name="'items['+index+'][quantity]'" x-model="item.quantity" class="rounded border-gray-300 w-24" min="1" @input="calculateTotal(index)">
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" step="0.01" :name="'items['+index+'][price]'" x-model="item.price" class="rounded border-gray-300 w-32" min="0" @input="calculateTotal(index)">
                                        </td>
                                        <td class="px-6 py-4">
                                            <span x-text="(item.quantity * item.price).toFixed(2)"></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900">Remove</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <button type="button" @click="addItem()" class="mt-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center">
                            <span>+ Add Item</span>
                        </button>
                    </div>

                    <div class="flex justify-end border-t pt-4">
                        <div class="text-xl font-bold">Total: $<span x-text="grandTotal.toFixed(2)"></span></div>
                    </div>

                    <div class="mt-6 flex items-center justify-end">
                        <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-700">
                            Create Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function orderForm() {
            return {
                items: [
                    { product_id: '', quantity: 1, price: 0 }
                ],
                get grandTotal() {
                    return this.items.reduce((sum, item) => sum + (item.quantity * item.price), 0);
                },
                addItem() {
                    this.items.push({ product_id: '', quantity: 1, price: 0 });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                calculateTotal(index) {
                    // Logic handled by x-text and getter
                }
            }
        }
    </script>
</x-app-layout>
