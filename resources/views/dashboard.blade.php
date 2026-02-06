@extends('layouts.app')

@section('content')
    <div x-data="{ activeTab: 'overview' }" class="flex flex-1 flex-col transition-all duration-300 animate-in fade-in zoom-in-95 duration-500">

        <!-- Enterprise Dashboard Header (Glassmorphic) -->
        <div class="relative z-30 flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-6 py-6 lg:px-8 border-b border-border/40 bg-background/60 backdrop-blur-xl supports-[backdrop-filter]:bg-background/40">
            <div class="space-y-1">
                <h1 class="text-3xl font-black tracking-tighter bg-gradient-to-br from-foreground via-foreground/90 to-foreground/70 bg-clip-text text-transparent font-heading">
                    {{ auth()->user()->name }} Dashboard
                </h1>
                <div class="flex items-center gap-3 text-sm text-muted-foreground font-medium">
                    <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500 ring-1 ring-emerald-500/20 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Live Store
                    </span>
                    <span class="text-border/50">|</span>
                    <span>{{ now()->format('l, F j, Y') }}</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                 <!-- Tab Switcher -->
                 <div class="flex p-1 bg-muted/50 rounded-xl border border-border/50 backdrop-blur-md mr-2">
                     <button @click="activeTab = 'overview'" 
                             :class="activeTab === 'overview' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                             class="px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-lg transition-all duration-200">
                         Overview
                     </button>
                     <button @click="activeTab = 'orders'" 
                             :class="activeTab === 'orders' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                             class="px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-lg transition-all duration-200">
                         Order History
                     </button>
                 </div>

                 <!-- Premium Period Selector -->
                 <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-background/50 border border-input px-4 py-2 text-sm font-medium text-foreground shadow-sm hover:bg-accent hover:text-accent-foreground hover:border-primary/20 transition-all duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        <span>
                            {{ [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'week' => 'This Week',
        'month' => 'This Month',
        'year' => 'This Year',
        '30days' => 'Last 30 Days'
    ][$period ?? 'today'] }}
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
                    </button>

                    <div x-show="open" @click.away="open = false" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="absolute right-0 mt-2 w-48 rounded-2xl border border-border/50 bg-background/95 backdrop-blur-xl shadow-2xl z-[100] overflow-hidden py-1 ring-1 ring-black/5" 
                         style="display: none;">
                        @foreach(['today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', '30days' => 'Last 30 Days'] as $key => $label)
                            <a href="{{ request()->fullUrlWithQuery(['period' => $key]) }}" 
                               class="flex items-center px-4 py-2.5 text-xs font-bold uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors {{ ($period ?? '30days') === $key ? 'bg-primary/5 text-primary' : 'text-muted-foreground' }}">
                               {{ $label }}
                               @if(($period ?? '30days') === $key)
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="ml-auto"><polyline points="20 6 9 17 4 12"/></svg>
                               @endif
                            </a>
                        @endforeach
                    </div>
                 </div>

                <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    Download Report
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="p-6 lg:p-10 space-y-10 min-h-screen">

            <!-- Tab Content: Overview -->
            <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

                <!-- Enterprise KPI Grid -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    @foreach($stats as $stat)
                        <div class="group relative overflow-hidden rounded-3xl border border-border/50 bg-card p-6 shadow-sm transition-all duration-500 hover:shadow-2xl hover:border-primary/30 hover:-translate-y-2">
                            <!-- Dynamic Glow -->
                            <div class="absolute -inset-px bg-gradient-to-br from-primary/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>

                            <div class="relative flex flex-row items-start justify-between pb-4">
                                <div class="space-y-1.5">
                                    <span class="text-xs font-black uppercase tracking-[0.2em] text-muted-foreground group-hover:text-primary/70 transition-colors">{{ $stat['title'] }}</span>
                                    <div class="text-3xl font-black font-heading tracking-tight">{{ $stat['value'] }}</div>
                                </div>
                                 <div class="rounded-2xl bg-secondary/50 p-3 text-muted-foreground group-hover:text-primary group-hover:bg-primary/10 transition-all duration-300 group-hover:rotate-12">
                                     @if($stat['icon'] == 'dollar-sign')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                     @elseif($stat['icon'] == 'users')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                     @elseif($stat['icon'] == 'refresh-cw')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/></svg>
                                     @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                     @endif
                                 </div>
                            </div>

                            <div class="relative mt-2 flex items-center justify-between">
                                <div class="flex flex-col gap-1">
                                     @if($stat['trend'] === 'up')
                                         <span class="flex items-center text-[10px] text-emerald-500 font-black uppercase tracking-widest px-2 py-0.5 bg-emerald-500/10 rounded-lg w-fit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                                            {{ $stat['change'] }}
                                         </span>
                                     @else
                                         <span class="flex items-center text-[10px] text-rose-500 font-black uppercase tracking-widest px-2 py-0.5 bg-rose-500/10 rounded-lg w-fit">
                                             <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/></svg>
                                             {{ $stat['change'] }}
                                         </span>
                                     @endif
                                     <span class="text-[10px] font-bold text-muted-foreground/60 uppercase tracking-tighter">{{ $stat['desc'] }}</span>
                                </div>

                                <!-- Sparkline (SVG) -->
                                <div class="h-10 w-24">
                                    <svg class="h-full w-full" viewBox="0 0 100 40">
                                        <path d="M 0 35 Q 10 30, 20 {{ $stat['trend'] === 'up' ? 25 : 38 }} T 40 {{ $stat['trend'] === 'up' ? 15 : 30 }} T 60 {{ $stat['trend'] === 'up' ? 20 : 35 }} T 80 {{ $stat['trend'] === 'up' ? 5 : 25 }} T 100 {{ $stat['trend'] === 'up' ? 10 : 32 }}" 
                                              fill="none" 
                                              stroke="{{ $stat['trend'] === 'up' ? 'rgba(16,185,129,0.5)' : 'rgba(239,68,68,0.5)' }}" 
                                              stroke-width="3" 
                                              stroke-linecap="round" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div> <!-- End KPI Grid -->

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-7">

                <!-- Left Column (Chart + Recent Orders) -->
                <div class="col-span-1 lg:col-span-5 space-y-6">
                    
                    <!-- Sales Over Time Chart -->
                    <!-- Sales Over Time Chart -->


                    <!-- Recent Orders (Balancing the layout) -->
                    <div class="rounded-3xl border border-border/50 bg-card/40 backdrop-blur-xl shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-border/40 flex justify-between items-center">
                             <h3 class="text-lg font-bold text-foreground font-heading">Recent Flux</h3>
                             <a href="{{ route(auth()->user()->hasRole('Super Admin') ? 'central.orders.index' : 'tenant.orders.index') }}" class="text-xs font-black uppercase tracking-widest text-primary hover:text-primary/70 transition-colors">View All →</a>
                        </div>

                        <div class="divide-y divide-border/40">
                             @foreach($recentOrders as $order)
                             <div onclick="window.location.href='{{ tenant('id') ? route('tenant.orders.show', $order) : route('central.orders.show', $order) }}'"
                                  class="group p-4 px-6 flex items-center justify-between hover:bg-primary/5 transition-all duration-300 cursor-pointer">
                                 <div class="flex items-center gap-4">
                                     <div class="h-10 w-10 rounded-2xl bg-secondary/80 flex items-center justify-center text-xs font-black text-foreground border border-border group-hover:scale-110 transition-transform">
                                         {{ substr($order->customer->name ?? 'G', 0, 1) }}
                                     </div>
                                     <div>
                                         <p class="text-sm font-black text-foreground group-hover:text-primary transition-colors leading-tight">{{ $order->customer->name ?? 'Guest' }}</p>
                                         <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-0.5">Ref: #{{ $order->order_number }}</p>
                                         @if($order->creator)
                                         <div class="flex items-center gap-1 mt-1">
                                             <svg class="w-3 h-3 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                             <span class="text-[9px] font-medium text-muted-foreground">
                                                 {{ $order->creator->name }} 
                                                 <span class="opacity-50">({{ $order->creator->location ?? 'N/A' }})</span>
                                             </span>
                                         </div>
                                         @endif
                                     </div>
                                 </div>
                                 <div class="text-right space-y-1">
                                     <p class="text-sm font-black text-foreground tracking-tight">Rs {{ number_format($order->grand_total, 2) }}</p>
                                     @php
                                         $statusMap = [
                                             'completed' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                                             'processing' => 'bg-blue-500/10 text-blue-600 border-blue-500/20',
                                             'cancelled' => 'bg-rose-500/10 text-rose-600 border-rose-500/20',
                                             'pending' => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                                         ];
                                         $statusClass = $statusMap[$order->status] ?? 'bg-muted text-muted-foreground border-border';
                                     @endphp
                                     <span class="inline-flex items-center rounded-lg px-2 py-0.5 text-[9px] font-black uppercase tracking-widest border {{ $statusClass }}">
                                         {{ $order->status }}
                                     </span>
                                 </div>
                             </div>
                             @endforeach
                        </div>
                    </div>
                </div>

                    <!-- Activity / Quick Actions -->
                    <div class="col-span-1 lg:col-span-2 space-y-6">

                        <!-- Team Activity (Refined) -->
                        <div x-data="{ searchQuery: '' }" class="rounded-3xl border border-border/50 bg-card/40 backdrop-blur-xl shadow-sm overflow-hidden h-full">
                            <div class="p-4 px-6 border-b border-border/40 bg-muted/20 space-y-3">
                                <div class="flex justify-between items-center">
                                     <h3 class="text-sm font-black uppercase tracking-widest font-heading flex items-center gap-2">
                                        <span class="relative flex h-2 w-2">
                                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                        Team Activity
                                     </h3>
                                     <div class="flex items-center gap-2">
                                        <a href="{{ route('central.team.export') }}" class="text-[10px] font-bold text-primary hover:underline uppercase tracking-wider" title="Export CSV">Export</a>
                                        <span class="text-border/40">|</span>
                                        <span class="text-[10px] font-bold text-muted-foreground">LIVE</span>
                                     </div>
                                </div>
                                <!-- Search Box -->
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <input type="text" x-model="searchQuery" placeholder="Search online users..." 
                                           class="w-full bg-background/50 border border-border/50 rounded-xl py-2 pl-9 pr-3 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-all placeholder:text-muted-foreground/70">
                                </div>
                            </div>
                            <div class="divide-y divide-border/40 max-h-[500px] overflow-y-auto custom-scrollbar">
                                @foreach($onlineUsers ?? [] as $onlineUser)
                                    @php $isOnline = $onlineUser->isOnline(); @endphp
                                    @if($isOnline) <!-- Only Show Online Users -->
                                    <div x-show="!searchQuery || '{{ strtolower($onlineUser->name) }}'.includes(searchQuery.toLowerCase())" 
                                         class="flex items-center justify-between p-3 px-4 hover:bg-primary/5 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="relative">
                                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                                    {{ substr($onlineUser->name, 0, 2) }}
                                                </div>
                                                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-background bg-emerald-500 animate-pulse"></span>
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-foreground">{{ $onlineUser->name }}</div>
                                                <div class="text-[10px] text-muted-foreground flex items-center gap-1">
                                                    <svg class="w-3 h-3 text-muted-foreground/70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    {{ $onlineUser->location ?? 'Unknown' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end gap-0.5">
                                            <div class="text-[10px] font-black text-foreground">
                                                Rs {{ number_format($onlineUser->total_revenue ?? 0, 2) }}
                                            </div>
                                            <div class="flex gap-1">
                                                <span class="text-[9px] font-bold text-muted-foreground">
                                                    {{ $onlineUser->orders_count }} Orders
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                                @if(collect($onlineUsers)->where(fn($u) => $u->isOnline())->isEmpty())
                                    <div class="p-8 text-center text-muted-foreground">
                                        <p class="text-xs">No users online</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <!-- Tab Content: Order History -->
            <div x-show="activeTab === 'orders'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <!-- Table View (Hidden on mobile) -->
                <!-- Table View (Hidden on mobile) -->
                <div class="hidden md:block rounded-3xl border border-border/50 bg-card/50 backdrop-blur-sm shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-border/40 flex items-center justify-between bg-muted/20">
                        <div>
                            <h3 class="text-xl font-bold font-heading tracking-tight text-foreground">Order History</h3>
                            <p class="text-sm text-muted-foreground font-medium">Recent transactions and order status</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest px-4 py-1.5 bg-secondary/50 rounded-xl border border-border/30">
                                Total Orders: {{ count($orderHistory) }}
                            </span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-border/40 bg-muted/10">
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/70">Order ID</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/70">Customer</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/70">Date</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/70 text-right">Amount</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/70 text-center">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/70">Created By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                @foreach($orderHistory as $order)
                                    <tr class="group hover:bg-primary/[0.03] transition-all cursor-pointer" @click="window.location.href='{{ tenant('id') ? route('tenant.orders.show', $order) : route('central.orders.show', $order) }}'">
                                        <td class="px-6 py-5">
                                            <span class="font-mono text-sm font-black text-foreground group-hover:text-primary transition-colors tracking-tighter">#{{ $order->order_number }}</span>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-4">
                                                <div class="size-9 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary flex items-center justify-center font-black text-xs border border-primary/10 group-hover:scale-110 transition-transform">
                                                    {{ substr($order->customer->name ?? 'G', 0, 1) }}
                                                </div>
                                                <span class="text-sm font-black text-foreground group-hover:translate-x-1 transition-transform">{{ $order->customer->name ?? 'Guest Entity' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-black text-foreground">{{ $order->created_at->format('M d, Y') }}</span>
                                                <span class="text-[10px] font-bold text-muted-foreground tracking-widest">{{ $order->created_at->format('H:i:s') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <span class="font-mono text-sm font-black text-foreground group-hover:text-primary transition-colors">Rs {{ number_format($order->grand_total, 2) }}</span>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            @php
                                                $statusClass = [
                                                    'completed' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                                    'processing' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                                    'cancelled' => 'bg-rose-500/10 text-rose-500 border-rose-500/20',
                                                    'pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                                    'shipped' => 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20',
                                                ][$order->status] ?? 'bg-muted text-muted-foreground border-border';
                                            @endphp
                                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-widest border {{ $statusClass }}">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-2">
                                                <div class="size-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                                <span class="text-[10px] font-black uppercase tracking-tight text-muted-foreground group-hover:text-foreground transition-colors">
                                                    {{ $order->creator->name ?? 'SYSTEM_SECURE' }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View (Shown only on small screens) -->
                <div class="md:hidden space-y-4">
                    @foreach($orderHistory as $order)
                        <div @click="window.location.href='{{ tenant('id') ? route('tenant.orders.show', $order) : route('central.orders.show', $order) }}'"
                             class="group relative overflow-hidden rounded-3xl border border-border/50 bg-card p-5 shadow-sm active:scale-95 transition-all">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center font-black text-sm border border-primary/20">
                                        {{ substr($order->customer->name ?? 'G', 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-foreground">{{ $order->customer->name ?? 'Guest Entity' }}</h4>
                                        <p class="text-[10px] font-bold text-muted-foreground tracking-widest uppercase">#{{ $order->order_number }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                     <p class="text-sm font-black text-foreground">Rs {{ number_format($order->grand_total, 2) }}</p>
                                     <p class="text-[10px] font-bold text-muted-foreground">{{ $order->created_at->format('M d, H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex justify-between items-center bg-muted/30 rounded-2xl p-3">
                                 @php
                                    $statusClass = [
                                        'completed' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                        'processing' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                        'cancelled' => 'bg-rose-500/10 text-rose-500 border-rose-500/20',
                                        'pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                        'shipped' => 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20',
                                    ][$order->status] ?? 'bg-muted text-muted-foreground border-border';
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-widest border {{ $statusClass }}">
                                    {{ $order->status }}
                                </span>
                                <span class="text-[9px] font-black uppercase tracking-widest text-muted-foreground">BY: {{ $order->creator->name ?? 'SYSTEM' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
