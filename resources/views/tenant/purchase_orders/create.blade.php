<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Purchase Order') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="poForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('tenant.purchase-orders.store') }}" method="POST" class="p-6 text-gray-900">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Supplier -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Supplier</label>
                            <select name="supplier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->company_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Warehouse -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Destination Warehouse</label>
                            <select name="warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- PO Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PO Number</label>
                            <input type="text" name="po_number" value="PO-{{ strtoupper(Str::random(8)) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="mb-6">
                        <h3 class="text-lg font-bold mb-4">Items</h3>
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left">Product</th>
                                    <th class="px-6 py-3 text-left">Quantity</th>
                                    <th class="px-6 py-3 text-left">Unit Cost</th>
                                    <th class="px-6 py-3 text-left">Total</th>
                                    <th class="px-6 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-6 py-4">
                                            <select :name="'items['+index+'][product_id]'" x-model="item.product_id" @change="updateCost(index)" class="rounded border-gray-300 w-full">
                                                <option value="">Select Product</option>
                                                @foreach($products as $prod)
                                                    <option value="{{ $prod->id }}" data-cost="{{ $prod->cost_price }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" :name="'items['+index+'][quantity]'" x-model="item.quantity" class="rounded border-gray-300 w-24" min="1">
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" step="0.01" :name="'items['+index+'][cost]'" x-model="item.cost" class="rounded border-gray-300 w-32" min="0">
                                        </td>
                                        <td class="px-6 py-4">
                                            <span x-text="(item.quantity * item.cost).toFixed(2)"></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <button type="button" @click="removeItem(index)" class="text-red-600">Remove</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <button type="button" @click="addItem()" class="mt-4 bg-gray-200 px-4 py-2 rounded">+ Add Item</button>
                    </div>

                    <div class="flex justify-end pt-4 border-t">
                        <button type="submit" class="bg-gray-800 text-white font-bold py-2 px-6 rounded hover:bg-gray-700">Create PO</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function poForm() {
            return {
                items: [{ product_id: '', quantity: 1, cost: 0 }],
                addItem() { this.items.push({ product_id: '', quantity: 1, cost: 0 }); },
                removeItem(index) { this.items.splice(index, 1); },
                updateCost(index) {
                    // Simple hack to get cost from option attribute if needed, 
                    // or user types it manually. For now letting user type or handle via backend
                }
            }
        }
    </script>
</x-app-layout>
