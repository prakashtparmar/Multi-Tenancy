@extends('layouts.app')

@section('content')
    <div x-data="{ selected: [], allIds: {{ $orders->pluck('id') }} }"
        class="flex flex-1 flex-col space-y-6 p-6 md:p-8 max-w-[1600px] mx-auto w-full animate-in fade-in duration-500">

        <!-- Header Area -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-2">
            <div class="space-y-1">
                <h1
                    class="text-4xl font-extrabold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                    Order Processing (Central)
                </h1>
                <p class="text-muted-foreground text-sm font-medium">Manage fulfillment, track shipments, and handle
                    returns.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3">
                <a href="{{ route('central.processing.returns.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border/60 bg-background/50 hover:bg-background hover:border-border text-sm font-semibold transition-all shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                    Returns
                </a>
                <form action="{{ route('central.orders.export') }}" method="POST">
                    @csrf
                    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                    @if(request('date_filter'))
                        <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
                        @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                        @endif
                        @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                    @endif
                    <input type="hidden" name="ids" :value="selected.join(',')">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-900 text-white text-sm font-semibold shadow-lg shadow-gray-900/10 hover:shadow-gray-900/20 hover:bg-black transition-all transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Export Orders
                    </button>
                </form>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div
            class="sticky top-0 z-30 bg-background/95 backdrop-blur-xl border-b border-border/40 -mx-6 px-6 md:-mx-8 md:px-8 pt-2">
            <div class="flex items-center gap-1 overflow-x-auto no-scrollbar pb-0">
                @php
                    $navItems = [
                        ['label' => 'Active Orders', 'route' => route('central.processing.orders.index'), 'active' => !request('status')],
                        ['label' => 'Confirmed', 'route' => route('central.processing.orders.index', ['status' => 'confirmed']), 'active' => request('status') === 'confirmed', 'color' => 'blue'],
                        ['label' => 'Processing', 'route' => route('central.processing.orders.index', ['status' => 'processing']), 'active' => request('status') === 'processing', 'color' => 'purple'],
                        ['label' => 'Ready to Ship', 'route' => route('central.processing.orders.index', ['status' => 'ready_to_ship']), 'active' => request('status') === 'ready_to_ship', 'color' => 'emerald'],
                        ['label' => 'Dispatched', 'route' => route('central.processing.orders.index', ['status' => 'shipped']), 'active' => request('status') === 'shipped', 'color' => 'indigo'],
                        ['label' => 'Delivered', 'route' => route('central.processing.orders.index', ['status' => 'delivered']), 'active' => request('status') === 'delivered', 'color' => 'green'],
                        ['label' => 'Cancelled', 'route' => route('central.processing.orders.index', ['status' => 'cancelled']), 'active' => request('status') === 'cancelled', 'color' => 'red'],
                        ['label' => 'All History', 'route' => route('central.processing.orders.index', ['status' => 'all']), 'active' => request('status') === 'all'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    <a href="{{ $item['route'] }}"
                        class="relative px-5 py-3 text-sm font-medium transition-all duration-200 whitespace-nowrap {{ $item['active'] ? 'text-foreground' : 'text-muted-foreground hover:text-foreground' }}">
                        {{ $item['label'] }}
                        @if($item['active'])
                            <div
                                class="absolute bottom-0 left-0 w-full h-0.5 bg-primary rounded-t-full shadow-[0_-2px_6px_rgba(0,0,0,0.1)]">
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-2">
            <!-- Left: Bulk Actions -->
            <div class="flex items-center gap-3">
                <div class="flex items-center px-2 py-1.5 rounded-lg hover:bg-muted/50 transition-colors cursor-pointer"
                    title="Select All on Page">
                    <input type="checkbox" x-on:change="$el.checked ? selected = allIds : selected = []"
                        x-bind:checked="selected.length === allIds.length && allIds.length > 0"
                        class="h-5 w-5 rounded-md border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
                </div>

                <div x-cloak x-show="selected.length > 0" x-transition.opacity.duration.300ms
                    class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4">

                    <div
                        class="px-3 py-1.5 rounded-lg bg-primary/10 border border-primary/20 text-primary text-xs font-semibold shadow-sm">
                        <span x-text="selected.length"></span> selected
                    </div>

                    <!-- Bulk Options Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false"
                            class="group flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-input shadow-sm hover:shadow-md hover:border-primary/30 transition-all duration-200 text-sm font-medium">
                            <span>Bulk Actions</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="text-muted-foreground group-hover:text-primary transition-colors">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <div x-show="open" style="display: none;"
                            class="absolute left-0 top-full z-50 mt-2 min-w-[240px] p-1.5 rounded-xl border border-border/60 bg-white/95 backdrop-blur-xl shadow-xl shadow-black/5 animate-in zoom-in-95 fade-in-0 slide-in-from-top-2">

                            <div
                                class="px-2 py-1.5 text-xs font-semibold text-muted-foreground/60 uppercase tracking-wider">
                                Update Status</div>

                            <form action="{{ route('central.processing.orders.bulk-status') }}" method="POST">
                                @csrf
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id">
                                </template>

                                <button type="submit" name="status" value="confirmed"
                                    @click="if(!confirm('Are you sure you want to mark ' + selected.length + ' orders as Confirmed?')) $event.preventDefault()"
                                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-blue-50 hover:text-blue-600 text-foreground/80">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    Mark as Confirmed
                                </button>
                                <button type="submit" name="status" value="processing"
                                    @click="if(!confirm('Are you sure you want to mark ' + selected.length + ' orders as Processing?')) $event.preventDefault()"
                                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-purple-50 hover:text-purple-600 text-foreground/80">
                                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                    Mark as Processing
                                </button>
                                <button type="submit" name="status" value="ready_to_ship"
                                    @click="if(!confirm('Are you sure you want to mark ' + selected.length + ' orders as Ready to Ship? This will generate invoices.')) $event.preventDefault()"
                                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-emerald-50 hover:text-emerald-600 text-foreground/80">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Mark as Ready
                                </button>
                                <button type="submit" name="status" value="delivered"
                                    @click="if(!confirm('Are you sure you want to mark ' + selected.length + ' orders as Delivered?')) $event.preventDefault()"
                                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-green-50 hover:text-green-600 text-foreground/80">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    Mark as Delivered
                                </button>
                            </form>

                            <div class="my-1.5 h-px bg-border/50"></div>
                            <div class="px-2 py-1 text-xs font-semibold text-muted-foreground/60 uppercase tracking-wider">
                                Printing</div>

                            <form action="{{ route('central.processing.orders.bulk-print') }}" method="POST"
                                target="_blank">
                                @csrf
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id">
                                </template>

                                <button type="submit" name="type" value="invoice"
                                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground text-foreground/80">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M6 9V2h12v7" />
                                        <path
                                            d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                        <path d="M6 14h12v8H6z" />
                                    </svg>
                                    Print Invoices
                                </button>
                                <button type="submit" name="type" value="cod"
                                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground text-foreground/80">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect width="20" height="14" x="2" y="5" rx="2" />
                                        <line x1="2" x2="22" y1="10" y2="10" />
                                    </svg>
                                    Print COD Receipts
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right: Additional Actions (Search & Export & Date Filter) -->
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">

                <!-- Date Filter -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/50 border border-input shadow-sm hover:shadow-md hover:border-primary/30 transition-all text-sm font-medium">
                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span
                            x-text="new URLSearchParams(window.location.search).get('date_filter') ? new URLSearchParams(window.location.search).get('date_filter').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Date Filter'"></span>
                        <svg class="w-3 h-3 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="open" style="display: none;"
                        class="absolute right-0 top-full z-50 mt-2 w-64 p-2 rounded-xl border border-border/60 bg-popover/95 backdrop-blur-xl shadow-xl animate-in zoom-in-95 fade-in-0 slide-in-from-top-2">

                        <form action="{{ url()->current() }}" method="GET" class="space-y-2">
                            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

                            <div class="grid grid-cols-2 gap-2">
                                <button type="submit" name="date_filter" value="today"
                                    class="px-3 py-2 text-xs font-medium rounded-lg hover:bg-muted text-left">Today</button>
                                <button type="submit" name="date_filter" value="yesterday"
                                    class="px-3 py-2 text-xs font-medium rounded-lg hover:bg-muted text-left">Yesterday</button>
                                <button type="submit" name="date_filter" value="this_week"
                                    class="px-3 py-2 text-xs font-medium rounded-lg hover:bg-muted text-left">This
                                    Week</button>
                                <button type="submit" name="date_filter" value="this_month"
                                    class="px-3 py-2 text-xs font-medium rounded-lg hover:bg-muted text-left">This
                                    Month</button>
                            </div>
                            <div class="h-px bg-border/50 my-1"></div>
                            <div class="px-2 pb-1">
                                <p class="text-[10px] font-bold uppercase text-muted-foreground mb-1">Custom Range</p>
                                <input type="date" name="start_date" value="{{ request('start_date') }}"
                                    class="w-full text-xs h-8 rounded-md border-input bg-background mb-1">
                                <input type="date" name="end_date" value="{{ request('end_date') }}"
                                    class="w-full text-xs h-8 rounded-md border-input bg-background mb-2">
                                <button type="submit" name="date_filter" value="custom"
                                    class="w-full px-3 py-1.5 text-xs font-bold text-primary-foreground bg-primary rounded-md hover:bg-primary/90">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Search Form -->
                <form action="{{ url()->current() }}" method="GET" class="relative w-full sm:w-64">
                    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                    @if(request('date_filter'))
                        <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
                        @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                        @endif
                        @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                    @endif

                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted-foreground">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders..." x-data="{
                                                    async performSearch() {
                                                        const url = new URL(window.location.href);
                                                        url.searchParams.set('search', $el.value);
                                                        // Reset pagination to page 1 when searching
                                                        url.searchParams.delete('page'); 

                                                        const newUrl = url.toString();

                                                        window.history.pushState({}, '', newUrl);

                                                        try {
                                                            const response = await fetch(newUrl, {
                                                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                                            });
                                                            if (response.ok) {
                                                                const html = await response.text();
                                                                document.getElementById('orders-list-container').innerHTML = html;
                                                            } else {
                                                                console.error('Search Error:', response.status);
                                                            }
                                                        } catch (error) {
                                                            console.error('Search Failed:', error);
                                                        }
                                                    }
                                                }" @refresh-orders.window="performSearch()"
                        @input.debounce.500ms="performSearch"
                        class="flex h-10 w-full rounded-xl border border-input bg-background/50 pl-9 pr-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary/50 transition-all">
                </form>

                <form action="{{ route('central.orders.export') }}" method="POST">
                    @csrf
                    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                    @if(request('date_filter'))
                        <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
                        @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                        @endif
                        @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                    @endif
                    <input type="hidden" name="ids" :value="selected.join(',')">

                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-100 px-4 py-2.5 text-sm font-semibold text-orange-700 shadow-sm hover:bg-orange-200 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Export
                    </button>
                </form>

                <button @click="$dispatch('open-import-modal')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-100 px-4 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                        </path>
                    </svg>
                    Import CSV
                </button>
                </form>
            </div>
        </div>

        <!-- Orders Grid -->
        <div id="orders-list-container">
            @include('central.processing.orders.partials.orders-list')
        </div>
    </div>

    <!-- Dispatch Modal -->
    <div x-data="{ open: false, orderId: '', orderNumber: '' }"
        x-on:open-dispatch-modal.window="open = true; orderId = $event.detail.orderId; orderNumber = $event.detail.orderNumber"
        x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        style="display: none;">

        <div @click.away="open = false"
            class="bg-white border border-gray-100 shadow-2xl rounded-2xl w-full max-w-md p-8 space-y-6 animate-in fade-in zoom-in duration-200 relative overflow-hidden">

            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-emerald-400 to-teal-500"></div>

            <div class="space-y-1">
                <h3 class="text-xl font-bold text-gray-900">Dispatch Order</h3>
                <p class="text-sm text-gray-500">Enter courier details for <span x-text="orderNumber"
                        class="font-mono font-semibold text-gray-900"></span></p>
            </div>

            <form x-bind:action="'/processing/orders/' + orderId + '/dispatch'" method="POST" class="space-y-5">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 block">Courier
                            Service</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="courier" required placeholder="e.g. DHL, FedEx, Local"
                                class="flex h-11 w-full rounded-xl border border-gray-200 bg-gray-50 pl-10 pr-3 text-sm focus:border-emerald-500 focus:ring-emerald-500/20 transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 block">Tracking
                            Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                            </div>
                            <input type="text" name="tracking_number" required placeholder="Tracking ID"
                                class="flex h-11 w-full rounded-xl border border-gray-200 bg-gray-50 pl-10 pr-3 text-sm focus:border-emerald-500 focus:ring-emerald-500/20 transition-all">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold bg-gray-900 text-white hover:bg-black rounded-xl shadow-lg shadow-gray-900/20 transition-all transform hover:-translate-y-0.5">
                        Confirm Dispatch
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Dispatch CSV Modal -->
    <div x-data="{ open: false }" x-on:open-import-modal.window="open = true" x-show="open"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        style="display: none;">

        <div @click.away="open = false"
            class="bg-white border border-gray-100 shadow-2xl rounded-2xl w-full max-w-md p-8 space-y-6 animate-in fade-in zoom-in duration-200 relative overflow-hidden">

            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-emerald-400 to-teal-500"></div>

            <div class="space-y-1">
                <h3 class="text-xl font-bold text-gray-900">Bulk Dispatch</h3>
                <p class="text-sm text-gray-500">Upload a CSV file to dispatch multiple orders.</p>
                <p class="text-xs text-muted-foreground mt-1">Required columns: <code
                        class="bg-gray-100 px-1 py-0.5 rounded">order_number</code>, <code
                        class="bg-gray-100 px-1 py-0.5 rounded">courier</code>, <code
                        class="bg-gray-100 px-1 py-0.5 rounded">tracking_number</code></p>
            </div>

            <form action="{{ route('central.processing.orders.bulk-dispatch') }}" method="POST"
                enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 block">CSV
                            File</label>
                        <input type="file" name="csv_file" accept=".csv, .txt" required
                            class="flex w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold bg-gray-900 text-white hover:bg-black rounded-xl shadow-lg shadow-gray-900/20 transition-all transform hover:-translate-y-0.5">
                        Upload & Dispatch
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal (Reused) -->


    <!-- Premium Processed Order Modal (AJAX) -->
    <div x-data="{ 
                                open: false, 
                                order: null,
                                processOrder(orderId) {
                                    fetch(`/processing/orders/${orderId}/process`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                        }
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            this.order = data.order;
                                            this.open = true;
                                        } else {
                                            alert('Error processing order: ' + (data.message || 'Unknown error'));
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('An error occurred');
                                    });
                                },
                                closeModal() {
                            this.open = false;
                            this.$dispatch('refresh-orders'); 
                        }
                            }" @open-process-modal.window="processOrder($event.detail.orderId)">

        <div x-show="open" style="display: none;"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-md animate-in fade-in duration-300">
            <div
                class="bg-white shadow-2xl rounded-3xl w-full max-w-2xl overflow-hidden animate-in zoom-in-95 duration-300 relative border border-white/20">

                <!-- Decorative Background -->
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 via-purple-50/50 to-pink-50/50 -z-10">
                </div>

                <div class="p-8 space-y-8 max-h-[85vh] overflow-y-auto custom-scrollbar" @click.outside="closeModal()">
                    <div class="text-center space-y-3">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-tr from-green-400 to-emerald-600 text-white mb-2 shadow-lg shadow-green-500/30">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-black tracking-tight text-gray-900">Order Processed!</h2>
                        <p class="text-gray-500 text-lg">Order <span class="font-mono font-bold text-gray-900"
                                x-text="'#' + (order ? order.order_number : '')"></span> is ready for packing.</p>
                    </div>

                    <div
                        class="bg-white/60 backdrop-blur-sm rounded-2xl p-5 border border-gray-200/60 shadow-sm flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Customer</p>
                                <p class="font-bold text-gray-900"
                                    x-text="order ? (order.customer.first_name + ' ' + order.customer.last_name) : ''"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Destination</p>
                            <p class="font-bold text-gray-900"
                                x-text="order?.shipping_address?.city || 'Local Pickup / N/A'"></p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="font-bold text-sm text-gray-400 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                            Items to Pack
                        </h3>
                        <div class="max-h-[200px] overflow-y-auto pr-2 space-y-3 custom-scrollbar">
                            <template x-for="item in order?.items" :key="item.id">
                                <div
                                    class="flex items-center gap-4 p-3 bg-white border border-gray-100 rounded-2xl shadow-sm">
                                    <div
                                        class="w-14 h-14 rounded-xl bg-gray-50 flex-shrink-0 overflow-hidden border border-gray-100">
                                        <img :src="item.product.image_url" :alt="item.product_name"
                                            class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-gray-900 truncate" x-text="item.product_name"></p>
                                        <p class="text-xs text-gray-500 font-medium" x-text="'SKU: ' + item.sku"></p>
                                    </div>
                                    <div class="text-right px-2">
                                        <span class="block text-xl font-black text-indigo-600"
                                            x-text="item.quantity"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button @click="closeModal()"
                            class="w-full py-3.5 rounded-xl bg-gray-900 hover:bg-black text-white font-bold text-base shadow-lg shadow-gray-900/20 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection