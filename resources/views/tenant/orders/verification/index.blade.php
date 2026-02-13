@extends('layouts.app')

@section('content')
    <div id="verification-page-wrapper"
        class="flex flex-1 flex-col space-y-6 p-4 md:p-8 animate-in fade-in duration-500 bg-background/50">

        <!-- Header Area -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-1.5">
                <h1
                    class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/60 bg-clip-text text-transparent">
                    Order Verification</h1>
                <p class="text-muted-foreground text-sm font-medium">Verify new orders and manage customer follow-ups.</p>
            </div>

            <!-- Status Tabs -->
            <div
                class="flex items-center p-1 bg-muted/60 rounded-xl border border-border/40 backdrop-blur-sm self-start sm:self-auto overflow-x-auto max-w-full no-scrollbar shadow-inner">


                <a href="{{ route('tenant.orders.verification.index', ['status' => 'unverified']) }}"
                    class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-all duration-300 whitespace-nowrap {{ request('status', 'unverified') === 'unverified' ? 'bg-background text-amber-600 shadow-sm ring-1 ring-amber-500/10' : 'text-muted-foreground/80 hover:text-amber-600 hover:bg-background/40' }}">
                    Unverified
                </a>
                <div class="w-px h-4 bg-border/40 mx-1 shrink-0"></div>

                <a href="{{ route('tenant.orders.verification.index', ['status' => 'pending_followup']) }}"
                    class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-all duration-300 whitespace-nowrap {{ request('status') === 'pending_followup' ? 'bg-background text-blue-600 shadow-sm ring-1 ring-blue-500/10' : 'text-muted-foreground/80 hover:text-blue-600 hover:bg-background/40' }}">
                    Pending Follow-up
                </a>
                <div class="w-px h-4 bg-border/40 mx-1 shrink-0"></div>

                <a href="{{ route('tenant.orders.verification.index', ['status' => 'verified']) }}"
                    class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-all duration-300 whitespace-nowrap {{ request('status') === 'verified' ? 'bg-background text-emerald-600 shadow-sm ring-1 ring-emerald-500/10' : 'text-muted-foreground/80 hover:text-emerald-600 hover:bg-background/40' }}">
                    Verified
                </a>
                <div class="w-px h-4 bg-border/40 mx-1 shrink-0"></div>

                <a href="{{ route('tenant.orders.verification.index', ['status' => 'cancelled']) }}"
                    class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-all duration-300 whitespace-nowrap {{ request('status') === 'cancelled' ? 'bg-background text-destructive shadow-sm ring-1 ring-destructive/10' : 'text-muted-foreground/80 hover:text-destructive hover:bg-background/40' }}">
                    Cancelled
                </a>
                <div class="w-px h-4 bg-border/40 mx-1 shrink-0"></div>

                <a href="{{ route('tenant.orders.verification.index', ['status' => 'all']) }}"
                    class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-all duration-300 whitespace-nowrap {{ request('status') === 'all' ? 'bg-background text-primary shadow-sm ring-1 ring-primary/10' : 'text-muted-foreground/80 hover:text-primary hover:bg-background/40' }}">
                    All Orders
                </a>
            </div>
        </div>

        <div id="orders-table-container" x-data="{ selected: [], verifyModalOpen: false, activeOrder: null }">

            <!-- Verification Modal -->
            <div x-show="verifyModalOpen" style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="bg-background w-full max-w-5xl rounded-2xl shadow-2xl relative overflow-hidden flex flex-col max-h-[90vh] ring-1 ring-black/5"
                    @click.away="verifyModalOpen = false" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-8 scale-95">

                    <!-- Header -->
                    <div
                        class="px-8 py-5 border-b border-border/40 flex justify-between items-center bg-muted/10 backdrop-blur-sm sticky top-0 z-10">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-foreground">Verify Order</h2>
                            <div class="flex items-center gap-3 mt-1.5 text-sm text-muted-foreground">
                                <span
                                    class="flex items-center gap-1.5 bg-muted/50 px-2 py-0.5 rounded-md border border-border/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                        <path d="M3 6h18" />
                                        <path d="M16 10a4 4 0 0 1-8 0" />
                                    </svg>
                                    <span class="font-mono font-medium" x-text="activeOrder?.order_number"></span>
                                </span>
                                <span class="text-border">|</span>
                                <span
                                    x-text="new Date(activeOrder?.created_at).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })"></span>
                            </div>
                        </div>
                        <button @click="verifyModalOpen = false"
                            class="text-muted-foreground hover:text-foreground hover:bg-muted/50 p-2 rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 overflow-y-auto custom-scrollbar flex-1 pr-1">
                        <!-- Order Details Column -->
                        <div class="md:col-span-7 space-y-6">
                            <!-- Customer Info Card -->
                            <div
                                class="flex items-start gap-4 p-5 rounded-xl bg-card border border-border/50 shadow-sm hover:shadow-md transition-shadow duration-300">
                                <div class="p-3 bg-primary/10 rounded-full text-primary shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                </div>
                                <div class="grid grid-cols-2 gap-x-8 gap-y-4 w-full">
                                    <div>
                                        <span
                                            class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold block mb-1">Customer
                                            Name</span>
                                        <span class="font-semibold text-base text-foreground"
                                            x-text="(activeOrder?.customer?.first_name || '') + ' ' + (activeOrder?.customer?.last_name || '')"></span>
                                    </div>
                                    <div>
                                        <span
                                            class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold block mb-1">Mobile</span>
                                        <span class="font-medium font-mono text-sm"
                                            x-text="activeOrder?.customer?.mobile || '-'"></span>
                                    </div>
                                    <div class="col-span-2 pt-2 border-t border-border/30">
                                        <span
                                            class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold block mb-1">Email</span>
                                        <span class="font-medium text-sm text-foreground/80"
                                            x-text="activeOrder?.customer?.email || '-'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Addresses -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Billing Address -->
                                <template x-if="activeOrder?.billing_address">
                                    <div
                                        class="flex flex-col h-full rounded-xl border border-border/50 bg-card shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group">
                                        <div
                                            class="px-5 py-3 bg-muted/20 border-b border-border/40 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="text-primary group-hover:scale-110 transition-transform duration-300">
                                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                                <polyline points="9 22 9 12 15 12 15 22" />
                                            </svg>
                                            <h3 class="text-sm font-semibold text-foreground/90">Billing Address</h3>
                                        </div>
                                        <div class="p-5 flex-1 relative">
                                            <div class="grid grid-cols-2 gap-x-3 gap-y-4 text-xs">
                                                <div class="col-span-2">
                                                    <span
                                                        class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">Address</span>
                                                    <span class="font-medium text-foreground leading-relaxed">
                                                        <span x-text="activeOrder.billing_address.address_line1"></span>
                                                        <span x-show="activeOrder.billing_address.address_line2"
                                                            x-text="', ' + activeOrder.billing_address.address_line2"></span>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span
                                                        class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">Village</span>
                                                    <span class="font-medium text-foreground"
                                                        x-text="activeOrder.billing_address.village || '-'"></span>
                                                </div>
                                                <div>
                                                    <span
                                                        class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">Taluka</span>
                                                    <span class="font-medium text-foreground"
                                                        x-text="activeOrder.billing_address.taluka || '-'"></span>
                                                </div>
                                                <div>
                                                    <span
                                                        class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">District</span>
                                                    <span class="font-medium text-foreground"
                                                        x-text="activeOrder.billing_address.district || '-'"></span>
                                                </div>
                                                <div>
                                                    <span
                                                        class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">State</span>
                                                    <span class="font-medium text-foreground"
                                                        x-text="activeOrder.billing_address.state || '-'"></span>
                                                </div>
                                                <div
                                                    class="col-span-2 pt-2 mt-1 border-t border-border/40 flex justify-between items-end">
                                                    <div>
                                                        <span
                                                            class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">Pincode</span>
                                                        <span class="font-bold text-primary font-mono"
                                                            x-text="activeOrder.billing_address.pincode || '-'"></span>
                                                    </div>
                                                    <div class="text-right"
                                                        x-show="activeOrder.billing_address.contact_phone">
                                                        <span
                                                            class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">Phone</span>
                                                        <span class="font-medium font-mono"
                                                            x-text="activeOrder.billing_address.contact_phone"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!activeOrder?.billing_address">
                                    <div
                                        class="rounded-xl border border-dashed border-border bg-muted/10 flex flex-col items-center justify-center p-8 text-center h-full min-h-[200px]">
                                        <div class="p-3 bg-muted/20 rounded-full mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="text-muted-foreground/50">
                                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                                <polyline points="9 22 9 12 15 12 15 22" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-muted-foreground">No Billing Address</p>
                                    </div>
                                </template>

                                <!-- Shipping Address -->
                                <template x-if="activeOrder?.shipping_address">
                                    <div
                                        class="flex flex-col h-full rounded-xl border border-border/50 bg-card shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group">
                                        <div
                                            class="px-5 py-3 bg-muted/20 border-b border-border/40 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="text-primary group-hover:scale-110 transition-transform duration-300">
                                                <rect x="1" y="3" width="15" height="13" rx="2" ry="2" />
                                                <line x1="16" y1="8" x2="20" y2="8" />
                                                <line x1="16" y1="16" x2="23" y2="16" />
                                                <path d="M16 12h7" />
                                            </svg>
                                            <h3 class="text-sm font-semibold text-foreground/90">Shipping Address</h3>
                                        </div>
                                        <div class="p-5 flex-1 relative">
                                            <div class="grid grid-cols-2 gap-x-3 gap-y-4 text-xs">
                                                <div class="col-span-2">
                                                    <span
                                                        class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">Address</span>
                                                    <span class="font-medium text-foreground leading-relaxed">
                                                        <span x-text="activeOrder.shipping_address.address_line1"></span>
                                                        <span x-show="activeOrder.shipping_address.address_line2"
                                                            x-text="', ' + activeOrder.shipping_address.address_line2"></span>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span
                                                        class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">Village</span>
                                                    <span class="font-medium text-foreground"
                                                        x-text="activeOrder.shipping_address.village || '-'"></span>
                                                </div>
                                                <div>
                                                    <span
                                                        class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">Taluka</span>
                                                    <span class="font-medium text-foreground"
                                                        x-text="activeOrder.shipping_address.taluka || '-'"></span>
                                                </div>
                                                <div>
                                                    <span
                                                        class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">District</span>
                                                    <span class="font-medium text-foreground"
                                                        x-text="activeOrder.shipping_address.district || '-'"></span>
                                                </div>
                                                <div>
                                                    <span
                                                        class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">State</span>
                                                    <span class="font-medium text-foreground"
                                                        x-text="activeOrder.shipping_address.state || '-'"></span>
                                                </div>
                                                <div
                                                    class="col-span-2 pt-2 mt-1 border-t border-border/40 flex justify-between items-end">
                                                    <div>
                                                        <span
                                                            class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">Pincode</span>
                                                        <span class="font-bold text-primary font-mono"
                                                            x-text="activeOrder.shipping_address.pincode || '-'"></span>
                                                    </div>
                                                    <div class="text-right"
                                                        x-show="activeOrder.shipping_address.contact_phone">
                                                        <span
                                                            class="text-[10px] uppercase tracking-wider text-muted-foreground/80 font-semibold block mb-0.5">Phone</span>
                                                        <span class="font-medium font-mono"
                                                            x-text="activeOrder.shipping_address.contact_phone"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!activeOrder?.shipping_address">
                                    <div
                                        class="rounded-xl border border-dashed border-border bg-muted/10 flex flex-col items-center justify-center p-8 text-center h-full min-h-[200px]">
                                        <div class="p-3 bg-muted/20 rounded-full mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="text-muted-foreground/50">
                                                <rect x="1" y="3" width="15" height="13" rx="2" ry="2" />
                                                <line x1="16" y1="8" x2="20" y2="8" />
                                                <line x1="16" y1="16" x2="23" y2="16" />
                                                <path d="M16 12h7" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-muted-foreground">No Shipping Address</p>
                                    </div>
                                </template>
                            </div>

                            <!-- Order Items -->
                            <div class="space-y-4">
                                <h3 class="text-base font-semibold text-foreground flex items-center gap-2">
                                    <div class="w-1 h-5 bg-primary rounded-full"></div>
                                    Order Summary
                                </h3>
                                <div class="rounded-xl border border-border/50 overflow-hidden shadow-sm">
                                    <table class="w-full text-sm">
                                        <thead class="bg-muted/30 text-xs text-muted-foreground uppercase tracking-wider">
                                            <tr>
                                                <th class="px-4 py-3 text-left font-semibold">Item Details</th>
                                                <th class="px-4 py-3 text-center font-semibold">Qty</th>
                                                <th class="px-4 py-3 text-right font-semibold">Price</th>
                                                <th class="px-4 py-3 text-right font-semibold">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border/30 bg-card">
                                            <template x-for="item in activeOrder?.items" :key="item.id">
                                                <tr class="hover:bg-muted/10 transition-colors">
                                                    <td class="px-4 py-3">
                                                        <div class="font-medium text-foreground" x-text="item.product_name">
                                                        </div>
                                                        <div class="text-[11px] text-muted-foreground font-mono mt-0.5"
                                                            x-text="'SKU: ' + item.sku">
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 text-center font-medium" x-text="item.quantity">
                                                    </td>
                                                    <td class="px-4 py-3 text-right text-muted-foreground"
                                                        x-text="parseFloat(item.unit_price).toFixed(2)"></td>
                                                    <td class="px-4 py-3 text-right font-semibold text-foreground"
                                                        x-text="parseFloat(item.total_price).toFixed(2)"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                        <tfoot class="bg-muted/10">
                                            <tr>
                                                <td colspan="3"
                                                    class="px-4 py-2 text-right text-xs text-muted-foreground font-medium">
                                                    Subtotal</td>
                                                <td class="px-4 py-2 text-right font-medium"
                                                    x-text="parseFloat(activeOrder?.total_amount || 0).toFixed(2)"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3"
                                                    class="px-4 py-2 text-right text-xs text-muted-foreground font-medium">
                                                    Discount</td>
                                                <td class="px-4 py-2 text-right text-emerald-600 font-medium"
                                                    x-text="'-' + parseFloat(activeOrder?.discount_amount || 0).toFixed(2)">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3"
                                                    class="px-4 py-2 text-right text-xs text-muted-foreground font-medium">
                                                    Tax</td>
                                                <td class="px-4 py-2 text-right font-medium"
                                                    x-text="parseFloat(activeOrder?.tax_amount || 0).toFixed(2)"></td>
                                            </tr>
                                            <tr class="border-t border-border/40 bg-muted/20">
                                                <td colspan="3"
                                                    class="px-4 py-3 text-right text-sm font-bold text-foreground">Grand
                                                    Total</td>
                                                <td class="px-4 py-3 text-right text-lg font-bold text-primary"
                                                    x-text="parseFloat(activeOrder?.grand_total || 0).toFixed(2)"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Edit Button -->
                            <template
                                x-if="!(['verified', 'rejected'].includes(activeOrder?.verification_status) || ['confirmed', 'cancelled'].includes(activeOrder?.status))">
                                <div class="flex justify-start">
                                    <a :href="`{{ url('orders') }}/${activeOrder?.id}/edit`"
                                        class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:text-primary/80 hover:underline transition-colors group">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="group-hover:translate-x-0.5 transition-transform">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                                        </svg>
                                        Edit Order Details
                                    </a>
                                </div>
                            </template>
                        </div>

                        <!-- Verification Form Column -->
                        <div class="md:col-span-5 md:border-l border-border/50 md:pl-6">
                            <!-- Locked State Message -->
                            <template
                                x-if="['verified', 'rejected'].includes(activeOrder?.verification_status) || ['confirmed', 'cancelled'].includes(activeOrder?.status)">
                                <div
                                    class="h-full flex flex-col items-center justify-center text-center p-6 space-y-4 bg-muted/10 rounded-xl border border-border/50">
                                    <div class="h-16 w-16 rounded-full flex items-center justify-center" :class="{
                                                    'bg-emerald-100 text-emerald-600': activeOrder?.verification_status === 'verified' || activeOrder?.status === 'confirmed',
                                                    'bg-red-100 text-red-600': activeOrder?.verification_status === 'rejected' || activeOrder?.status === 'cancelled'
                                                }">
                                        <svg x-show="activeOrder?.verification_status === 'verified' || activeOrder?.status === 'confirmed'"
                                            xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                            <polyline points="22 4 12 14.01 9 11.01" />
                                        </svg>
                                        <svg x-show="activeOrder?.verification_status === 'rejected' || activeOrder?.status === 'cancelled'"
                                            xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <line x1="15" y1="9" x2="9" y2="15" />
                                            <line x1="9" y1="9" x2="15" y2="15" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-foreground">Order Processed</h3>
                                        <p class="text-muted-foreground text-sm mt-1">
                                            This order has been <span class="font-semibold"
                                                x-text="activeOrder?.status === 'confirmed' ? 'confirmed' : (activeOrder?.status === 'cancelled' ? 'cancelled' : activeOrder?.verification_status)"></span>.
                                            Modifications specifically for verification are no longer available.
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <!-- Verification Form -->
                            <template
                                x-if="!(['verified', 'rejected'].includes(activeOrder?.verification_status) || ['confirmed', 'cancelled'].includes(activeOrder?.status))">
                                <form :action="`{{ url('orders') }}/${activeOrder?.id}/verification`" method="POST"
                                    class="space-y-4 h-full flex flex-col">
                                    @csrf

                                    <div class="space-y-3">
                                        <label class="block text-sm font-medium text-foreground">Verification Status</label>
                                        <div class="grid grid-cols-1 gap-3">
                                            <label class="cursor-pointer relative">
                                                <input type="radio" name="status" value="verified" class="peer sr-only">
                                                <div
                                                    class="p-3 rounded-lg border border-border bg-card hover:bg-accent/50 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-500/5 peer-checked:ring-1 peer-checked:ring-emerald-500 flex items-center gap-3">
                                                    <div
                                                        class="h-8 w-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12" />
                                                        </svg>
                                                    </div>
                                                    <div class="text-left">
                                                        <span class="text-sm font-semibold block">Verified</span>
                                                        <span class="text-[10px] text-muted-foreground block">Ready for
                                                            processing</span>
                                                    </div>
                                                </div>
                                            </label>

                                            <label class="cursor-pointer relative">
                                                <input type="radio" name="status" value="pending_followup"
                                                    class="peer sr-only" checked>
                                                <div
                                                    class="p-3 rounded-lg border border-border bg-card hover:bg-accent/50 transition-all peer-checked:border-amber-500 peer-checked:bg-amber-500/5 peer-checked:ring-1 peer-checked:ring-amber-500 flex items-center gap-3">
                                                    <div
                                                        class="h-8 w-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path
                                                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                                        </svg>
                                                    </div>
                                                    <div class="text-left">
                                                        <span class="text-sm font-semibold block">Follow-up</span>
                                                        <span class="text-[10px] text-muted-foreground block">Customer needs
                                                            to
                                                            be contacted</span>
                                                    </div>
                                                </div>
                                            </label>

                                            <label class="cursor-pointer relative">
                                                <input type="radio" name="status" value="rejected" class="peer sr-only">
                                                <div
                                                    class="p-3 rounded-lg border border-border bg-card hover:bg-accent/50 transition-all peer-checked:border-destructive peer-checked:bg-destructive/5 peer-checked:ring-1 peer-checked:ring-destructive flex items-center gap-3">
                                                    <div
                                                        class="h-8 w-8 rounded-full bg-destructive/10 text-destructive flex items-center justify-center shrink-0">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <circle cx="12" cy="12" r="10" />
                                                            <line x1="15" y1="9" x2="9" y2="15" />
                                                            <line x1="9" y1="9" x2="15" y2="15" />
                                                        </svg>
                                                    </div>
                                                    <div class="text-left">
                                                        <span class="text-sm font-semibold block">Rejected</span>
                                                        <span class="text-[10px] text-muted-foreground block">Cancel the
                                                            order</span>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1.5 text-foreground">Remarks</label>
                                        <textarea name="remarks" rows="4"
                                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 outline-none resize-none"
                                            placeholder="Enter remarks from customer interaction..." required></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1.5 text-foreground">Next Follow-up
                                            (Optional)</label>
                                        <div class="relative">
                                            <input type="datetime-local" name="next_followup_at"
                                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 outline-none">
                                        </div>
                                        <p class="text-[10px] text-muted-foreground mt-1">Leave empty if verifying or
                                            rejecting.
                                        </p>
                                    </div>

                                    <div class="pt-2 flex justify-end gap-3 border-t border-border mt-auto">
                                        <button type="button" @click="verifyModalOpen = false"
                                            class="px-4 py-2 rounded-lg text-sm font-medium text-muted-foreground hover:bg-accent transition-colors">Cancel</button>
                                        <button type="submit"
                                            class="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-semibold shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all">
                                            Save Verification
                                        </button>
                                    </div>
                                </form>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Control Bar -->
            <div
                class="flex flex-wrap items-center justify-between gap-4 p-2 pl-3 bg-white/40 dark:bg-black/20 border border-white/20 dark:border-white/5 backdrop-blur-xl rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] mb-6 transition-all duration-300 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
                <div class="flex items-center gap-3">
                    <form id="search-form" method="GET" action="{{ url()->current() }}"
                        class="relative transition-all duration-300 group-focus-within:w-64 w-56">
                        <input type="hidden" name="status" value="{{ request('status', 'unverified') }}">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="text-muted-foreground group-focus-within:text-primary transition-colors">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search (Order #, Name, Mobile...)"
                            class="block w-full rounded-xl border-border/50 py-2 pl-9 pr-8 text-foreground bg-background/50 placeholder:text-muted-foreground/70 focus:bg-background focus:ring-2 focus:ring-primary/20 text-sm leading-6 transition-all shadow-sm outline-none">
                        @if(request('search'))
                            <a href="{{ url()->current() }}?status={{ request('status', 'unverified') }}"
                                class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-muted-foreground hover:text-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div
                class="rounded-2xl border border-border/40 bg-card/60 backdrop-blur-xl shadow-[0_2px_20px_rgb(0,0,0,0.02)] overflow-hidden relative">
                <div id="table-loading"
                    class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent shadow-lg">
                    </div>
                </div>
                <div
                    class="border-b border-border/40 p-3 bg-muted/10 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-xs text-muted-foreground font-medium px-2">
                        <span
                            class="flex h-5 w-7 items-center justify-center rounded bg-background border border-border/50 font-bold text-foreground shadow-sm text-[10px]">
                            {{ $orders->total() }}
                        </span>
                        <span class="tracking-wide uppercase text-[10px] opacity-70">orders found</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <form id="per-page-form" method="GET" action="{{ url()->current() }}"
                            class="flex items-center gap-2">
                            <input type="hidden" name="status" value="{{ request('status', 'unverified') }}">
                            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            <label for="per_page"
                                class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground whitespace-nowrap">Show</label>
                            <div class="relative">
                                <select name="per_page" id="per_page"
                                    class="appearance-none h-7 pl-2.5 pr-7 rounded-lg border border-border/50 bg-background text-xs font-semibold focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors cursor-pointer hover:bg-accent/50 hover:border-border shadow-sm">
                                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-1.5 text-muted-foreground">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="relative w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr
                                class="border-b border-border/40 transition-colors hover:bg-muted/10 data-[state=selected]:bg-muted bg-muted/5">
                                <th
                                    class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">
                                    Order & Date</th>
                                <th
                                    class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">
                                    Customer</th>
                                <th
                                    class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">
                                    Total</th>
                                <th
                                    class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">
                                    Current Status</th>
                                <th
                                    class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">
                                    Verification</th>
                                @if(request('status') === 'verified')
                                    <th
                                        class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">
                                        Verified By</th>
                                    <th
                                        class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">
                                        Remarks</th>
                                @endif
                                <th
                                    class="h-10 px-4 text-right align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0 text-sm">
                            @forelse($orders as $order)
                                <tr class="group border-b border-border/40 transition-all duration-300 hover:bg-muted/30">
                                    <td class="p-4 px-4 align-middle">
                                        <div class="flex flex-col space-y-1">
                                            <a href="{{ route('tenant.orders.show', $order) }}"
                                                class="font-bold text-primary hover:underline text-sm tracking-tight transition-colors">
                                                {{ $order->order_number }}
                                            </a>
                                            <span
                                                class="text-[10px] font-mono text-muted-foreground">{{ $order->created_at->format('M d, H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4 px-4 align-middle">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-6 w-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-[9px] font-bold text-white shadow-sm ring-1 ring-white/20">
                                                {{ substr($order->customer->alt_name ?? $order->customer->first_name ?? 'G', 0, 1) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-xs font-semibold">{{ $order->customer->first_name ?? 'Guest' }}
                                                    {{ $order->customer->last_name ?? '' }}</span>
                                                @if($order->customer && $order->customer->mobile)
                                                    <span
                                                        class="text-[10px] text-muted-foreground leading-none mt-0.5">{{ $order->customer->mobile }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 px-4 align-middle">
                                        <span class="font-bold text-sm">Rs {{ number_format($order->grand_total, 2) }}</span>
                                    </td>
                                    <td class="p-4 px-4 align-middle">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-bold rounded-full bg-muted/50 text-muted-foreground border border-border/50 uppercase tracking-wide">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="p-4 px-4 align-middle">
                                        @if($order->verification_status === 'verified')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 shadow-sm">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Verified
                                            </span>
                                        @elseif($order->verification_status === 'pending_followup')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-amber-500/10 text-amber-600 border border-amber-500/20 shadow-sm">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                Follow-up
                                            </span>
                                        @elseif($order->verification_status === 'rejected')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-destructive/10 text-destructive border border-destructive/20 shadow-sm">
                                                <span class="h-1.5 w-1.5 rounded-full bg-destructive"></span>
                                                Rejected
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-muted text-muted-foreground border border-border/50 shadow-sm">
                                                <span class="h-1.5 w-1.5 rounded-full bg-muted-foreground/50"></span>
                                                Unverified
                                            </span>
                                        @endif
                                    </td>
                                    @if(request('status') === 'verified')
                                        <td class="p-4 px-4 align-middle">
                                            @php $lastVerification = $order->verifications->last(); @endphp
                                            @if($lastVerification && $lastVerification->user)
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-semibold">{{ $lastVerification->user->name }}</span>
                                                    <span
                                                        class="text-[10px] text-muted-foreground">{{ $lastVerification->created_at->format('M d, H:i') }}</span>
                                                </div>
                                            @else
                                                <span class="text-xs text-muted-foreground">-</span>
                                            @endif
                                        </td>
                                        <td class="p-4 px-4 align-middle">
                                            <span class="text-xs text-muted-foreground truncate max-w-[150px] block"
                                                title="{{ $order->verifications->last()->remarks ?? '' }}">
                                                {{ $order->verifications->last()->remarks ?? '-' }}
                                            </span>
                                        </td>
                                    @endif
                                    <td class="p-4 px-4 align-middle text-right">
                                        <button @click="activeOrder = {{ $order->toJson() }}; verifyModalOpen = true"
                                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-primary-foreground shadow-sm shadow-primary/20 hover:bg-primary/90 transition-all hover:scale-[1.02] active:scale-95">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                                <polyline points="22 4 12 14.01 9 11.01" />
                                            </svg>
                                            Verify
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-16 text-center text-muted-foreground">
                                        <div class="flex flex-col items-center gap-2">
                                            <div
                                                class="h-12 w-12 rounded-full bg-muted flex items-center justify-center text-muted-foreground/50 mb-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <path d="m9 12 2 2 4-4" />
                                                </svg>
                                            </div>
                                            <span class="font-medium">No orders pending verification</span>
                                            <span class="text-xs text-muted-foreground/60">Great job! All orders are
                                                verified.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($orders->hasPages())
                    <div
                        class="border-t border-border/40 p-3 bg-muted/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs text-muted-foreground px-2">Page <span
                                class="font-medium text-foreground">{{ $orders->currentPage() }}</span> of <span
                                class="font-medium">{{ $orders->lastPage() }}</span></div>
                        <div>{{ $orders->links() }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('orders-table-container');
            const loading = document.getElementById('table-loading');
            let searchTimeout;

            async function loadContent(url, pushState = true) {
                if (loading) loading.style.opacity = '1';
                try {
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) throw new Error('Network response was not ok');
                    const html = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('orders-table-container');
                    if (newContent) {
                        container.innerHTML = newContent.innerHTML;
                        if (pushState) window.history.pushState({}, '', url);
                        if (typeof Alpine !== 'undefined') Alpine.initTree(container);
                    } else {
                        window.location.href = url;
                    }
                } catch (err) {
                    window.location.href = url;
                } finally {
                    if (loading) loading.style.opacity = '0';
                }
            }

            window.addEventListener('popstate', () => loadContent(window.location.href, false));

            container.addEventListener('click', (e) => {
                const link = e.target.closest('a.page-link') || e.target.closest('nav[role="navigation"] a') || e.target.closest('.pagination a');
                if (link && container.contains(link) && link.href) {
                    e.preventDefault();
                    loadContent(link.href);
                }
            });

            // Event Delegation for Search Input (Auto-search)
            container.addEventListener('input', (e) => {
                if (e.target.name === 'search') {
                    const form = e.target.closest('form');
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const url = new URL(form.action);
                        const params = new URLSearchParams(new FormData(form));
                        loadContent(`${url.origin}${url.pathname}?${params.toString()}`);
                    }, 400);
                }
            });

            // Event Delegation for Form Submits (Search & Pagination)
            container.addEventListener('submit', (e) => {
                if (e.target.id === 'per-page-form' || e.target.id === 'search-form') {
                    e.preventDefault();
                    const form = e.target;
                    const url = new URL(form.action);
                    const params = new URLSearchParams(new FormData(form));
                    loadContent(`${url.origin}${url.pathname}?${params.toString()}`);
                }
            });

            container.addEventListener('change', (e) => {
                if (e.target.id === 'per_page') {
                    const form = e.target.closest('form');
                    const url = new URL(form.action);
                    const params = new URLSearchParams(new FormData(form));
                    loadContent(`${url.origin}${url.pathname}?${params.toString()}`);
                }
            });
        });
    </script>
@endsection