@extends('layouts.app')

@section('content')
    <div class="flex flex-1 flex-col space-y-8 p-6 md:p-8 max-w-7xl mx-auto w-full animate-in fade-in duration-500">

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('tenant.orders.index') }}"
                    class="group flex h-10 w-10 items-center justify-center rounded-xl bg-background border border-border/50 shadow-sm transition-all hover:bg-accent hover:text-accent-foreground hover:scale-105 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-muted-foreground group-hover:text-foreground transition-colors">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </a>
                <div class="space-y-1">
                    <h1
                        class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                        Create New Order
                    </h1>
                    <p class="text-muted-foreground text-sm">
                        Create a new sales order, add products, and assign to a customer.
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl shadow-lg shadow-black/5 overflow-hidden"
            x-data="orderForm({{ json_encode($customers->map(fn($c) => ['id' => $c->id, 'addresses' => $c->addresses])->values()) }})">

            <!-- Decoration Line -->
            <div class="h-1 w-full bg-gradient-to-r from-primary/20 via-primary/50 to-primary/20"></div>

            <form action="{{ route('tenant.orders.store') }}" method="POST" class="p-6 md:p-8 space-y-8">
                @csrf

                <!-- Error Handling -->
                @if ($errors->any())
                    <div
                        class="mb-4 p-4 rounded-xl bg-destructive/10 border border-destructive/20 text-destructive text-sm font-medium animate-in slide-in-from-top-2">
                        <div class="flex items-center gap-2 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="m15 9-6 6" />
                                <path d="m9 9 6 6" />
                            </svg>
                            <span>Please correct the following errors:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 opacity-90">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid gap-8 lg:grid-cols-3">

                    <!-- Main Order Details -->
                    <div class="lg:col-span-2 space-y-8">

                        <!-- Basic Components -->
                        <div class="rounded-xl border border-border/50 bg-background/40 p-6 space-y-6">
                            <div class="flex items-center gap-2 pb-2 border-b border-border/40">
                                <h2 class="text-lg font-semibold tracking-tight text-foreground/80">Order Details</h2>
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label for="customer_id"
                                        class="text-sm font-medium leading-none text-foreground/80">Customer <span
                                            class="text-destructive">*</span></label>
                                    <select name="customer_id" id="customer_id" x-model="customerId" required
                                        class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->first_name }}
                                                {{ $customer->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label for="warehouse_id"
                                        class="text-sm font-medium leading-none text-foreground/80">Warehouse <span
                                            class="text-destructive">*</span></label>
                                    <select name="warehouse_id" id="warehouse_id" required
                                        class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                                        <option value="">Select Warehouse</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-2 sm:col-span-2">
                                    <label for="order_number"
                                        class="text-sm font-medium leading-none text-foreground/80">Order Number</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted-foreground">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                                <path d="M3 6h18" />
                                                <path d="M16 10a4 4 0 0 1-8 0" />
                                            </svg>
                                        </div>
                                        <input type="text" name="order_number" id="order_number"
                                            value="ORD-{{ strtoupper(Str::random(8)) }}" readonly
                                            class="flex h-10 w-full pl-9 rounded-xl border border-input bg-muted/50 px-3 py-2 text-sm text-muted-foreground ring-offset-background cursor-not-allowed">
                                    </div>
                                </div>
                            </div>

                            <!-- Address Section -->
                            <div class="grid gap-6 sm:grid-cols-2 pt-4 border-t border-border/30"
                                x-show="availableAddresses.length > 0" x-transition>
                                <div class="space-y-2">
                                    <label for="billing_address_id"
                                        class="text-sm font-medium leading-none text-foreground/80">Billing Address <span
                                            class="text-destructive">*</span></label>
                                    <select name="billing_address_id" id="billing_address_id" x-model="billingAddressId"
                                        required
                                        class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                                        <option value="">Select Address</option>
                                        <template x-for="addr in availableAddresses" :key="addr.id">
                                            <option :value="addr.id"
                                                x-text="addr.label || (addr.address_line1 + ', ' + (addr.city || addr.village))">
                                            </option>
                                        </template>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label for="shipping_address_id"
                                            class="text-sm font-medium leading-none text-foreground/80">Shipping
                                            Address</label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" x-model="sameAsBilling"
                                                class="rounded border-primary text-primary focus:ring-primary/20">
                                            <span class="text-xs text-muted-foreground">Same as Billing</span>
                                        </label>
                                    </div>
                                    <select name="shipping_address_id" id="shipping_address_id" x-model="shippingAddressId"
                                        :disabled="sameAsBilling"
                                        class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm disabled:opacity-50">
                                        <option value="">Select Address</option>
                                        <template x-for="addr in availableAddresses" :key="addr.id">
                                            <option :value="addr.id"
                                                x-text="addr.label || (addr.address_line1 + ', ' + (addr.city || addr.village))">
                                            </option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <!-- No Address Warning -->
                            <div x-show="customerId && availableAddresses.length === 0"
                                class="p-4 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-600 text-sm"
                                x-cloak>
                                <p class="font-medium">Selected customer has no saved addresses.</p>
                                <p class="text-xs opacity-90 mt-1">Please add an address to the customer profile before
                                    placing an order.</p>
                            </div>

                            <!-- Order Type Section -->
                            <div class="grid gap-6 sm:grid-cols-2 pt-4 border-t border-border/30"
                                x-data="{ isFuture: false }">
                                <div class="space-y-4">
                                    <label class="text-sm font-medium leading-none text-foreground/80 block">Order
                                        Type</label>
                                    <div class="flex items-center gap-4">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="is_future_order" value="0" x-model="isFuture"
                                                @click="isFuture = false" checked
                                                class="text-primary focus:ring-primary/20">
                                            <span class="text-sm">Immediate</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="is_future_order" value="1" x-model="isFuture"
                                                @click="isFuture = true" class="text-primary focus:ring-primary/20">
                                            <span class="text-sm">Future Order</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="space-y-2" x-show="isFuture"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0">
                                    <label for="scheduled_at"
                                        class="text-sm font-medium leading-none text-foreground/80">Scheduled At <span
                                            class="text-destructive">*</span></label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted-foreground">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                                                <line x1="16" x2="16" y1="2" y2="6" />
                                                <line x1="8" x2="8" y1="2" y2="6" />
                                                <line x1="3" x2="21" y1="10" y2="10" />
                                            </svg>
                                        </div>
                                        <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                                            :required="isFuture"
                                            class="flex h-10 w-full pl-9 rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between gap-2 pb-2 border-b border-border/40">
                                <h2 class="text-lg font-semibold tracking-tight text-foreground/80">Order Items</h2>
                                <button type="button" @click="addItem()"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-primary/10 px-3 py-1.5 text-xs font-medium text-primary hover:bg-primary/20 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    Add Item
                                </button>
                            </div>

                            <div class="rounded-xl border border-border/50 bg-background/40 overflow-hidden">
                                <div class="overflow-auto">
                                    <table class="w-full text-sm">
                                        <thead class="bg-muted/50 text-muted-foreground font-medium">
                                            <tr>
                                                <th class="px-4 py-3 text-left w-1/3">Product</th>
                                                <th class="px-4 py-3 text-center w-24">Qty</th>
                                                <th class="px-4 py-3 text-right w-32">Price</th>
                                                <th class="px-4 py-3 text-center w-32">Discount</th>
                                                <th class="px-4 py-3 text-right w-32">Total</th>
                                                <th class="px-4 py-3 text-center w-12"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border/30">
                                            <template x-for="(item, index) in items" :key="index">
                                                <tr class="group hover:bg-muted/30 transition-colors">
                                                    <td class="px-4 py-3 relative" @click.away="item.searchResults = []">
                                                        <input type="hidden" :name="'items['+index+'][product_id]'"
                                                            x-model="item.product_id">
                                                        <input type="text" x-model="item.product_query"
                                                            @input.debounce.300ms="searchProduct(index, $event.target.value)"
                                                            class="flex h-9 w-full rounded-lg border border-input bg-background/50 px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary/50 transition-all placeholder:text-muted-foreground/50"
                                                            placeholder="Search Product...">

                                                        <!-- Dropdown -->
                                                        <div x-show="item.searchResults && item.searchResults.length > 0"
                                                            class="absolute z-50 left-0 w-[150%] mt-1 bg-card border border-border rounded-xl shadow-xl max-h-60 overflow-y-auto">
                                                            <template x-for="result in item.searchResults" :key="result.id">
                                                                <div @click="selectProduct(index, result)"
                                                                    class="px-4 py-3 hover:bg-accent hover:text-accent-foreground cursor-pointer text-sm border-b border-border/50 last:border-0 transition-colors">
                                                                    <div class="font-semibold text-foreground"
                                                                        x-text="result.name"></div>
                                                                    <div
                                                                        class="text-xs text-muted-foreground mt-0.5 flex justify-between">
                                                                        <span>SKU: <span x-text="result.sku"></span></span>
                                                                        <span class="font-mono">Rs <span
                                                                                x-text="result.price"></span></span>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <input type="number" :name="'items['+index+'][quantity]'"
                                                            x-model="item.quantity" min="1"
                                                            class="flex h-9 w-20 mx-auto rounded-lg border border-input bg-background/50 px-2 text-center text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary/50 transition-all">
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <input type="number" step="0.01" :name="'items['+index+'][price]'"
                                                            x-model="item.price" min="0"
                                                            class="flex h-9 w-28 ml-auto rounded-lg border border-input bg-background/50 px-3 text-right text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary/50 transition-all">
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex items-center gap-1">
                                                            <select :name="'items['+index+'][discount_type]'"
                                                                x-model="item.discount_type"
                                                                class="h-9 rounded-lg border-border bg-background/50 text-[10px] focus:ring-primary/20">
                                                                <option value="fixed">Rs</option>
                                                                <option value="percent">%</option>
                                                            </select>
                                                            <input type="number" step="0.01"
                                                                :name="'items['+index+'][discount_value]'"
                                                                x-model="item.discount_value"
                                                                class="flex h-9 w-16 rounded-lg border border-input bg-background/50 px-2 text-right text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 text-right font-medium text-foreground/90">
                                                        Rs <span x-text="calculateItemTotal(item).toFixed(2)"></span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <button type="button" @click="removeItem(index)"
                                                            class="text-muted-foreground/50 group-hover:text-destructive hover:bg-destructive/10 p-1.5 rounded-md transition-all">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round">
                                                                <path d="M3 6h18" />
                                                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                                                <line x1="10" x2="10" y1="11" y2="17" />
                                                                <line x1="14" x2="14" y1="11" y2="17" />
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Empty State -->
                                <template x-if="items.length === 0">
                                    <div
                                        class="p-8 text-center text-muted-foreground border-t border-dashed border-border/50">
                                        <p>No items added yet. Click "Add Item" to start.</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar Summary -->
                    <div class="space-y-8">
                        <div class="rounded-xl border border-border/50 bg-muted/20 p-6 space-y-6 sticky top-24">
                            <div class="flex items-center gap-2 pb-2 border-b border-border/40">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="text-primary">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                    <path d="M3 6h18" />
                                    <path d="M16 10a4 4 0 0 1-8 0" />
                                </svg>
                                <h3 class="font-semibold text-foreground/80">Order Summary</h3>
                            </div>

                            <div class="space-y-4 text-sm">
                                <div class="flex justify-between items-center text-muted-foreground">
                                    <span>Subtotal</span>
                                    <span>Rs <span x-text="subTotal.toFixed(2)"></span></span>
                                </div>
                                <div class="flex justify-between items-center text-primary text-xs"
                                    x-show="itemDiscountTotal > 0">
                                    <span>Item Discounts</span>
                                    <span>- Rs <span x-text="itemDiscountTotal.toFixed(2)"></span></span>
                                </div>

                                <div class="pt-2 border-t border-border/20">
                                    <label class="text-[10px] font-bold text-muted-foreground uppercase mb-1 block">Order
                                        Discount</label>
                                    <div class="flex gap-2">
                                        <select name="discount_type" x-model="orderDiscountType"
                                            class="h-8 rounded-lg border-border bg-background/50 text-[10px] focus:ring-primary/20">
                                            <option value="fixed">Rs</option>
                                            <option value="percent">%</option>
                                        </select>
                                        <input type="number" step="0.01" name="discount_value" x-model="orderDiscountValue"
                                            class="flex h-8 w-full rounded-lg border border-input bg-background/50 px-2 text-right text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                                    </div>
                                </div>

                                <div class="flex justify-between items-center text-muted-foreground">
                                    <span>Shipping</span>
                                    <span>Rs 0.00</span>
                                </div>
                                <div class="h-px bg-border/50"></div>
                                <div class="flex justify-between items-center font-bold text-lg text-foreground">
                                    <span>Total</span>
                                    <span>Rs <span x-text="grandTotal.toFixed(2)"></span></span>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                                Confirm Order
                            </button>

                            <a href="{{ route('tenant.orders.index') }}"
                                class="w-full inline-flex items-center justify-center rounded-xl bg-background border border-input px-4 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground">
                                Cancel
                            </a>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script>
        function orderForm(customersData = []) {
            return {
                customers: customersData,
                customerId: '',
                billingAddressId: '',
                shippingAddressId: '',
                sameAsBilling: true,
                availableAddresses: [],

                items: [
                    { product_id: '', product_query: '', quantity: 1, price: 0, discount_type: 'fixed', discount_value: 0, searchResults: [] }
                ],
                orderDiscountType: 'fixed',
                orderDiscountValue: 0,

                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('customer_id')) {
                        this.customerId = urlParams.get('customer_id');
                    }

                    this.$watch('customerId', (val) => {
                        const cust = this.customers.find(c => c.id == val);
                        this.availableAddresses = cust ? cust.addresses : [];

                        // Auto-select default
                        const def = this.availableAddresses.find(a => a.is_default);
                        if (def) {
                            this.billingAddressId = def.id;
                        } else if (this.availableAddresses.length > 0) {
                            this.billingAddressId = this.availableAddresses[0].id;
                        } else {
                            this.billingAddressId = '';
                        }
                    });
                },

                get subTotal() {
                    return this.items.reduce((sum, item) => sum + (item.quantity * item.price), 0);
                },

                get itemDiscountTotal() {
                    return this.items.reduce((sum, item) => {
                        let d = 0;
                        if (item.discount_type === 'percent') {
                            d = (item.quantity * item.price) * (item.discount_value / 100);
                        } else {
                            d = parseFloat(item.discount_value || 0);
                        }
                        return sum + d;
                    }, 0);
                },

                get orderDiscountAmount() {
                    let afterItemDiscounts = this.subTotal - this.itemDiscountTotal;
                    if (this.orderDiscountType === 'percent') {
                        return afterItemDiscounts * (this.orderDiscountValue / 100);
                    }
                    return parseFloat(this.orderDiscountValue || 0);
                },

                get grandTotal() {
                    return Math.max(0, this.subTotal - this.itemDiscountTotal - this.orderDiscountAmount);
                },

                calculateItemTotal(item) {
                    let base = item.quantity * item.price;
                    let d = 0;
                    if (item.discount_type === 'percent') {
                        d = base * (item.discount_value / 100);
                    } else {
                        d = parseFloat(item.discount_value || 0);
                    }
                    return Math.max(0, base - d);
                },

                addItem() {
                    this.items.push({ product_id: '', product_query: '', quantity: 1, price: 0, discount_type: 'fixed', discount_value: 0, searchResults: [] });
                },
                searchProduct(index, query) {
                    if (query.length < 2) {
                        this.items[index].searchResults = [];
                        return;
                    }

                    fetch(`{{ route('tenant.products.search') }}?query=${query}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(response => response.json())
                        .then(data => {
                            this.items[index].searchResults = data;
                        });
                },
                selectProduct(index, product) {
                    this.items[index].product_id = product.id;
                    this.items[index].product_query = product.name;
                    this.items[index].price = parseFloat(product.price);
                    this.items[index].discount_type = product.default_discount_type || 'fixed';
                    this.items[index].discount_value = parseFloat(product.default_discount_value || 0);

                    this.items[index].searchResults = [];
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    } else {
                        this.items[0].product_id = '';
                        this.items[0].product_query = '';
                        this.items[0].quantity = 1;
                        this.items[0].price = 0;
                        this.items[0].discount_value = 0;
                        this.items[0].searchResults = [];
                    }
                }
            }
        }
    </script>
@endsection