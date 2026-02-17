@extends('layouts.app')

@section('content')
    <div class="flex flex-1 flex-col space-y-6 p-4 md:p-8 animate-in fade-in duration-500 bg-background/50">

        <!-- Header Area -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="space-y-2">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                        <!-- Order ID : -->
                        <span x-data="{ 
                            copying: false,
                            copy() {
                                navigator.clipboard.writeText('{{ $order->order_number }}');
                                this.copying = true;
                                setTimeout(() => this.copying = false, 2000);
                            }
                        }" 
                        @click="copy()"
                        class="cursor-pointer hover:text-indigo-600 transition-colors relative group"
                        title="Click to copy">
                            {{ $order->order_number }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block opacity-0 group-hover:opacity-100 transition-opacity ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                            <span x-show="copying" 
                                  x-transition:enter="transition ease-out duration-200"
                                  x-transition:enter-start="opacity-0 translate-y-1"
                                  x-transition:enter-end="opacity-100 translate-y-0"
                                  x-transition:leave="transition ease-in duration-150"
                                  x-transition:leave-start="opacity-100 translate-y-0"
                                  x-transition:leave-end="opacity-0 translate-y-1"
                                  class="absolute -bottom-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] py-1 px-2 rounded font-bold whitespace-nowrap z-50">
                                Copied!
                            </span>
                        </span>
                    </h1>
                    @php
                        $statusColors = match ($order->status) {
                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'shipped' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            'in_transit' => 'bg-orange-50 text-orange-700 border-orange-200',
                            'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                            'processing' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'ready_to_ship' => 'bg-sky-50 text-sky-700 border-sky-200',
                            'delivered' => 'bg-green-50 text-green-700 border-green-200',
                            'returned' => 'bg-rose-50 text-rose-700 border-rose-200',
                            'scheduled' => 'bg-violet-50 text-violet-700 border-violet-200',
                            default => 'bg-amber-50 text-amber-700 border-amber-200',
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border {{ $statusColors }}">
                        {{ str_replace('_', ' ', $order->status) }}
                    </span>
                </div>
                <p class="text-gray-500 text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Placed on {{ $order->placed_at->format('F d, Y \a\t h:i A') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">

                {{-- Sequential Lifecycle Actions --}}
                <div class="flex flex-wrap items-center gap-2 bg-gray-50 p-2 rounded-xl border border-gray-100">

                    {{-- 1. Confirm --}}
                    @php 
                                                                                                                                                                        $isPending = in_array($order->status, ['pending', 'draft', 'scheduled']);
                        $isConfirmedOrLater = in_array($order->status, ['confirmed', 'processing', 'ready_to_ship', 'shipped', 'in_transit', 'delivered', 'completed', 'returned']);
                    @endphp
                    @if($isPending)
                        @can('orders approve')
                            <form action="{{ route('central.orders.update-status', $order) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="confirm">
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-all">
                                    Confirm
                                </button>
                            </form>
                        @else
                            <button disabled class="inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">Confirm</button>
                        @endcan
                    @elseif($isConfirmedOrLater)
                        <button disabled class="inline-flex items-center px-3 py-1.5 border border-emerald-200 shadow-sm text-xs font-bold rounded-lg text-emerald-600 bg-emerald-50 cursor-not-allowed opacity-80">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Confirmed
                        </button>
                    @else
                        <button disabled class="inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">Confirm</button>
                    @endif

                    <!-- Arrow -->
                    <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>

                    {{-- 2. Process --}}
                    @php 
                        $isProcessingOrLater = in_array($order->status, ['processing', 'ready_to_ship', 'shipped', 'in_transit', 'delivered', 'completed', 'returned']);
                    @endphp
                    @if($order->status === 'confirmed')
                        @can('orders process')
                            <form action="{{ route('central.orders.update-status', $order) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="process">
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-all">
                                    Process
                                </button>
                            </form>
                        @else
                            <button disabled class="inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">Process</button>
                        @endcan
                    @elseif($isProcessingOrLater)
                        <button disabled class="inline-flex items-center px-3 py-1.5 border border-emerald-200 shadow-sm text-xs font-bold rounded-lg text-emerald-600 bg-emerald-50 cursor-not-allowed opacity-80">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Processed
                        </button>
                    @else
                         <button disabled class="inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">Process</button>
                    @endif

                    <!-- Arrow -->
                    <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>

                    {{-- 3. Ready to Ship --}}
                    @php 
                         $isReadyOrLater = in_array($order->status, ['ready_to_ship', 'shipped', 'in_transit', 'delivered', 'completed', 'returned']);
                    @endphp
                    @if($order->status === 'processing')
                        @can('orders process')
                            <form action="{{ route('central.orders.update-status', $order) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="ready_to_ship">
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-all">
                                    Ready To Ship
                                </button>
                            </form>
                        @else
                            <button disabled class="inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">Ready To Ship</button>
                        @endcan
                    @elseif($isReadyOrLater)
                         <button disabled class="inline-flex items-center px-3 py-1.5 border border-emerald-200 shadow-sm text-xs font-bold rounded-lg text-emerald-600 bg-emerald-50 cursor-not-allowed opacity-80">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Ready
                        </button>
                     @else
                         <button disabled class="inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">Ready To Ship</button>
                    @endif

                    <!-- Arrow -->
                    <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>

                    {{-- 4. Ship --}}
                    @php 
                         $isShippedOrLater = in_array($order->status, ['shipped', 'in_transit', 'delivered', 'completed', 'returned']);
                    @endphp
                    @if($order->status === 'ready_to_ship' && $order->invoices->isNotEmpty())
                        @can('orders ship')
                            <button onclick="document.getElementById('ship-dialog').showModal()" class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-all">
                                Dispatch
                            </button>
                        @else
                             <button disabled class="inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">Dispatch</button>
                        @endcan
                     @elseif($isShippedOrLater)
                        <button disabled class="inline-flex items-center px-3 py-1.5 border border-emerald-200 shadow-sm text-xs font-bold rounded-lg text-emerald-600 bg-emerald-50 cursor-not-allowed opacity-80">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Dispatched
                        </button>
                     @else
                         <button disabled class="inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">Dispatch</button>
                    @endif

                    <!-- Arrow -->
                    <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>

                    {{-- 5. Deliver --}}
                    @php 
                         $isDeliveredOrLater = in_array($order->status, ['delivered', 'completed', 'returned']);
                    @endphp
                    @if(in_array($order->status, ['shipped', 'in_transit']) || $order->shipping_status === 'shipped')
                        @can('orders deliver')
                            <form action="{{ route('central.orders.update-status', $order) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="deliver">
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs font-bold rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none transition-all">
                                    Deliver
                                </button>
                            </form>
                        @else
                            <button disabled class="inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">Deliver</button>
                        @endcan
                    @elseif($isDeliveredOrLater)
                         <button disabled class="inline-flex items-center px-3 py-1.5 border border-emerald-200 shadow-sm text-xs font-bold rounded-lg text-emerald-600 bg-emerald-50 cursor-not-allowed opacity-80">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Delivered
                        </button>
                    @else
                         <button disabled class="inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">Deliver</button>
                    @endif

                    {{-- Cancel (Separate) --}}
                     @if(!in_array($order->status, ['completed', 'cancelled', 'returned']))
                        @can('orders cancel')
                            <form action="{{ route('central.orders.update-status', $order) }}" method="POST"
                                onsubmit="return confirm('Are you sure?');" class="ml-2 pl-2 border-l border-gray-200">
                                @csrf
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" title="Cancel Order"
                                    class="text-red-500 hover:text-red-700 transition p-1 rounded-md hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </form>
                        @endcan
                    @endif

                </div>

                <div class="h-6 w-px bg-gray-200 mx-2"></div>

                {{-- Edit --}}
                @can('orders edit')
                    @if(!in_array($order->status, ['confirmed', 'completed', 'cancelled', 'returned']))
                        <a href="{{ route('central.orders.edit', $order) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-all gap-1">
                            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            Edit
                        </a>
                    @endif
                @endcan

                {{-- Print Actions --}}
                <div class="flex items-center gap-2">
                    @if($order->invoices->isNotEmpty())
                        @php $invoice = $order->invoices->first(); @endphp
                        @can('invoices view')
                            <a href="{{ route('central.invoices.pdf', $invoice) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-all gap-1" title="Print Invoice">
                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Invoice
                            </a>
                        @endcan
                    @endif

                    @can('orders-receipt view')
                        <a href="{{ route('central.orders.receipt', $order) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-all gap-1" title="Print Receipt">
                            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Receipt
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Order Progress Stepper -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <h3 class="text-base font-bold text-gray-900 mb-8 flex items-center gap-2">
                 <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Order Tracking
            </h3>
            <div class="relative flex justify-between items-center w-full px-4">
                <div class="absolute left-0 top-5 w-full h-1.5 bg-gray-100 rounded-full z-0"></div>
                @php
                    $progress = match ($order->status) {
                        'confirmed' => '20%',
                        'processing' => '40%',
                        'ready_to_ship' => '60%',
                        'shipped', 'in_transit' => '80%',
                        'delivered', 'completed' => '100%',
                        default => '0%',
                    };
                 @endphp
                <div class="absolute left-0 top-5 h-1.5 bg-indigo-600 rounded-full z-0 transition-all duration-1000 ease-out shadow-sm" style="width: {{ $progress }}"></div>

                <div class="relative z-10 flex justify-between w-full">
                    <!-- Placed -->
                    <div class="flex flex-col items-center group">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-indigo-600 text-white shadow-lg shadow-indigo-200 ring-4 ring-white transition-transform group-hover:scale-110">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div class="text-center mt-3">
                            <span class="text-sm font-bold text-gray-900 block">Placed</span>
                            <span class="text-xs text-gray-400 font-medium">{{ $order->created_at->format('M d, H:i') }}</span>
                        </div>
                    </div>

                    <!-- Processing -->
                    @php $isProc = in_array($order->status, ['confirmed', 'processing', 'ready_to_ship', 'shipped', 'in_transit', 'delivered', 'completed']); @endphp
                    <div class="flex flex-col items-center group">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $isProc ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white border-2 border-gray-200 text-gray-300' }} ring-4 ring-white transition-all duration-500 group-hover:scale-110">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        </div>
                        <span class="mt-3 text-sm font-bold {{ $isProc ? 'text-gray-900' : 'text-gray-400' }}">Processing</span>
                    </div>

                    <!-- Shipped -->
                    @php $isShip = in_array($order->status, ['shipped', 'in_transit', 'delivered', 'completed']); @endphp
                    <div class="flex flex-col items-center group">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $isShip ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white border-2 border-gray-200 text-gray-300' }} ring-4 ring-white transition-all duration-500 group-hover:scale-110">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                        </div>
                        <span class="mt-3 text-sm font-bold {{ $isShip ? 'text-gray-900' : 'text-gray-400' }}">Dispatched</span>
                    </div>

                    <!-- Delivered -->
                    @php $isDone = in_array($order->status, ['delivered', 'completed']); @endphp
                    <div class="flex flex-col items-center group">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $isDone ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white border-2 border-gray-200 text-gray-300' }} ring-4 ring-white transition-all duration-500 group-hover:scale-110">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="mt-3 text-sm font-bold {{ $isDone ? 'text-gray-900' : 'text-gray-400' }}">Delivered</span>
                    </div>
                </div>
            </div>

            @if($order->shipments->isNotEmpty())
                <div class="mt-10 bg-indigo-50/50 rounded-xl border border-indigo-100 p-6 flex flex-wrap gap-12 items-center">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white rounded-lg text-indigo-600 shadow-sm border border-indigo-50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Carrier</p>
                            <p class="text-base font-bold text-gray-900">{{ $order->shipments->first()->carrier ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                         <div class="p-3 bg-white rounded-lg text-indigo-600 shadow-sm border border-indigo-50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Tracking ID</p>
                            <p class="text-base font-mono font-bold text-indigo-600">{{ $order->shipments->first()->tracking_number ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white rounded-lg text-indigo-600 shadow-sm border border-indigo-50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Dispatched At</p>
                            <p class="text-base font-bold text-gray-900">{{ $order->shipments->first()->shipped_at ? $order->shipments->first()->shipped_at->format('M d, Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Column: Items & Summary -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Items Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-900">Order Items</h3>
                        <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">{{ $order->items->count() }} items</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-xs uppercase font-bold text-gray-500 tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Product</th>
                                    <th class="px-6 py-4 text-right">Price</th>
                                    <th class="px-6 py-4 text-center">Qty</th>
                                    <th class="px-6 py-4 text-center">Tax</th>
                                    <th class="px-6 py-4 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($order->items as $item)
                                    @php
                                        $baseTotal = $item->unit_price * $item->quantity;
                                        $taxAmount = ($baseTotal * ($item->tax_percent ?? 0)) / 100;
                                        $lineTotal = $baseTotal + $taxAmount - ($item->discount_amount ?? 0);
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <p class="font-bold text-gray-900">{{ $item->product_name }}</p>
                                            <p class="text-xs text-gray-500 mt-1 font-mono">{{ $item->sku }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-600">
                                            Rs {{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 text-xs font-bold">
                                                {{ $item->quantity }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex flex-col items-center">
                                                <span class="text-xs font-semibold text-gray-700">{{ $item->tax_percent > 0 ? (float) $item->tax_percent . '%' : '-' }}</span>
                                                @if($taxAmount > 0)
                                                    <span class="text-[10px] text-gray-400">Rs {{ number_format($taxAmount, 2) }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-900">
                                            Rs {{ number_format($lineTotal, 2) }}
                                            @if($item->discount_amount > 0)
                                                <div class="text-[10px] font-normal text-green-600">
                                                    - Rs {{ number_format($item->discount_amount, 2) }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tax Summary (Premium Style with Bifurcation) -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 4h6m-9 3h9M3 3h18a2 2 0 012 2v14a2 2 0 01-2 2H3a2 2 0 01-2-2V5a2 2 0 012-2z"></path></svg>
                        Payment Summary
                    </h3>

                    <div class="space-y-3">
                        <!-- Base Amount -->
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal (Taxable Value)</span>
                            <span class="font-medium text-gray-900">Rs {{ number_format((float) $order->total_amount, 2) }}</span>
                        </div>

                        <!-- Discount -->
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-sm text-emerald-600">
                                <span>Discount</span>
                                <span class="font-medium">- Rs {{ number_format((float) $order->discount_amount, 2) }}</span>
                            </div>
                        @endif

                        <!-- Shipping -->
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Shipping</span>
                            <span class="font-medium text-gray-900">Rs {{ number_format((float) ($order->shipping_amount ?? 0), 2) }}</span>
                        </div>

                        <!-- Tax Bifurcation -->
                        @php
                            $totalTax = (float) ($order->tax_amount ?? 0);
                            $cgst = $totalTax / 2;
                            $sgst = $totalTax / 2;
                        @endphp

                        <div class="my-4 p-4 bg-gray-50 rounded-xl border border-gray-100 space-y-2">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">CGST (Central Tax)</span>
                                <span class="font-medium text-gray-900">Rs {{ number_format($cgst, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">SGST (State Tax)</span>
                                <span class="font-medium text-gray-900">Rs {{ number_format($sgst, 2) }}</span>
                            </div>
                            <div class="border-t border-gray-200 pt-2 flex justify-between text-sm font-semibold text-gray-700">
                                <span>Total Tax</span>
                                <span>Rs {{ number_format($totalTax, 2) }}</span>
                            </div>
                        </div>

                        <div class="h-px bg-gray-100 my-4"></div>

                        <!-- Grand Total -->
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-gray-900">Grand Total</span>
                            <span class="text-2xl font-bold text-indigo-600">Rs {{ number_format((float) $order->grand_total, 2) }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Customer & Info -->
            <div class="space-y-6">

                <!-- Customer Card -->
                <div class="rounded-2xl border border-white/20 bg-white/40 dark:bg-black/20 backdrop-blur-xl shadow-sm p-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div
                            class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-lg font-bold text-white shadow-md">
                            {{ substr($order->customer->name ?? 'G', 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-tight">{{ $order->customer->name }}</h3>
                            <p class="text-xs text-muted-foreground">Customer</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <a href="mailto:{{ $order->customer->email }}" class="flex items-center gap-3 text-sm group">
                            <div
                                class="h-8 w-8 rounded-lg bg-background flex items-center justify-center text-muted-foreground group-hover:text-primary transition-colors border border-border">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="20" height="16" x="2" y="4" rx="2" />
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                </svg>
                            </div>
                            <span class="truncate">{{ $order->customer->email }}</span>
                        </a>

                        <div class="flex items-center gap-3 text-sm">
                            <div
                                class="h-8 w-8 rounded-lg bg-background flex items-center justify-center text-muted-foreground border border-border">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                </svg>
                            </div>
                            <span>{{ $order->customer->mobile ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Addresses -->
                <div class="grid grid-cols-1 gap-6">
                     <!-- Shipping Address -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 relative overflow-hidden group hover:shadow-md transition-all">
                         <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2 relative z-10">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Shipping Address
                        </h3>
                         @if($order->shippingAddress)
                             <div class="space-y-3 relative z-10">
                                <div class="font-bold text-gray-900 text-sm border-b border-gray-100 pb-2 mb-2">{{ $order->shippingAddress->contact_name ?? $order->customer->name }}</div>

                                <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                                     <div class="col-span-2">
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">Address</span>
                                         <span class="text-gray-700 font-medium">
                                            {{ $order->shippingAddress->address_line1 }}
                                            @if($order->shippingAddress->address_line2)
                                                , {{ $order->shippingAddress->address_line2 }}
                                            @endif
                                         </span>
                                    </div>

                                    <div>
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">Village</span>
                                         <span class="text-gray-700 font-medium">{{ $order->shippingAddress->village ?? '-' }}</span>
                                    </div>

                                    <div>
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">Post Office</span>
                                         <span class="text-gray-700 font-medium">{{ $order->shippingAddress->post_office ?? '-' }}</span>
                                    </div>

                                    <div>
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">Taluka</span>
                                         <span class="text-gray-700 font-medium">{{ $order->shippingAddress->taluka ?? '-' }}</span>
                                    </div>

                                    <div>
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">District</span>
                                         <span class="text-gray-700 font-medium">{{ $order->shippingAddress->district ?? '-' }}</span>
                                    </div>

                                    <div>
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">State</span>
                                         <span class="text-gray-700 font-medium">{{ $order->shippingAddress->state ?? '-' }}</span>
                                    </div>

                                    <div>
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">Pincode</span>
                                         <span class="text-gray-700 font-medium font-mono">{{ $order->shippingAddress->pincode ?? '-' }}</span>
                                    </div>
                                </div>

                                @if($order->shippingAddress->contact_phone)
                                    <div class="pt-2 mt-1 border-t border-gray-100">
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">Phone</span>
                                        <p class="text-indigo-600 font-medium flex items-center gap-1 text-xs">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            {{ $order->shippingAddress->contact_phone }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-gray-400 italic relative z-10 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8"/><path d="M10 9l-1-2-1 2"/><path d="M14 9l1-2 1 2"/><path d="M3 21h18"/></svg>
                                Warehouse Pickup: {{ $order->warehouse->name }}
                            </p>
                        @endif
                    </div>

                    <!-- Billing Address -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 relative overflow-hidden group hover:shadow-md transition-all">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-gray-50 to-gray-100 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2 relative z-10">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            Billing Address
                        </h3>
                        @if($order->billingAddress)
                             <div class="space-y-3 relative z-10">
                                <div class="font-bold text-gray-900 text-sm border-b border-gray-100 pb-2 mb-2">{{ $order->billingAddress->contact_name ?? $order->customer->name }}</div>

                                <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                                     <div class="col-span-2">
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">Address</span>
                                         <span class="text-gray-700 font-medium">
                                            {{ $order->billingAddress->address_line1 }}
                                            @if($order->billingAddress->address_line2)
                                                , {{ $order->billingAddress->address_line2 }}
                                            @endif
                                         </span>
                                    </div>

                                    <div>
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">Village</span>
                                         <span class="text-gray-700 font-medium">{{ $order->billingAddress->village ?? '-' }}</span>
                                    </div>

                                    <div>
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">Post Office</span>
                                         <span class="text-gray-700 font-medium">{{ $order->billingAddress->post_office ?? '-' }}</span>
                                    </div>

                                    <div>
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">Taluka</span>
                                         <span class="text-gray-700 font-medium">{{ $order->billingAddress->taluka ?? '-' }}</span>
                                    </div>

                                    <div>
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">District</span>
                                         <span class="text-gray-700 font-medium">{{ $order->billingAddress->district ?? '-' }}</span>
                                    </div>

                                    <div>
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">State</span>
                                         <span class="text-gray-700 font-medium">{{ $order->billingAddress->state ?? '-' }}</span>
                                    </div>

                                    <div>
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">Pincode</span>
                                         <span class="text-gray-700 font-medium font-mono">{{ $order->billingAddress->pincode ?? '-' }}</span>
                                    </div>
                                </div>

                                @if($order->billingAddress->contact_phone)
                                    <div class="pt-2 mt-1 border-t border-gray-100">
                                         <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block mb-0.5">Phone</span>
                                        <p class="text-indigo-600 font-medium flex items-center gap-1 text-xs">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            {{ $order->billingAddress->contact_phone }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-gray-400 italic relative z-10">Same as Shipping Address</p>
                        @endif
                    </div>
                </div>

                <!-- Order Heritage (Timeline) -->
                 <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Order History
                    </h3>
                    <div class="relative pl-4 border-l-2 border-gray-100 space-y-6">
                        <!-- Created -->
                         <div class="relative">
                            <div class="absolute -left-[21px] top-1 h-3 w-3 rounded-full bg-gray-200 border-2 border-white ring-1 ring-gray-100"></div>
                            <p class="text-xs text-gray-500 font-mono">{{ $order->created_at->format('M d, Y H:i') }}</p>
                            <p class="text-sm font-bold text-gray-900">Order Placed</p>
                             <p class="text-[10px] text-gray-400 mt-0.5">by {{ $order->creator?->name ?? 'System' }}</p>
                        </div>

                        <!-- Updated -->
                        @if($order->updated_at->ne($order->created_at))
                             <div class="relative">
                                <div class="absolute -left-[21px] top-1 h-3 w-3 rounded-full bg-indigo-500 border-2 border-white ring-1 ring-indigo-100"></div>
                                <p class="text-xs text-gray-500 font-mono">{{ $order->updated_at->format('M d, Y H:i') }}</p>
                                <p class="text-sm font-bold text-gray-900">Last Updated</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">by {{ $order->updater?->name ?? 'System' }}</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Ship Dialog (Hidden) -->
    <dialog id="ship-dialog"
        class="p-0 rounded-2xl shadow-2xl backdrop:bg-black/50 w-full max-w-md bg-white border border-gray-100">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Dispatch Order</h3>
                <button onclick="document.getElementById('ship-dialog').close()"
                    class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form action="{{ route('central.orders.update-status', $order) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="action" value="ship">

                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Courier / Carrier</label>
                    <input type="text" name="carrier"
                        class="flex h-10 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="e.g. FedEx, BlueDart">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Tracking Number</label>
                    <input type="text" name="tracking_number"
                        class="flex h-10 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="Tracking Scan ID">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('ship-dialog').close()"
                        class="px-4 py-2 text-sm font-semibold text-gray-500 hover:text-gray-700 transition">Cancel</button>
                    <button type="submit"
                        class="px-6 py-2 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">Confirm
                        Dispatch</button>
                </div>
            </form>
        </div>
    </dialog>

@endsection