<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            {{ __('Order Details') }} - 
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
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Top Info Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Customer Card -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start gap-4 transition-shadow hover:shadow-md">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-lg font-bold shadow-md shrink-0">
                                {{ substr($order->customer->first_name ?? 'C', 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base font-bold text-gray-900 mb-1">Customer Details</h3>
                                <p class="text-lg font-semibold text-gray-800">{{ $order->customer->first_name }} {{ $order->customer->last_name }}</p>
                                <div class="mt-3 space-y-2">
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        {{ $order->customer->email }}
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        {{ $order->customer->mobile ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Info Card -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 transition-shadow hover:shadow-md">
                            <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                Order Information
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs uppercase tracking-wider text-gray-500 font-medium mb-1">Order Date</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $order->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $order->created_at->format('h:i A') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wider text-gray-500 font-medium mb-1">Current Status</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <div class="col-span-2 pt-2 border-t border-gray-50">
                                    <p class="text-xs uppercase tracking-wider text-gray-500 font-medium mb-1">Source Warehouse</p>
                                    <p class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        {{ $order->warehouse->name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Addresses -->
                    <!-- Addresses -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Billing Address -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 transition-shadow hover:shadow-md h-full relative overflow-hidden group">
                            <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4 flex items-center gap-2 relative z-10">
                                <div class="p-1.5 rounded-md bg-indigo-50 text-indigo-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                Billing Address
                            </h3>
                            @if($order->billingAddress)
                                <div class="space-y-3 relative z-10">
                                    <div class="font-bold text-gray-900 text-sm border-b border-gray-100 pb-2 mb-2">{{ $order->billingAddress->contact_name ?? $order->customer->first_name . ' ' . $order->customer->last_name }}</div>

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
                                <div class="h-full flex items-center justify-center text-gray-400 italic text-sm py-4 bg-gray-50/50 rounded-lg">
                                    No billing address available.
                                </div>
                            @endif
                        </div>

                        <!-- Shipping Address -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 transition-shadow hover:shadow-md h-full relative overflow-hidden group">
                             <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                             <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4 flex items-center gap-2 relative z-10">
                                <div class="p-1.5 rounded-md bg-emerald-50 text-emerald-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
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
                                <div class="h-full flex flex-col justify-center items-center text-sm text-gray-500 italic bg-gray-50/50 rounded-lg py-6">
                                    @if($order->warehouse)
                                        <div class="flex items-center gap-2 mb-1 text-gray-700 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            Warehouse Pickup
                                        </div>
                                        <span class="text-xs">{{ $order->warehouse->name }}</span>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                            Same as Billing Address
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Order Progress Stepper -->
                    <div class="mb-10 p-8 bg-white rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="text-base font-bold text-gray-900 mb-8 flex items-center gap-2">
                             <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            Order Tracking
                        </h3>
                        <div class="relative flex justify-between items-center w-full px-4">
                            <div class="absolute left-0 top-5 w-full h-1.5 bg-gray-100 rounded-full z-0"></div>
                            <div class="absolute left-0 top-5 h-1.5 bg-indigo-600 rounded-full z-0 transition-all duration-1000 ease-out shadow-sm" style="width: {{ $order->status === 'delivered' ? '100%' : ($order->status === 'shipped' ? '66%' : ($order->status === 'confirmed' || $order->status === 'processing' ? '33%' : '0%')) }}"></div>

                            <!-- Step 1: Placed -->
                            <div class="relative z-10 flex flex-col items-center group">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center bg-indigo-600 text-white shadow-lg shadow-indigo-200 ring-4 ring-white transition-transform group-hover:scale-110">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <span class="mt-3 text-sm font-bold text-gray-900">Placed</span>
                                <span class="text-xs text-gray-400 font-medium">{{ $order->created_at->format('M d, h:i A') }}</span>
                            </div>

                            <!-- Step 2: Confirmed -->
                            @php $isConfirmed = in_array($order->status, ['confirmed', 'processing', 'shipped', 'delivered', 'completed']); @endphp
                            <div class="relative z-10 flex flex-col items-center group">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $isConfirmed ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white border-2 border-gray-200 text-gray-300' }} ring-4 ring-white transition-all duration-500 group-hover:scale-110">
                                    @if($isConfirmed)
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        <span class="text-lg font-bold">2</span>
                                    @endif
                                </div>
                                <span class="mt-3 text-sm font-bold {{ $isConfirmed ? 'text-gray-900' : 'text-gray-400' }}">Confirmed</span>
                            </div>

                            <!-- Step 3: Shipped -->
                            @php $isShipped = in_array($order->status, ['shipped', 'delivered', 'completed']); @endphp
                            <div class="relative z-10 flex flex-col items-center group">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $isShipped ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white border-2 border-gray-200 text-gray-300' }} ring-4 ring-white transition-all duration-500 group-hover:scale-110">
                                    @if($isShipped)
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        <span class="text-lg font-bold">3</span>
                                    @endif
                                </div>
                                <span class="mt-3 text-sm font-bold {{ $isShipped ? 'text-gray-900' : 'text-gray-400' }}">Shipped</span>
                                @if($isShipped && $order->shipments->isNotEmpty())
                                    <span class="text-xs text-gray-400 font-medium">{{ $order->shipments->first()->shipped_at ? $order->shipments->first()->shipped_at->format('M d') : '' }}</span>
                                @endif
                            </div>

                            <!-- Step 4: Delivered -->
                            @php $isDelivered = in_array($order->status, ['delivered', 'completed']); @endphp
                            <div class="relative z-10 flex flex-col items-center group">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $isDelivered ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white border-2 border-gray-200 text-gray-300' }} ring-4 ring-white transition-all duration-500 group-hover:scale-110">
                                    @if($isDelivered)
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        <span class="text-lg font-bold">4</span>
                                    @endif
                                </div>
                                <span class="mt-3 text-sm font-bold {{ $isDelivered ? 'text-gray-900' : 'text-gray-400' }}">Delivered</span>
                            </div>
                        </div>

                        <!-- Tracking Info Box -->
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
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Shipped At</p>
                                        <p class="text-base font-bold text-gray-900">{{ $order->shipments->first()->shipped_at ? $order->shipments->first()->shipped_at->format('M d, Y h:i A') : 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <!-- Order Items Table -->
                    <div class="border border-gray-100 rounded-xl overflow-hidden mb-10 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Discount</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($order->items as $item)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $item->product->name ?? 'Product #' . $item->product_id }}</div>
                                                    <div class="text-xs text-gray-500">ID: {{ $item->product_id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-600 font-medium">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap text-sm text-gray-600">Rs {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap text-sm">
                                            @if($item->discount_amount > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700">
                                                    - Rs {{ number_format($item->discount_amount, 2) }}
                                                    @if($item->discount_type == 'percent')
                                                        <span class="ml-1 opacity-75">({{ $item->discount_value }}%)</span>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-bold text-gray-900">
                                            Rs {{ number_format(($item->quantity * $item->unit_price) - $item->discount_amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50/50">
                                <tr>
                                    <td colspan="5" class="px-6 py-4">
                                        <div class="flex flex-col items-end space-y-3">
                                            <div class="flex justify-between w-full max-w-xs text-sm">
                                                <span class="text-gray-500 font-medium">Subtotal</span>
                                                <span class="text-gray-900 font-bold">Rs {{ number_format((float) $order->total_amount, 2) }}</span>
                                            </div>
                                            
                                            @php 
                                                                                                                                                                                                $itemDiscounts = $order->items->sum('discount_amount');
                                                $orderDiscount = $order->discount_amount - $itemDiscounts;
                                            @endphp

                                            @if($itemDiscounts > 0)
                                                <div class="flex justify-between w-full max-w-xs text-sm">
                                                    <span class="text-green-600 font-medium">Item Discounts</span>
                                                    <span class="text-green-600 font-bold">- Rs {{ number_format((float) $itemDiscounts, 2) }}</span>
                                                </div>
                                            @endif

                                            @if($orderDiscount > 0)
                                                <div class="flex justify-between w-full max-w-xs text-sm">
                                                    <span class="text-green-600 font-medium">
                                                        Order Discount 
                                                        @if($order->discount_type == 'percent')
                                                            <span class="text-xs">({{ $order->discount_value }}%)</span>
                                                        @endif
                                                    </span>
                                                    <span class="text-green-600 font-bold">- Rs {{ number_format((float) $orderDiscount, 2) }}</span>
                                                </div>
                                            @endif

                                            <div class="w-full max-w-xs border-t border-gray-200 mt-2 pt-3">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-base font-bold text-gray-900">Grand Total</span>
                                                    <span class="text-xl font-bold text-indigo-600">Rs {{ number_format((float) $order->grand_total, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col md:flex-row justify-between items-center gap-6 mt-8 p-6 bg-gray-50/50 rounded-2xl border border-gray-100" x-data="{ showShipModal: false }">
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <a href="{{ route('tenant.orders.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-all">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Back to List
                            </a>
                            
                            @can('orders edit')
                                @if(!in_array($order->status, ['confirmed', 'completed', 'delivered', 'cancelled', 'returned']))
                                    <a href="{{ route('tenant.orders.edit', $order) }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none transition-all">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Edit Order
                                    </a>
                                @endif
                            @endcan

                            @if(in_array($order->status, ['completed', 'delivered']))
                                 <a href="{{ route('tenant.returns.create', ['order_id' => $order->id]) }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-orange-700 bg-orange-50 hover:bg-orange-100 focus:outline-none transition-all">
                                    Request Return
                                </a>
                            @endif
                            
                            <!-- Print Actions -->
                            <div class="flex items-center gap-2 border-l border-gray-200 pl-3">
                                <a href="{{ route('tenant.orders.invoice', $order) }}" target="_blank" class="text-gray-400 hover:text-gray-600 transition p-2 hover:bg-gray-100 rounded-lg" title="Print Invoice">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                </a>
                                <a href="{{ route('tenant.orders.receipt', $order) }}" target="_blank" class="text-gray-400 hover:text-gray-600 transition p-2 hover:bg-gray-100 rounded-lg" title="Print COD Receipt">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </a>
                            </div>
                        </div>

                        <!-- Sequential Lifecycle Actions -->
                        <div class="flex flex-wrap items-center gap-3">
                            
                            {{-- 1. Confirm --}}
                            @php
                                $isPending = $order->status === 'pending';
                                $isConfirmedOrLater = in_array($order->status, ['confirmed', 'processing', 'shipped', 'delivered', 'completed', 'returned']);
                            @endphp
                            @if($isPending)
                                @can('orders approve')
                                    <form action="{{ route('tenant.orders.status', $order) }}" method="POST" onsubmit="return confirm('Confirm this order?')">
                                        @csrf
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                                            Confirm
                                        </button>
                                    </form>
                                @else
                                    <button disabled class="inline-flex items-center px-4 py-2 border border-gray-200 shadow-sm text-sm font-medium rounded-lg text-gray-400 bg-gray-50 cursor-not-allowed">Confirm</button>
                                @endcan
                            @elseif($isConfirmedOrLater)
                                <button disabled class="inline-flex items-center px-4 py-2 border border-emerald-200 shadow-sm text-sm font-medium rounded-lg text-emerald-600 bg-emerald-50 cursor-not-allowed opacity-80">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Confirmed
                                </button>
                            @else
                                <button disabled class="inline-flex items-center px-4 py-2 border border-gray-200 shadow-sm text-sm font-medium rounded-lg text-gray-400 bg-gray-50 cursor-not-allowed">Confirm</button>
                            @endif

                            <!-- Arrow -->
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>

                            {{-- 2. Ship --}}
                            @php
                                $isReadyToShip = in_array($order->status, ['confirmed', 'processing']);
                                $isShippedOrLater = in_array($order->status, ['shipped', 'delivered', 'completed', 'returned']);
                            @endphp
                            @if($isReadyToShip)
                                @can('orders process')
                                    <button @click="showShipModal = true" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                                        Ship Order
                                    </button>
                                @else
                                    <button disabled class="inline-flex items-center px-4 py-2 border border-gray-200 shadow-sm text-sm font-medium rounded-lg text-gray-400 bg-gray-50 cursor-not-allowed">Ship Order</button>
                                @endcan
                            @elseif($isShippedOrLater)
                                <button disabled class="inline-flex items-center px-4 py-2 border border-emerald-200 shadow-sm text-sm font-medium rounded-lg text-emerald-600 bg-emerald-50 cursor-not-allowed opacity-80">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Shipped
                                </button>
                            @else
                                <button disabled class="inline-flex items-center px-4 py-2 border border-gray-200 shadow-sm text-sm font-medium rounded-lg text-gray-400 bg-gray-50 cursor-not-allowed">Ship Order</button>
                            @endif

                            <!-- Arrow -->
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>

                            {{-- 3. Deliver --}}
                            @php
                                $isShipped = $order->status === 'shipped';
                                $isDeliveredOrLater = in_array($order->status, ['delivered', 'completed', 'returned']);
                            @endphp
                            @if($isShipped)
                                @can('orders deliver')
                                    <form action="{{ route('tenant.orders.status', $order) }}" method="POST" onsubmit="return confirm('Mark as Delivered?')">
                                        @csrf
                                        <input type="hidden" name="status" value="delivered">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all">
                                            Deliver
                                        </button>
                                    </form>
                                @else
                                    <button disabled class="inline-flex items-center px-4 py-2 border border-gray-200 shadow-sm text-sm font-medium rounded-lg text-gray-400 bg-gray-50 cursor-not-allowed">Deliver</button>
                                @endcan
                            @elseif($isDeliveredOrLater)
                                <button disabled class="inline-flex items-center px-4 py-2 border border-emerald-200 shadow-sm text-sm font-medium rounded-lg text-emerald-600 bg-emerald-50 cursor-not-allowed opacity-80">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Delivered
                                </button>
                            @else
                                <button disabled class="inline-flex items-center px-4 py-2 border border-gray-200 shadow-sm text-sm font-medium rounded-lg text-gray-400 bg-gray-50 cursor-not-allowed">Deliver</button>
                            @endif

                            {{-- Cancel (Separate) --}}
                            @if(!in_array($order->status, ['cancelled', 'delivered', 'returned', 'completed']))
                                @can('orders cancel')
                                    <form action="{{ route('tenant.orders.status', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?')" class="ml-4 pl-4 border-l border-gray-200">
                                        @csrf
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm transition flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Cancel
                                        </button>
                                    </form>
                                @endcan
                            @endif
                        </div>

                        <!-- Shipping Modal -->
                        <div x-show="showShipModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showShipModal = false"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <form action="{{ route('tenant.orders.status', $order) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="shipped">
                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Ship Order</h3>
                                            <div class="mt-4 space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Courier / Carrier</label>
                                                    <input type="text" name="carrier" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. FedEx, Local">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Tracking Number</label>
                                                    <input type="text" name="tracking_number" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                                Confirm Shipment
                                            </button>
                                            <button type="button" @click="showShipModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
