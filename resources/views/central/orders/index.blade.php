@extends('layouts.app')

@section('content')
<div id="orders-page-wrapper" class="flex flex-1 flex-col space-y-6 p-4 md:p-8 animate-in fade-in duration-500 bg-background/50">
   
   <!-- Header Area -->
   <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div class="space-y-1.5">
         <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/60 bg-clip-text text-transparent">Orders</h1>
         <p class="text-muted-foreground text-sm font-medium">Manage customer orders and fulfillment status.</p>
      </div>

      <!-- Status Tabs (Segmented Control) -->
      <div class="flex items-center p-1 bg-muted/60 rounded-xl border border-border/40 backdrop-blur-sm self-start sm:self-auto overflow-x-auto max-w-full no-scrollbar">
         <a href="{{ route('central.orders.index') }}" 
            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-all duration-300 {{ request('status') === null ? 'bg-background text-foreground shadow-sm ring-1 ring-border/5' : 'text-muted-foreground/80 hover:text-foreground hover:bg-background/40' }}">
            All
         </a>
         <div class="w-px h-4 bg-border/40 mx-1 shrink-0"></div>
         
         <a href="{{ route('central.orders.index', ['status' => 'pending']) }}" 
            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-all duration-300 whitespace-nowrap {{ request('status') === 'pending' ? 'bg-background text-amber-600 shadow-sm ring-1 ring-amber-500/10' : 'text-muted-foreground/80 hover:text-amber-600 hover:bg-background/40' }}">
            Pending
         </a>
         <div class="w-px h-4 bg-border/40 mx-1 shrink-0"></div>

         <a href="{{ route('central.orders.index', ['status' => 'ready_to_ship']) }}" 
            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-all duration-300 whitespace-nowrap {{ request('status') === 'ready_to_ship' ? 'bg-background text-sky-600 shadow-sm ring-1 ring-sky-500/10' : 'text-muted-foreground/80 hover:text-sky-600 hover:bg-background/40' }}">
            Ready to Ship
         </a>
         <div class="w-px h-4 bg-border/40 mx-1 shrink-0"></div>

         <a href="{{ route('central.orders.index', ['status' => 'completed']) }}" 
            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-all duration-300 whitespace-nowrap {{ request('status') === 'completed' ? 'bg-background text-emerald-600 shadow-sm ring-1 ring-emerald-500/10' : 'text-muted-foreground/80 hover:text-emerald-600 hover:bg-background/40' }}">
            Completed
         </a>
      </div>
   </div>

   <div id="orders-table-container" x-data="{ selected: [] }">
      
      <!-- Control Bar (Glassmorphism) -->
      <!-- Control Bar (Glassmorphism) -->
      <div class="flex flex-wrap items-center justify-between gap-4 p-2 pl-3 bg-white/40 dark:bg-black/20 border border-white/20 dark:border-white/5 backdrop-blur-xl rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] mb-6 transition-all duration-300 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
         
         <div class="flex flex-wrap items-center gap-3 flex-1 min-w-[280px]">
            <!-- Filters Form -->
            <form id="search-form" method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto relative z-10">
               @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
               @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

               <!-- Date Range (Joined) -->
               <div class="flex items-center rounded-lg border border-border/50 bg-background/50 overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                   <input type="date" name="start_date" value="{{ request('start_date') }}" class="h-9 border-none bg-transparent text-xs px-2 focus:ring-0 outline-none w-28 text-muted-foreground uppercase tracking-wide font-medium" placeholder="Start">
                   <div class="w-px h-4 bg-border/50"></div>
                   <input type="date" name="end_date" value="{{ request('end_date') }}" class="h-9 border-none bg-transparent text-xs px-2 focus:ring-0 outline-none w-28 text-muted-foreground uppercase tracking-wide font-medium" placeholder="End">
               </div>

               <!-- Status Dropdowns -->
               <div class="flex items-center gap-2">
                   <select name="status" class="h-9 rounded-lg border-border/50 bg-background/50 text-xs font-medium cursor-pointer shadow-sm hover:bg-background transition-colors focus:ring-2 focus:ring-primary/20 outline-none w-32">
                       <option value="">Status: All</option>
                       <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                       <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                       <option value="ready_to_ship" {{ request('status') === 'ready_to_ship' ? 'selected' : '' }}>Ready to Ship</option>
                       <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                       <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                       <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                       <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                   </select>

                   <select name="payment_status" class="h-9 rounded-lg border-border/50 bg-background/50 text-xs font-medium cursor-pointer shadow-sm hover:bg-background transition-colors focus:ring-2 focus:ring-primary/20 outline-none w-28">
                       <option value="">Payment</option>
                       <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                       <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                       <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                   </select>
               </div>

               <!-- Search -->
               <div class="relative transition-all duration-300 group-focus-within:w-64 w-56">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                     <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-focus-within:text-primary transition-colors">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                     </svg>
                  </div>
                  <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." 
                     class="block w-full rounded-xl border-border/50 py-2 pl-9 pr-3 text-foreground bg-background/50 placeholder:text-muted-foreground/70 focus:bg-background focus:ring-2 focus:ring-primary/20 text-sm leading-6 transition-all shadow-sm outline-none">
               </div>
            </form>
         </div>

         <!-- Right Actions -->
         <div class="flex items-center gap-3 relative z-20 shrink-0 ml-auto">
            <div x-data="{ open: false }" class="relative">
               <x-ui.button variant="outline" @click="open = !open" class="gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                  </svg>
                  Export
               </x-ui.button>
               <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 rounded-xl border border-border bg-popover p-1 shadow-xl z-50">
                  <form action="{{ route('central.orders.export') }}" method="POST">
                     @csrf
                     <button name="format" value="csv" class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-accent transition-colors">Export CSV</button>
                     <button name="format" value="xlsx" class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-accent transition-colors">Export Excel (.xlsx)</button>
                     <button name="format" value="pdf" class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-accent transition-colors">Export PDF</button>
                  </form>
               </div>
            </div>
            <a href="{{ route('central.orders.create', ['reset' => 1]) }}" 
               onclick="localStorage.removeItem('order_wizard_state')"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
               <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                  <path d="M12 5v14"/>
               </svg>
               <span>Create Order</span>
            </a>
         </div>
      </div>

    <!-- Floating Bulk Action Bar -->
    <div x-cloak 
         x-show="selected.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-10 scale-95"
         class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 w-full max-w-2xl px-4 pointer-events-none">
        
        <div class="pointer-events-auto flex items-center justify-between gap-4 p-2 pl-4 bg-foreground/90 text-background backdrop-blur-xl rounded-full shadow-2xl border border-white/10 ring-1 ring-black/5">
            
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center h-6 w-6 rounded-full bg-primary text-primary-foreground text-xs font-bold">
                    <span x-text="selected.length"></span>
                </div>
                <span class="text-sm font-medium">Selected</span>
                
                <div class="h-4 w-px bg-background/20"></div>
                
                <button @click="selected = []" class="text-xs text-background/70 hover:text-background transition-colors font-medium">
                    Clear
                </button>
            </div>

            <div class="flex items-center gap-2">
                <form id="bulk-print-form" action="{{ route('central.orders.bulk-print') }}" method="POST" target="_blank" class="flex gap-2">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    
                    <button type="submit" name="type" value="invoice" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-background/10 hover:bg-background/20 text-background transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                        Invoices
                    </button>
                    
                    <button type="submit" name="type" value="cod" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-background/10 hover:bg-background/20 text-background transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        COD
                    </button>
                </form>
            </div>
        </div>
    </div>
      <div class="rounded-2xl border border-border/40 bg-card/60 backdrop-blur-xl shadow-[0_2px_20px_rgb(0,0,0,0.02)] overflow-hidden relative">
         <div id="table-loading" class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent shadow-lg"></div>
         </div>
         <div class="border-b border-border/40 p-4 bg-muted/10 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-xs text-muted-foreground font-medium">
               <span class="flex h-6 w-8 items-center justify-center rounded-md bg-background border border-border/50 font-bold text-foreground shadow-sm">
               {{ $orders->total() }}
               </span>
               <span class="tracking-wide uppercase text-[10px]">orders found</span>
            </div>
            <div class="flex items-center gap-3">
               <form id="per-page-form" method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                  @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                  @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                  <label for="per_page" class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground whitespace-nowrap">Show</label>
                  <div class="relative">
                     <select name="per_page" id="per_page" class="appearance-none h-7 pl-2.5 pr-7 rounded-lg border border-border/50 bg-background text-xs font-semibold focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors cursor-pointer hover:bg-accent/50 hover:border-border">
                     <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                     <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                     <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                     </select>
                     <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-1.5 text-muted-foreground">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                     </div>
                  </div>
               </form>
            </div>
         </div>
         <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
               <thead class="[&_tr]:border-b">
                  <tr class="border-b border-border/40 transition-colors hover:bg-muted/10 data-[state=selected]:bg-muted bg-muted/5">
                     <th class="h-10 w-[50px] px-6 text-left align-middle">
                        <div class="flex items-center">
                           <input type="checkbox" class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary shadow-sm" @click="selected = $event.target.checked ? [{{ $orders->pluck('id')->join(',') }}] : []">
                        </div>
                     </th>
                     <th class="h-10 px-6 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Order</th>
                     <th class="h-10 px-6 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Customer</th>
                     <th class="h-10 px-6 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Created By</th>
                     <th class="h-10 px-6 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Date</th>
                     <th class="h-10 px-6 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Total</th>
                     <th class="h-10 px-6 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Status</th>
                     <th class="h-10 px-6 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Payment</th>
                     <th class="h-10 px-6 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Shipping</th>
                     <th class="h-10 px-6 text-right align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]"></th>
                  </tr>
               </thead>
               <tbody class="[&_tr:last-child]:border-0 text-sm">
                            @forelse($orders as $order)
                           <tr class="group border-b border-border/40 transition-all duration-300 hover:bg-muted/30 data-[state=selected]:bg-muted/60">

                               <!-- Checkbox -->
                               <td class="p-4 px-6 align-middle">
                                   <input type="checkbox"
                                          value="{{ $order->id }}"
                                          x-model="selected"
                                          class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary shadow-sm">
                               </td>

                               <!-- Order Number + Tracking -->
                               <td class="p-4 px-6 align-middle">
                                   <div class="flex flex-col space-y-1">
                                       <a href="{{ route('central.orders.show', $order) }}"
                                          class="font-bold text-primary hover:underline text-sm tracking-tight transition-colors">
                                           {{ $order->order_number }}
                                       </a>

                                       @if(
                                           in_array($order->shipping_status, ['shipped','in_transit','delivered']) &&
                                           $order->shipments->isNotEmpty()
                                       )
                                           <div class="flex items-center gap-1.5 text-muted-foreground/80">
                                              <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8h14"/><path d="M5 12h14"/><path d="M5 16h14"/></svg>
                                              <span class="text-[10px] font-mono tracking-tighter">
                                                  {{ $order->shipments->first()->tracking_number }}
                                              </span>
                                           </div>
                                       @endif
                                   </div>
                               </td>

                               <!-- Customer -->
                               <td class="p-4 px-6 align-middle">
                                   <div class="flex items-center gap-2">
                                       <div class="h-6 w-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-[9px] font-bold text-white shadow-sm ring-1 ring-white/20">
                                           {{ substr($order->customer->name ?? 'G', 0, 1) }}
                                       </div>
                                       <a href="{{ $order->customer_id ? route('central.customers.show', $order->customer_id) : '#' }}"
                                          class="text-xs font-semibold hover:text-primary hover:underline truncate max-w-[120px]">
                                           {{ $order->customer->name ?? 'Guest' }}
                                       </a>
                                   </div>
                               </td>

                               <!-- Created By -->
                               <td class="p-4 px-6 align-middle">
                                   <span class="text-xs text-muted-foreground font-medium">
                                       {{ $order->creator->name ?? 'System' }}
                                   </span>
                               </td>

                               <!-- Date / Scheduled -->
                               <td class="p-4 px-6 align-middle">
                                   @if($order->is_future_order && $order->scheduled_at)
                                       <div class="flex flex-col">
                                           <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wide">Scheduled</span>
                                           <span class="text-xs font-mono text-muted-foreground">{{ $order->scheduled_at->format('M d, H:i') }}</span>
                                       </div>
                                   @else
                                       <span class="text-xs font-mono text-muted-foreground">{{ $order->created_at->format('M d, Y') }}</span>
                                   @endif
                               </td>

                               <!-- Total -->
                               <td class="p-4 px-6 align-middle">
                                   <span class="font-semibold text-sm text-foreground">Rs {{ number_format($order->grand_total, 2) }}</span>
                               </td>

                               <!-- STATUS (Badges) -->
                               <td class="p-4 px-6 align-middle">
                                   @switch($order->status)

                                       @case('completed')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                               <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                               Completed
                                           </span>
                                           @break

                                       @case('shipped')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-green-500/10 text-green-600 border border-green-500/20">
                                               <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                               Shipped
                                           </span>
                                           @break

                                       @case('confirmed')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-blue-500/10 text-blue-600 border border-blue-500/20">
                                               <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                               Confirmed
                                           </span>
                                           @break

                                       @case('ready_to_ship')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-sky-500/10 text-sky-600 border border-sky-500/20">
                                               <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                               Ready to Ship
                                           </span>
                                           @break

                                       @case('processing')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-indigo-500/10 text-indigo-600 border border-indigo-500/20">
                                               <span class="animate-pulse h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                               Processing
                                           </span>
                                           @break

                                       @case('scheduled')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-violet-500/10 text-violet-600 border border-violet-500/20">
                                               <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
                                               Scheduled
                                           </span>
                                           @break

                                       @case('cancelled')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-destructive/10 text-destructive border border-destructive/20">
                                               <span class="h-1.5 w-1.5 rounded-full bg-destructive"></span>
                                               Cancelled
                                           </span>
                                           @break

                                       @default
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-amber-500/10 text-amber-600 border border-amber-500/20">
                                               <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                               Pending
                                           </span>
                                   @endswitch
                               </td>

                               <!-- Payment -->
                               <td class="p-4 px-6 align-middle">
                                   <span class="capitalize text-[11px] font-semibold text-muted-foreground/80 px-2 py-0.5 rounded border border-border/50 bg-background/50">
                                       {{ $order->payment_status }}
                                   </span>
                               </td>

                               <!-- Shipping Status -->
                               <td class="p-4 px-6 align-middle">
                                   <span class="text-[11px] font-medium text-muted-foreground/90">
                                       {{ ucwords(str_replace('_',' ', $order->shipping_status)) }}
                                   </span>
                               </td>

                               <!-- Actions -->
                               <td class="p-4 px-6 align-middle text-right">
                                   <div class="relative flex justify-end" x-data="{ open: false }" @click.away="open = false">

                                       <button @click="open = !open"
                                           class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-muted-foreground hover:bg-accent hover:text-foreground transition-colors">
                                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                       </button>

                                       <div x-show="open" x-transition.opacity.scale.95
                                            class="absolute right-0 top-8 z-50 min-w-[160px] rounded-xl border border-border/60 bg-popover/95 backdrop-blur-xl p-1 shadow-lg ring-1 ring-black/5">

                                           <a href="{{ route('central.orders.show', $order) }}"
                                              class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium hover:bg-accent rounded-lg transition-colors">
                                               <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                               View Details
                                           </a>

                                           @if(!in_array($order->status, ['completed','cancelled']))
                                               <a href="{{ route('central.orders.edit', $order) }}"
                                                  class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium hover:bg-accent rounded-lg transition-colors">
                                                   <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                                   Edit Order
                                               </a>
                                           @endif

                                           @if(in_array($order->status, ['completed','delivered']))
                                               <a href="{{ route('central.returns.create', ['order_id'=>$order->id]) }}"
                                                  class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium hover:bg-orange-500/10 text-orange-600 rounded-lg transition-colors">
                                                   <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                                                   Request Return
                                               </a>
                                           @endif

                                           <div class="h-px bg-border/50 my-1"></div>

                                           @if($order->invoices->isNotEmpty())
                                               <a href="{{ route('central.invoices.pdf', $order->invoices->first()) }}"
                                                  target="_blank"
                                                  class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium hover:bg-accent rounded-lg transition-colors">
                                                   <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                                   Print Invoice
                                               </a>
                                           @endif

                                           <a href="{{ route('central.orders.receipt', $order) }}"
                                              target="_blank"
                                              class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium hover:bg-accent rounded-lg transition-colors">
                                               <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17V7"/></svg>
                                               Print Receipt
                                           </a>
                                       </div>
                                   </div>
                               </td>

                           </tr>
                           @empty
