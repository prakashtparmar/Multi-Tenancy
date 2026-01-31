@extends('layouts.app')

@section('content')
<div id="returns-page-wrapper" class="flex flex-1 flex-col space-y-8 p-8 animate-in fade-in duration-500">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">Returns (RMA)</h1>
            <p class="text-muted-foreground text-sm">Manage return requests, approvals, and refunds.</p>
        </div>
        <div class="flex items-center p-1 bg-muted/50 rounded-xl border border-border/50 backdrop-blur-sm">
            <a href="{{ route('central.returns.index') }}" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === null ? 'bg-background text-foreground shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-foreground hover:bg-background/50' }}">
                All Returns
            </a>
            <div class="w-px h-4 bg-border/40 mx-1"></div>
            <a href="{{ route('central.returns.index', ['status' => 'requested']) }}" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'requested' ? 'bg-background text-amber-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-amber-600 hover:bg-background/50' }}">
                Requested
            </a>
            <div class="w-px h-4 bg-border/40 mx-1"></div>
            <a href="{{ route('central.returns.index', ['status' => 'approved']) }}" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'approved' ? 'bg-background text-blue-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-blue-600 hover:bg-background/50' }}">
                Approved
            </a>
            <div class="w-px h-4 bg-border/40 mx-1"></div>
            <a href="{{ route('central.returns.index', ['status' => 'completed']) }}" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'completed' ? 'bg-background text-emerald-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-emerald-600 hover:bg-background/50' }}">
                Completed
            </a>
        </div>
    </div>

    <div id="returns-table-container" x-data="{ selected: [] }">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-1.5 rounded-2xl">
            <div class="flex items-center gap-3 min-h-[44px]">
                 <div x-cloak x-show="selected.length > 0" x-transition.opacity.duration.300ms class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4">
                    <div class="px-3 py-1.5 rounded-lg bg-primary/10 border border-primary/20 text-primary text-xs font-semibold shadow-sm">
                        <span x-text="selected.length"></span> selected
                    </div>
                 </div>
    
                <form id="search-form" method="GET" action="{{ url()->current() }}" class="flex items-center gap-2 group">
                    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                    @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif
    
                    <div class="relative transition-all duration-300 group-focus-within:w-72" :class="selected.length > 0 ? 'w-48' : 'w-64'">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-focus-within:text-primary transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search RMA, orders..." 
                               class="block w-full rounded-xl border-0 py-2.5 pl-10 pr-3 text-foreground bg-muted/40 ring-1 ring-inset ring-transparent placeholder:text-muted-foreground focus:bg-background focus:ring-2 focus:ring-primary/20 sm:text-sm sm:leading-6 transition-all shadow-sm">
                    </div>
                </form>
            </div>
    
             <a href="{{ route('central.returns.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14v1a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1"/><path d="M16 10 12 6 8 10"/><path d="M12 6v12"/></svg>
                <span>Process Return</span>
            </a>
        </div>
    
        <div class="rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl shadow-sm overflow-hidden relative">
            <div id="table-loading" class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent shadow-lg"></div>
            </div>

            <div class="border-b border-border/40 p-4 bg-muted/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-background border border-border font-medium text-foreground shadow-sm">
                        {{ $returns->total() }}
                    </span>
                    <span>returns found</span>
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
                            <th class="h-12 w-[50px] px-6 text-left align-middle">
                                <div class="flex items-center">
                                    <input type="checkbox" class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary" @click="selected = $event.target.checked ? [{{ $returns->pluck('id')->join(',') }}] : []">
                                </div>
                            </th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">RMA #</th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Order</th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Reason</th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Status</th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Refund</th>
                            <th class="h-12 px-6 text-right align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0 text-sm">
                        @forelse($returns as $return)
                        <tr class="group border-b border-border/40 transition-all duration-200 hover:bg-muted/40 data-[state=selected]:bg-muted/60">
                             <td class="p-6 align-middle">
                                <div class="flex items-center">
                                    <input type="checkbox" value="{{ $return->id }}" x-model="selected" class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
                                </div>
                            </td>
                            <td class="p-6 align-middle">
                                <span class="font-semibold text-foreground text-sm tracking-tight">{{ $return->rma_number }}</span>
                            </td>
                            <td class="p-6 align-middle">
                                <div class="flex flex-col space-y-0.5">
                                    <span class="text-sm font-medium">#{{ $return->order->order_number ?? 'Unknown' }}</span>
                                    <span class="text-xs text-muted-foreground">{{ $return->order->customer->name ?? '' }}</span>
                                </div>
                            </td>
                            <td class="p-6 align-middle">
                                <span class="text-sm">{{ Str::limit($return->reason, 20) }}</span>
                            </td>
                            <td class="p-6 align-middle">
                                @if($return->status === 'approved')
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-500/10 text-blue-600 border border-blue-500/20">
                                        Approved
                                    </span>
                                @elseif($return->status === 'rejected')
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-destructive/10 text-destructive border border-destructive/20">
                                        Rejected
                                    </span>
                                @elseif($return->status === 'completed')
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                        Completed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-500/10 text-amber-600 border border-amber-500/20">
                                        Requested
                                    </span>
                                @endif
                            </td>
                             <td class="p-6 align-middle">
                                @if($return->refund_status === 'refunded')
                                    <span class="text-xs font-semibold text-emerald-600">Refunded</span>
                                @else
                                    <span class="text-xs text-muted-foreground">Pending</span>
                                @endif
                            </td>
                            <td class="p-6 align-middle text-right">
                                 <div class="relative flex justify-end" x-data="{ open: false }" @click.away="open = false">
                                    <button @click="open = !open" class="group/btn inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground/70 transition-all hover:text-foreground hover:bg-accent focus:outline-none focus:ring-2 focus:ring-ring active:scale-95">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                    </button>
                                    <div x-show="open" class="absolute right-0 top-9 z-50 min-w-[180px] overflow-hidden rounded-xl border border-border/60 bg-popover/95 p-1 text-popover-foreground shadow-xl shadow-black/5 backdrop-blur-xl" style="display: none;">
                                        <div class="px-2 py-1.5 text-xs font-semibold text-muted-foreground/50 uppercase tracking-wider">Manage</div>
                                        <a href="{{ route('central.returns.show', $return) }}" class="flex w-full cursor-pointer select-none items-center gap-2 rounded-lg px-2 py-2 text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground">
                                            View Details
                                        </a>
                                        <a href="{{ route('central.returns.edit', $return) }}" class="flex w-full cursor-pointer select-none items-center gap-2 rounded-lg px-2 py-2 text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground">
                                            Update RMA
                                        </a>
                                    </div>
                                 </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-16 text-center">
                                <div class="flex flex-col items-center justify-center text-muted-foreground/50">
                                    <p class="text-lg font-semibold text-foreground">No return requests found</p>
                                    <p class="text-sm mt-1">Manage new returns here.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($returns->hasPages())
            <div class="border-t border-border/40 p-4 bg-muted/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                 <div class="text-xs text-muted-foreground px-2">Page <span class="font-medium text-foreground">{{ $returns->currentPage() }}</span> of <span class="font-medium">{{ $returns->lastPage() }}</span></div>
                 <div>{{ $returns->links() }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('returns-table-container');
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
                const newContent = doc.getElementById('returns-table-container');
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
