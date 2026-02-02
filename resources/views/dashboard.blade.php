@extends('layouts.app')

@section('content')
<div x-data="{ activeTab: 'overview' }" class="flex flex-1 flex-col transition-all duration-300 animate-in fade-in zoom-in-95 duration-500">
    
    <!-- Enterprise Dashboard Header (Glassmorphic) -->
    <div class="relative z-30 flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-6 py-6 lg:px-8 border-b border-border/40 bg-background/60 backdrop-blur-xl supports-[backdrop-filter]:bg-background/40">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent font-heading">
                Dashboard
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
             <!-- Date Range Picker (Static) -->
            <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-background/50 border border-input px-4 py-2 text-sm font-medium text-foreground shadow-sm hover:bg-accent hover:text-accent-foreground hover:border-primary/20 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                Last 30 Days
            </button>
            <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                Download Report
            </button>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="p-6 lg:p-8 space-y-8 min-h-screen">
        
        <!-- Tab Content: Overview -->
        <div class="space-y-6">
            
            <!-- Enterprise KPI Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @foreach($stats as $stat)
                <div class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-primary/20 hover:-translate-y-1">
                    <!-- Subtle Glow Effect -->
                    <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    
                    <div class="relative flex flex-row items-center justify-between space-y-0 pb-2">
                        <span class="text-sm font-medium text-muted-foreground group-hover:text-foreground transition-colors">{{ $stat['title'] }}</span>
                         <div class="rounded-full bg-secondary/50 p-2 text-muted-foreground group-hover:text-primary group-hover:bg-primary/10 transition-colors">
                             @if($stat['icon'] == 'dollar-sign')
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                             @elseif($stat['icon'] == 'users')
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                             @elseif($stat['icon'] == 'refresh-cw')
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/></svg>
                             @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                             @endif
                         </div>
                    </div>
                    
                    <div class="relative space-y-1">
                        <div class="text-2xl font-bold font-heading tracking-tight">{{ $stat['value'] }}</div>
                        <div class="flex items-center text-xs">
                             @if($stat['trend'] === 'up')
                                 <span class="flex items-center text-emerald-500 font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                                    {{ $stat['change'] }}
                                 </span>
                             @else
                                 <span class="flex items-center text-rose-500 font-medium">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/></svg>
                                     {{ $stat['change'] }}
                                 </span>
                             @endif
                             <span class="text-muted-foreground ml-2">{{ $stat['desc'] }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-7">
                
                <!-- Sales Over Time Chart -->
                <div class="col-span-1 lg:col-span-5 rounded-3xl border border-border/50 bg-card/50 backdrop-blur-sm shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-border/40 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold font-heading tracking-tight">Total Revenue</h3>
                             <p class="text-sm text-muted-foreground">Revenue trends over the last period</p>
                        </div>
                        <div class="flex gap-2">
                             <button class="text-xs px-2.5 py-1 rounded-md bg-primary/10 text-primary font-medium transition-colors hover:bg-primary/20">Daily</button>
                             <button class="text-xs px-2.5 py-1 rounded-md text-muted-foreground font-medium transition-colors hover:bg-secondary hover:text-foreground">Weekly</button>
                        </div>
                    </div>
                    <div class="p-6">
                        <!-- CSS Bar Chart (Premium Look) -->
                        <div class="relative h-[320px] w-full pt-8 flex items-end justify-between gap-2 px-2">
                             <!-- Grid Lines -->
                             <div class="absolute inset-0 flex flex-col justify-between text-xs text-muted-foreground/30 pointer-events-none pb-6">
                                 <div class="border-b border-border/30 w-full h-0"></div>
                                 <div class="border-b border-border/30 w-full h-0"></div>
                                 <div class="border-b border-border/30 w-full h-0"></div>
                                 <div class="border-b border-border/30 w-full h-0"></div>
                                 <div class="border-b border-border/30 w-full h-0"></div>
                             </div>

                             @php
                                $maxVal = count($chartData) > 0 ? max($chartData) : 1;
                                if ($maxVal == 0) $maxVal = 1;
                             @endphp
                             @foreach($chartData as $index => $value)
                                <div class="relative flex-1 group z-10" title="Rs {{ number_format($value, 2) }}">
                                      <div class="w-full bg-gradient-to-t from-primary/60 to-primary rounded-t-sm opacity-80 group-hover:opacity-100 group-hover:to-primary group-hover:from-primary/80 transition-all duration-300 shadow-[0_0_15px_rgba(var(--primary),0.3)] hover:shadow-[0_0_20px_rgba(var(--primary),0.5)]" 
                                           style="height: {{ ($value / $maxVal) * 100 }}%"></div>
                                </div>
                             @endforeach
                        </div>
                         <div class="flex justify-between text-xs font-medium text-muted-foreground mt-4 px-2">
                             <span>Oct 1</span>
                             <span>Oct 8</span>
                             <span>Oct 15</span>
                             <span>Oct 22</span>
                             <span>Oct 30</span>
                         </div>
                    </div>
                </div>

                <!-- Activity / Quick Actions -->
                <div class="col-span-1 lg:col-span-2 space-y-6">
                    
                    <!-- Quick Actions Card -->
                    <div class="rounded-3xl border border-border/50 bg-gradient-to-br from-card to-background shadow-sm p-6 relative overflow-hidden">
                         <div class="absolute top-0 right-0 p-4 opacity-10">
                             <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                         </div>
                         
                         <h3 class="text-sm font-bold font-heading mb-4 flex items-center gap-2">
                            <span class="flex h-2 w-2 rounded-full bg-blue-500"></span>
                            Setup Progress
                         </h3>
                         
                         <div class="space-y-4 relative z-10">
                             <!-- Completed Step -->
                             <div class="flex items-start gap-3 group">
                                 <div class="h-6 w-6 rounded-full bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 flex items-center justify-center shrink-0">
                                      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                 </div>
                                 <div class="flex-1">
                                    <span class="text-sm font-medium text-muted-foreground/60 line-through decoration-muted-foreground/40">Customize theme</span>
                                 </div>
                             </div>
                             
                             <!-- Current Step -->
                             <div class="flex items-start gap-3 group">
                                 <div class="h-6 w-6 rounded-full bg-primary text-primary-foreground border border-primary flex items-center justify-center shrink-0 shadow-sm shadow-primary/20 ring-2 ring-primary/20">
                                     <span class="text-[10px] font-bold">2</span>
                                 </div>
                                 <div class="flex-1">
                                    <span class="text-sm font-semibold text-foreground">Add your first product</span>
                                    <p class="text-xs text-muted-foreground mt-0.5">Start selling by adding inventory.</p>
                                 </div>
                                 <button class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-primary hover:bg-primary/10 rounded">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                 </button>
                             </div>

                             <!-- Future Step -->
                              <div class="flex items-start gap-3 group opacity-60">
                                 <div class="h-6 w-6 rounded-full border border-border bg-muted flex items-center justify-center shrink-0">
                                     <span class="text-[10px] font-medium text-muted-foreground">3</span>
                                 </div>
                                 <span class="text-sm text-muted-foreground">Set up payments</span>
                              </div>
                              
                              <!-- Future Step -->
                              <div class="flex items-start gap-3 group opacity-60">
                                 <div class="h-6 w-6 rounded-full border border-border bg-muted flex items-center justify-center shrink-0">
                                     <span class="text-[10px] font-medium text-muted-foreground">4</span>
                                 </div>
                                 <span class="text-sm text-muted-foreground">Launch Store</span>
                              </div>
                         </div>
                         
                         <button class="mt-6 w-full rounded-xl bg-primary py-2.5 text-xs font-bold text-primary-foreground hover:bg-primary/90 transition-all hover:scale-[1.02] active:scale-95 shadow-lg shadow-primary/20">
                             Continue Setup
                         </button>
                    </div>

                    <!-- Recent Orders List -->
                    <div class="rounded-3xl border border-border/50 bg-card shadow-sm overflow-hidden">
                        <div class="p-4 px-5 border-b border-border/40 flex justify-between items-center bg-muted/30">
                             <h3 class="text-sm font-bold font-heading">Recent Transactions</h3>
                             <a href="#" class="text-xs font-medium text-primary hover:underline">View All</a>
                        </div>
                        <div class="divide-y divide-border/40">
                            @foreach($recentOrders as $order)
                            <div @click="window.location.href='{{ tenant('id') ? route('tenant.orders.show', $order) : route('central.orders.show', $order) }}'"
                                 class="group p-4 px-5 flex items-center justify-between hover:bg-muted/50 transition-colors cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-secondary flex items-center justify-center text-xs font-bold text-muted-foreground border border-border">
                                        {{ substr($order->customer->name ?? 'G', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors">{{ $order->customer->name ?? 'Guest' }}</p>
                                        <p class="text-xs text-muted-foreground">#{{ $order->order_number }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-foreground">Rs {{ number_format($order->grand_total, 2) }}</p>
                                    @php
                                        $statusBaseClasses = 'inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-medium border';
                                        $statusMap = [
                                            'completed' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                                            'processing' => 'bg-blue-500/10 text-blue-600 border-blue-500/20',
                                            'cancelled' => 'bg-destructive/10 text-destructive border-destructive/20',
                                            'pending' => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                                        ];
                                        $statusClass = $statusMap[$order->status] ?? 'bg-muted text-muted-foreground border-border';
                                    @endphp
                                    <span class="{{ $statusBaseClasses }} {{ $statusClass }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
