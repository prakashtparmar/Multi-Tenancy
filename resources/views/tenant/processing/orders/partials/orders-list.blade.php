<div class="mt-4 mb-4">
    {{ $orders->withQueryString()->links() }}
</div>
<div class="grid grid-cols-1 gap-6">
    @forelse($orders as $order)
        <div class="group relative rounded-2xl border border-border/40 bg-white/70 backdrop-blur-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden">
            {{-- Card Header --}}
            <div class="p-5 md:p-6 border-b border-border/40 bg-gradient-to-r from-gray-50/50 to-transparent">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        {{-- Checkbox --}}
                        <div class="flex items-center h-10 w-10 rounded-lg bg-white shadow-sm border border-border/50 justify-center">
                            <input type="checkbox" value="{{ $order->id }}" x-model="selected" class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <div class="relative" x-data="{ copied: false }">
                                    <button @click="navigator.clipboard.writeText('{{ $order->order_number }}'); copied = true; setTimeout(() => copied = false, 2000)" class="text-lg font-bold text-gray-900 tracking-tight hover:text-blue-600 transition-colors flex items-center gap-2 group/copy" title="Click to copy">
                                        {{ $order->order_number }}
                                        <svg x-show="!copied" class="w-3.5 h-3.5 text-gray-400 group-hover/copy:text-blue-500 opacity-0 group-hover/copy:opacity-100 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        <svg x-show="copied" x-transition class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                    <span x-show="copied" x-transition class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-0.5 bg-gray-900 text-white text-[10px] rounded shadow-lg whitespace-nowrap z-10">Copied!</span>
                                </div>
                                {{-- Status Badge --}}
                                @php
                                    $statusColors = [
                                        'placed' => 'bg-gray-100 text-gray-700 border-gray-200',
                                        'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'processing' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'ready_to_ship' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'shipped' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'delivered' => 'bg-green-50 text-green-700 border-green-200',
                                        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                                    ];
                                    $colorClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider border {{ $colorClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-muted-foreground mt-1.5 font-medium">
                                <div class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    {{ $order->customer->name }}
                                </div>
                                <span class="w-1 h-1 rounded-full bg-border"></span>
                                <span>{{ $order->created_at->format('M d, Y • h:i A') }}</span>
                                <span class="w-1 h-1 rounded-full bg-border"></span>
                                <span class="text-foreground">{{ $order->warehouse->name ?? 'Main Warehouse' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-extrabold text-foreground tracking-tight">Rs {{ number_format($order->grand_total, 2) }}</p>
                        <p class="text-xs text-muted-foreground font-medium">{{ $order->items->count() }} items</p>
                    </div>
                </div>
            </div>

            {{-- Items Preview --}}
            <div class="px-6 py-4 bg-gray-50/30">
                <div class="flex items-center gap-4 overflow-x-auto no-scrollbar pb-2">
                    @foreach($order->items as $item)
                        <div class="group/item relative flex-shrink-0 w-16 h-16 rounded-lg bg-white border border-border/50 overflow-hidden shadow-sm" title="{{ $item->product_name }} (x{{ $item->quantity }})">
                            @if($item->product && $item->product->image_url)
                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover/item:scale-110">
                            @else
                                <div class="h-full w-full flex items-center justify-center bg-gray-100 text-gray-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            <div class="absolute bottom-0 right-0 bg-black/60 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-tl-md backdrop-blur-sm">
                                x{{ $item->quantity }}
                            </div>
                        </div>
                    @endforeach
                    @if($order->items->count() > 5)
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-white border border-border flex items-center justify-center text-xs font-bold text-muted-foreground shadow-sm">
                            +{{ $order->items->count() - 5 }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Dispatch Details --}}
            @if($order->shipments->isNotEmpty())
                <div class="px-6 py-3 border-t border-border/40 bg-blue-50/50 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-sm text-blue-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="font-semibold">Dispatch Info:</span>
                    </div>
                    <div class="flex flex-wrap gap-3 text-sm">
                        @foreach($order->shipments as $shipment)
                            <div class="flex items-center gap-2 bg-white px-3 py-1 rounded-full border border-blue-200/60 shadow-sm">
                                <span class="font-medium text-gray-600 text-xs uppercase tracking-wider">{{ $shipment->carrier }}:</span>
                                <span class="font-mono font-bold text-blue-700 select-all">{{ $shipment->tracking_number }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Lifecycle & Actions --}}
            <div class="px-6 py-5 border-t border-border/40 bg-white">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex-1 max-w-2xl">
                        <div class="relative flex justify-between items-center w-full">
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-gray-100 rounded-full z-0"></div>
                            @php
                                $statusOrder = ['placed', 'confirmed', 'processing', 'ready_to_ship', 'shipped', 'delivered'];
                                $orderStatus = ($order->status == 'completed') ? 'delivered' : $order->status;
                                $currentIdx = array_search($orderStatus, $statusOrder);
                                $currentIdx = $currentIdx === false ? -1 : $currentIdx; 
                            @endphp
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full z-0 transition-all duration-1000 ease-out" style="width: {{ max(0, min(100, $currentIdx * 20)) }}%"></div>

                            @foreach($statusOrder as $idx => $step)
                                @if($step === 'placed') @continue @endif
                                @php
                                    $isCompleted = $idx <= $currentIdx;
                                    $isCurrent = $idx === $currentIdx;
                                    $label = match ($step) {
                                        'confirmed' => 'Confirmed',
                                        'processing' => 'Processing',
                                        'ready_to_ship' => 'Ready',
                                        'shipped' => 'Dispatched',
                                        'delivered' => 'Delivered',
                                        default => ucfirst($step)
                                    };
                                @endphp
                                <div class="relative z-10 flex flex-col items-center group/step">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all duration-300 {{ $isCompleted ? 'bg-white border-indigo-600 text-indigo-600 shadow-md shadow-indigo-100' : 'bg-white border-gray-200 text-gray-300' }} {{ $isCurrent ? 'ring-4 ring-indigo-50 ring-offset-2 scale-110' : '' }}">
                                        @if($isCompleted)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        @else
                                            <span class="text-[10px] font-bold font-mono">{{ $idx }}</span>
                                        @endif
                                    </div>
                                    <span class="mt-2 text-[10px] font-bold uppercase tracking-wider transition-colors duration-300 {{ $isCompleted ? 'text-indigo-900' : 'text-gray-400' }}">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center gap-3 self-end lg:self-center">
                        @if(!$order->hasInvoice() && $order->status === 'ready_to_ship')
                            <span class="hidden md:inline-flex px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold border border-amber-100 items-center gap-1.5 animate-pulse">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Pending Invoice
                            </span>
                        @endif

                        @if($order->status === 'confirmed')
                            <button type="button" @click="$dispatch('open-process-modal', { orderId: '{{ $order->id }}' })" class="inline-flex items-center gap-2 px-5 py-2 bg-gray-900 text-white text-xs font-bold rounded-xl hover:bg-black transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                Start Processing
                            </button>
                        @endif

                        @if($order->status === 'processing')
                            <form action="{{ route('tenant.processing.orders.ready', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-xs font-bold rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all shadow-md shadow-purple-200 hover:shadow-lg hover:shadow-purple-300 transform hover:-translate-y-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                    Ready to Ship
                                </button>
                            </form>
                        @endif

                        @if($order->status === 'ready_to_ship')
                            <div class="flex items-center gap-2">
                                <div class="flex bg-gray-100 rounded-lg p-0.5">
                                    <a href="{{ $order->invoices->isNotEmpty() ? route('tenant.orders.invoice', $order) : '#' }}" target="{{ $order->invoices->isNotEmpty() ? '_blank' : '_self' }}" class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-white rounded-md transition-all shadow-sm" title="Print Invoice">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </a>
                                    <a href="{{ route('tenant.orders.receipt', $order) }}" target="_blank" class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-white rounded-md transition-all shadow-sm" title="Print COD Receipt">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </a>
                                </div>
                                <button type="button" @click="$dispatch('open-dispatch-modal', { orderId: '{{ $order->id }}', orderNumber: '{{ $order->order_number }}', actionUrl: '{{ route('tenant.processing.orders.dispatch', $order) }}' })" class="inline-flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-xs font-bold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all shadow-md shadow-emerald-200 hover:shadow-lg hover:shadow-emerald-300 transform hover:-translate-y-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    Dispatch
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed border-gray-200">
            <div class="w-20 h-20 mx-auto bg-gray-50 rounded-full flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">No orders found</h3>
            <p class="text-gray-500 mt-2 max-w-sm mx-auto">There are no orders matching your current criteria. Try adjusting your filters.</p>
            <a href="{{ route('tenant.processing.orders.index') }}" class="inline-flex items-center gap-2 mt-6 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                Clear Filters
            </a>
        </div>
    @endforelse
</div>
<div class="mt-8">
    {{ $orders->withQueryString()->links() }}
</div>
