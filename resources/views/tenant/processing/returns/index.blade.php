@extends('layouts.app')

@section('content')
    <div class="flex flex-1 flex-col space-y-8 p-4 md:p-8 max-w-7xl mx-auto w-full animate-in fade-in duration-500" 
         x-data="{ 
            showModal: false, 
            currentReturn: null,
            items: [],
            actionUrl: '',
            
            inspect(ret) {
                this.currentReturn = ret;
                this.actionUrl = '{{ route('tenant.processing.returns.receive', ':id') }}'.replace(':id', ret.id);
                this.items = ret.items.map(i => ({
                    item_id: i.id,
                    name: i.product ? i.product.name : 'Unknown Product',
                    quantity: i.quantity,
                    requested_condition: i.condition,
                    condition: i.condition,
                    verified: false,
                    image_url: (i.product && i.product.image_url) ? i.product.image_url : null
                }));
                this.showModal = true;
                // Prevent body scroll
                document.body.style.overflow = 'hidden';
            },
            
            closeModal() {
                this.showModal = false;
                document.body.style.overflow = 'auto';
            },
            
            get canSubmit() {
                return this.items.length > 0 && this.items.every(i => i.verified);
            }
         }"
         @keydown.escape.window="closeModal()">

        <!-- Header ... (same as before) ... -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-1">
                <h1
                    class="text-3xl font-bold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-400 bg-clip-text text-transparent">
                    Return Processing
                </h1>
                <p class="text-muted-foreground text-sm">
                    Inspect and receive returned items into inventory.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('tenant.processing.orders.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    Order Processing
                </a>
            </div>
        </div>

        <!-- Stats Overview ... (same as before) ... -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Requests</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-sm">
                <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-1">Waiting Receipt</p>
                <p class="text-2xl font-black text-blue-600">{{ $stats['approved'] }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-sm">
                <p class="text-xs font-bold text-emerald-500 uppercase tracking-wider mb-1">Received</p>
                <p class="text-2xl font-black text-emerald-600">{{ $stats['received'] }}</p>
            </div>
        </div>

        <!-- Filters & Tabs ... (same as before) ... -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white/50 dark:bg-zinc-900/50 p-2 rounded-2xl border border-gray-100 dark:border-zinc-800 backdrop-blur-sm">
            <div class="flex items-center p-1 bg-gray-100 dark:bg-zinc-800 rounded-xl w-full sm:w-auto">
                <a href="{{ route('tenant.processing.returns.index', ['status' => 'all']) }}" 
                   class="flex-1 sm:flex-none px-4 py-2 text-xs font-bold rounded-lg transition-all {{ request('status', 'all') === 'all' ? 'bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    All
                </a>
                <a href="{{ route('tenant.processing.returns.index', ['status' => 'approved']) }}" 
                   class="flex-1 sm:flex-none px-4 py-2 text-xs font-bold rounded-lg transition-all {{ request('status') === 'approved' ? 'bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Approved
                </a>
                <a href="{{ route('tenant.processing.returns.index', ['status' => 'received']) }}" 
                   class="flex-1 sm:flex-none px-4 py-2 text-xs font-bold rounded-lg transition-all {{ request('status') === 'received' ? 'bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Received
                </a>
            </div>

            <div class="relative w-full sm:w-64">
                <form action="{{ route('tenant.processing.returns.index') }}" method="GET">
                    <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search RMA or Order..."
                           class="w-full pl-10 pr-4 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl text-xs focus:ring-2 focus:ring-gray-900 transition-all">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </form>
            </div>
        </div>

        <!-- Returns List -->
        <div class="space-y-4">
            @forelse($returns as $return)
                <div class="group bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-sm hover:shadow-md transition-all overflow-hidden">
                    <div class="flex flex-col lg:flex-row">
                        <!-- Left: Return Info -->
                        <div class="flex-1 p-6 border-b lg:border-b-0 lg:border-r border-gray-50 dark:border-zinc-800/50">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-gray-50 dark:bg-zinc-800 rounded-xl">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">RMA Number</span>
                                        <h4 class="text-base font-black text-gray-900 dark:text-white leading-none mt-0.5">{{ $return->rma_number }}</h4>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider border 
                                    {{ $return->status === 'approved' ? 'bg-blue-50 text-blue-600 border-blue-200' : '' }}
                                    {{ $return->status === 'received' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : '' }}">
                                    <span class="w-1 h-1 rounded-full {{ $return->status === 'approved' ? 'bg-blue-600' : 'bg-emerald-600' }}"></span>
                                    {{ $return->status }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-6 mt-6">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Customer</p>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $return->order->customer->first_name }} {{ $return->order->customer->last_name }}</p>
                                    <p class="text-xs text-gray-500">Order #{{ $return->order->order_number }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Requested On</p>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $return->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $return->created_at->format('H:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Items & Action -->
                        <div class="flex-1 flex flex-col p-6 bg-gray-50/50 dark:bg-zinc-800/10">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Items to Receive</p>
                            <div class="flex-1 space-y-3">
                                @foreach($return->items as $item)
                                    <div class="flex items-center gap-3 p-2 bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 shadow-sm">
                                        <div class="h-10 w-10 rounded-lg bg-gray-50 dark:bg-zinc-800 overflow-hidden flex-shrink-0 border border-gray-100 dark:border-zinc-700">
                                            @if($item->product && $item->product->image_url)
                                                <img src="{{ $item->product->image_url }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="h-full w-full flex items-center justify-center text-gray-300">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="m21 8-9-4-9 4m18 8-9 4-9-4m18-4-9 4-9-4"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $item->product->name }}</p>
                                            <p class="text-[10px] text-gray-500 leading-none mt-0.5">Qty: {{ $item->quantity }} • <span class="capitalize">{{ $item->condition }}</span></p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6">
                                @if($return->status === 'approved')
                                    <button type="button"
                                        @click="inspect({{ $return->toJson() }})"
                                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm font-bold rounded-xl hover:bg-black dark:hover:bg-gray-100 transition-all shadow-lg shadow-gray-900/10 dark:shadow-none hover:-translate-y-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                        Inspect & Receive Items
                                    </button>
                                @else
                                    <div class="w-full py-3 px-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 rounded-xl flex items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Inventory Updated</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-20 text-center bg-white dark:bg-zinc-900 rounded-3xl border border-dashed border-gray-200 dark:border-zinc-800">
                    <div class="w-20 h-20 bg-gray-50 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4a2 2 0 012-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">No returns found</h3>
                    <p class="text-sm text-gray-500 max-w-xs mx-auto mt-1">There are currently no return requests matching the selected status.</p>
                </div>
            @endforelse
        </div>

        <!-- Inspection Modal -->
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
             x-cloak>
            
            <div x-show="showModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="bg-white dark:bg-zinc-900 w-full max-w-2xl rounded-3xl shadow-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden"
                 @click.away="closeModal()">
                
                <form :action="actionUrl" method="POST">
                    @csrf
                    <!-- Modal Header -->
                    <div class="px-8 py-6 border-b border-gray-50 dark:border-zinc-800 flex items-center justify-between bg-gray-50/50 dark:bg-zinc-800/50">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M20 6 9 17l-5-5"/></svg>
                                Inspection Checklist
                            </h3>
                            <p class="text-sm text-gray-500 mt-1" x-text="'Processing ' + (currentReturn ? currentReturn.rma_number : '')"></p>
                        </div>
                        <button type="button" @click="closeModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-xl transition-colors text-gray-400 hover:text-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-8 py-6 max-h-[60vh] overflow-y-auto space-y-4 custom-scrollbar">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-4 rounded-2xl border transition-all duration-200" 
                                 :class="item.verified ? 'bg-blue-50/30 border-blue-200 dark:bg-blue-500/5 dark:border-blue-500/30' : 'bg-white dark:bg-zinc-900 border-gray-100 dark:border-zinc-800'">
                                <div class="flex flex-col sm:flex-row gap-4">
                                    <div class="flex items-start gap-4 flex-1">
                                        <!-- Checkbox -->
                                        <div class="pt-1">
                                            <input type="checkbox" x-model="item.verified" :name="'items['+index+'][verified]'" value="1"
                                                   class="h-6 w-6 rounded-lg border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer shadow-sm transition-all">
                                        </div>

                                        <!-- Product Info -->
                                        <div class="flex items-start gap-3 flex-1">
                                            <div class="h-12 w-12 rounded-xl bg-gray-50 dark:bg-zinc-800 overflow-hidden flex-shrink-0 border border-gray-100 dark:border-zinc-700">
                                                <template x-if="item.image_url">
                                                    <img :src="item.image_url" class="h-full w-full object-cover">
                                                </template>
                                                <template x-if="!item.image_url">
                                                    <div class="h-full w-full flex items-center justify-center text-gray-300">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="m21 8-9-4-9 4m18 8-9 4-9-4m18-4-9 4-9-4"/></svg>
                                                    </div>
                                                </template>
                                            </div>
                                            <div>
                                                <h5 class="text-sm font-black text-gray-900 dark:text-white leading-snug" x-text="item.name"></h5>
                                                <p class="text-xs text-gray-500 mt-0.5" x-text="'Requested Qty: ' + item.quantity"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Condition Selector -->
                                    <div class="w-full sm:w-40" x-show="item.verified" x-transition>
                                        <input type="hidden" :name="'items['+index+'][item_id]'" :value="item.item_id">
                                        <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1.5 px-1">Inspected Condition</label>
                                        <select :name="'items['+index+'][condition]'" x-model="item.condition"
                                                class="w-full h-10 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 transition-all">
                                            <option value="sellable">Sellable (Restock)</option>
                                            <option value="damaged">Damaged (No Restock)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-8 py-6 border-t border-gray-50 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-800/50 flex flex-col sm:flex-row items-center gap-4">
                        <div class="flex-1 text-center sm:text-left">
                            <p class="text-xs font-bold" :class="canSubmit ? 'text-emerald-600' : 'text-gray-400'">
                                <template x-if="canSubmit">
                                    <span class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        All items verified and ready for receipt
                                    </span>
                                </template>
                                <template x-if="!canSubmit">
                                    <span x-text="'Please verify ' + items.filter(i => !i.verified).length + ' remaining item(s)'"></span>
                                </template>
                            </p>
                        </div>
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <button type="button" @click="closeModal()" class="flex-1 sm:flex-none px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-700 dark:hover:text-white transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    :disabled="!canSubmit"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm font-black rounded-2xl transition-all shadow-xl shadow-gray-900/10 dark:shadow-none hover:-translate-y-0.5 disabled:opacity-30 disabled:pointer-events-none disabled:grayscale">
                                <span>Complete Receipt</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $returns->links() }}
        </div>
    </div>
@endsection
