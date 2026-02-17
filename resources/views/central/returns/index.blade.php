@extends('layouts.app')

@section('content')
    <div x-data="{ 
        showModal: false,
        currentReturn: null,
        items: [],
        actionUrl: '',
        
        inspect(rma) {
            this.currentReturn = rma;
            this.items = rma.items.map(item => ({
                item_id: item.id,
                name: item.product.name,
                sku: item.product.sku,
                quantity: item.quantity,
                image_url: item.product.image_url,
                verified: false,
                condition: 'sellable'
            }));
            this.actionUrl = '{{ route('central.returns.inspect.store', ':id') }}'.replace(':id', rma.id);
            this.showModal = true;
        },
        
        closeModal() {
            this.showModal = false;
            this.currentReturn = null;
            this.items = [];
        },
        
        get canSubmit() {
            return this.items.length > 0 && this.items.every(i => i.verified);
        }
    }" 
    class="flex flex-col space-y-8 p-8 max-w-[1600px] mx-auto w-full animate-in fade-in duration-500">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1">
                <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                    Returns Management
                </h1>
                <p class="text-muted-foreground text-sm font-medium">Process RMAs, inspect items, and issue refunds.</p>
            </div>
            
            <a href="{{ route('central.returns.create') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 hover:bg-black text-white text-sm font-semibold rounded-xl shadow-lg shadow-gray-900/10 transition-all hover:-translate-y-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Process New Return
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Total Requests</span>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $stats['all'] }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Pending Action</span>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $stats['requested'] }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Received</span>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $stats['received'] }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="m17 5-9 13"/><path d="m5 17 9-13"/></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Refunded</span>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $stats['refunded'] }}</div>
            </div>
        </div>

        <!-- Filters & Toolbar -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 sticky top-0 z-30 bg-gray-50/95 backdrop-blur-xl p-2 rounded-2xl border border-gray-200/60 shadow-sm">
            <div class="flex items-center gap-1 overflow-x-auto no-scrollbar w-full md:w-auto">
                @php
                    $filters = [
                        ['label' => 'All', 'value' => null],
                        ['label' => 'Requested', 'value' => 'requested'],
                        ['label' => 'Approved', 'value' => 'approved'],
                        ['label' => 'Received', 'value' => 'received'],
                        ['label' => 'Refunded', 'value' => 'refunded'],
                        ['label' => 'Rejected', 'value' => 'rejected'],
                        ['label' => 'Completed', 'value' => 'completed'],
                    ];
                @endphp
                @foreach($filters as $filter)
                    <a href="{{ route('central.returns.index', ['status' => $filter['value']]) }}" 
                       class="px-4 py-2 rounded-xl text-sm font-medium transition-all whitespace-nowrap {{ request('status') == $filter['value'] ? 'bg-white text-gray-900 shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-900 hover:bg-white/50' }}">
                        {{ $filter['label'] }}
                    </a>
                @endforeach
            </div>

            <form action="{{ url()->current() }}" method="GET" class="relative group w-full md:w-72">
                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search RMA, Order, Customer..." 
                       class="w-full h-10 pl-10 pr-4 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
            </form>
        </div>

        <!-- Content Area -->
        <div class="space-y-4">
            @forelse($returns as $rma)
                <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:border-primary/20 transition-all duration-300 overflow-hidden relative">
                    <!-- Status Accent Line -->
                    <div class="absolute left-0 top-0 bottom-0 w-1 
                        {{ $rma->status === 'requested' ? 'bg-amber-400' : 
                          ($rma->status === 'approved' ? 'bg-blue-500' : 
                          ($rma->status === 'received' ? 'bg-purple-500' : 
                          ($rma->status === 'refunded' || $rma->status === 'completed' ? 'bg-emerald-500' : 
                          ($rma->status === 'rejected' ? 'bg-red-500' : 'bg-gray-300')))) }}"></div>

                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row gap-8">
                            <!-- Left: Info -->
                            <div class="flex-1 space-y-6">
                                <!-- Header -->
                                <div class="flex items-start justify-between">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-3">
                                            <h3 class="text-lg font-bold text-gray-900 tracking-tight">{{ $rma->rma_number }}</h3>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide
                                                {{ $rma->status === 'requested' ? 'bg-amber-50 text-amber-700' : 
                                                  ($rma->status === 'approved' ? 'bg-blue-50 text-blue-700' : 
                                                  ($rma->status === 'received' ? 'bg-purple-50 text-purple-700' : 
                                                  ($rma->status === 'refunded' || $rma->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 
                                                  ($rma->status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-gray-50 text-gray-700')))) }}">
                                                {{ ucfirst($rma->status) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500">Created on {{ $rma->created_at->format('M d, Y') }} • Order <a href="#" class="text-primary hover:underline font-medium">#{{ $rma->order->order_number ?? 'N/A' }}</a></p>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        @if($rma->status === 'requested')
                                            <a href="{{ route('central.returns.edit', $rma) }}" class="px-3 py-1.5 text-xs font-semibold bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">Edit</a>
                                        @elseif($rma->status === 'approved')
                                            <button @click="inspect(@js($rma))" class="px-3 py-1.5 text-xs font-bold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all flex items-center gap-1 shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                                Inspect & Receive
                                            </button>
                                        @endif
                                        <a href="{{ route('central.returns.show', $rma) }}" class="p-2 text-gray-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                        </a>
                                    </div>
                                </div>

                                <!-- Customer & Context -->
                                <div class="flex items-center gap-4 p-4 bg-gray-50/50 rounded-xl border border-gray-100/50">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center text-gray-600 font-bold text-sm">
                                        {{ substr($rma->order->customer->first_name ?? 'G', 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-900">{{ $rma->order->customer->first_name ?? 'Guest' }} {{ $rma->order->customer->last_name ?? '' }}</p>
                                        <p class="text-xs text-gray-500">{{ $rma->order->customer->email ?? '' }}</p>
                                    </div>
                                    <div class="text-right px-4 border-l border-gray-200">
                                        <p class="text-[10px] uppercase font-bold text-gray-400">Items</p>
                                        <p class="text-lg font-black text-gray-900 leading-none">{{ $rma->items->sum('quantity') }}</p>
                                    </div>
                                </div>

                                <!-- Grid of Products -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($rma->items->take(4) as $item)
                                        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                                            <div class="h-10 w-10 rounded-md bg-white border border-gray-100 overflow-hidden flex-shrink-0">
                                                @if($item->product && $item->product->image_url)
                                                    <img src="{{ $item->product->image_url }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-300">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $item->product->name ?? 'Unknown Item' }}</p>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs text-gray-500">Qty: {{ $item->quantity }}</span>
                                                    <span class="text-[10px] px-1.5 py-px rounded bg-gray-100 text-gray-600 font-medium">{{ ucfirst($item->condition) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($rma->items->count() > 4)
                                        <div class="flex items-center justify-center p-2 text-xs font-medium text-gray-500">
                                            +{{ $rma->items->count() - 4 }} more items
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Right: Lifecycle Stepper -->
                            <div class="lg:w-80 flex-shrink-0 bg-gray-50 rounded-xl p-6 border border-gray-100/80">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-200 pb-2">Workflow Progress</h4>
                                
                                <div class="relative pl-4 space-y-8 border-l-2 border-gray-200 ml-2">
                                    @php
                                        // Stepper Logic for Central Admin
                                        $steps = ['requested', 'approved', 'received', 'refunded', 'completed'];
                                        $isRejected = $rma->status === 'rejected';
                                        
                                        if ($isRejected) {
                                            $steps = ['requested', 'rejected'];
                                        }

                                        $currentIndex = array_search($rma->status, $steps);
                                        // Normalize 'completed' and 'refunded' as final stages if not explicitly in flow
                                        if ($rma->status === 'refunded' && !in_array('refunded', $steps)) $currentIndex = 3;
                                        if ($rma->status === 'completed' && !in_array('completed', $steps)) $currentIndex = 4;
                                        
                                        if ($currentIndex === false) $currentIndex = 0;
                                    @endphp

                                    @foreach($steps as $index => $step)
                                        @php
                                            $isCompleted = $index <= $currentIndex;
                                            $isActive = $index === $currentIndex;
                                            
                                            // Colors
                                            if ($step === 'rejected') {
                                                $colorClass = $isActive ? 'bg-red-500 ring-red-200 text-red-700' : 'bg-gray-200 text-gray-400';
                                            } elseif ($step === 'refunded' || $step === 'completed') {
                                                $colorClass = $isActive || $isCompleted ? 'bg-emerald-500 ring-emerald-200 text-emerald-700' : 'bg-gray-200 text-gray-400';
                                            } else {
                                                $colorClass = $isActive || $isCompleted ? 'bg-blue-500 ring-blue-200 text-blue-700' : 'bg-gray-200 text-gray-400';
                                            }
                                        @endphp

                                        <div class="relative flex flex-col gap-1">
                                            <!-- Dot -->
                                            <div class="absolute -left-[25px] top-1.5 w-4 h-4 rounded-full border-2 border-white shadow-sm ring-4 ring-transparent transition-all duration-300 z-10 
                                                {{ $isActive ? 'scale-110 ring-offset-2 ring-opacity-50 ' . str_replace('text-', 'ring-', $colorClass) : '' }} 
                                                {{ $isCompleted ? str_replace('text-', 'bg-', $colorClass) : 'bg-gray-200' }}"></div>
                                            
                                            <span class="text-sm font-bold uppercase tracking-wide {{ $isActive ? 'text-gray-900' : 'text-gray-400' }}">
                                                {{ ucfirst($step) }}
                                            </span>

                                            @if($isActive)
                                                <span class="text-xs text-gray-500 animate-pulse font-medium">Current Status</span>
                                            @endif
                                            
                                            @if($step === 'requested')
                                                 <p class="text-[10px] text-gray-400 line-clamp-1" title="{{ $rma->reason }}">{{ $rma->reason }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border border-dashed border-gray-300">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">No returns found</h3>
                    <p class="text-gray-500 text-sm">Or try adjusting your filters.</p>
                </div>
            @endforelse

            <div class="mt-8">
                {{ $returns->links() }}
            </div>
        </div>

        <!-- Inspection Modal -->
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
             x-show="showModal"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            
            <div x-show="showModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl border border-gray-100 overflow-hidden"
                 @click.away="closeModal()">
                
                <form :action="actionUrl" method="POST">
                    @csrf
                    <!-- Modal Header -->
                    <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M20 6 9 17l-5-5"/></svg>
                                Inspection Checklist
                            </h3>
                            <p class="text-sm text-gray-500 mt-1" x-text="'Processing ' + (currentReturn ? currentReturn.rma_number : '')"></p>
                        </div>
                        <button type="button" @click="closeModal()" class="p-2 hover:bg-gray-100 rounded-xl transition-colors text-gray-400 hover:text-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-8 py-6 max-h-[60vh] overflow-y-auto space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-4 rounded-2xl border transition-all duration-200" 
                                 :class="item.verified ? 'bg-blue-50/30 border-blue-200' : 'bg-white border-gray-100'">
                                <div class="flex flex-col sm:flex-row gap-4">
                                    <div class="flex items-start gap-4 flex-1">
                                        <!-- Checkbox -->
                                        <div class="pt-1">
                                            <input type="checkbox" x-model="item.verified" :name="'items['+index+'][verified]'" value="1"
                                                   class="h-6 w-6 rounded-lg border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer shadow-sm transition-all">
                                        </div>

                                        <!-- Product Info -->
                                        <div class="flex items-start gap-3 flex-1">
                                            <div class="h-12 w-12 rounded-xl bg-gray-50 overflow-hidden flex-shrink-0 border border-gray-100">
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
                                                <h5 class="text-sm font-black text-gray-900 leading-snug" x-text="item.name"></h5>
                                                <p class="text-xs text-gray-500 mt-0.5" x-text="'Requested Qty: ' + item.quantity"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Condition Selector -->
                                    <div class="w-full sm:w-40" x-show="item.verified" x-transition>
                                        <input type="hidden" :name="'items['+index+'][item_id]'" :value="item.item_id">
                                        <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1.5 px-1">Inspected Condition</label>
                                        <select :name="'items['+index+'][condition]'" x-model="item.condition"
                                                class="w-full h-10 bg-white border-gray-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 transition-all">
                                            <option value="sellable">Sellable (Restock)</option>
                                            <option value="damaged">Damaged (No Restock)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-8 py-6 border-t border-gray-50 bg-gray-50/50 flex flex-col sm:flex-row items-center gap-4">
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
                            <button type="button" @click="closeModal()" class="flex-1 sm:flex-none px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    :disabled="!canSubmit"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-3 bg-gray-900 text-white text-sm font-black rounded-2xl transition-all shadow-xl shadow-gray-900/10 hover:-translate-y-0.5 disabled:opacity-30 disabled:pointer-events-none disabled:grayscale">
                                <span>Complete Receipt</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection