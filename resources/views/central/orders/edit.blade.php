<x-app-layout>
    <div class="flex flex-1 flex-col p-8 animate-in fade-in duration-500" x-data="orderForm({{ $order->toJson() }})">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-foreground">Edit Order #{{ $order->order_number }}</h1>
            <x-ui.button variant="outline" href="{{ route('central.orders.show', $order) }}">
                Cancel
            </x-ui.button>
        </div>

        @if(!in_array($order->status, ['draft', 'pending']))
            <div class="mb-6 p-4 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center gap-2">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="text-sm font-medium">Warning: This order is {{ $order->status }}. Editing it may affect inventory and shipments.</span>
            </div>
        @endif

        <form action="{{ route('central.orders.update', $order) }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @csrf
            @method('PUT')
            
            <div class="lg:col-span-2 space-y-6">
                 <!-- Order Items -->
                <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm">
                    <div class="p-6 border-b border-border flex justify-between items-center">
                        <h3 class="font-semibold">Items</h3>
                        <button type="button" @click="addItem()" class="text-sm font-medium text-primary hover:underline">+ Add Item</button>
                    </div>
                    <div class="p-0">
                         <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/40">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Product</th>
                                        <th class="px-4 py-3 text-left font-medium text-muted-foreground w-24">Stock</th>
                                        <th class="px-4 py-3 text-left font-medium text-muted-foreground w-32">Price</th>
                                        <th class="px-4 py-3 text-left font-medium text-muted-foreground w-24">Qty</th>
                                        <th class="px-4 py-3 text-right font-medium text-muted-foreground w-32">Total</th>
                                        <th class="px-4 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/50">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr class="group hover:bg-muted/10">
                                            <td class="p-4">
                                                <select :name="'items['+index+'][product_id]'" x-model="item.product_id" @change="updateLineItem(index)" class="w-full rounded-lg border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-1 focus:ring-primary" required>
                                                    <option value="">Select Product...</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product['id'] }}" data-price="{{ $product['price'] }}" data-stock="{{ $product['stock_on_hand'] }}" data-unit="{{ $product['unit_type'] }}">
                                                            {{ $product['name'] }} ({{ $product['sku'] }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="p-4 text-muted-foreground">
                                                <span x-text="item.stock"></span> <span x-text="item.unit"></span>
                                            </td>
                                            <td class="p-4">
                                                <div class="relative">
                                                     <span class="absolute left-2 top-2.5 text-muted-foreground">Rs</span>
                                                     <input type="number" step="0.01" :name="'items['+index+'][price]'" x-model="item.price" class="w-full pl-8 rounded-lg border-border bg-background shadow-sm focus:border-primary focus:ring-1 focus:ring-primary" required>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <input type="number" step="1" :name="'items['+index+'][quantity]'" x-model="item.quantity" class="w-full rounded-lg border-border bg-background shadow-sm focus:border-primary focus:ring-1 focus:ring-primary" min="1" required>
                                            </td>
                                            <td class="p-4 text-right font-medium">
                                                Rs <span x-text="(item.quantity * item.price).toFixed(2)"></span>
                                            </td>
                                            <td class="p-4 text-center">
                                                <button type="button" @click="removeItem(index)" class="text-muted-foreground hover:text-destructive transition-colors">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end pr-6">
                    <button type="button" @click="addItem()" class="flex items-center gap-2 text-sm font-medium text-primary hover:bg-primary/5 px-3 py-2 rounded-lg transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Another Item
                    </button>
                </div>
            </div>

            <!-- Right Sidebar: Details & Summary -->
            <div class="space-y-6">
                <!-- Order Details -->
                <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm p-6 space-y-4">
                    <h3 class="font-semibold">Order Details</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1.5">Order Number</label>
                        <input type="text" name="order_number" value="{{ $order->order_number }}" class="w-full rounded-lg border-border bg-muted/50 text-foreground font-mono shadow-sm" readonly>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1.5">Customer</label>
                        <select name="customer_id" class="w-full rounded-lg border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-1 focus:ring-primary" required>
                             <option value="">Select Customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ $order->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->first_name }} {{ $customer->last_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1.5">Fulfillment Warehouse</label>
                        <select name="warehouse_id" class="w-full rounded-lg border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-1 focus:ring-primary" required>
                             <option value="">Select Warehouse...</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $order->warehouse_id == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm p-6 bg-muted/20">
                    <h3 class="font-semibold mb-4">Summary</h3>
                     <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>Rs <span x-text="grandTotal.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between text-muted-foreground">
                            <span>Tax (0%)</span>
                            <span>Rs 0.00</span>
                        </div>
                        <div class="h-px bg-border my-2"></div>
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span>Rs <span x-text="grandTotal.toFixed(2)"></span></span>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full mt-6 flex justify-center items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/25 hover:bg-primary/90 transition-all">
                        Update Order
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function orderForm(orderData) {
            return {
                items: orderData.items.map(item => ({
                    product_id: item.product_id,
                    quantity: parseFloat(item.quantity),
                    price: parseFloat(item.unit_price),
                    stock: '-', // Initial stock unknown until manual match or API load, or we assume safe
                    unit: '' 
                })) || [
                    { product_id: '', quantity: 1, price: 0, stock: '-', unit: '' }
                ],
                get grandTotal() {
                    return this.items.reduce((sum, item) => sum + (item.quantity * item.price), 0);
                },
                addItem() {
                    this.items.push({ product_id: '', quantity: 1, price: 0, stock: '-', unit: '' });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },
                updateLineItem(index) {
                    const select = document.getElementsByName('items['+index+'][product_id]')[0];
                    if (select && select.selectedIndex > 0) {
                        const option = select.options[select.selectedIndex];
                        this.items[index].price = parseFloat(option.dataset.price);
                        this.items[index].stock = option.dataset.stock;
                        this.items[index].unit = option.dataset.unit || '';
                    } else {
                        this.items[index].price = 0;
                        this.items[index].stock = '-';
                        this.items[index].unit = '';
                    }
                }
            }
        }
    </script>
</x-app-layout>
