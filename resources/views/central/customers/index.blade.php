@extends('layouts.app')

@section('content')
<div id="customers-page-wrapper" class="flex flex-1 flex-col space-y-8 p-8 animate-in fade-in duration-500">
    
    <!-- Page Header & Stats -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">Customer Management</h1>
            <p class="text-muted-foreground text-sm">Manage your client base, farmer profiles, and vendor relationships.</p>
        </div>
        
        <!-- Tab Navigation (Pills) -->
        <div class="flex items-center p-1 bg-muted/50 rounded-xl border border-border/50 backdrop-blur-sm">
            <a href="/customers" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === null && request('trashed') === null ? 'bg-background text-foreground shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-foreground hover:bg-background/50' }}">
                All
            </a>
            <div class="w-px h-4 bg-border/40 mx-1"></div>
            <a href="/customers?status=active" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'active' ? 'bg-background text-emerald-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-emerald-600 hover:bg-background/50' }}">
                Active
            </a>
            <div class="w-px h-4 bg-border/40 mx-1"></div>
            <a href="/customers?status=inactive" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'inactive' ? 'bg-background text-amber-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-amber-600 hover:bg-background/50' }}">
                Inactive
            </a>
            <div class="w-px h-4 bg-border/40 mx-1"></div>
            <a href="/customers?trashed=only" class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('trashed') === 'only' ? 'bg-background text-destructive shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-destructive hover:bg-background/50' }}">
                Trash
                <span class="flex items-center justify-center h-5 px-1.5 rounded-md bg-muted text-[10px] font-bold text-muted-foreground">
                    {{ \App\Models\Customer::onlyTrashed()->count() }}
                </span>
            </a>
        </div>
    </div>

    <!-- AJAX Container Wrapper -->
    <div id="customers-table-container" x-data="{ selected: [] }">
        <!-- Toolbar Section -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-1.5 rounded-2xl">
            
            <!-- Left: Bulk Actions -->
            <div class="flex items-center gap-3 min-h-[44px]">
                 <div x-cloak x-show="selected.length > 0" x-transition.opacity.duration.300ms class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4">
                    <div class="px-3 py-1.5 rounded-lg bg-primary/10 border border-primary/20 text-primary text-xs font-semibold shadow-sm">
                        <span x-text="selected.length"></span> selected
                    </div>
                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="group flex items-center gap-2 px-4 py-2 rounded-xl bg-background border border-input shadow-sm hover:shadow-md hover:border-primary/30 transition-all duration-200 text-sm font-medium">
                            <span>Bulk Options</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-hover:text-primary transition-colors"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        
                        <div x-show="open" style="display: none;" class="absolute left-0 top-full z-50 mt-2 min-w-[220px] p-1.5 rounded-xl border border-border/60 bg-popover/95 backdrop-blur-xl shadow-xl shadow-black/5 animate-in zoom-in-95 fade-in-0 slide-in-from-top-2">
                             <form action="/customers/bulk" method="POST">
                                @csrf
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id">
                                </template>
                                
                                @if(request('trashed') === 'only')
                                    <button type="submit" name="action" value="restore" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-emerald-500/10 hover:text-emerald-600 text-muted-foreground">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
                                        Restore Selected
                                    </button>
                                    <button type="submit" name="action" value="force_delete" onclick="return confirm('This action is permanent! Are you sure?')" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-destructive/10 hover:text-destructive text-muted-foreground">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                        Permanently Delete
                                    </button>
                                @else
                                    <div class="px-2 py-1.5 text-xs font-semibold text-muted-foreground/60 uppercase tracking-wider">Status</div>
                                    <button type="submit" name="action" value="active" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground text-foreground/80">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        Mark Active
                                    </button>
                                    <button type="submit" name="action" value="inactive" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground text-foreground/80">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-500"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                                        Mark Inactive
                                    </button>
                                    <div class="my-1.5 h-px bg-border/50"></div>
                                    <div class="px-2 py-1 text-xs font-semibold text-muted-foreground/60 uppercase tracking-wider">Danger</div>
                                    <button type="submit" name="action" value="delete" onclick="return confirm('Move to trash?')" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-destructive/10 hover:text-destructive text-destructive/80">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                        Move to Trash
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                 </div>
    
                <!-- Search Bar (AJAX Enabled) -->
                <form id="search-form" method="GET" action="{{ url()->current() }}" class="flex items-center gap-2 group">
                    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                    @if(request('trashed')) <input type="hidden" name="trashed" value="{{ request('trashed') }}"> @endif
                    @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif
    
                    <div class="relative transition-all duration-300 group-focus-within:w-72" :class="selected.length > 0 ? 'w-48' : 'w-64'">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-focus-within:text-primary transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customers..." 
                               class="block w-full rounded-xl border-0 py-2.5 pl-10 pr-3 text-foreground bg-muted/40 ring-1 ring-inset ring-transparent placeholder:text-muted-foreground focus:bg-background focus:ring-2 focus:ring-primary/20 sm:text-sm sm:leading-6 transition-all shadow-sm">
                    </div>
                </form>
            </div>
    
            <!-- Right: Add Action -->
            <div class="flex flex-col items-end gap-1">
                <!-- Debug Info (Temporary) -->
                <div class="text-[10px] text-muted-foreground font-mono bg-muted px-2 py-0.5 rounded">
                     Host: {{ request()->getHost() }} | Tenant: {{ tenant('id') ?? 'null' }}
                </div>
                <!-- Robust Link Generation using relative path to force browser to keep current domain -->
                <a href="/customers/create" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    <span>Add Customer</span>
                </a>
            </div>
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
                        {{ $customers->total() }}
                    </span>
                    <span>total customers found</span>
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
                        @if(request('trashed')) <input type="hidden" name="trashed" value="{{ request('trashed') }}"> @endif
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
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b border-border/40 transition-colors hover:bg-muted/30 data-[state=selected]:bg-muted bg-muted/20">
                            <!-- Checkbox -->
                            <th class="h-12 w-[50px] px-6 text-left align-middle">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                    class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary"
                                    @click="selected = $event.target.checked ? [{{ $customers->pluck('id')->join(',') }}] : []">
                                </div>
                            </th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Customer</th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Details</th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Status</th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Joined</th>
                            <th class="h-12 px-6 text-right align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0 text-sm">
                        @forelse($customers as $customer)
                        <tr class="group border-b border-border/40 transition-all duration-200 hover:bg-muted/40 data-[state=selected]:bg-muted/60 {{ $customer->trashed() ? 'bg-destructive/5' : '' }}">
                            <!-- Row Checkbox -->
                             <td class="p-6 align-middle">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                    value="{{ $customer->id }}" 
                                    x-model="selected"
                                    class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
                                </div>
                            </td>
                            <td class="p-6 align-middle">
                                <div class="flex items-center gap-4">
                                    <div class="relative flex shrink-0 overflow-hidden rounded-full h-10 w-10 ring-2 ring-background shadow-sm transition-transform group-hover:scale-105">
                                        <span class="flex h-full w-full items-center justify-center rounded-full bg-gradient-to-br from-primary/10 to-primary/5 text-primary font-bold text-xs border border-primary/10">
                                            {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name ?? '', 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="flex flex-col space-y-0.5">
<a href="{{ url('/customers/' . $customer->id) }}"
   class="font-semibold text-foreground text-sm tracking-tight hover:text-primary transition-colors">
    {{ $customer->first_name }} {{ $customer->last_name }}
</a>
                                        <span class="text-xs text-muted-foreground font-mono">{{ $customer->email ?? $customer->mobile }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6 align-middle">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex w-fit items-center rounded-md border border-border bg-secondary/50 px-2 py-0.5 text-[10px] font-medium text-secondary-foreground">
                                        {{ $customer->customer_code }}
                                    </span>
                                    <span class="text-xs text-muted-foreground">{{ ucfirst($customer->type) }} ({{ ucfirst($customer->category) }})</span>
                                </div>
                            </td>
                            <td class="p-6 align-middle">
                                @if($customer->trashed())
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-destructive/10 text-destructive border border-destructive/20">
                                        Deleted
                                    </span>
                                @elseif($customer->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-500/10 text-amber-600 border border-amber-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="p-6 align-middle text-muted-foreground font-mono text-xs">
                                {{ $customer->created_at->format('M d, Y') }}
                            </td>
                            <td class="p-6 align-middle text-right">
                                 <div class="relative flex justify-end" x-data="{ open: false }" @click.away="open = false">
                                    <button @click="open = !open" class="group/btn inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground/70 transition-all hover:text-foreground hover:bg-accent focus:outline-none focus:ring-2 focus:ring-ring active:scale-95">
                                        <span class="sr-only">Open menu</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                    </button>
                                    
                                    <div x-show="open" 
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                                         class="absolute right-0 top-9 z-50 min-w-[180px] overflow-hidden rounded-xl border border-border/60 bg-popover/95 p-1 text-popover-foreground shadow-xl shadow-black/5 backdrop-blur-xl"
                                         style="display: none;">
                                        
                                        <div class="px-2 py-1.5 text-xs font-semibold text-muted-foreground/50 uppercase tracking-wider">Manage</div>
                                        
                                        @if($customer->trashed())
                                            <form action="/customers/{{ $customer->id }}/restore" method="POST">
                                                @csrf
                                                <button type="submit" class="w-full flex cursor-pointer select-none items-center gap-2 rounded-lg px-2 py-2 text-sm outline-none transition-colors hover:bg-emerald-500/10 hover:text-emerald-600 focus:bg-accent focus:text-accent-foreground">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
                                                    Restore
                                                </button>
                                            </form>
                                            <div class="my-1 h-px bg-border/50"></div>
                                            <form action="/customers/{{ $customer->id }}" method="POST" onsubmit="return confirm('Permanently delete?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full flex cursor-pointer select-none items-center gap-2 rounded-lg px-2 py-2 text-sm outline-none transition-colors hover:bg-destructive/10 hover:text-destructive focus:bg-destructive/10">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                                    Permanently Delete
                                                </button>
                                            </form>
                                        @else
                                            <a href="/customers/{{ $customer->id }}/edit" class="flex w-full cursor-pointer select-none items-center gap-2 rounded-lg px-2 py-2 text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                                Edit Details
                                            </a>
                                            <a href="/customers/{{ $customer->id }}" class="flex w-full cursor-pointer select-none items-center gap-2 rounded-lg px-2 py-2 text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                                                View Profile
                                            </a>
                                            
                                            <div class="my-1 h-px bg-border/50"></div>
                                            
                                            <div class="my-1 h-px bg-border/50"></div>
                                            
                                            <form action="/customers/{{ $customer->id }}" method="POST" onsubmit="return confirm('Move to trash?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full flex cursor-pointer select-none items-center gap-2 rounded-lg px-2 py-2 text-sm outline-none transition-colors hover:bg-destructive/10 hover:text-destructive text-destructive/80">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                                    Move to Trash
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                 </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-16 text-center">
                                <div class="flex flex-col items-center justify-center text-muted-foreground/50">
                                    <div class="h-16 w-16 rounded-full bg-muted/30 flex items-center justify-center mb-4 ring-1 ring-border/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    </div>
                                    <p class="text-lg font-semibold text-foreground">No customers found</p>
                                    <p class="text-sm mt-1 max-w-xs mx-auto">Try adjusting your safe terms or add a new customer to get started.</p>
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
            
            @if($customers->hasPages() || $customers->total() > 5)
            <div class="border-t border-border/40 p-4 bg-muted/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                 <div class="text-xs text-muted-foreground px-2">Page <span class="font-medium text-foreground">{{ $customers->currentPage() }}</span> of <span class="font-medium">{{ $customers->lastPage() }}</span></div>
                 <div>{{ $customers->onEachSide(1)->links() }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('customers-table-container');
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
                const newContent = doc.getElementById('customers-table-container');
                
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

