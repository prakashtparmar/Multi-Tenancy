<x-layouts.app>
    <!-- Breadcrumbs -->
    <div class="mb-4 flex items-center text-sm text-gray-500 dark:text-gray-400">
       <a href="/dashboard" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
       <span class="mx-2">›</span>
       <a href="/customers" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Customers') }}</a>
       <span class="mx-2">›</span>
       <span>{{ $customer->first_name }}</span>
    </div>

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
       <div>
          <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
             {{ $customer->display_name }}
             <span class="px-2 py-0.5 text-xs rounded-full {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                 {{ $customer->is_active ? 'Active' : 'Inactive' }}
             </span>
             @if($customer->type)
                 <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 uppercase">{{ $customer->type }}</span>
             @endif
          </h1>
          <p class="text-sm text-gray-600 dark:text-gray-400">{{ $customer->customer_code }} • {{ ucfirst($customer->category) }}</p>
       </div>
       <div class="flex gap-2">
          <a href="/customers/{{ $customer->id }}/edit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium">
             Edit Customer
          </a>
       </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
       <!-- Left Column: Info Cards -->
       <div class="lg:col-span-2 space-y-6">
          
          <!-- Identity & Contact -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
             <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Identity & Contact</h3>
             </div>
             <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                   <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase">Full Name</span>
                   <span class="text-gray-900 dark:text-gray-200 font-medium">{{ $customer->first_name }} {{ $customer->last_name }}</span>
                </div>
                <div>
                   <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase">Mobile</span>
                   <span class="text-gray-900 dark:text-gray-200 font-medium">{{ $customer->mobile }}</span>
                </div>
                @if($customer->phone_number_2)
                <div>
                   <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase">Phone 2</span>
                   <span class="text-gray-900 dark:text-gray-200">{{ $customer->phone_number_2 }}</span>
                </div>
                @endif
                @if($customer->relative_phone)
                <div>
                   <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase">Relative Phone</span>
                   <span class="text-gray-900 dark:text-gray-200">{{ $customer->relative_phone }}</span>
                </div>
                @endif
                <div>
                   <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase">Email</span>
                   <span class="text-gray-900 dark:text-gray-200">{{ $customer->email ?? '-' }}</span>
                </div>
                <div>
                   <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase">Source</span>
                   <span class="text-gray-900 dark:text-gray-200">{{ ucfirst($customer->source ?? '-') }}</span>
                </div>
             </div>
          </div>

          <!-- Agriculture Profile -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
             <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Agriculture Profile</h3>
             </div>
             <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                   <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase">Land Area</span>
                   <span class="text-gray-900 dark:text-gray-200 font-medium">{{ $customer->land_area }} {{ ucfirst($customer->land_unit) }}</span>
                </div>
                <div>
                   <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase">Irrigation</span>
                   <span class="text-gray-900 dark:text-gray-200 font-medium">{{ ucfirst($customer->irrigation_type ?? '-') }}</span>
                </div>
                <div class="md:col-span-2">
                   <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase mb-1">Primary Crops</span>
                   <div class="flex flex-wrap gap-1">
                      @forelse($customer->crops['primary'] ?? [] as $crop)
                          <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-xs dark:bg-emerald-900 dark:text-emerald-200">{{ $crop }}</span>
                      @empty
                          <span class="text-gray-400 italic">None</span>
                      @endforelse
                   </div>
                </div>
                <div class="md:col-span-2">
                   <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase mb-1">Secondary Crops</span>
                   <div class="flex flex-wrap gap-1">
                      @forelse($customer->crops['secondary'] ?? [] as $crop)
                          <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 text-xs dark:bg-blue-900 dark:text-blue-200">{{ $crop }}</span>
                      @empty
                          <span class="text-gray-400 italic">None</span>
                      @endforelse
                   </div>
                </div>
             </div>
          </div>

          <!-- Recent Activity / Notes (Placeholder) -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
               <h3 class="font-semibold text-gray-900 dark:text-gray-100">Internal Notes</h3>
            </div>
            <div class="p-4 text-sm text-gray-700 dark:text-gray-300">
               {{ $customer->internal_notes ?? 'No notes available.' }}
            </div>
         </div>
       </div>

       <!-- Right Column: Address & Finance -->
       <div class="space-y-6">
          <!-- Address -->
          @php
              $addr = $customer->addresses->firstWhere('is_default', true);
          @endphp
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
             <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Primary Address</h3>
             </div>
             <div class="p-4 text-sm text-gray-700 dark:text-gray-300 space-y-2">
                @if($addr)
                    <p>{{ $addr->address_line1 }}</p>
                    @if($addr->address_line2)<p>{{ $addr->address_line2 }}</p>@endif
                    <p>
                        {{ $addr->village ? $addr->village . ', ' : '' }}
                        {{ $addr->taluka ? $addr->taluka : '' }}
                    </p>
                    <p>
                        {{ $addr->district ? $addr->district . ', ' : '' }}
                        {{ $addr->state }} - {{ $addr->pincode }}
                    </p>
                    <p class="text-gray-500">{{ $addr->country }}</p>
                    @if($addr->post_office)
                        <p class="text-xs text-gray-500 mt-1">PO: {{ $addr->post_office }}</p>
                    @endif
                @else
                    <p class="italic text-gray-500">No address found.</p>
                @endif
             </div>
          </div>

          <!-- Financial Status -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
             <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Financial Status</h3>
             </div>
             <div class="p-4 space-y-3">
                <div class="flex justify-between items-center">
                   <span class="text-sm text-gray-500 dark:text-gray-400">Credit Limit</span>
                   <span class="font-medium text-gray-900 dark:text-gray-100">Rs {{ number_format($customer->credit_limit, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                   <span class="text-sm text-gray-500 dark:text-gray-400">Outstanding</span>
                   <span class="font-medium text-red-600">Rs {{ number_format($customer->outstanding_balance, 2) }}</span>
                </div>
                <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Credit Valid Until: {{ $customer->credit_valid_till ? $customer->credit_valid_till->format('d M, Y') : 'N/A' }}
                    </span>
                </div>
             </div>
          </div>

          <!-- Business Details -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
             <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Business Details</h3>
             </div>
             <div class="p-4 space-y-2 text-sm">
                <div>
                    <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase">Company / Shop</span>
                    <span class="text-gray-900 dark:text-gray-200">{{ $customer->company_name ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase">GST Number</span>
                    <span class="text-gray-900 dark:text-gray-200">{{ $customer->gst_number ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase">PAN Number</span>
                    <span class="text-gray-900 dark:text-gray-200">{{ $customer->pan_number ?? '-' }}</span>
                </div>
             </div>
          </div>
       </div>
    </div>
</x-layouts.app>
