@extends('layouts.app')

@section('content')
    <div id="products-page-wrapper" class="flex flex-1 flex-col space-y-8 p-8 animate-in fade-in duration-500">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-1">
                <h1
                    class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                    Products</h1>
                <p class="text-muted-foreground text-sm">Manage global product catalog, pricing, and stock.</p>
            </div>
            <div class="flex items-center p-1 bg-muted/50 rounded-xl border border-border/50 backdrop-blur-sm">
                <a href="{{ route('central.products.index') }}"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === null ? 'bg-background text-foreground shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-foreground hover:bg-background/50' }}">
                    All Products
                </a>
                <div class="w-px h-4 bg-border/40 mx-1"></div>
                <a href="{{ route('central.products.index', ['status' => 'active']) }}"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'active' ? 'bg-background text-emerald-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-emerald-600 hover:bg-background/50' }}">
                    Active
                </a>
                <div class="w-px h-4 bg-border/40 mx-1"></div>
                <a href="{{ route('central.products.index', ['stock' => 'low']) }}"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request('stock') === 'low' ? 'bg-background text-amber-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-amber-600 hover:bg-background/50' }}">
                    Low Stock
                </a>
            </div>
        </div>

        <div id="products-table-container" x-data="{ selected: [] }">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-1.5 rounded-2xl">
                <div class="flex items-center gap-3 min-h-[44px]">
                    <div x-cloak x-show="selected.length > 0" x-transition.opacity.duration.300ms
                        class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4">
                        <div
                            class="px-3 py-1.5 rounded-lg bg-primary/10 border border-primary/20 text-primary text-xs font-semibold shadow-sm">
                            <span x-text="selected.length"></span> selected
                        </div>
                    </div>

                    <form id="search-form" method="GET" action="{{ url()->current() }}"
                        class="flex items-center gap-2 group">
                        @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                        @if(request('stock')) <input type="hidden" name="stock" value="{{ request('stock') }}"> @endif
                        @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                        @endif

                        <div class="relative transition-all duration-300 group-focus-within:w-72"
                            :class="selected.length > 0 ? 'w-48' : 'w-64'">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <circle cx="11" cy="11" r="8" />
                                    <path d="m21 21-4.3-4.3" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search products..."
                                class="block w-full rounded-xl border-0 py-2.5 pl-10 pr-3 text-foreground bg-muted/40 ring-1 ring-inset ring-transparent placeholder:text-muted-foreground focus:bg-background focus:ring-2 focus:ring-primary/20 sm:text-sm sm:leading-6 transition-all shadow-sm">
                        </div>
                    </form>
                </div>

                <a href="{{ route('central.products.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="M12 5v14" />
                    </svg>
                    <span>Add Product</span>
                </a>
            </div>

            <div class="rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl shadow-sm overflow-hidden relative">
                <div id="table-loading"
                    class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent shadow-lg">
                    </div>
                </div>

                <div
                    class="border-b border-border/40 p-4 bg-muted/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                        <span
                            class="flex h-6 w-6 items-center justify-center rounded-md bg-background border border-border font-medium text-foreground shadow-sm">
                            {{ $products->total() }}
                        </span>
                        <span>products found</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <form id="per-page-form" method="GET" action="{{ url()->current() }}"
                            class="flex items-center gap-2">
                            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            @if(request('stock')) <input type="hidden" name="stock" value="{{ request('stock') }}"> @endif
                            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

                            <label for="per_page"
                                class="text-xs font-medium text-muted-foreground whitespace-nowrap">View</label>
                            <div class="relative">
                                <select name="per_page" id="per_page"
                                    class="appearance-none h-8 pl-3 pr-8 rounded-lg border border-border bg-background text-xs font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors cursor-pointer hover:bg-accent/50">
                                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="relative w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr
                                class="border-b border-border/40 transition-colors hover:bg-muted/30 data-[state=selected]:bg-muted bg-muted/20">
                                <th class="h-12 w-[50px] px-6 text-left align-middle">
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                            class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary"
                                            @click="selected = $event.target.checked ? [{{ $products->pluck('id')->join(',') }}] : []">
                                    </div>
                                </th>
                                <th
                                    class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">
                                    Product</th>
                                <th
                                    class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">
                                    Category</th>
                                <th
                                    class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">
                                    Type</th>
                                <th
                                    class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">
                                    Origin</th>
                                <th
                                    class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">
                                    Price</th>
                                <th
                                    class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">
                                    Details</th>
                                <th
                                    class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">
                                    Stock</th>
                                <th
                                    class="h-12 px-6 text-right align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0 text-sm">
                            @forelse($products as $product)
                                <tr
                                    class="group border-b border-border/40 transition-all duration-200 hover:bg-muted/40 data-[state=selected]:bg-muted/60">
                                    <td class="p-6 align-middle">
                                        <div class="flex items-center">
                                            <input type="checkbox" value="{{ $product->id }}" x-model="selected"
                                                class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
                                        </div>
                                    </td>
                                    <td class="p-6 align-middle">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="h-12 w-12 rounded-xl bg-muted flex items-center justify-center overflow-hidden border border-border/50 shadow-sm group-hover:scale-105 transition-transform duration-300">
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                    class="h-full w-full object-cover">
                                            </div>
                                            <div class="flex flex-col space-y-0.5">
                                                <span
                                                    class="font-semibold text-foreground text-sm tracking-tight">{{ $product->name }}</span>
                                                <span class="text-xs text-muted-foreground font-mono flex items-center gap-1.5">
                                                    {{ $product->sku }}
                                                    @if($product->brand)
                                                        <span class="inline-block w-1 h-1 rounded-full bg-border"></span>
                                                        <span class="text-primary/70">{{ $product->brand->name }}</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-6 align-middle">
                                        @if($product->category)
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-primary/10 bg-primary/5 px-2.5 py-1 text-xs font-medium text-primary shadow-sm">
                                                {{ $product->category->name }}
                                            </span>
                                        @else
                                            <span class="text-muted-foreground/40 text-xs italic">Uncategorized</span>
                                        @endif
                                    </td>
                                    <td class="p-6 align-middle">
                                        <div class="flex flex-col gap-1.5">
                                            <span
                                                class="capitalize text-xs font-semibold px-2 py-0.5 rounded-md bg-secondary text-secondary-foreground w-fit">{{ $product->type }}</span>
                                            @if($product->is_organic)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-600 uppercase tracking-tight">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <path
                                                            d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z" />
                                                        <path d="M2 21c0-3 1.85-5.36 5.08-6C10.9 14.36 12 12 12 12" />
                                                    </svg>
                                                    Organic
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-6 align-middle">
                                        <span class="text-xs text-muted-foreground font-medium flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" class="opacity-50">
                                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                                <circle cx="12" cy="10" r="3" />
                                            </svg>
                                            {{ $product->origin ?? 'Global' }}
                                        </span>
                                    </td>
                                    <td class="p-6 align-middle">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-foreground text-sm">Rs
                                                {{ number_format($product->price, 2) }}</span>
                                            @if($product->cost_price > 0)
                                                <span class="text-[10px] text-muted-foreground/60 italic">Cost: Rs
                                                    {{ number_format($product->cost_price, 2) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-6 align-middle">
                                        @if($product->default_discount_value > 0)
                                            <div
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500/10 border border-amber-500/20 px-2.5 py-1 text-xs font-semibold text-amber-600 shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" class="opacity-70">
                                                    <path d="M7 10v4h3v7h4v-7h3l-5-7Z" />
                                                </svg>
                                                {{ $product->default_discount_type == 'percent' ? $product->default_discount_value . '%' : 'Rs ' . number_format($product->default_discount_value, 2) }}
                                            </div>
                                        @else
                                            <span class="text-muted-foreground/30">—</span>
                                        @endif
                                    </td>
                                    <td class="p-6 align-middle">
                                        @if($product->stock_on_hand > 10)
                                            <div class="flex flex-col gap-1">
                                                <span
                                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 w-fit">
                                                    {{ floatval($product->stock_on_hand) }} {{ $product->unit_type }}
                                                </span>
                                                <div class="h-1 w-16 bg-muted rounded-full overflow-hidden">
                                                    <div class="h-full bg-emerald-500 w-full"></div>
                                                </div>
                                            </div>
                                        @elseif($product->stock_on_hand > 0)
                                            <div class="flex flex-col gap-1">
                                                <span
                                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-500/10 text-amber-600 border border-amber-500/20 w-fit">
                                                    {{ floatval($product->stock_on_hand) }} low
                                                </span>
                                                <div class="h-1 w-16 bg-muted rounded-full overflow-hidden">
                                                    <div class="h-full bg-amber-500 w-1/3"></div>
                                                </div>
                                            </div>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-destructive/10 text-destructive border border-destructive/20">
                                                Out of Stock
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-6 align-middle text-right">
                                        <div class="relative flex justify-end" x-data="{ open: false }"
                                            @click.away="open = false">
                                            <button @click="open = !open"
                                                class="group/btn inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground/70 transition-all hover:text-foreground hover:bg-accent focus:outline-none focus:ring-2 focus:ring-ring active:scale-95">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="1" />
                                                    <circle cx="12" cy="5" r="1" />
                                                    <circle cx="12" cy="19" r="1" />
                                                </svg>
                                            </button>
                                            <div x-show="open"
                                                class="absolute right-0 top-9 z-50 min-w-[180px] overflow-hidden rounded-xl border border-border/60 bg-popover/95 p-1 text-popover-foreground shadow-xl shadow-black/5 backdrop-blur-xl"
                                                style="display: none;">
                                                <div
                                                    class="px-2 py-1.5 text-xs font-semibold text-muted-foreground/50 uppercase tracking-wider">
                                                    Manage</div>
                                                <a href="{{ route('central.products.edit', $product) }}"
                                                    class="flex w-full cursor-pointer select-none items-center gap-2 rounded-lg px-2 py-2 text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground">
                                                    Edit Details
                                                </a>
                                                <div class="my-1 h-px bg-border/50"></div>
                                                <form action="{{ route('central.products.destroy', $product) }}" method="POST"
                                                    onsubmit="return confirm('Delete product?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-full flex cursor-pointer select-none items-center gap-2 rounded-lg px-2 py-2 text-sm outline-none transition-colors hover:bg-destructive/10 hover:text-destructive text-destructive/80">
                                                        Delete Product
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-16 text-center">
                                        <div class="flex flex-col items-center justify-center text-muted-foreground/50">
                                            <p class="text-lg font-semibold text-foreground">No products found</p>
                                            <p class="text-sm mt-1">Add a new product to get started.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                    <div
                        class="border-t border-border/40 p-4 bg-muted/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs text-muted-foreground px-2">Page <span
                                class="font-medium text-foreground">{{ $products->currentPage() }}</span> of <span
                                class="font-medium">{{ $products->lastPage() }}</span></div>
                        <div>{{ $products->links() }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('products-table-container');
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
                    const newContent = doc.getElementById('products-table-container');
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