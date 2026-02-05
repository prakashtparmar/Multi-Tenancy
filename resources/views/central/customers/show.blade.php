<x-layouts.app>
<div class="min-h-screen bg-muted/5 pb-20">
    <!-- Header with Gradient Background -->
    <div class="relative bg-gradient-to-b from-primary/10 to-transparent pt-10 pb-20 px-6 md:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                <!-- Customer Identity -->
                <div class="flex items-start gap-5">
                    <div class="relative group">
                        <div class="h-20 w-20 rounded-2xl bg-white shadow-xl flex items-center justify-center text-3xl font-bold text-primary ring-4 ring-white/50">
                            {{ substr($customer->first_name, 0, 1) }}
                        </div>
                        <div class="absolute -bottom-2 -right-2 h-6 w-6 rounded-full border-2 border-white flex items-center justify-center {{ $customer->is_active ? 'bg-emerald-500' : 'bg-amber-500' }}" title="{{ $customer->is_active ? 'Active' : 'Inactive' }}">
                            @if($customer->is_active)
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span class="block h-2 w-2 rounded-full bg-white"></span>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-1 pt-1">
                        <h1 class="text-3xl font-black tracking-tight text-gray-900 flex items-center gap-3">
                            {{ $customer->display_name }}
                            @if($customer->is_blacklisted)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest bg-destructive text-destructive-foreground">Blacklisted</span>
                            @endif
                        </h1>
                        <div class="flex items-center gap-3 text-sm font-medium text-muted-foreground">
                            <span class="flex items-center gap-1.5 bg-white/50 px-2 py-0.5 rounded-md border border-gray-200 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                {{ $customer->customer_code }}
                            </span>
                            <span class="capitalize px-2 py-0.5 rounded-md bg-primary/5 text-primary border border-primary/10">
                                {{ $customer->type ?? 'Standard' }}
                            </span>
                             <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Joined {{ $customer->created_at->format('M Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                     <a href="{{ route('central.orders.create', ['customer_query' => $customer->customer_code]) }}" 
                       class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-primary-foreground shadow-lg shadow-primary/25 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        Create Order
                    </a>
                    <button onclick="openModal()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white border border-gray-200 px-4 py-3 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-all">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Log Interaction
                    </button>
                    <a href="{{ url('customers/' . $customer->id . '/edit') }}" class="p-3 rounded-xl bg-white border border-gray-200 text-muted-foreground hover:text-gray-900 hover:bg-gray-50 transition-all shadow-sm" title="Edit Profile">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-6 md:px-8 -mt-12">
        <div class="grid lg:grid-cols-12 gap-8">
            
            <!-- Left Sidebar: Profile Card -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Core Info Card -->
                <div class="rounded-2xl border border-white/50 bg-white/60 shadow-xl backdrop-blur-xl overflow-hidden p-1">
                    <div class="bg-white rounded-xl p-6 space-y-6 border border-gray-100">
                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 gap-4 pb-6 border-b border-gray-100">
                            <div class="space-y-1">
                                <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Outstanding</span>
                                <div class="text-xl font-black {{ $customer->outstanding_balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    ₹{{ number_format((float) $customer->outstanding_balance, 0) }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Credit Limit</span>
                                <div class="text-xl font-black text-gray-900">
                                    ₹{{ number_format((float) $customer->credit_limit, 0) }}
                                </div>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <div class="space-y-4">
                            <div class="flex items-start gap-4 group">
                                <div class="h-10 w-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-muted-foreground uppercase">Mobile</span>
                                    <div class="font-bold text-gray-900">{{ $customer->mobile }}</div>
                                    @if($customer->phone_number_2)
                                        <div class="text-xs text-muted-foreground">{{ $customer->phone_number_2 }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-start gap-4 group">
                                <div class="h-10 w-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-muted-foreground uppercase">Email</span>
                                    <div class="font-medium text-gray-900 break-all">{{ $customer->email ?? 'N/A' }}</div>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 group">
                                <div class="h-10 w-10 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-muted-foreground uppercase">Address</span>
                                    @php $addr = $customer->addresses->firstWhere('is_default', true) ?? $customer->addresses->first(); @endphp
                                    @if($addr)
                                        <div class="text-sm font-medium leading-relaxed text-gray-700">
                                            {{ $addr->address_line1 }}, 
                                            {{ $addr->village ? $addr->village . ',' : '' }} {{ $addr->state }}
                                            <span class="block text-xs text-muted-foreground font-mono mt-0.5">{{ $addr->pincode }}</span>
                                        </div>
                                    @else
                                        <div class="text-sm text-muted-foreground italic">No address found</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Agri Profile -->
                        <div class="pt-6 border-t border-gray-100">
                            <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground block mb-3">Agri Profile</span>
                             <div class="flex flex-wrap gap-2 mb-3">
                                @forelse($customer->crops['primary'] ?? [] as $crop)
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        {{ $crop }}
                                    </span>
                                @empty
                                    <span class="text-xs text-muted-foreground italic">No primary crops</span>
                                @endforelse
                            </div>
                             <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="bg-muted/30 p-2.5 rounded-lg border border-gray-100">
                                    <span class="block text-[10px] text-muted-foreground uppercase mb-0.5">Land Area</span>
                                    <span class="font-bold text-gray-900">{{ $customer->land_area ?? 0 }} {{ $customer->land_unit ?? 'Acre' }}</span>
                                </div>
                                <div class="bg-muted/30 p-2.5 rounded-lg border border-gray-100">
                                    <span class="block text-[10px] text-muted-foreground uppercase mb-0.5">Irrigation</span>
                                    <span class="font-bold text-gray-900 capitalize">{{ $customer->irrigation_type ?? 'N/A' }}</span>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content -->
            <div class="lg:col-span-8">
                 <div class="rounded-2xl border border-white/50 bg-white/60 shadow-xl backdrop-blur-xl overflow-hidden min-h-[600px] flex flex-col">
                    
                    <!-- Tabs Header -->
                    <div class="flex items-center gap-1 p-2 border-b border-gray-100 bg-white/40 backdrop-blur-md sticky top-0 z-10 overflow-x-auto">
                         <button onclick="switchTab('timeline')" id="tab-btn-timeline" class="flex-1 px-4 py-3 text-sm font-bold rounded-xl transition-all tab-btn active-tab bg-white shadow-sm ring-1 ring-black/5 text-primary whitespace-nowrap">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Activity
                            </span>
                        </button>
                        <button onclick="switchTab('orders')" id="tab-btn-orders" class="flex-1 px-4 py-3 text-sm font-bold rounded-xl transition-all tab-btn text-muted-foreground hover:bg-white/50 hover:text-foreground whitespace-nowrap">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Pay & Orders
                            </span>
                        </button>
                        <button onclick="switchTab('profile')" id="tab-btn-profile" class="flex-1 px-4 py-3 text-sm font-bold rounded-xl transition-all tab-btn text-muted-foreground hover:bg-white/50 hover:text-foreground whitespace-nowrap">
                             <span class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Full Profile
                            </span>
                        </button>
                    </div>

                    <!-- Timeline Tab -->
                    <div id="tab-timeline" class="tab-content flex-1 p-6 bg-white/40">
                         @php
                            $mergedEvents = collect();
                            if ($orders) {
                                foreach ($orders as $order) {
                                    $mergedEvents->push([
                                        'type' => 'order',
                                        'date' => $order->created_at,
                                        'data' => $order
                                    ]);
                                }
                            }
                            if ($interactions) {
                                foreach ($interactions as $interaction) {
                                    $mergedEvents->push([
                                        'type' => 'interaction',
                                        'date' => $interaction->created_at,
                                        'data' => $interaction
                                    ]);
                                }
                            }
                            $sortedEvents = $mergedEvents->sortByDesc('date')->values();
                        @endphp

                        <div class="relative space-y-8 before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-primary/20 before:via-border/40 before:to-transparent">
                            @forelse($sortedEvents as $event)
                                <div class="relative flex gap-6 group">
                                    <!-- Icon -->
                                    <div class="absolute left-0 mt-1 flex h-10 w-10 items-center justify-center rounded-full border-4 border-white shadow-sm z-10 
                                        {{ $event['type'] === 'order' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                        @if($event['type'] === 'order')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        @endif
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="flex-1 ml-12 rounded-2xl bg-white border border-gray-100 p-5 shadow-sm hover:shadow-md transition-all">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 border-b border-gray-50 pb-3">
                                            <div>
                                                <span class="text-xs font-bold uppercase tracking-wider {{ $event['type'] === 'order' ? 'text-blue-600' : 'text-purple-600' }}">
                                                    {{ $event['type'] === 'order' ? 'Order Placed' : 'Interaction Logged' }}
                                                </span>
                                                <div class="text-xs text-muted-foreground">{{ $event['date']->format('d M Y, h:i A') }}</div>
                                            </div>
                                            <div>
                                                 @if($event['type'] === 'order')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-muted text-gray-700">
                                                        {{ ucfirst($event['data']->status) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($event['type'] === 'order')
                                            <div class="flex items-center justify-between">
                                                <div class="flex -space-x-2">
                                                    @foreach($event['data']->items->take(3) as $item)
                                                        <div class="h-8 w-8 rounded-full bg-muted ring-2 ring-white flex items-center justify-center text-[10px] font-bold overflow-hidden" title="{{ $item->product->name ?? 'Item' }}">
                                                            @if($item->product && $item->product->images && count($item->product->images) > 0)
                                                                <img src="{{ asset('storage/' . $item->product->images[0]) }}" class="h-full w-full object-cover">
                                                            @else
                                                                {{ substr($item->product->name ?? '?', 0, 1) }}
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-xs text-muted-foreground">Order Total</div>
                                                    <div class="font-black text-lg text-gray-900">₹{{ number_format($event['data']->grand_total, 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="mt-3 pt-3 border-t border-gray-50 flex items-center gap-2">
                                                <a href="{{ url('orders/' . $event['data']->id) }}" class="text-xs font-bold text-primary hover:underline">View Order Details &rarr;</a>
                                            </div>
                                        @else
                                            <div class="space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 capitalize border border-purple-100">{{ $event['data']->type }}</span>
                                                    @if($event['data']->outcome)
                                                        <span class="text-xs text-gray-700 font-medium">Outcome: {{ $event['data']->outcome }}</span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-muted-foreground leading-relaxed italic">"{{ $event['data']->description ?? $event['data']->notes }}"</p>
                                                <div class="flex items-center gap-2 pt-2">
                                                    <div class="h-5 w-5 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary">
                                                        {{ substr($event['data']->user->name ?? 'S', 0, 1) }}
                                                    </div>
                                                    <span class="text-xs text-muted-foreground">Logged by {{ $event['data']->user->name ?? 'System' }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-12 text-center">
                                    <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-muted mb-4">
                                        <svg class="w-6 h-6 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-900">No recent activity</h3>
                                    <p class="text-xs text-muted-foreground">Orders and interactions will appear here.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    
                    <!-- Separate Orders Tab -->
                    <div id="tab-orders" class="tab-content hidden p-0 bg-white">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-muted/30 border-b border-border/40">
                                    <th class="px-6 py-4 text-xs font-bold uppercase text-muted-foreground tracking-wider">Order</th>
                                    <th class="px-6 py-4 text-xs font-bold uppercase text-muted-foreground tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-xs font-bold uppercase text-muted-foreground tracking-wider text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/20">
                                @forelse($orders as $order)
                                    <tr class="hover:bg-muted/10 transition-colors cursor-pointer" onclick="window.location='/orders/{{ $order->id }}'">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900">#{{ $order->order_number }}</div>
                                            <div class="text-xs text-muted-foreground">{{ $order->created_at->format('d M Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-muted text-gray-700 uppercase tracking-wide">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="font-bold text-gray-900">₹{{ number_format($order->grand_total, 2) }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-8 text-muted-foreground text-sm">No orders found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                         @if($orders->count() > 0 && method_exists($orders, 'links'))
                             <div class="p-4 border-t border-border/20">
                                {{ $orders->appends(['interactions_page' => $interactions->currentPage()])->links() }}
                            </div>
                        @endif
                    </div>

                    <!-- Full Profile Tab (NEW) -->
                    <div id="tab-profile" class="tab-content hidden p-6 bg-white/40">
                         <div class="grid md:grid-cols-2 gap-6">
                            <!-- Personal Info -->
                            <div class="rounded-xl bg-white border border-gray-100 p-6 shadow-sm">
                                <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Personal Details
                                </h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between border-b border-gray-50 pb-2.5">
                                        <span class="text-sm text-muted-foreground">Full Name</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ $customer->first_name }} {{ $customer->last_name }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-50 pb-2.5">
                                        <span class="text-sm text-muted-foreground">Category</span>
                                        <span class="text-sm font-semibold text-gray-900 capitalize">{{ $customer->category ?? 'General' }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-50 pb-2.5">
                                        <span class="text-sm text-muted-foreground">Aadhaar (Last 4)</span>
                                        <span class="text-sm font-semibold text-gray-900 tracking-wider">{{ $customer->aadhaar_last4 ?? '-' }}</span>
                                    </div>
                                    <div class="flex justify-between pt-1">
                                         <span class="text-sm text-muted-foreground">KYC Status</span>
                                         @if($customer->kyc_completed)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 text-emerald-700">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Verified {{ $customer->kyc_verified_at ? 'on ' . $customer->kyc_verified_at->format('d M Y') : '' }}
                                            </span>
                                         @else
                                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700">Pending</span>
                                         @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Financial & Business -->
                             <div class="rounded-xl bg-white border border-gray-100 p-6 shadow-sm">
                                <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Business & Compliance
                                </h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between border-b border-gray-50 pb-2.5">
                                        <span class="text-sm text-muted-foreground">GST Number</span>
                                        <span class="text-sm font-mono font-semibold text-gray-900">{{ $customer->gst_number ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-50 pb-2.5">
                                        <span class="text-sm text-muted-foreground">PAN Number</span>
                                        <span class="text-sm font-mono font-semibold text-gray-900">{{ $customer->pan_number ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-50 pb-2.5">
                                        <span class="text-sm text-muted-foreground">Credit Validity</span>
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ $customer->credit_valid_till ? $customer->credit_valid_till->format('d M, Y') : 'Lifetime' }}
                                        </span>
                                    </div>
                                    <div class="pt-1">
                                        <span class="block text-xs uppercase tracking-wider text-muted-foreground mb-1">Tags</span>
                                        <div class="flex flex-wrap gap-1.5">
                                            @forelse($customer->tags ?? [] as $tag)
                                                <span class="px-2 py-0.5 rounded text-xs bg-muted text-gray-700 font-medium">{{ $tag }}</span>
                                            @empty
                                                <span class="text-sm italic text-muted-foreground">No tags assigned</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                             <!-- Internal Notes -->
                            <div class="col-span-1 md:col-span-2 rounded-xl bg-amber-50/50 border border-amber-100 p-6 shadow-sm">
                                <h4 class="font-bold text-amber-900 mb-2 text-sm uppercase tracking-wide">Internal Notes</h4>
                                <p class="text-sm text-amber-800 italic leading-relaxed">
                                    "{{ $customer->internal_notes ?? 'No internal notes available for this customer.' }}"
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Same as before but styled -->
<div id="interaction-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm transition-opacity">
    <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
             <div class="relative transform overflow-hidden rounded-2xl bg-white border border-white/20 shadow-2xl transition-all w-full max-w-lg">
                <div class="bg-white p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Log Interaction</h3>
                    <p class="text-sm text-muted-foreground mb-6">Record details to maintain the customer timeline.</p>
                     <form id="interaction-form" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase text-muted-foreground mb-1.5">Type</label>
                            <select name="type" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-900">
                                <option value="call">Phone Call</option>
                                <option value="visit">Site Visit</option>
                                <option value="general">General Inquiry</option>
                                <option value="payment">Payment Follow-up</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-muted-foreground mb-1.5">Outcome</label>
                            <input type="text" name="outcome" required class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-900" placeholder="e.g. Call connected, Order promised">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-muted-foreground mb-1.5">Notes</label>
                            <textarea name="notes" rows="3" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-900"></textarea>
                        </div>
                        <input type="hidden" name="close_session" value="1">
                    </form>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                    <button onclick="closeModal()" class="px-4 py-2 rounded-lg text-sm font-bold text-gray-600 hover:bg-white hover:text-gray-900 transition-colors">Cancel</button>
                    <button onclick="submitInteraction()" class="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-bold shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all">Save Interaction</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal() { document.getElementById('interaction-modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('interaction-modal').classList.add('hidden'); }
    
    function switchTab(id) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('tab-' + id).classList.remove('hidden');
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-white', 'shadow-sm', 'ring-1', 'ring-black/5', 'text-primary');
            btn.classList.add('text-muted-foreground');
        });
        const active = document.getElementById('tab-btn-' + id);
        active.classList.remove('text-muted-foreground');
        active.classList.add('bg-white', 'shadow-sm', 'ring-1', 'ring-black/5', 'text-primary');
    }

    function submitInteraction() {
        const form = document.getElementById('interaction-form');
        if (!form.checkValidity()) { form.reportValidity(); return; }
        
        const btn = document.querySelector('button[onclick="submitInteraction()"]');
        const oldHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Saving...';

        const formData = new FormData(form);
        fetch("{{ route('central.customers.interaction', $customer->id) }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        }).then(res => res.json()).then(data => {
            if(data.success) { window.location.reload(); }
            else { alert(data.message || 'Error'); btn.disabled=false; btn.innerHTML=oldHtml; }
        }).catch(err => {
            console.error(err);
            alert('Server Error');
            btn.disabled=false; btn.innerHTML=oldHtml; 
        });
    }
</script>
</div>
</x-layouts.app>