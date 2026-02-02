<x-layouts.app>
<div class="flex flex-1 flex-col space-y-8 p-6 md:p-8 max-w-7xl mx-auto w-full animate-in fade-in duration-500">
    
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ url('customers') }}" class="group flex h-10 w-10 items-center justify-center rounded-xl bg-background border border-border/50 shadow-sm transition-all hover:bg-accent hover:text-accent-foreground hover:scale-105 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-hover:text-foreground transition-colors"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent flex items-center gap-3">
                    {{ $customer->display_name }}
                    @if($customer->is_blacklisted)
                        <span class="inline-flex items-center rounded-full bg-destructive/10 px-2.5 py-0.5 text-xs font-semibold text-destructive animate-pulse">
                            Blacklisted
                        </span>
                    @endif
                </h1>
                <p class="text-muted-foreground text-sm flex items-center gap-2">
                    <span class="font-mono">{{ $customer->customer_code }}</span> 
                    <span>•</span> 
                    <span class="capitalize">{{ $customer->type ?? 'Customer' }}</span>
                    <span>•</span>
                    <span class="inline-flex items-center gap-1 {{ $customer->is_active ? 'text-emerald-500' : 'text-amber-500' }}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ url('customers/' . $customer->id . '/edit') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-background border border-border/50 px-4 py-2.5 text-sm font-semibold shadow-sm hover:bg-accent hover:text-accent-foreground transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                <span>Edit Profile</span>
            </a>
            <button onclick="openModal()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                <span>New Interaction</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-border/40 bg-card/50 p-6 shadow-sm backdrop-blur-xl transition-all hover:shadow-md">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10 text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 16.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0Z"/><path d="m10 16 2 3h10"/><path d="M10 16H3.04a2 2 0 0 1-1.99-2.34l1.13-6.43A2 2 0 0 1 4.15 5.5H15l.54 1.5H21a2 2 0 0 1 1.99 2.34l-1.13 6.43A2 2 0 0 1 19.88 18H13"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Orders</p>
                    <p class="text-2xl font-bold">{{ $customer->orders_count ?? $customer->orders->count() }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-border/40 bg-card/50 p-6 shadow-sm backdrop-blur-xl transition-all hover:shadow-md">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-500/10 text-rose-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Outstanding</p>
                    <p class="text-2xl font-bold">₹{{ number_format((float)$customer->outstanding_balance, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-border/40 bg-card/50 p-6 shadow-sm backdrop-blur-xl transition-all hover:shadow-md">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" y1="22" x2="12" y2="12"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Credit Limit</p>
                    <p class="text-2xl font-bold">₹{{ number_format((float)$customer->credit_limit, 0) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-border/40 bg-card/50 p-6 shadow-sm backdrop-blur-xl transition-all hover:shadow-md">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Last Interaction</p>
                    <p class="text-lg font-bold">{{ $customer->interactions->first()?->created_at?->diffForHumans() ?? 'None' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-12">
        
        <!-- Sidebar: Info & Tags -->
        <div class="lg:col-span-4 space-y-8">
            
            <!-- Tags & Taggings -->
            <div class="rounded-2xl border border-border/40 bg-card/50 shadow-sm backdrop-blur-xl overflow-hidden">
                <div class="p-4 border-b border-border/40 bg-muted/20">
                    <h3 class="font-semibold flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M7 7h.01"/></svg>
                        Taggings & Labels
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Generic Tags -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3 block">Custom Tags</label>
                        <div class="flex flex-wrap gap-2">
                            @forelse($customer->tags ?? [] as $tag)
                                <span class="px-2.5 py-1 text-xs font-medium rounded-lg bg-primary/10 text-primary border border-primary/20">
                                    {{ $tag }}
                                </span>
                            @empty
                                <span class="text-sm text-muted-foreground italic">No tags assigned</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Crops as Tags -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3 block">Primary Crops</label>
                        <div class="flex flex-wrap gap-2">
                            @forelse($customer->crops['primary'] ?? [] as $crop)
                                <span class="px-2.5 py-1 text-xs font-medium rounded-lg bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                    {{ $crop }}
                                </span>
                            @empty
                                <span class="text-xs text-muted-foreground italic">Not specified</span>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3 block">Secondary Crops</label>
                        <div class="flex flex-wrap gap-2">
                            @forelse($customer->crops['secondary'] ?? [] as $crop)
                                <span class="px-2.5 py-1 text-xs font-medium rounded-lg bg-blue-500/10 text-blue-600 border border-blue-500/20">
                                    {{ $crop }}
                                </span>
                            @empty
                                <span class="text-xs text-muted-foreground italic">Not specified</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="rounded-2xl border border-border/40 bg-card/50 shadow-sm backdrop-blur-xl overflow-hidden">
                <div class="p-4 border-b border-border/40 bg-muted/20">
                    <h3 class="font-semibold flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Contact Details
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg bg-muted text-muted-foreground"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" r="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></div>
                        <div>
                            <p class="text-xs text-muted-foreground uppercase font-semibold">Email</p>
                            <p class="text-sm font-medium">{{ $customer->email ?? 'Not provided' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg bg-muted text-muted-foreground"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
                        <div>
                            <p class="text-xs text-muted-foreground uppercase font-semibold">Mobile</p>
                            <p class="text-sm font-medium">{{ $customer->mobile }}</p>
                            @if($customer->phone_number_2)
                                <p class="text-xs text-muted-foreground mt-1">Alt: {{ $customer->phone_number_2 }}</p>
                            @endif
                        </div>
                    </div>
                    @php $addr = $customer->addresses->firstWhere('is_default', true); @endphp
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg bg-muted text-muted-foreground"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></div>
                        <div>
                            <p class="text-xs text-muted-foreground uppercase font-semibold">Primary Address</p>
                            @if($addr)
                                <p class="text-sm font-medium leading-relaxed">
                                    {{ $addr->address_line1 }}<br>
                                    @if($addr->address_line2){{ $addr->address_line2 }}<br>@endif
                                    {{ $addr->village ? $addr->village . ', ' : '' }}{{ $addr->taluka ? $addr->taluka : '' }}<br>
                                    {{ $addr->district ? $addr->district . ', ' : '' }}{{ $addr->state }} - {{ $addr->pincode }}
                                </p>
                            @else
                                <p class="text-sm italic text-muted-foreground">No address set</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business & Metadata -->
            <div class="rounded-2xl border border-border/40 bg-card/50 shadow-sm backdrop-blur-xl overflow-hidden">
                <div class="p-4 border-b border-border/40 bg-muted/20">
                    <h3 class="font-semibold text-sm uppercase text-muted-foreground tracking-wider">Business Info</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-muted-foreground uppercase">GST</p>
                            <p class="text-sm font-mono font-medium">{{ $customer->gst_number ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground uppercase">PAN</p>
                            <p class="text-sm font-mono font-medium">{{ $customer->pan_number ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-border/40">
                         <p class="text-xs text-muted-foreground">Internal Notes</p>
                         <p class="text-sm mt-1 text-foreground/80 leading-relaxed italic">
                            "{{ $customer->internal_notes ?? 'No internal notes for this customer.' }}"
                         </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Main Content: Orders & Interactions -->
        <div class="lg:col-span-8 space-y-8">
            
            <!-- Tabs Navigation -->
            <div class="flex items-center gap-1 p-1 bg-muted/30 rounded-2xl border border-border/40 w-fit">
                <button onclick="switchTab('orders')" id="tab-btn-orders" class="px-6 py-2.5 text-sm font-semibold rounded-xl transition-all tab-btn active-tab bg-primary text-primary-foreground shadow-sm">
                    Past Orders
                </button>
                <button onclick="switchTab('history')" id="tab-btn-history" class="px-6 py-2.5 text-sm font-semibold rounded-xl transition-all tab-btn text-muted-foreground hover:bg-muted/50">
                    Interactions History
                </button>
                <button onclick="switchTab('details')" id="tab-btn-details" class="px-6 py-2.5 text-sm font-semibold rounded-xl transition-all tab-btn text-muted-foreground hover:bg-muted/50">
                    Full Details
                </button>
            </div>

            <!-- Orders Tab -->
            <div id="tab-orders" class="tab-content animate-in fade-in slide-in-from-bottom-2 duration-300">
                <div class="rounded-2xl border border-border/40 bg-card/50 shadow-sm overflow-hidden backdrop-blur-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-muted/30 border-b border-border/40">
                                <th class="px-6 py-4 text-xs font-bold uppercase text-muted-foreground tracking-wider">Order Info</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-muted-foreground tracking-wider">Status</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-muted-foreground tracking-wider">Items</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-muted-foreground tracking-wider text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/20">
                            @forelse($orders as $order)
                                <tr class="hover:bg-muted/20 transition-colors group cursor-pointer" onclick="window.location='/orders/{{ $order->id }}'">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-foreground group-hover:text-primary transition-colors">#{{ $order->order_number }}</div>
                                        <div class="text-xs text-muted-foreground">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                            {{ $order->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}
                                            {{ $order->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                            {{ !in_array($order->status, ['completed', 'cancelled', 'pending']) ? 'bg-blue-100 text-blue-700' : '' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex -space-x-2 overflow-hidden">
                                            @foreach($order->items->take(4) as $item)
                                                <div class="inline-block h-8 w-8 rounded-full ring-2 ring-background bg-muted flex items-center justify-center text-[10px] font-bold" title="{{ $item->product->name ?? 'Unknown item' }}">
                                                    @if($item->product && $item->product->images && count($item->product->images) > 0)
                                                        <img src="{{ asset('storage/' . $item->product->images[0]) }}" class="h-full w-full rounded-full object-cover">
                                                    @else
                                                        {{ substr($item->product->name ?? '?', 0, 1) }}
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if($order->items->count() > 4)
                                                <div class="inline-block h-8 w-8 rounded-full ring-2 ring-background bg-muted flex items-center justify-center text-[10px] font-bold">
                                                    +{{ $order->items->count() - 4 }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="font-bold text-lg">₹{{ number_format($order->grand_total, 2) }}</div>
                                        <div class="text-[10px] text-muted-foreground uppercase tracking-widest">{{ $order->payment_status }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-muted-foreground italic">
                                        No order history found for this customer.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($orders->count() > 0 && method_exists($orders, 'links'))
                        <div class="p-4 border-t border-border/20 bg-muted/10">
                            {{ $orders->appends(['interactions_page' => $interactions->currentPage()])->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Interactions History Tab -->
            <div id="tab-history" class="tab-content hidden animate-in fade-in slide-in-from-bottom-2 duration-300">
                <div class="relative space-y-6 before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-border/50 before:to-transparent">
                    @forelse($interactions as $interaction)
                        <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group select-none">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full border border-border bg-card shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10">
                                <svg class="fill-primary" xmlns="http://www.w3.org/2000/svg" width="12" height="12">
                                    <path d="M12 2.1L4.5 9.6 0 5.1l1.4-1.4 3.1 3.1 6.1-6.1z" />
                                </svg>
                            </div>
                            <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-2xl border border-border/40 bg-card/50 shadow-sm backdrop-blur-xl">
                                <div class="flex items-center justify-between space-x-2 mb-1">
                                    <div class="font-bold text-foreground capitalize">{{ str_replace('_', ' ', $interaction->type) }}</div>
                                    <time class="font-mono text-xs text-muted-foreground">{{ $interaction->created_at->format('M d, Y') }}</time>
                                </div>
                                <div class="text-sm text-muted-foreground leading-relaxed">{{ $interaction->description }}</div>
                                <div class="mt-4 flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary">
                                        {{ substr($interaction->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <span class="text-xs font-medium text-foreground/70">Recorded by {{ $interaction->user->name ?? 'System' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-muted-foreground italic rounded-2xl border border-dashed border-border/60">
                            No interaction history recorded yet.
                        </div>
                    @endforelse
                </div>
                @if($interactions->count() > 0 && method_exists($interactions, 'links'))
                    <div class="mt-8">
                        {{ $interactions->appends(['orders_page' => $orders->currentPage()])->links() }}
                    </div>
                @endif
            </div>

            <!-- Full Details Tab -->
            <div id="tab-details" class="tab-content hidden animate-in fade-in slide-in-from-bottom-2 duration-300">
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-2xl border border-border/40 bg-card/50 p-6 space-y-6 backdrop-blur-xl">
                        <h4 class="font-bold text-lg mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Personal & Agri Profile
                        </h4>
                        <div class="space-y-4">
                            <div class="flex justify-between border-b border-border/20 pb-2">
                                <span class="text-sm text-muted-foreground">Full Name</span>
                                <span class="text-sm font-semibold">{{ $customer->first_name }} {{ $customer->last_name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-border/20 pb-2">
                                <span class="text-sm text-muted-foreground">Category</span>
                                <span class="text-sm font-semibold capitalize">{{ $customer->category }}</span>
                            </div>
                            <div class="flex justify-between border-b border-border/20 pb-2">
                                <span class="text-sm text-muted-foreground">Land Area</span>
                                <span class="text-sm font-semibold">{{ $customer->land_area }} {{ ucfirst($customer->land_unit) }}</span>
                            </div>
                            <div class="flex justify-between border-b border-border/20 pb-2">
                                <span class="text-sm text-muted-foreground">Irrigation</span>
                                <span class="text-sm font-semibold capitalize">{{ $customer->irrigation_type ?? '-' }}</span>
                            </div>
                             <div class="flex justify-between border-b border-border/20 pb-2">
                                <span class="text-sm text-muted-foreground">Aadhaar Last 4</span>
                                <span class="text-sm font-semibold">{{ $customer->aadhaar_last4 ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border/40 bg-card/50 p-6 space-y-6 backdrop-blur-xl">
                         <h4 class="font-bold text-lg mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            Financial Compliance
                        </h4>
                        <div class="space-y-4">
                            <div class="flex justify-between border-b border-border/20 pb-2">
                                <span class="text-sm text-muted-foreground">Credit Limit</span>
                                <span class="text-sm font-semibold">₹ {{ number_format($customer->credit_limit, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-b border-border/20 pb-2">
                                <span class="text-sm text-muted-foreground">Outstanding</span>
                                <span class="text-sm font-semibold text-rose-500">₹ {{ number_format($customer->outstanding_balance, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-b border-border/20 pb-2">
                                <span class="text-sm text-muted-foreground">Credit Validity</span>
                                <span class="text-sm font-semibold">{{ $customer->credit_valid_till ? $customer->credit_valid_till->format('d M, Y') : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-border/20 pb-2">
                                <span class="text-sm text-muted-foreground">KYC Status</span>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $customer->kyc_completed ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $customer->kyc_completed ? 'Completed' : 'Pending' }}
                                </span>
                            </div>
                            @if($customer->kyc_verified_at)
                            <div class="flex justify-between border-b border-border/20 pb-2">
                                <span class="text-sm text-muted-foreground">KYC Verified Date</span>
                                <span class="text-sm font-semibold">{{ $customer->kyc_verified_at->format('d M, Y') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>




<!-- Interaction Modal -->
<div id="interaction-modal" class="fixed inset-0 z-50 hidden bg-background/80 backdrop-blur-sm transition-opacity" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-card border border-border text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-card px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-foreground" id="modal-title">Log Interaction & Close Profile</h3>
                            <div class="mt-2">
                                <p class="text-sm text-muted-foreground mb-4">Record the outcome of your interaction with this customer to unlock navigation.</p>
                                
                                <form id="interaction-form" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label for="type" class="block text-sm font-medium leading-6 text-foreground">Interaction Type</label>
                                        <select id="type" name="type" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-foreground ring-1 ring-inset ring-border focus:ring-2 focus:ring-primary sm:text-sm sm:leading-6 bg-background">
                                            <option value="call">Phone Call</option>
                                            <option value="visit">Site Visit</option>
                                            <option value="order">Order Inquiry</option>
                                            <option value="payment">Payment Follow-up</option>
                                            <option value="general">General Enquiry</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="outcome" class="block text-sm font-medium leading-6 text-foreground">Outcome</label>
                                        <input type="text" name="outcome" id="outcome" class="mt-2 block w-full rounded-md border-0 py-1.5 text-foreground shadow-sm ring-1 ring-inset ring-border placeholder:text-muted-foreground focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 bg-background" placeholder="e.g. Order placed, payment promised, callback scheduled" required>
                                    </div>

                                    <div>
                                        <label for="notes" class="block text-sm font-medium leading-6 text-foreground">Detailed Notes (Optional)</label>
                                        <textarea id="notes" name="notes" rows="3" class="mt-2 block w-full rounded-md border-0 py-1.5 text-foreground shadow-sm ring-1 ring-inset ring-border placeholder:text-muted-foreground focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 bg-background"></textarea>
                                    </div>
                                    
                                    <input type="hidden" name="close_session" value="1">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-muted/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" onclick="submitInteraction()" class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 sm:ml-3 sm:w-auto transition-all">Submit & Close</button>
                    <button type="button" onclick="closeModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-background px-3 py-2 text-sm font-semibold text-foreground shadow-sm ring-1 ring-inset ring-border hover:bg-accent sm:mt-0 sm:w-auto transition-all">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('interaction-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('interaction-modal').classList.add('hidden');
    }

    function submitInteraction() {
        const form = document.getElementById('interaction-form');
        
        // HTML5 Validation
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        const btn = document.querySelector('button[onclick="submitInteraction()"]');
        const originalBtnText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';

        fetch("{{ route('central.customers.interaction', $customer->id) }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                // Determine error message
                let errorMsg = data.message || 'Error saving interaction';
                if (data.errors) {
                    const firstError = Object.values(data.errors)[0];
                    errorMsg = Array.isArray(firstError) ? firstError[0] : firstError;
                }
                throw new Error(errorMsg);
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                window.location.href = "{{ route('central.customers.index') }}"; 
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message);
            btn.disabled = false;
            btn.innerHTML = originalBtnText;
        });
    }

    function switchTab(tabId) {
        // Hide all content
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        // Show target content
        document.getElementById('tab-' + tabId).classList.remove('hidden');

        // Update button states
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-primary', 'text-primary-foreground', 'shadow-sm', 'active-tab');
            btn.classList.add('text-muted-foreground');
            btn.classList.remove('hover:bg-muted/50');
            btn.classList.add('hover:bg-muted/50');
        });

        const activeBtn = document.getElementById('tab-btn-' + tabId);
        activeBtn.classList.remove('text-muted-foreground', 'hover:bg-muted/50');
        activeBtn.classList.add('bg-primary', 'text-primary-foreground', 'shadow-sm', 'active-tab');
    }

    // Preserve tab on page reload if hash exists
    window.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash.replace('#', '');
        if (['orders', 'history', 'details'].includes(hash)) {
            switchTab(hash);
        }
    });

    // Update hash on click
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.id.replace('tab-btn-', '');
            window.history.replaceState(null, null, '#' + id);
        });
    });
</script>

<style>
    .active-tab {
        animation: scale-up 0.2s ease-out;
    }
    @keyframes scale-up {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
</style>

</x-layouts.app>
