<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h2 class="font-extrabold text-3xl text-gray-900 tracking-tight leading-tight">
                    {{ __('RMA Details') }} <span class="text-gray-400">#{{ $orderReturn->rma_number }}</span>
                </h2>
                <div class="flex items-center gap-2 text-sm text-gray-500 font-medium">
                    <span>Requested on {{ $orderReturn->created_at->format('M d, Y') }}</span>
                    <span>&bull;</span>
                    <span>{{ $orderReturn->created_at->diffForHumans() }}</span>
                </div>
            </div>
            <a href="{{ route('central.returns.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Back to Returns
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Lifecycle Stepper -->
            <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm overflow-x-auto no-scrollbar">
                <div class="flex items-center justify-between min-w-[600px] relative">
                    <!-- Progress Line -->
                    <div class="absolute left-0 top-[22px] right-0 h-1 bg-gray-100 -z-0"></div>

                    @php
                        $steps = ['requested', 'approved', 'received', 'refunded', 'completed'];
                        if ($orderReturn->status === 'rejected')
                            $steps = ['requested', 'rejected'];
                        $currentIndex = array_search($orderReturn->status, $steps);
                        if ($currentIndex === false)
                            $currentIndex = 0;
                    @endphp

                    @foreach($steps as $index => $step)
                        @php
                            $isCompleted = $index <= $currentIndex;
                            $isActive = $index === $currentIndex;
                            $colorClass = match ($step) {
                                'rejected' => 'red',
                                'refunded', 'completed' => 'emerald',
                                default => 'blue',
                            };
                        @endphp
                        <div class="flex flex-col items-center gap-3 relative z-10 flex-1">
                            <div class="w-12 h-12 rounded-full border-4 border-white shadow-md flex items-center justify-center transition-all duration-500
                                        {{ $isCompleted ? 'bg-' . $colorClass . '-500 scale-110' : 'bg-gray-200' }}">
                                @if($isCompleted)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                @else
                                    <span class="text-gray-500 font-bold text-sm">{{ $index + 1 }}</span>
                                @endif
                            </div>
                            <div class="text-center">
                                <p
                                    class="text-[10px] font-black uppercase tracking-widest {{ $isCompleted ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ ucfirst($step) }}
                                </p>
                                @if($isActive)
                                    <span class="text-[9px] font-bold text-{{ $colorClass }}-500 animate-pulse">Current
                                        Stage</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Context Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Status Card -->
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Current Status</h3>
                        <div class="flex items-center gap-3">
                            @if($orderReturn->status === 'approved')
                                <div
                                    class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-2xl font-bold text-gray-900">Approved</span>
                            @elseif($orderReturn->status === 'rejected')
                                <div
                                    class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <span class="text-2xl font-bold text-gray-900">Rejected</span>
                            @elseif($orderReturn->status === 'completed')
                                <div
                                    class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-2xl font-bold text-gray-900">Completed</span>
                            @elseif($orderReturn->status === 'received')
                                <div
                                    class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <span class="text-2xl font-bold text-gray-900">Received</span>
                            @elseif($orderReturn->status === 'refunded')
                                <div
                                    class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                                <span class="text-2xl font-bold text-gray-900">Refunded</span>
                            @else
                                <div
                                    class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-2xl font-bold text-gray-900">Requested</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Customer Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Customer Details</h3>
                    <div class="flex items-center gap-4">
                        <div
                            class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-lg">
                            {{ substr($orderReturn->customer->first_name ?? 'G', 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-gray-900">{{ $orderReturn->customer->name ?? 'Guest Customer' }}
                            </div>
                            <div class="text-sm text-gray-500">{{ $orderReturn->customer->email ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $orderReturn->customer->phone ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Order Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Original Order</h3>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-600">Order Number</span>
                        <span class="font-bold text-gray-900">#{{ $orderReturn->order->order_number }}</span>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-600">Order Date</span>
                        <span class="text-gray-900">{{ $orderReturn->order->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="pt-3 border-t border-gray-100">
                        <div class="text-xs text-gray-500 mb-1">Reason for Return</div>
                        <p class="text-sm text-gray-900 italic">"{{ $orderReturn->reason }}"</p>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-900">Items to Return</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product</th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Condition</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Return Qty</th>
                                @if(in_array($orderReturn->status, ['received', 'refunded', 'completed']))
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Received Qty</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Rec. Condition</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($orderReturn->items as $item)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="h-10 w-10 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden border border-gray-200">
                                                @if($item->product && $item->product->image_url)
                                                    <img src="{{ $item->product->image_url }}" alt=""
                                                        class="h-full w-full object-cover">
                                                @else
                                                    <div class="h-full w-full flex items-center justify-center text-gray-400">
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $item->product->name ?? 'Unknown Product' }}
                                                </div>
                                                <div class="text-xs text-gray-500">SKU: {{ $item->product->sku ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->condition === 'sellable' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($item->condition) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                        {{ $item->quantity }}
                                    </td>
                                    @if(in_array($orderReturn->status, ['received', 'refunded', 'completed']))
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                            {{ $item->quantity_received ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($item->condition_received)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->condition_received === 'sellable' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($item->condition_received) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Workflow Actions -->
            <div
                class="bg-gray-50 rounded-xl p-4 border border-gray-200/60 flex flex-col sm:flex-row justify-between items-center gap-4">
                <a href="{{ route('central.returns.index') }}"
                    class="text-sm text-gray-500 hover:text-gray-900 font-medium flex items-center gap-1 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Returns
                </a>

                <div class="flex flex-wrap items-center gap-3 justify-end">
                    @if($orderReturn->status === 'requested')
                        <a href="{{ route('central.returns.edit', $orderReturn) }}"
                            class="inline-flex justify-center rounded-lg border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition-all">
                            Edit Request
                        </a>
                        <form action="{{ route('central.returns.update-status', $orderReturn) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit"
                                class="inline-flex justify-center rounded-lg border border-transparent bg-red-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all"
                                onclick="return confirm('Are you sure you want to REJECT this return request?')">
                                Reject
                            </button>
                        </form>
                        <form action="{{ route('central.returns.update-status', $orderReturn) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="approved">
                            <button type="submit"
                                class="inline-flex justify-center rounded-lg border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                                Approve Request
                            </button>
                        </form>
                    @endif

                    @if($orderReturn->status === 'approved')
                        <a href="{{ route('central.returns.inspect', $orderReturn) }}"
                            class="inline-flex justify-center rounded-lg border border-transparent bg-emerald-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all">
                            Inspect & Receive Items
                        </a>
                    @endif

                    @if($orderReturn->status === 'received')
                        <a href="{{ route('central.returns.refund', $orderReturn) }}"
                            class="inline-flex justify-center rounded-lg border border-transparent bg-purple-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all">
                            Issue Refund
                        </a>
                    @endif

                    @if($orderReturn->status === 'refunded')
                        <div class="px-4 py-2 bg-purple-50 rounded-lg border border-purple-100 flex items-center gap-2">
                            <span class="text-sm text-purple-700 font-medium">Refunded:</span>
                            <span
                                class="text-sm font-bold text-purple-900">${{ number_format($orderReturn->refunded_amount, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>
</x-app-layout>