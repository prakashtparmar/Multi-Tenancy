@extends('layouts.app')

@section('content')
<div class="flex flex-1 flex-col space-y-8 p-6 md:p-8 max-w-7xl mx-auto w-full animate-in fade-in duration-500">

    <!-- Header & Action Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                Sales Orders
            </h1>
            <p class="text-muted-foreground text-sm">
                Manage, track, and fulfill your customer orders across all channels.
            </p>
        </div>
        
        <div class="flex items-center gap-3">
             <div class="flex items-center p-1 bg-muted/50 rounded-xl border border-border/50 backdrop-blur-sm">
                <a href="{{ route('tenant.orders.index') }}" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ !request('status') ? 'bg-background text-foreground shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-foreground' }}">
                    All
                </a>
                <a href="{{ route('tenant.orders.index', ['status' => 'pending']) }}" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'pending' ? 'bg-background text-amber-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-amber-600' }}">
                    Pending
                </a>
                <a href="{{ route('tenant.orders.index', ['status' => 'completed']) }}" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'completed' ? 'bg-background text-emerald-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-emerald-600' }}">
                    Completed
                </a>
                <a href="{{ route('tenant.orders.index', ['status' => 'scheduled']) }}" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'scheduled' ? 'bg-background text-indigo-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-indigo-600' }}">
                    Scheduled
                </a>
            </div>

            <a href="{{ route('tenant.orders.create', ['reset' => 1]) }}" 
               onclick="localStorage.removeItem('order_wizard_state')"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Create Order
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-border/40 bg-card/50 p-6 shadow-sm flex items-center gap-4">
             <div class="h-12 w-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
             </div>
             <div>
                 <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Orders</p>
                 <p class="text-2xl font-bold">{{ $orders->total() }}</p>
             </div>
        </div>
        <!-- More stats could go here -->
    </div>

    <!-- Table Section -->
    <div class="rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl shadow-lg shadow-black/5 overflow-hidden">
        
        <!-- Table Header Toolbar -->
        <div class="p-4 border-b border-border/40 bg-muted/20 flex flex-col sm:flex-row items-center justify-between gap-4">
            <form action="{{ url()->current() }}" method="GET" class="relative w-full sm:w-80">
                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders..." class="flex h-10 w-full rounded-xl border border-input bg-background/50 pl-9 pr-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary/50 transition-all">
            </form>

            <div class="flex items-center gap-2" x-data="{ open: false }">
                 <button @click="open = !open" class="inline-flex items-center gap-2 rounded-lg border border-input bg-background px-3 py-2 text-xs font-medium hover:bg-accent transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    Export
                 </button>
                 <div x-show="open" @click.away="open = false" class="absolute right-0 mt-32 w-48 rounded-xl border border-border bg-popover p-1 shadow-xl z-50">
                    <form action="{{ route('tenant.orders.export') }}" method="POST">
                        @csrf
                        <button name="format" value="csv" class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-accent transition-colors">Export CSV</button>
                        <button name="format" value="xlsx" class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-accent transition-colors">Export Excel (.xlsx)</button>
                        <button name="format" value="pdf" class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-accent transition-colors">Export PDF</button>
                    </form>
                 </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-muted/50 text-muted-foreground font-medium border-b border-border/40">
                    <tr>
                        <th class="px-6 py-4">Order #</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Placed At</th>
                        <th class="px-6 py-4 text-right">Total</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    @forelse($orders as $order)
                    <tr class="group hover:bg-muted/30 transition-colors">
                        <td class="px-6 py-4 font-mono font-medium text-foreground">
                            <a href="{{ route('tenant.orders.show', $order) }}" class="font-semibold text-primary hover:underline">{{ $order->order_number }}</a>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-[10px]">
                                    {{ substr($order->customer->first_name ?? 'G', 0, 1) }}{{ substr($order->customer->last_name ?? '', 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <a href="{{ $order->customer_id ? route('tenant.customers.show', $order->customer_id) : '#' }}" class="font-medium text-foreground hover:text-primary hover:underline transition-colors">{{ $order->customer->first_name ?? 'Guest' }} {{ $order->customer->last_name ?? '' }}</a>
                                    <span class="text-[10px] text-muted-foreground">{{ $order->warehouse->name ?? 'Default' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                                    'completed' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                                    'cancelled' => 'bg-destructive/10 text-destructive border-destructive/20',
                                    'scheduled' => 'bg-indigo-500/10 text-indigo-600 border-indigo-500/20',
                                ]
                            @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $statusClasses[$order->status] ?? 'bg-muted text-muted-foreground' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-muted-foreground">
                            @if($order->is_future_order && $order->scheduled_at)
                                <div class="flex flex-col">
                                    <span class="text-indigo-600 font-bold text-[10px] uppercase">Scheduled</span>
                                    <span class="text-xs">{{ $order->scheduled_at->format('M d, Y H:i') }}</span>
                                </div>
                            @else
                                {{ $order->created_at->format('M d, Y') }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-foreground">
                            Rs {{ number_format($order->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                             <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('tenant.orders.show', $order) }}" class="p-2 rounded-lg hover:bg-primary/10 text-muted-foreground hover:text-primary transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                    <button @click="open = !open" class="p-2 rounded-lg hover:bg-accent text-muted-foreground transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                    </button>
                                    <div x-show="open" class="absolute right-0 top-10 z-50 min-w-[160px] bg-white border border-border rounded-lg shadow-xl p-1" style="display: none;">
                                        <a href="{{ route('tenant.orders.invoice', $order) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
                                            Print Invoice
                                        </a>
                                        <a href="{{ route('tenant.orders.receipt', $order) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
                                            Print Receipt
                                        </a>
                                        <a href="{{ route('tenant.orders.edit', $order) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md border-t pt-2 mt-1">
                                            Edit Order
                                        </a>
                                    </div>
                                </div>
                             </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-muted-foreground/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-4"><rect width="16" height="20" x="4" y="2" rx="2"/><path d="M9 22v-4h6v4"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/></svg>
                                <p class="text-lg font-semibold text-foreground">No orders found</p>
                                <p class="text-sm mt-1">Start by creating your first sales order.</p>
                                <a href="{{ route('tenant.orders.create', ['reset' => 1]) }}" 
                                   onclick="localStorage.removeItem('order_wizard_state')"
                                   class="mt-4 text-primary font-medium hover:underline">Create Order</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->count() > 0)
        <div class="p-4 border-t border-border/40 bg-muted/20">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
