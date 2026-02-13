@extends('layouts.app')

@section('content')
<div class="flex flex-1 flex-col space-y-8 p-8 animate-in fade-in duration-500">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/60 bg-clip-text text-transparent">
                Warehouse Details: {{ $warehouse->name }}
            </h1>
            <p class="text-muted-foreground text-sm">Overview of location details and current inventory levels.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('central.warehouses.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm hover:bg-muted transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to List
            </a>
            <a href="{{ route('central.warehouses.edit', $warehouse) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2 text-sm font-bold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                Edit Details
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Info Sidebar -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-border/40 bg-card/60 backdrop-blur-xl p-6 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-6">General Information</h3>
                
                <div class="space-y-5">
                    <div>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Unique Code</p>
                        <p class="text-sm font-mono font-semibold bg-muted/30 px-2 py-1 rounded inline-block">{{ $warehouse->code }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Status</p>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold {{ $warehouse->is_active ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-destructive/10 text-destructive border border-destructive/20' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $warehouse->is_active ? 'bg-emerald-500' : 'bg-destructive' }}"></span>
                            {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Contact Email</p>
                        <p class="text-sm font-medium">{{ $warehouse->email ?? 'Not specified' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Phone Number</p>
                        <p class="text-sm font-medium">{{ $warehouse->phone ?? 'Not specified' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Address</p>
                        <p class="text-sm font-medium leading-relaxed">{{ $warehouse->address ?? 'No address provided' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Levels -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-border/40 bg-card/60 backdrop-blur-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-border/40 flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Current Inventory</h3>
                    <div class="h-8 w-8 flex items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-muted/30">
                                <th class="px-6 py-3 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Product</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-muted-foreground uppercase tracking-widest text-center">In Stock</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-muted-foreground uppercase tracking-widest text-center">Reserved</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-muted-foreground uppercase tracking-widest text-center">Available</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40 text-sm">
                            @forelse($stocks as $stock)
                                <tr class="hover:bg-muted/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-foreground">{{ $stock->product->name }}</div>
                                        <div class="text-xs text-muted-foreground font-mono">{{ $stock->product->sku }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-semibold">{{ $stock->quantity }}</td>
                                    <td class="px-6 py-4 text-center font-medium text-muted-foreground">{{ $stock->reserve_quantity }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center px-2 py-1 rounded bg-primary/5 text-primary font-bold">
                                            {{ $stock->quantity - $stock->reserve_quantity }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-muted-foreground italic">
                                        No inventory records found for this warehouse.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
