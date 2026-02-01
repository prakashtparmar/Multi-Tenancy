@extends('layouts.app')

@section('content')
<div id="inventory-page-wrapper" class="flex flex-1 flex-col space-y-8 p-8 animate-in fade-in duration-500">
    
    <!-- Page Header & Stats -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">Central Inventory</h1>
            <p class="text-muted-foreground text-sm">Global monitoring and stock adjustments for master catalog.</p>
        </div>
        
        <!-- Tab Navigation (Pills) -->
        <div class="flex items-center p-1 bg-muted/50 rounded-xl border border-border/50 backdrop-blur-sm">
            <a href="/inventory" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === null ? 'bg-background text-foreground shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-foreground hover:bg-background/50' }}">
                All Stock
            </a>
            <div class="w-px h-4 bg-border/40 mx-1"></div>
            <a href="/inventory?status=low_stock" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'low_stock' ? 'bg-background text-amber-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-amber-600 hover:bg-background/50' }}">
                Low Stock
            </a>
            <div class="w-px h-4 bg-border/40 mx-1"></div>
            <a href="/inventory?status=out_of_stock" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'out_of_stock' ? 'bg-background text-destructive shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-destructive hover:bg-background/50' }}">
                Out of Stock
            </a>
        </div>
    </div>

    <!-- AJAX Container Wrapper -->
    <div id="inventory-table-container" x-data="{ selected: [] }">
        <!-- Toolbar Section -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-1.5 rounded-2xl">
            
            <!-- Left: Bulk Actions (visible on selection) -->
            <div class="flex items-center gap-3 min-h-[44px]">
                 <div x-cloak x-show="selected.length > 0" x-transition.opacity.duration.300ms class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4">
                    <div class="px-3 py-1.5 rounded-lg bg-primary/10 border border-primary/20 text-primary text-xs font-semibold shadow-sm">
                        <span x-text="selected.length"></span> selected
                    </div>
                 </div>
    
                <!-- Search Bar (AJAX Enabled) -->
                <form id="search-form" method="GET" action="{{ url()->current() }}" class="flex items-center gap-2 group">
                    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
    
                    <div class="relative transition-all duration-300 group-focus-within:w-72" :class="selected.length > 0 ? 'w-48' : 'w-64'">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-focus-within:text-primary transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by SKU or Product..." 
                               class="block w-full rounded-xl border-0 py-2.5 pl-10 pr-3 text-foreground bg-muted/40 ring-1 ring-inset ring-transparent placeholder:text-muted-foreground focus:bg-background focus:ring-2 focus:ring-primary/20 sm:text-sm sm:leading-6 transition-all shadow-sm">
                    </div>
                </form>
            </div>
    
            <!-- Right: Add Action -->
            <button @click="$dispatch('open-adjustment-modal')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/><path d="m16 14-4-4-4 4"/></svg>
                <span>Stock Adjustment</span>
            </button>
        </div>
    
        <!-- Data Table Card -->
        <div class="rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl shadow-sm overflow-hidden relative">
            
            <!-- Loading Overlay -->
            <div id="table-loading" class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent shadow-lg"></div>
            </div>

            <!-- Table Toolbar Header -->
            <div class="border-b border-border/40 p-4 bg-muted/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-background border border-border font-medium text-foreground shadow-sm">
                        {{ $products->total() }}
                    </span>
                    <span>products found</span>
                     @if(request('search'))
                        <span class="flex items-center gap-1 ml-2 px-2 py-0.5 rounded-full bg-primary/10 text-primary">
                            search: "{{ request('search') }}"
                            <a href="{{ url()->current() }}" class="hover:text-primary/80"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></a>
                        </span>
                    @endif
                </div>
                
                <div class="flex items-center gap-3">
                     <form id="per-page-form" method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                        @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
    
                        <label for="per_page" class="text-xs font-medium text-muted-foreground whitespace-nowrap">View</label>
                        <div class="relative">
                            <select name="per_page" id="per_page" class="appearance-none h-8 pl-3 pr-8 rounded-lg border border-border bg-background text-xs font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors cursor-pointer hover:bg-accent/50">
                                <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
    
            <div class="relative w-full overflow-auto">
                {{-- <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b border-border/40 transition-colors hover:bg-muted/30 data-[state=selected]:bg-muted bg-muted/20">
                            <!-- Checkbox -->
                            <th class="h-12 w-[50px] px-6 text-left align-middle">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                    class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary"
                                    @click="selected = $event.target.checked ? [{{ $products->pluck('id')->join(',') }}] : []">
                                </div>
                            </th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Product</th>
                            <th class="h-12 px-6 text-center align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">SKU</th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Category</th>
                             @foreach($warehouses as $wh)
                                <th class="h-12 px-6 text-center align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">{{ $wh->code }}</th>
                            @endforeach
                            <th class="h-12 px-6 text-right align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Total Stock</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0 text-sm">
                        @forelse($products as $product)
                        <tr class="group border-b border-border/40 transition-all duration-200 hover:bg-muted/40 data-[state=selected]:bg-muted/60">
                             <td class="p-6 align-middle">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                    value="{{ $product->id }}" 
                                    x-model="selected"
                                    class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
                                </div>
                            </td>
                            <td class="p-6 align-middle">
                                <div class="flex items-center gap-4">
                                    <div class="size-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold shadow-sm transition-transform group-hover:scale-105">
                                        {{ substr($product->name, 0, 1) }}
                                    </div>
                                    <div class="flex flex-col space-y-0.5">
                                        <span class="font-semibold text-foreground text-sm tracking-tight">{{ $product->name }}</span>
                                        <div class="text-[10px] text-muted-foreground uppercase">{{ $product->unit_type }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6 align-middle text-center font-mono text-xs text-muted-foreground">
                                {{ $product->sku ?? '-' }}
                            </td>
                            <td class="p-6 align-middle">
                                <span class="inline-flex items-center rounded-md border border-border bg-secondary/50 px-2.5 py-0.5 text-xs font-medium text-secondary-foreground">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            @foreach($warehouses as $wh)
                                @php 
                                    $stock = $product->stocks->firstWhere('warehouse_id', $wh->id);
                                    $qty = $stock ? $stock->quantity : 0;
                                @endphp
                                <td class="p-6 align-middle text-center">
                                    <span class="font-medium {{ $qty <= 10 ? 'text-amber-600' : 'text-foreground' }}">
                                        {{ number_format($qty, 0) }}
                                    </span>
                                </td>
                            @endforeach
                            <td class="p-6 align-middle text-right">
                                <div class="flex flex-col items-end">
                                    <span class="text-lg font-bold {{ $product->stock_on_hand <= 10 ? 'text-destructive' : 'text-primary' }}">
                                        {{ number_format($product->stock_on_hand, 0) }}
                                    </span>
                                    @if($product->stock_on_hand <= 10)
                                        <span class="inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-[10px] font-bold bg-destructive/10 text-destructive border border-destructive/20 mt-1">
                                            Low Stock
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ 5 + count($warehouses) }}" class="p-16 text-center">
                                <div class="flex flex-col items-center justify-center text-muted-foreground/50">
                                    <div class="h-16 w-16 rounded-full bg-muted/30 flex items-center justify-center mb-4 ring-1 ring-border/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                    </div>
                                    <p class="text-lg font-semibold text-foreground">No products found</p>
                                    <p class="text-sm mt-1 max-w-xs mx-auto">Try adjusting your filters or add new products to your catalog.</p>
                                    @if(request('search'))
                                        <a href="{{ url()->current() }}" class="mt-4 text-primary text-sm font-medium hover:underline">Clear Filters</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table> --}}
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b border-border/40 transition-colors hover:bg-muted/30 data-[state=selected]:bg-muted bg-muted/20">
                            <!-- Checkbox -->
                            <th class="h-12 w-[50px] px-6 text-left align-middle">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                    class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary"
                                    @click="selected = $event.target.checked ? [{{ $products->pluck('id')->join(',') }}] : []">
                                </div>
                            </th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Product</th>
                            <th class="h-12 px-6 text-center align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">SKU</th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Category</th>
                             @foreach($warehouses as $wh)
                                <th class="h-12 px-6 text-center align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">{{ $wh->code }}</th>
                            @endforeach
                            <th class="h-12 px-6 text-right align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Total Stock</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0 text-sm">
                        @forelse($products as $product)
                        <tr class="group border-b border-border/40 transition-all duration-200 hover:bg-muted/40 data-[state=selected]:bg-muted/60">
                             <td class="p-6 align-middle">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                    value="{{ $product->id }}" 
                                    x-model="selected"
                                    class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
                                </div>
                            </td>
                            <td class="p-6 align-middle">
                                <div class="flex items-center gap-4">
                                    <div class="size-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold shadow-sm transition-transform group-hover:scale-105">
                                        {{ substr($product->name, 0, 1) }}
                                    </div>
                                    <div class="flex flex-col space-y-0.5">
                                        <span class="font-semibold text-foreground text-sm tracking-tight">{{ $product->name }}</span>
                                        <div class="text-[10px] text-muted-foreground uppercase">{{ $product->unit_type }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6 align-middle text-center font-mono text-xs text-muted-foreground">
                                {{ $product->sku ?? '-' }}
                            </td>
                            <td class="p-6 align-middle">
                                <span class="inline-flex items-center rounded-md border border-border bg-secondary/50 px-2.5 py-0.5 text-xs font-medium text-secondary-foreground">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            @foreach($warehouses as $wh)
                                @php 
                                    $stock = $product->stocks->firstWhere('warehouse_id', $wh->id);
                                    $qty = $stock ? $stock->quantity : 0;
                                @endphp
                                <td class="p-6 align-middle text-center">
                                    <span class="font-medium {{ $qty <= 10 ? 'text-amber-600' : 'text-foreground' }}">
                                        {{ number_format($qty, 0) }}
                                    </span>
                                </td>
                            @endforeach
                            <td class="p-6 align-middle text-right">
                                <div class="flex flex-col items-end">
                                    <span class="text-lg font-bold {{ $product->stock_on_hand <= 10 ? 'text-destructive' : 'text-primary' }}">
                                        {{ number_format($product->stock_on_hand, 0) }}
                                    </span>
                                    @if($product->stock_on_hand <= 10)
                                        <span class="inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-[10px] font-bold bg-destructive/10 text-destructive border border-destructive/20 mt-1">
                                            Low Stock
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ 5 + count($warehouses) }}" class="p-16 text-center">
                                <div class="flex flex-col items-center justify-center text-muted-foreground/50">
                                    <div class="h-16 w-16 rounded-full bg-muted/30 flex items-center justify-center mb-4 ring-1 ring-border/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                    </div>
                                    <p class="text-lg font-semibold text-foreground">No products found</p>
                                    <p class="text-sm mt-1 max-w-xs mx-auto">Try adjusting your filters or add new products to your catalog.</p>
                                    @if(request('search'))
                                        <a href="{{ url()->current() }}" class="mt-4 text-primary text-sm font-medium hover:underline">Clear Filters</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($products->hasPages() || $products->total() > 5)
            <div class="border-t border-border/40 p-4 bg-muted/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                 <div class="text-xs text-muted-foreground px-2">Page <span class="font-medium text-foreground">{{ $products->currentPage() }}</span> of <span class="font-medium">{{ $products->lastPage() }}</span></div>
                 <div>{{ $products->onEachSide(1)->links() }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Adjustment Modal -->
<div x-data="{ open: false, product_id: '', warehouse_id: '', type: 'add', quantity: 0, reason: '' }"
     @open-adjustment-modal.window="open = true"
     x-show="open"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
     x-cloak>
    <div @click.away="open = false" 
         class="w-full max-w-lg rounded-2xl border border-border/60 bg-background p-8 shadow-2xl animate-in zoom-in-95 duration-200">
        <h2 class="text-2xl font-bold mb-2">Stock Adjustment</h2>
        <p class="text-muted-foreground mb-6 text-sm">Manually add, remove, or set stock levels for products.</p>

        <form action="{{ route('central.inventory.adjust') }}" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-muted-foreground uppercase tracking-widest">Product</label>
                <select name="product_id" x-model="product_id" required 
                    class="w-full rounded-xl border-border/60 bg-background px-4 py-2.5 shadow-sm focus:ring-primary/20 focus:border-primary">
                    <option value="">Select Product...</option>
                    @foreach(App\Models\Product::all() as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->sku }})</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-muted-foreground uppercase tracking-widest">Warehouse</label>
                <select name="warehouse_id" x-model="warehouse_id" required 
                    class="w-full rounded-xl border-border/60 bg-background px-4 py-2.5 shadow-sm focus:ring-primary/20 focus:border-primary">
                    <option value="">Select Warehouse...</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-muted-foreground uppercase tracking-widest">Action</label>
                    <select name="type" x-model="type" required 
                        class="w-full rounded-xl border-border/60 bg-background px-4 py-2.5 shadow-sm focus:ring-primary/20 focus:border-primary">
                        <option value="add">Add Stock (+)</option>
                        <option value="subtract">Subtract Stock (-)</option>
                        <option value="set">Set Exact Quantity (=)</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-muted-foreground uppercase tracking-widest">Quantity</label>
                    <input type="number" name="quantity" x-model="quantity" step="1" min="0" required 
                        class="w-full rounded-xl border-border/60 bg-background px-4 py-2.5 shadow-sm focus:ring-primary/20 focus:border-primary">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-muted-foreground uppercase tracking-widest">Reason / Note</label>
                <input type="text" name="reason" x-model="reason" placeholder="e.g. Initial stock, Restock, Damage..." required 
                    class="w-full rounded-xl border-border/60 bg-background px-4 py-2.5 shadow-sm focus:ring-primary/20 focus:border-primary">
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" @click="open = false" 
                    class="flex-1 rounded-xl border border-border bg-background px-4 py-3 font-semibold hover:bg-accent transition-all text-sm">
                    Cancel
                </button>
                <button type="submit" 
                    class="flex-1 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all active:scale-95 text-sm">
                    Apply Adjustment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('inventory-table-container');
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
                const newContent = doc.getElementById('inventory-table-container');
                
                if (newContent) {
                    container.innerHTML = newContent.innerHTML;
                    if (pushState) window.history.pushState({}, '', url);
                    if (typeof Alpine !== 'undefined') Alpine.initTree(container);
                } else {
                    window.location.href = url;
                }
            } catch (err) {
                console.error('AJAX Error:', err);
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
            if (e.target.id === 'per_page') {
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
