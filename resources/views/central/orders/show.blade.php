@extends('layouts.app')

@section('content')
    <div class="flex flex-1 flex-col space-y-6 p-4 md:p-8 animate-in fade-in duration-500 bg-background/50">

        <!-- Header Area -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1.5">
                <div class="flex items-center gap-3">
                    <h1
                        class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/60 bg-clip-text text-transparent">
                        Order #{{ $order->order_number }}
                    </h1>
                    @php
                        $statusColors = match ($order->status) {
                            'completed' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                            'shipped' => 'bg-indigo-500/10 text-indigo-600 border-indigo-500/20',
                            'confirmed' => 'bg-blue-500/10 text-blue-600 border-blue-500/20',
                            'cancelled' => 'bg-destructive/10 text-destructive border-destructive/20',
                            'processing' => 'bg-purple-500/10 text-purple-600 border-purple-500/20',
                            'ready_to_ship' => 'bg-sky-500/10 text-sky-600 border-sky-500/20',
                            default => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                        };
                    @endphp
                    <span
                        class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border {{ $statusColors }}">
                        {{ str_replace('_', ' ', $order->status) }}
                    </span>
                </div>
                <p class="text-muted-foreground text-sm font-medium flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                        <line x1="16" x2="16" y1="2" y2="6" />
                        <line x1="8" x2="8" y1="2" y2="6" />
                        <line x1="3" x2="21" y1="10" y2="10" />
                    </svg>
                    Placed on {{ $order->placed_at->format('F d, Y \a\t h:i A') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                    {{-- Pending / Draft / Scheduled → Confirm --}}
                    @if(in_array($order->status, ['pending', 'draft', 'scheduled']))
                        @can('orders approve')
                            <form action="{{ route('central.orders.update-status', $order) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="confirm">
                                <x-ui.button type="submit" class="shadow-lg shadow-primary/20">Confirm Order</x-ui.button>
                            </form>
                        @endcan
                    @endif
 
                    {{-- Confirmed → Processing --}}
                    @if($order->status === 'confirmed')
                        @can('orders process')
                            <form action="{{ route('central.orders.update-status', $order) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="process">
                                <x-ui.button type="submit" class="shadow-lg shadow-primary/20">Start Processing</x-ui.button>
                            </form>
                        @endcan
                    @endif
 
                    {{-- Processing → Ready to Ship --}}
                    @if($order->status === 'processing')
                        @can('orders process')
                            <form action="{{ route('central.orders.update-status', $order) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="ready_to_ship">
                                <x-ui.button type="submit" class="shadow-lg shadow-primary/20">Ready to Ship</x-ui.button>
                            </form>
                        @endcan
                    @endif
 
                    {{-- Ready to Ship → Ship --}}
                    @if($order->status === 'ready_to_ship' && $order->invoices->isNotEmpty())
                        @can('orders ship')
                            <x-ui.button onclick="document.getElementById('ship-dialog').showModal()"
                                class="shadow-lg shadow-primary/20">
                                Ship Order
                            </x-ui.button>
                        @endcan
                    @endif
 
                    {{-- Shipped → Delivered --}}
                    @if(in_array($order->status, ['shipped', 'in_transit']) || $order->shipping_status === 'shipped')
                        @can('orders deliver')
                            <form action="{{ route('central.orders.update-status', $order) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="deliver">
                                <x-ui.button type="submit" variant="outline"
                                    class="border-emerald-500/50 text-emerald-600 hover:bg-emerald-500/10 hover:text-emerald-700">Mark
                                    Delivered</x-ui.button>
                            </form>
                        @endcan
                    @endif
 
                    {{-- Cancel --}}
                    @if(!in_array($order->status, ['completed', 'cancelled']))
                        @can('orders cancel')
                            <form action="{{ route('central.orders.update-status', $order) }}" method="POST"
                                onsubmit="return confirm('Are you sure?');">
                                @csrf
                                <input type="hidden" name="action" value="cancel">
                                <x-ui.button type="submit" variant="destructive"
                                    class="bg-destructive/10 text-destructive hover:bg-destructive/20 border border-destructive/20 shadow-none">Cancel
                                    Order</x-ui.button>
                            </form>
                        @endcan
                    @endif

                {{-- Edit --}}
                @can('orders edit')
                    @if(!in_array($order->status, ['completed', 'cancelled', 'returned']))
                        <x-ui.button variant="outline" href="{{ route('central.orders.edit', $order) }}" class="gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9" />
                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                            </svg>
                            Edit
                        </x-ui.button>
                    @endif
                @endcan

                <div class="h-8 w-px bg-border mx-1"></div>

                {{-- Print Actions --}}
                <div class="flex items-center gap-2">
                    @if($order->invoices->isNotEmpty())
                        @php $invoice = $order->invoices->first(); @endphp
                        @can('invoices view')
                            <x-ui.button variant="outline" href="{{ route('central.invoices.pdf', $invoice) }}" target="_blank"
                                class="gap-2" title="Print Invoice">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <path d="M16 13H8" />
                                    <path d="M16 17H8" />
                                    <path d="M10 9H8" />
                                </svg>
                                Invoice
                            </x-ui.button>
                        @endcan
                    @endif

                    @can('orders-receipt view')
                        <x-ui.button variant="outline" href="{{ route('central.orders.receipt', $order) }}" target="_blank"
                            class="gap-2" title="Print Receipt">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" />
                                <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
                                <path d="M12 17V7" />
                            </svg>
                            Receipt
                        </x-ui.button>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Tracking / Progress Bar -->
        <div class="rounded-2xl border border-white/20 bg-white/40 dark:bg-black/20 backdrop-blur-xl p-8 shadow-sm">
            <div class="relative">
                <div class="absolute left-0 top-1/2 -mt-0.5 w-full h-1 bg-muted rounded-full z-0"></div>

                @php
                    $progress = match ($order->status) {
                        'confirmed' => '20%',
                        'processing' => '40%',
                        'ready_to_ship' => '60%',
                        'shipped',
                        'in_transit' => '80%',
                        'completed' => '100%',
                        default => '0%',
                    };
                 @endphp
                <div class="absolute left-0 top-1/2 -mt-0.5 h-1 bg-primary rounded-full z-0 transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(var(--primary),0.5)]"
                    style="width: {{ $progress }}"></div>

                <div class="relative z-10 flex justify-between w-full">
                    <!-- Placed -->
                    <div class="flex flex-col items-center gap-3 group">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-primary text-primary-foreground shadow-lg ring-4 ring-background transition-transform duration-300 group-hover:scale-110">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14 2z" />
                                <polyline points="14 2 14 8 20 8" />
                                <path d="M12 18v-6" />
                                <path d="m9 15 3 3 3-3" />
                            </svg>
                        </div>
                        <div class="text-center">
                            <span class="text-sm font-bold block">Placed</span>
                            <span
                                class="text-[10px] text-muted-foreground font-medium uppercase tracking-wide">{{ $order->created_at->format('M d, H:i') }}</span>
                        </div>
                    </div>

                    <!-- Processing -->
                    @php $isProc = in_array($order->status, ['confirmed', 'processing', 'ready_to_ship', 'shipped', 'in_transit', 'completed']); @endphp
                    <div class="flex flex-col items-center gap-3 group">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm {{ $isProc ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground' }} shadow-lg ring-4 ring-background transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                                <line x1="12" y1="22.08" x2="12" y2="12" />
                            </svg>
                        </div>
                        <span
                            class="text-sm font-bold {{ $isProc ? 'text-foreground' : 'text-muted-foreground' }}">Processing</span>
                    </div>

                    <!-- Shipped -->
                    @php $isShip = in_array($order->status, ['shipped', 'in_transit', 'completed']); @endphp
                    <div class="flex flex-col items-center gap-3 group">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm {{ $isShip ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground' }} shadow-lg ring-4 ring-background transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="3" width="15" height="13"></rect>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                            </svg>
                        </div>
                        <span
                            class="text-sm font-bold {{ $isShip ? 'text-foreground' : 'text-muted-foreground' }}">Shipped</span>
                    </div>

                    <!-- Delivered -->
                    @php $isDone = $order->status === 'completed'; @endphp
                    <div class="flex flex-col items-center gap-3 group">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm {{ $isDone ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground' }} shadow-lg ring-4 ring-background transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                        </div>
                        <span
                            class="text-sm font-bold {{ $isDone ? 'text-foreground' : 'text-muted-foreground' }}">Delivered</span>
                    </div>
                </div>
            </div>

            @if($order->shipments->isNotEmpty())
                <div class="mt-8 flex flex-wrap gap-6 items-center p-4 bg-muted/30 rounded-xl border border-border/50">
                    <div class="px-4 border-l-2 border-primary/50">
                        <span
                            class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground block mb-1">Carrier</span>
                        <p class="font-medium text-sm">{{ $order->shipments->first()->carrier ?? 'N/A' }}</p>
                    </div>
                    <div class="px-4 border-l-2 border-primary/50">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground block mb-1">Tracking
                            Number</span>
                        <p class="font-mono text-sm font-medium text-primary tracking-wide">
                            {{ $order->shipments->first()->tracking_number ?? 'N/A' }}</p>
                    </div>
                    <div class="px-4 border-l-2 border-primary/50">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground block mb-1">Shipped
                            Date</span>
                        <p class="font-medium text-sm">
                            {{ $order->shipments->first()->shipped_at ? $order->shipments->first()->shipped_at->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Column: Items & Summary -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Items Table -->
                <div
                    class="rounded-2xl border border-white/20 bg-white/40 dark:bg-black/20 backdrop-blur-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-white/10 flex items-center justify-between">
                        <h3 class="text-lg font-bold">Order Items</h3>
                        <span class="text-xs font-medium text-muted-foreground">{{ $order->items->count() }} items</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-muted/30 text-xs uppercase font-bold text-muted-foreground tracking-wider">
                                <tr>
                                    <th class="px-6 py-3">Product</th>
                                    <th class="px-6 py-3 text-right">Price</th>
                                    <th class="px-6 py-3 text-center">Qty</th>
                                    <th class="px-6 py-3 text-center">Tax</th>
                                    <th class="px-6 py-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 dark:divide-white/5">
                                @foreach($order->items as $item)
                                    @php
                                        $baseTotal = $item->unit_price * $item->quantity;
                                        $taxAmount = ($baseTotal * ($item->tax_percent ?? 0)) / 100;
                                        $lineTotal = $baseTotal + $taxAmount - ($item->discount_amount ?? 0);
                                    @endphp
                                    <tr class="hover:bg-muted/10 transition-colors">
                                        <td class="px-6 py-4">
                                            <p class="font-bold text-foreground">{{ $item->product_name }}</p>
                                            <p class="text-xs text-muted-foreground mt-0.5">{{ $item->sku }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            Rs {{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="inline-flex items-center justify-center px-2 py-1 rounded-md bg-muted/50 text-xs font-medium">
                                                {{ $item->quantity }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex flex-col items-center">
                                                <span
                                                    class="text-xs font-semibold">{{ $item->tax_percent > 0 ? (float) $item->tax_percent . '%' : '-' }}</span>
                                                @if($taxAmount > 0)
                                                    <span class="text-[10px] text-muted-foreground">Rs
                                                        {{ number_format($taxAmount, 2) }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-foreground">
                                            Rs {{ number_format($lineTotal, 2) }}
                                            @if($item->discount_amount > 0)
                                                <div class="text-[10px] font-normal text-muted-foreground line-through">
                                                    Rs {{ number_format($baseTotal + $taxAmount, 2) }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tax Summary (Premium Style with Bifurcation) -->
                <div class="rounded-2xl border border-white/20 bg-white/40 dark:bg-black/20 backdrop-blur-xl shadow-sm p-6">
                    <h3 class="font-bold mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                        Payment Summary
                    </h3>

                    <div class="space-y-3">
                        <!-- Base Amount -->
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Subtotal (Taxable Value)</span>
                            <span class="font-medium">Rs {{ number_format($order->total_amount, 2) }}</span>
                        </div>

                        <!-- Discount -->
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-sm text-emerald-600">
                                <span>Discount</span>
                                <span class="font-medium">- Rs {{ number_format($order->discount_amount, 2) }}</span>
                            </div>
                        @endif

                        <!-- Shipping -->
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Shipping</span>
                            <span class="font-medium">Rs {{ number_format($order->shipping_amount ?? 0, 2) }}</span>
                        </div>

                        <!-- Tax Bifurcation -->
                        @php
                            $totalTax = $order->tax_amount ?? 0;
                            $cgst = $totalTax / 2;
                            $sgst = $totalTax / 2;
                        @endphp

                        <div class="my-2 p-3 bg-muted/20 rounded-lg space-y-2">
                            <div class="flex justify-between text-xs">
                                <span class="text-muted-foreground">CGST (Central Tax)</span>
                                <span class="font-medium">Rs {{ number_format($cgst, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-muted-foreground">SGST (State Tax)</span>
                                <span class="font-medium">Rs {{ number_format($sgst, 2) }}</span>
                            </div>
                            <div
                                class="border-t border-border/50 pt-2 flex justify-between text-sm font-semibold text-foreground/80">
                                <span>Total Tax</span>
                                <span>Rs {{ number_format($totalTax, 2) }}</span>
                            </div>
                        </div>

                        <div class="h-px bg-gradient-to-r from-transparent via-border to-transparent my-4"></div>

                        <!-- Grand Total -->
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold">Grand Total</span>
                            <span class="text-xl font-bold text-primary">Rs
                                {{ number_format($order->grand_total, 2) }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Customer & Info -->
            <div class="space-y-6">

                <!-- Customer Card -->
                <div class="rounded-2xl border border-white/20 bg-white/40 dark:bg-black/20 backdrop-blur-xl shadow-sm p-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div
                            class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-lg font-bold text-white shadow-md">
                            {{ substr($order->customer->name ?? 'G', 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-tight">{{ $order->customer->name }}</h3>
                            <p class="text-xs text-muted-foreground">Customer</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <a href="mailto:{{ $order->customer->email }}" class="flex items-center gap-3 text-sm group">
                            <div
                                class="h-8 w-8 rounded-lg bg-background flex items-center justify-center text-muted-foreground group-hover:text-primary transition-colors border border-border">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="20" height="16" x="2" y="4" rx="2" />
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                </svg>
                            </div>
                            <span class="truncate">{{ $order->customer->email }}</span>
                        </a>

                        <div class="flex items-center gap-3 text-sm">
                            <div
                                class="h-8 w-8 rounded-lg bg-background flex items-center justify-center text-muted-foreground border border-border">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                </svg>
                            </div>
                            <span>{{ $order->customer->mobile ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Addresses -->
                <div
                    class="rounded-2xl border border-white/20 bg-white/40 dark:bg-black/20 backdrop-blur-xl shadow-sm p-6 space-y-6">
                    <!-- Shipping -->
                    <div>
                        <h4
                            class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M10 17h4V5H2v12h3m10 0h7v-3.34a3 3 0 0 0-.88-2.12l-2.52-2.52A3 3 0 0 0 16.5 6h-2.5V5h-4" />
                                <circle cx="7.5" cy="17.5" r="2.5" />
                                <circle cx="17.5" cy="17.5" r="2.5" />
                            </svg>
                            Shipping Address
                        </h4>
                        <p
                            class="text-sm leading-relaxed text-foreground/90 bg-background/50 p-3 rounded-lg border border-border/50">
                            @if($order->shippingAddress)
                                <span class="font-semibold block mb-1">{{ $order->customer->name }}</span>
                                {{ $order->shippingAddress->address_line1 }}<br>
                                @if($order->shippingAddress->address_line2) {{ $order->shippingAddress->address_line2 }}<br>
                                @endif
                                {{ $order->shippingAddress->village }}, {{ $order->shippingAddress->state }}<br>
                                <span class="font-mono text-xs">{{ $order->shippingAddress->pincode }}</span>
                            @else
                                <span class="text-muted-foreground italic">Warehouse Pickup:
                                    {{ $order->warehouse->name }}</span>
                            @endif
                        </p>
                    </div>

                    <!-- Billing -->
                    <div>
                        <h4
                            class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                <path d="M3 6h18" />
                                <path d="M16 10a4 4 0 0 1-8 0" />
                            </svg>
                            Billing Address
                        </h4>
                        <p
                            class="text-sm leading-relaxed text-foreground/90 bg-background/50 p-3 rounded-lg border border-border/50">
                            @if($order->billingAddress)
                                <span class="font-semibold block mb-1">{{ $order->customer->name }}</span>
                                {{ $order->billingAddress->address_line1 }}<br>
                                @if($order->billingAddress->address_line2) {{ $order->billingAddress->address_line2 }}<br>
                                @endif
                                {{ $order->billingAddress->village }}, {{ $order->billingAddress->state }}<br>
                                <span class="font-mono text-xs">{{ $order->billingAddress->pincode }}</span>
                            @else
                                <span class="text-muted-foreground italic">Same as Shipping</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Order Heritage -->
                <div class="rounded-2xl border border-white/20 bg-white/40 dark:bg-black/20 backdrop-blur-xl shadow-sm p-6">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-muted-foreground mb-4">Timeline</h3>
                    <div class="space-y-6">
                        <div class="flex gap-4 relative">
                            <!-- Line -->
                            <div class="absolute left-[5px] top-2 bottom-[-24px] w-px bg-border"></div>

                            <div
                                class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1.5 shrink-0 z-10 ring-4 ring-background">
                            </div>
                            <div>
                                <p class="text-xs font-bold">Created</p>
                                <p class="text-[10px] text-muted-foreground">
                                    {{ $order->created_at->format('M d, Y h:i A') }}</p>
                                <p class="text-[10px] text-muted-foreground mt-0.5">by
                                    {{ $order->creator?->name ?? 'System' }}</p>
                            </div>
                        </div>

                        @if($order->updated_at > $order->created_at && $order->updated_by)
                            <div class="flex gap-4 relative">
                                <div class="w-2.5 h-2.5 rounded-full bg-blue-500 mt-1.5 shrink-0 z-10 ring-4 ring-background">
                                </div>
                                <div>
                                    <p class="text-xs font-bold">Updated</p>
                                    <p class="text-[10px] text-muted-foreground">
                                        {{ $order->updated_at->format('M d, Y h:i A') }}</p>
                                    <p class="text-[10px] text-muted-foreground mt-0.5">by
                                        {{ $order->updater?->name ?? 'System' }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Ship Dialog (Hidden) -->
    <dialog id="ship-dialog"
        class="p-0 rounded-2xl shadow-2xl backdrop:bg-black/50 w-full max-w-md bg-card border border-border">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold">Ship Order</h3>
                <button onclick="document.getElementById('ship-dialog').close()"
                    class="text-muted-foreground hover:text-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('central.orders.update-status', $order) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="action" value="ship">

                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Courier /
                        Carrier</label>
                    <input type="text" name="carrier"
                        class="flex h-10 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="e.g. FedEx, BlueDart">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Tracking Number</label>
                    <input type="text" name="tracking_number"
                        class="flex h-10 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Tracking Scan ID">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('ship-dialog').close()"
                        class="px-4 py-2 text-sm font-semibold text-muted-foreground hover:text-foreground">Cancel</button>
                    <button type="submit"
                        class="px-6 py-2 text-sm font-bold text-primary-foreground bg-primary rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20">Confirm
                        Shipment</button>
                </div>
            </form>
        </div>
    </dialog>

@endsection