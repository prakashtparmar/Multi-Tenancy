@extends('layouts.app')
@section('content')
<div id="orders-page-wrapper" class="flex flex-1 flex-col space-y-8 p-8 animate-in fade-in duration-500">
   <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div class="space-y-1">
         <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">Orders</h1>
         <p class="text-muted-foreground text-sm">Manage customer orders and fulfillment status.</p>
      </div>
      <div class="flex items-center p-1 bg-muted/50 rounded-xl border border-border/50 backdrop-blur-sm">
         <a href="{{ route('central.orders.index') }}" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === null ? 'bg-background text-foreground shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-foreground hover:bg-background/50' }}">
         All Orders
         </a>
         <div class="w-px h-4 bg-border/40 mx-1"></div>
         <a href="{{ route('central.orders.index', ['status' => 'pending']) }}" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'pending' ? 'bg-background text-amber-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-amber-600 hover:bg-background/50' }}">
         Pending
         </a>
         <div class="w-px h-4 bg-border/40 mx-1"></div>
         <a href="{{ route('central.orders.index', ['status' => 'processing']) }}" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'processing' ? 'bg-background text-blue-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-blue-600 hover:bg-background/50' }}">
         Processing
         </a>
         <div class="w-px h-4 bg-border/40 mx-1"></div>
         <a href="{{ route('central.orders.index', ['status' => 'completed']) }}" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'completed' ? 'bg-background text-emerald-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-emerald-600 hover:bg-background/50' }}">
         Completed
         </a>
         <div class="w-px h-4 bg-border/40 mx-1"></div>
         <a href="{{ route('central.orders.index', ['status' => 'scheduled']) }}" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'scheduled' ? 'bg-background text-indigo-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-indigo-600 hover:bg-background/50' }}">
         Scheduled
         </a>
      </div>
   </div>
   <div id="orders-table-container" x-data="{ selected: [] }">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-1.5 rounded-2xl">
         <div class="flex items-center gap-3 min-h-[44px]">
            <div x-cloak x-show="selected.length > 0" x-transition.opacity.duration.300ms class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4">
               <div class="px-3 py-1.5 rounded-lg bg-primary/10 border border-primary/20 text-primary text-xs font-semibold shadow-sm">
                  <span x-text="selected.length"></span> selected
               </div>
            </div>
            <form id="search-form" method="GET" action="{{ url()->current() }}" class="flex items-center gap-2 group">

               <!-- Extended Filters -->
               <select name="status" class="h-9 rounded-lg border-border bg-background text-xs cursor-pointer">
                   <option value="">Status: All</option>
                   <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                   <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                   <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                   <option value="ready_to_ship" {{ request('status') === 'ready_to_ship' ? 'selected' : '' }}>Ready to Ship</option>
                   <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                   <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                   <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                   <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                   <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                   <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
               </select>

               <select name="payment_status" class="h-9 rounded-lg border-border bg-background text-xs cursor-pointer">
                   <option value="">Payment: All</option>
                   <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                   <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                   <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
               </select>

               <select name="shipping_status" class="h-9 rounded-lg border-border bg-background text-xs cursor-pointer">
                   <option value="">Shipping: All</option>
                   <option value="pending" {{ request('shipping_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                   <option value="shipped" {{ request('shipping_status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                   <option value="in_transit" {{ request('shipping_status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                   <option value="delivered" {{ request('shipping_status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
               </select>

               <div class="relative transition-all duration-300 group-focus-within:w-72" :class="selected.length > 0 ? 'w-48' : 'w-64'">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                     <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-focus-within:text-primary transition-colors">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                     </svg>
                  </div>
                  <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders..." 
                     class="block w-full rounded-xl border-0 py-2.5 pl-10 pr-3 text-foreground bg-muted/40 ring-1 ring-inset ring-transparent placeholder:text-muted-foreground focus:bg-background focus:ring-2 focus:ring-primary/20 sm:text-sm sm:leading-6 transition-all shadow-sm">
               </div>
            </form>
         </div>
         <div class="flex items-center gap-2">
            <!-- Bulk Actions -->
            <div x-cloak x-show="selected.length > 0" x-transition class="flex items-center gap-2 mr-2">
                <form id="bulk-print-form" action="{{ route('central.orders.bulk-print') }}" method="POST" target="_blank" class="flex gap-2">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    
                    <button type="submit" name="type" value="invoice" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                        Print Invoices
                    </button>
                    
                    <button type="submit" name="type" value="cod" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        Print COD
                    </button>
                </form>
                <div class="w-px h-4 bg-border/40"></div>
            </div>
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
      <div class="rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl shadow-sm overflow-hidden relative">
         <div id="table-loading" class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent shadow-lg"></div>
         </div>
         <div class="border-b border-border/40 p-4 bg-muted/20 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-xs text-muted-foreground">
               <span class="flex h-6 w-6 items-center justify-center rounded-md bg-background border border-border font-medium text-foreground shadow-sm">
               {{ $orders->total() }}
               </span>
               <span>orders found</span>
            </div>
            <div class="flex items-center gap-3">
               <form id="per-page-form" method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                  @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                  @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                  <label for="per_page" class="text-xs font-medium text-muted-foreground whitespace-nowrap">View</label>
                  <div class="relative">
                     <select name="per_page" id="per_page" class="appearance-none h-8 pl-3 pr-8 rounded-lg border border-border bg-background text-xs font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors cursor-pointer hover:bg-accent/50">
                     <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                     <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                     <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                     </select>
                     <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground">
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
                  <tr class="border-b border-border/40 transition-colors hover:bg-muted/30 data-[state=selected]:bg-muted bg-muted/20">
                     <th class="h-12 w-[50px] px-6 text-left align-middle">
                        <div class="flex items-center">
                           <input type="checkbox" class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary" @click="selected = $event.target.checked ? [{{ $orders->pluck('id')->join(',') }}] : []">
                        </div>
                     </th>
                     <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Order</th>
                     <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Customer</th>
                     <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Created By</th>
                     <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Date</th>
                     <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Grand Total</th>
                     <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Status</th>
                     <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Payment</th>
                     <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Shipping</th>
                     <th class="h-12 px-6 text-right align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Actions</th>
                  </tr>
               </thead>
               <tbody class="[&_tr:last-child]:border-0 text-sm">
@forelse($orders as $order)
<tr class="group border-b border-border/40 transition-all duration-200 hover:bg-muted/40 data-[state=selected]:bg-muted/60">

    <!-- Checkbox -->
    <td class="p-6 align-middle">
        <input type="checkbox"
               value="{{ $order->id }}"
               x-model="selected"
               class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
    </td>

    <!-- Order Number + Tracking -->
    <td class="p-6 align-middle">
        <div class="flex flex-col space-y-0.5">
            <a href="{{ route('central.orders.show', $order) }}"
               class="font-semibold text-primary hover:underline text-sm tracking-tight">
                {{ $order->order_number }}
            </a>

            @if(
                in_array($order->shipping_status, ['shipped','in_transit','delivered']) &&
                $order->shipments->isNotEmpty()
            )
                <span class="text-[10px] font-mono text-muted-foreground/80 tracking-tighter">
                    {{ $order->shipments->first()->tracking_number }}
                </span>
            @endif
        </div>
    </td>

    <!-- Customer -->
    <td class="p-6 align-middle">
        <a href="{{ $order->customer_id ? route('central.customers.show', $order->customer_id) : '#' }}"
           class="text-sm font-medium hover:text-primary hover:underline">
            {{ $order->customer->name ?? 'Guest' }}
        </a>
    </td>

    <!-- Created By -->
    <td class="p-6 align-middle">
        <span class="text-xs text-muted-foreground font-medium">
            {{ $order->creator->name ?? 'System' }}
        </span>
    </td>

    <!-- Date / Scheduled -->
    <td class="p-6 align-middle text-muted-foreground font-mono text-xs">
        @if($order->is_future_order && $order->scheduled_at)
            <div class="flex flex-col">
                <span class="text-indigo-600 font-bold">Scheduled</span>
                <span>{{ $order->scheduled_at->format('M d, Y H:i') }}</span>
            </div>
        @else
            {{ $order->created_at->format('M d, Y') }}
        @endif
    </td>

    <!-- Total -->
    <td class="px-6 py-4 text-sm text-foreground">
        Rs {{ number_format($order->grand_total, 2) }}
    </td>

    <!-- STATUS (FIXED & COMPLETE) -->
    <td class="p-6 align-middle">
        @switch($order->status)

            @case('completed')
                <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full
                    bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                    Completed
                </span>
                @break

            @case('shipped')
                <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full
                    bg-green-500/10 text-green-600 border border-green-500/20">
                    Shipped
                </span>
                @break

            @case('confirmed')
                <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full
                    bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                    Confirmed
                </span>
                @break

            @case('ready_to_ship')
                <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full
                    bg-sky-500/10 text-sky-600 border border-sky-500/20">
                    Ready to Ship
                </span>
                @break

            @case('processing')
                <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full
                    bg-blue-500/10 text-blue-600 border border-blue-500/20">
                    Processing
                </span>
                @break

            @case('scheduled')
                <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full
                    bg-indigo-500/10 text-indigo-600 border border-indigo-500/20">
                    Scheduled
                </span>
                @break

            @case('cancelled')
                <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full
                    bg-destructive/10 text-destructive border border-destructive/20">
                    Cancelled
                </span>
                @break

            @default
                <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full
                    bg-amber-500/10 text-amber-600 border border-amber-500/20">
                    Pending
                </span>
        @endswitch
    </td>

    <!-- Payment -->
    <td class="p-6 align-middle">
        <span class="capitalize text-xs font-medium text-muted-foreground">
            {{ $order->payment_status }}
        </span>
    </td>

    <!-- Shipping Status (Human Readable) -->
    <td class="p-6 align-middle">
        <span class="text-xs font-medium text-muted-foreground">
            {{ ucwords(str_replace('_',' ', $order->shipping_status)) }}
        </span>
    </td>

    <!-- Actions -->
    <td class="p-6 align-middle text-right">
        <div class="relative flex justify-end" x-data="{ open: false }" @click.away="open = false">

            <button @click="open = !open"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-accent">
                ⋮
            </button>

            <div x-show="open" x-transition
                 class="absolute right-0 top-9 z-50 min-w-[180px] rounded-xl border border-border bg-popover p-1 shadow-xl">

                <a href="{{ route('central.orders.show', $order) }}"
                   class="block px-2 py-2 text-sm hover:bg-accent rounded-lg">
                    View Details
                </a>

                @if(!in_array($order->status, ['completed','cancelled']))
                    <a href="{{ route('central.orders.edit', $order) }}"
                       class="block px-2 py-2 text-sm hover:bg-accent rounded-lg">
                        Edit Order
                    </a>
                @endif

                @if(in_array($order->status, ['completed','delivered']))
                    <a href="{{ route('central.returns.create', ['order_id'=>$order->id]) }}"
                       class="block px-2 py-2 text-sm hover:bg-orange-500/10 text-orange-600 rounded-lg">
                        Request Return
                    </a>
                @endif

                <div class="h-px bg-border my-1"></div>

                @if($order->invoices->isNotEmpty())
                    <a href="{{ route('central.invoices.pdf', $order->invoices->first()) }}"
                       target="_blank"
                       class="block px-2 py-2 text-sm hover:bg-accent rounded-lg">
                        Print Invoice
                    </a>
                @endif

                <a href="{{ route('central.orders.receipt', $order) }}"
                   target="_blank"
                   class="block px-2 py-2 text-sm hover:bg-accent rounded-lg">
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