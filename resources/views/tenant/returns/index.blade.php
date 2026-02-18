<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-3xl leading-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                    {{ __('Returns & RMA') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Manage customer return requests and simple refunds.</p>
            </div>
            @can('returns create')
            <a href="{{ route('central.returns.create') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 hover:bg-black text-white text-sm font-semibold rounded-xl shadow-lg shadow-gray-900/10 transition-all hover:-translate-y-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Create Return
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8 max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8 animate-in fade-in duration-500">
        
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
        <div class="bg-white/80 backdrop-blur-xl border border-white/20 rounded-2xl shadow-sm p-2 flex flex-col md:flex-row items-center justify-between gap-4 sticky top-4 z-30">
            <div class="flex bg-gray-100/80 p-1 rounded-xl overflow-x-auto no-scrollbar max-w-full">
                @php
                    $filters = [
                        ['label' => 'All', 'value' => null],
                        ['label' => 'Requested', 'value' => 'requested'],
                        ['label' => 'Approved', 'value' => 'approved'],
                        ['label' => 'Received', 'value' => 'received'],
                        ['label' => 'Refunded', 'value' => 'refunded'],
                        ['label' => 'Rejected', 'value' => 'rejected'],
                    ];
                @endphp
                @foreach($filters as $filter)
                    <a href="{{ route('tenant.returns.index', ['status' => $filter['value']]) }}" 
                       class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap {{ request('status') == $filter['value'] ? 'bg-white text-gray-900 shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-900 hover:bg-white/50' }}">
                        {{ $filter['label'] }}
                    </a>
                @endforeach
            </div>

            <form action="{{ url()->current() }}" method="GET" class="relative group w-full md:w-64">
                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search RMA, Order..." 
                       class="w-full h-10 pl-10 pr-4 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all">
            </form>
        </div>

        <!-- Content Area -->
        <div class="space-y-4">
            @forelse($returns as $rma)
                <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:border-indigo-100 transition-all duration-300 overflow-hidden relative">
                    <!-- Status Accent Line -->
                    <div class="absolute left-0 top-0 bottom-0 w-1 
                        {{ $rma->status === 'requested' ? 'bg-amber-400' : 
                          ($rma->status === 'approved' ? 'bg-blue-500' : 
                          ($rma->status === 'received' ? 'bg-purple-500' : 
                          ($rma->status === 'refunded' ? 'bg-emerald-500' : 
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
                                                  ($rma->status === 'refunded' ? 'bg-emerald-50 text-emerald-700' : 
                                                  ($rma->status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-gray-50 text-gray-700')))) }}">
                                                {{ ucfirst($rma->status) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500">Created on {{ $rma->created_at->format('M d, Y') }} • Order <a href="#" class="text-indigo-600 hover:underline font-medium">#{{ $rma->order->order_number ?? 'N/A' }}</a></p>
                                    </div>
                                    <a href="{{ route('tenant.returns.show', $rma) }}" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    </a>
                                </div>

                                <!-- Customer & Context -->
                                <div class="flex items-center gap-4 p-4 bg-gray-50/50 rounded-xl border border-gray-100/50">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center text-gray-600 font-bold text-sm">
                                        {{ substr($rma->customer->first_name ?? 'G', 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-900">{{ $rma->customer->first_name ?? 'Guest' }} {{ $rma->customer->last_name ?? '' }}</p>
                                        <p class="text-xs text-gray-500">{{ $rma->customer->email ?? '' }}</p>
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
                            <div class="lg:w-72 flex-shrink-0 bg-gray-50 rounded-xl p-6 border border-gray-100/80">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">Workflow Status</h4>
                                
                                <div class="relative pl-4 space-y-8 border-l-2 border-gray-200">
                                    @php
                                        // Stepper Logic
                                        $steps = ['requested', 'approved', 'received', 'refunded'];
                                        $isRejected = $rma->status === 'rejected';
                                        
                                        if ($isRejected) {
                                            $steps = ['requested', 'rejected'];
                                        }

                                        $currentIndex = array_search($rma->status, $steps);
                                        if ($currentIndex === false) $currentIndex = 0; // Default or fallback
                                    @endphp

                                    @foreach($steps as $index => $step)
                                        @php
                                            $isCompleted = $index <= $currentIndex;
                                            $isActive = $index === $currentIndex;
                                            $isLast = $loop->last;
                                            
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
                                            <div class="absolute -left-[21px] top-1.5 w-3 h-3 rounded-full border-2 border-white shadow-sm ring-4 ring-transparent transition-all duration-300 {{ $isActive ? 'scale-125 ring-offset-2 ring-opacity-50 ' . str_replace('text-', 'ring-', $colorClass) : '' }} {{ $isCompleted ? str_replace('text-', 'bg-', $colorClass) : 'bg-gray-200' }}"></div>
                                            
                                            <span class="text-sm font-bold {{ $isActive ? 'text-gray-900' : 'text-gray-500' }}">
                                                {{ ucfirst($step) }}
                                            </span>
                                            
                                            @if($isActive)
                                                <span class="text-xs text-gray-500 animate-pulse">Current Stage</span>
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
    </div>
</x-app-layout>