<tr>
    <td colspan="10" class="p-16 text-center text-muted-foreground">
        No orders found
    </td>
</tr>
@endforelse
</tbody>

            </table>
         </div>
         @if($orders->hasPages())
         <div class="border-t border-border/40 p-4 bg-muted/20 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-muted-foreground px-2">Page <span class="font-medium text-foreground">{{ $orders->currentPage() }}</span> of <span class="font-medium">{{ $orders->lastPage() }}</span></div>
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
   
       container.addEventListener('input', (e) => {
           if (e.target.name === 'search') {
               clearTimeout(searchTimeout);
               searchTimeout = setTimeout(() => {
                   const form = e.target.closest('form');
                   const url = new URL(form.action);
                   const params = new URLSearchParams(new FormData(form));
                   loadContent(`${url.origin}${url.pathname}?${params.toString()}`);
               }, 400);
           }
       });
       
       container.addEventListener('change', (e) => {
            if (e.target.id === 'per_page' || 
                e.target.name === 'start_date' || 
                e.target.name === 'end_date' ||
                e.target.name === 'status' || 
                e.target.name === 'payment_status' ||
                e.target.name === 'shipping_status') {
                const form = e.target.closest('form');
                const url = new URL(form.action);
                const params = new URLSearchParams(new FormData(form));
               loadContent(`${url.origin}${url.pathname}?${params.toString()}`);
           }
       });
       
       container.addEventListener('submit', (e) => {
           if (e.target.id === 'search-form' || e.target.id === 'per-page-form') {
               e.preventDefault();
               const form = e.target;
               const url = new URL(form.action);
               const params = new URLSearchParams(new FormData(form));
               loadContent(`${url.origin}${url.pathname}?${params.toString()}`);
           }
       });
   });
</script>
@endsection