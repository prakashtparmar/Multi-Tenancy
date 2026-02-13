<x-app-layout>
    <div class="min-h-screen bg-gray-50/50 py-12" x-data="{ 
        tab: 'overview',
        showInteractionModal: false,
        openInteractionModal() { this.showInteractionModal = true; },
        closeInteractionModal() { this.showInteractionModal = false; }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-16 w-16 rounded-xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-2xl font-bold text-indigo-600">
                        {{ substr($customer->first_name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 flex items-center gap-3">
                            {{ $customer->display_name }}
                            @if($customer->is_active)
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Inactive</span>
                            @endif
                        </h2>
                        <div class="mt-1 flex items-center gap-4 text-sm text-gray-500">
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z" clip-rule="evenodd" /></svg>
                                {{ $customer->customer_code }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" /></svg>
                                Mixed Since {{ $customer->created_at->format('M Y') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <button @click="openInteractionModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                        Log Interaction
                    </button>
                    <a href="{{ route('central.orders.create', ['customer_query' => $customer->customer_code]) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5">
                        Create Order
                    </a>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                
                <!-- Left Sidebar - Navigation Pills -->
                <div class="lg:col-span-1">
                    <nav class="space-y-1 bg-white p-2 rounded-xl shadow-sm border border-gray-100 sticky top-6">
                        @foreach([
                                'overview' => ['label' => 'Overview', 'icon' => 'heroicon-o-home'],
                                'orders' => ['label' => 'Orders & Payments', 'icon' => 'heroicon-o-shopping-bag'],
                                'profile' => ['label' => 'Profile Details', 'icon' => 'heroicon-o-user'],
                                'interactions' => ['label' => 'History & interactions', 'icon' => 'heroicon-o-clock'],
                            ] as $key => $item)
                                <button type="button" 
                                    @click="tab = '{{ $key }}'"
                                    :class="{ 'bg-indigo-50 text-indigo-700 font-semibold ring-1 ring-indigo-200': tab === '{{ $key }}', 'text-gray-600 hover:bg-gray-50 hover:text-gray-900': tab !== '{{ $key }}' }"
                                    class="group flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out">
                                    <span :class="{ 'text-indigo-500': tab === '{{ $key }}', 'text-gray-400 group-hover:text-gray-500': tab !== '{{ $key }}' }" class="mr-3 flex-shrink-0 h-5 w-5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($key === 'overview') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                            @elseif($key === 'orders') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            @elseif($key === 'profile') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            @elseif($key === 'interactions') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path> @endif
                                        </svg>
                                    </span>
                                    {{ $item['label'] }}
                                </button>
                        @endforeach
                    </nav>

                    <!-- Quick Stats Widget -->
                    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Financial Status</h4>
                        <div class="space-y-4">
                            <div>
                                <div class="text-xs text-gray-500 mb-1">Outstanding Balance</div>
                                <div class="text-xl font-bold {{ $customer->outstanding_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    ₹{{ number_format((float) $customer->outstanding_balance, 0) }}
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-100">
                                <div class="text-xs text-gray-500 mb-1">Credit Limit</div>
                                <div class="text-lg font-semibold text-gray-900">
                                    ₹{{ number_format((float) $customer->credit_limit, 0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Content Area -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden min-h-[600px]">
                        
                        <!-- Overview Tab -->
                        <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-6 sm:p-8 space-y-8">
                            
                            <!-- Contact Cards Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 flex items-start space-x-4">
                                    <div class="bg-white p-2 rounded-lg shadow-sm text-indigo-600">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Mobile Number</p>
                                        <p class="text-lg font-bold text-indigo-600 mt-1">{{ $customer->mobile }}</p>
                                        @if($customer->phone_number_2)
                                            <p class="text-sm text-gray-500">{{ $customer->phone_number_2 }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 flex items-start space-x-4">
                                    <div class="bg-white p-2 rounded-lg shadow-sm text-gray-600">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <div class="w-full">
                                        <p class="text-sm font-medium text-gray-900 mb-2">Primary Address</p>
                                        @php $addr = $customer->addresses->firstWhere('is_default', true) ?? $customer->addresses->first(); @endphp
                                        @if($addr)
                                            <div class="space-y-1">
                                                <p class="text-sm text-gray-800 font-medium">{{ $addr->address_line1 }}</p>
                                                @if($addr->address_line2)
                                                    <p class="text-sm text-gray-600">{{ $addr->address_line2 }}</p>
                                                @endif

                                                <div class="grid grid-cols-2 gap-x-4 gap-y-2 mt-2 text-xs text-gray-500">
                                                    @if($addr->village)
                                                        <div>
                                                            <span class="block font-semibold text-gray-700">Village</span>
                                                            {{ $addr->village }}
                                                        </div>
                                                    @endif

                                                    @if($addr->post_office)
                                                        <div>
                                                            <span class="block font-semibold text-gray-700">Post Office</span>
                                                            {{ $addr->post_office }}
                                                        </div>
                                                    @endif

                                                    @if($addr->taluka)
                                                        <div>
                                                            <span class="block font-semibold text-gray-700">Taluka</span>
                                                            {{ $addr->taluka }}
                                                        </div>
                                                    @endif

                                                    @if($addr->district)
                                                        <div>
                                                            <span class="block font-semibold text-gray-700">District</span>
                                                            {{ $addr->district }}
                                                        </div>
                                                    @endif

                                                    <div>
                                                        <span class="block font-semibold text-gray-700">State & Pincode</span>
                                                        {{ $addr->state }} - {{ $addr->pincode }}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-400 italic mt-1">No address on file</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Crop Summary -->
                             <div>
                                <h3 class="text-lg font-semibold text-gray-900 border-b pb-4 mb-4">Crops & Farming</h3>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @forelse($customer->crops['primary'] ?? [] as $crop)
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-50 text-green-700 border border-green-200">
                                            {{ $crop }}
                                        </span>
                                    @empty
                                        <span class="text-sm text-gray-500 italic">No crops listed</span>
                                    @endforelse
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <span class="block text-xs text-gray-500 uppercase">Land Area</span>
                                        <span class="font-bold text-gray-900">{{ $customer->land_area ?? 0 }} {{ $customer->land_unit ?? 'Acres' }}</span>
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <span class="block text-xs text-gray-500 uppercase">Irrigation</span>
                                        <span class="font-bold text-gray-900">{{ $customer->irrigation_type ?? 'N/A' }}</span>
                                    </div>
                                </div>
                             </div>

                             <!-- Internal Notes -->
                            <div class="bg-yellow-50 rounded-xl border border-yellow-100 p-6">
                                <h4 class="text-sm font-bold text-yellow-900 uppercase tracking-widest mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Internal Notes
                                </h4>
                                <p class="text-sm text-yellow-800 italic">"{{ $customer->internal_notes ?? 'No internal notes available.' }}"</p>
                            </div>

                        </div>

                        <!-- Orders Tab -->
                        <div x-show="tab === 'orders'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                                <a href="{{ route('central.orders.create', ['customer_query' => $customer->customer_code]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                    + New Order
                                </a>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Info</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($orders as $order)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</div>
                                                            <div class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200 capitalize">
                                                        {{ $order->status }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                                    ₹{{ number_format($order->grand_total, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="{{ url('orders/' . $order->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-12 text-center text-gray-500 text-sm">
                                                    No orders found for this customer.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($orders->count() > 0 && method_exists($orders, 'links'))
                                <div class="p-4 border-t border-gray-100">
                                    {{ $orders->appends(['interactions_page' => $interactions->currentPage()])->links() }}
                                </div>
                            @endif
                        </div>

                        <!-- Profile Details Tab -->
                        <div x-show="tab === 'profile'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-6 sm:p-8 space-y-8" style="display: none;">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-4">Full Profile Details</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Personal Information</h4>
                                    <dl class="space-y-4">
                                        <div class="grid grid-cols-3 gap-4">
                                            <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                            <dd class="text-sm text-gray-900 col-span-2 font-semibold">{{ $customer->first_name }} {{ $customer->last_name }}</dd>
                                        </div>
                                        <div class="grid grid-cols-3 gap-4">
                                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                                            <dd class="text-sm text-gray-900 col-span-2">{{ $customer->email ?? 'N/A' }}</dd>
                                        </div>
                                        <div class="grid grid-cols-3 gap-4">
                                            <dt class="text-sm font-medium text-gray-500">Category</dt>
                                            <dd class="text-sm text-gray-900 col-span-2 capitalize">{{ $customer->category ?? 'General' }}</dd>
                                        </div>
                                        <div class="grid grid-cols-3 gap-4">
                                            <dt class="text-sm font-medium text-gray-500">KYC Status</dt>
                                            <dd class="text-sm col-span-2">
                                                 @if($customer->kyc_completed)
                                                    <span class="inline-flex items-center gap-1 text-green-700 font-medium">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        Verified
                                                    </span>
                                                 @else
                                                    <span class="text-red-600 font-medium">Pending</span>
                                                 @endif
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Business & Compliance</h4>
                                    <dl class="space-y-4">
                                        <div class="grid grid-cols-3 gap-4">
                                            <dt class="text-sm font-medium text-gray-500">GSTIN</dt>
                                            <dd class="text-sm text-gray-900 col-span-2 font-mono">{{ $customer->gst_number ?? 'N/A' }}</dd>
                                        </div>
                                        <div class="grid grid-cols-3 gap-4">
                                            <dt class="text-sm font-medium text-gray-500">PAN</dt>
                                            <dd class="text-sm text-gray-900 col-span-2 font-mono">{{ $customer->pan_number ?? 'N/A' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Interactions Tab -->
                        <div x-show="tab === 'interactions'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-6 sm:p-8" style="display: none;">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">Activity Timeline</h3>
                            </div>

                            <div class="flow-root">
                                <ul role="list" class="-mb-8">
                                    @php
                                        $mergedEvents = collect();
                                        if ($orders) {
                                            foreach ($orders as $order) {
                                                $mergedEvents->push([
                                                    'type' => 'order',
                                                    'date' => $order->created_at,
                                                    'data' => $order
                                                ]);
                                            }
                                        }
                                        if ($interactions) {
                                            foreach ($interactions as $interaction) {
                                                $mergedEvents->push([
                                                    'type' => 'interaction',
                                                    'date' => $interaction->created_at,
                                                    'data' => $interaction
                                                ]);
                                            }
                                        }
                                        $sortedEvents = $mergedEvents->sortByDesc('date')->values();
                                    @endphp

                                    @forelse($sortedEvents as $event)
                                        <li>
                                            <div class="relative pb-8">
                                                @if(!$loop->last)
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                            {{ $event['type'] === 'order' ? 'bg-blue-500' : 'bg-purple-500' }}">
                                                            @if($event['type'] === 'order')
                                                                <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" /></svg>
                                                            @else
                                                                <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 space-y-1">
                                                        <div>
                                                            <div class="text-sm text-gray-500">
                                                                @if($event['type'] === 'order')
                                                                    <span class="font-medium text-gray-900">Order Placed</span> 
                                                                    <span class="mx-1">&middot;</span>
                                                                    <a href="{{ url('orders/' . $event['data']->id) }}" class="font-medium text-blue-600 hover:text-blue-800">#{{ $event['data']->order_number }}</a>
                                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                                        {{ ucfirst($event['data']->status) }}
                                                                    </span>
                                                                @else
                                                                                                                            <span class="font-medium text-gray-900">Interaction Logged</span>
                                                                                                                            <span class="mx-1">&middot;</span>
                                                                                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                                                                                                                {{ $event['data']->type === 'call' ? 'bg-green-100 text-green-800' :
                                                                    ($event['data']->type === 'visit' ? 'bg-yellow-100 text-yellow-800' :
                                                                        ($event['data']->type === 'payment' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                                                                                                                {{ ucfirst($event['data']->type) }}
                                                                                                                            </span>
                                                                                                                            @if($event['data']->user)
                                                                                                                                <span class="text-xs text-gray-400 ml-1">by {{ $event['data']->user->name }}</span>
                                                                                                                            @endif
                                                                @endif
                                                            </div>
                                                            <div class="text-xs text-gray-400">
                                                                {{ $event['date']->format('d M Y, h:i A') }}
                                                                 <span class="mx-1">&middot;</span> {{ $event['date']->diffForHumans() }}
                                                            </div>
                                                        </div>

                                                        <div class="mt-2 text-sm text-gray-700">
                                                            @if($event['type'] === 'order')
                                                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                                                    <div class="flex justify-between items-center mb-1">
                                                                        <span class="text-gray-500 text-xs uppercase tracking-wider font-semibold">Total Amount</span>
                                                                        <span class="font-bold text-gray-900">₹{{ number_format($event['data']->grand_total, 2) }}</span>
                                                                    </div>
                                                                    @if($event['data']->items_count > 0)
                                                                        <p class="text-xs text-gray-500">{{ $event['data']->items_count }} items in this order</p>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 space-y-2">
                                                                    @if($event['data']->outcome)
                                                                        <div>
                                                                            <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Outcome</span>
                                                                            <p class="text-gray-900 font-medium">{{ $event['data']->outcome }}</p>
                                                                        </div>
                                                                    @endif
                                                                    @if($event['data']->notes)
                                                                        <div class="pt-2 border-t border-gray-200">
                                                                            <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Notes</span>
                                                                            <p class="text-gray-600 italic">"{{ $event['data']->notes }}"</p>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="py-4 text-center text-sm text-gray-500 italic">No recent activity</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden Interaction Modal (Alpine Controlled) -->
        <!-- Hidden Interaction Modal (Alpine Controlled) -->
        <template x-teleport="body">
            <div x-show="showInteractionModal" style="display: none;" class="fixed inset-0 z-[99] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div x-show="showInteractionModal" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0" 
                         x-transition:enter-end="opacity-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100" 
                         x-transition:leave-end="opacity-0" 
                         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                         aria-hidden="true" 
                         @click="closeInteractionModal()">
                    </div>

                    <!-- This element is to trick the browser into centering the modal contents. -->
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <!-- Modal panel -->
                    <div x-show="showInteractionModal" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                        
                        <div>
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Log Interaction</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Record a new interaction with this customer.</p>
                                </div>
                            </div>
                        </div>
                        
                        <form id="interaction-form" action="{{ route('central.customers.interaction', $customer->id) }}" method="POST">
                            @csrf
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type</label>
                                    <select name="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="call">Phone Call</option>
                                        <option value="visit">Site Visit</option>
                                        <option value="general">General Inquiry</option>
                                        <option value="payment">Payment Follow-up</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Outcome</label>
                                    <input type="text" name="outcome" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="e.g. Call connected">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea name="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                </div>
                                <input type="hidden" name="close_session" value="1">
                            </div>
                            
                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                                    Save
                                </button>
                                <button type="button" @click="closeInteractionModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>