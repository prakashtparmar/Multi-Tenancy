@extends('layouts.app')

@section('content')
    <div class="flex flex-1 flex-col space-y-8 p-6 md:p-8 max-w-7xl mx-auto w-full animate-in fade-in duration-500">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-1">
                <h1
                    class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                    Return Processing
                </h1>
                <p class="text-muted-foreground text-sm font-medium">
                    Manage and process customer returns efficiently.
                </p>
            </div>
            <div>
                <a href="{{ route('central.processing.orders.index') }}"
                    class="group inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm hover:shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-gray-500 group-hover:text-gray-900 transition-colors">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    Back to Orders
                </a>
            </div>
        </div>

        <!-- Returns Table -->
        <div
            class="rounded-3xl border border-gray-100 bg-white/80 backdrop-blur-xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead
                        class="bg-gray-50/50 text-gray-500 font-semibold border-b border-gray-100 uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-8 py-5">RMA #</th>
                            <th class="px-8 py-5">Order #</th>
                            <th class="px-8 py-5">Customer</th>
                            <th class="px-8 py-5">Status</th>
                            <th class="px-8 py-5">Items</th>
                            <th class="px-8 py-5 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($returns as $return)
                            <tr class="group hover:bg-gray-50/60 transition-colors duration-200">
                                <td class="px-8 py-5">
                                    <div class="font-mono font-bold text-gray-900">{{ $return->rma_number }}</div>
                                    <div class="text-[10px] uppercase font-bold tracking-wide text-gray-400 mt-0.5">
                                        {{ $return->created_at->format('M d, H:i') }}
                                    </div>
                                </td>
                                <td class="px-8 py-5 font-mono text-gray-600 font-medium">
                                    {{ $return->order->order_number }}
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center text-indigo-700 font-bold text-xs ring-2 ring-white shadow-sm">
                                            {{ substr($return->order->customer->first_name, 0, 1) }}{{ substr($return->order->customer->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900">{{ $return->order->customer->first_name }}
                                                {{ $return->order->customer->last_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $return->order->customer->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold border shadow-sm
                                                        {{ $return->status === 'approved' ? 'bg-blue-50 text-blue-700 border-blue-200 shadow-blue-100' : '' }}
                                                        {{ $return->status === 'received' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 shadow-emerald-100' : '' }}
                                                        {{ $return->status === 'requested' ? 'bg-amber-50 text-amber-700 border-amber-200 shadow-amber-100' : '' }}
                                                        {{ $return->status === 'rejected' ? 'bg-red-50 text-red-700 border-red-200 shadow-red-100' : '' }}
                                                    ">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $return->status === 'approved' ? 'bg-blue-500' : ($return->status === 'received' ? 'bg-emerald-500' : ($return->status === 'requested' ? 'bg-amber-500' : 'bg-red-500')) }}"></span>
                                        {{ ucfirst($return->status) }}
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex flex-col gap-1.5">
                                        @foreach($return->items as $item)
                                            <div class="flex items-center gap-2 text-xs">
                                                <span class="font-bold text-gray-900">{{ $item->quantity }}x</span>
                                                <span class="text-gray-600 truncate max-w-[150px]"
                                                    title="{{ $item->product->name }}">{{ $item->product->name }}</span>
                                                <span
                                                    class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-500 border border-gray-200">{{ $item->condition }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    @if($return->status === 'approved')
                                        <button @click="$dispatch('open-receive-modal', { 
                                                            id: {{ $return->id }}, 
                                                            rma: '{{ $return->rma_number }}', 
                                                            items: {{ $return->items->map(fn($i) => ['id' => $i->id, 'name' => $i->product->name, 'sku' => $i->product->sku, 'quantity' => $i->quantity, 'condition' => $i->condition, 'image' => $i->product->image_url ?? null]) }} 
                                                        })"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white text-xs font-bold rounded-xl hover:bg-black hover:scale-105 active:scale-95 transition-all shadow-lg shadow-gray-900/20 group-hover:shadow-gray-900/30">
                                            <span>Receive</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M5 12h14" />
                                                <path d="m12 5 7 7-7 7" />
                                            </svg>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                                <path d="m9 11 3 3L22 4" />
                                            </svg>
                                            Processed
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-24 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" class="text-gray-300">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                <path d="M14 2v6h6" />
                                                <path d="M16 13H8" />
                                                <path d="M16 17H8" />
                                                <path d="M10 9H8" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-900">No Approved Returns</h3>
                                        <p class="text-sm text-gray-500 max-w-xs mx-auto">There are no approved return requests
                                            waiting to be received at this time.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50/30">
                {{ $returns->links() }}
            </div>
        </div>
    </div>

    <!-- Premium Receive Modal -->
    <div x-data="{ 
            open: false, 
            returnId: null, 
            rma: '', 
            items: [],
            processing: false,
            error: null,

            init() {
                window.addEventListener('open-receive-modal', (e) => {
                    this.returnId = e.detail.id;
                    this.rma = e.detail.rma;
                    // Deep copy and initialize
                    this.items = e.detail.items.map(i => ({
                        ...i, 
                        new_condition: 'sellable', 
                        verified: true
                    })); 
                    this.error = null;
                    this.open = true;
                });
            },

            submit() {
                // CSRF Check
                const meta = document.querySelector('meta[name=csrf-token]');
                if (!meta || !meta.content) {
                    this.error = 'Security Token (CSRF) missing. Please refresh the page.';
                    return;
                }

                this.processing = true;
                this.error = null;

                try {
                    // Create form dynamically
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/processing/returns/${this.returnId}/receive`;
                    form.style.display = 'none'; // Hide it

                    // Add CSRF
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = meta.content;
                    form.appendChild(csrf);

                    // Add Items
                    this.items.forEach((item, index) => {
                        // ID
                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = `items[${index}][id]`;
                        idInput.value = item.id;
                        form.appendChild(idInput);

                        // Condition
                        const conditionInput = document.createElement('input');
                        conditionInput.type = 'hidden';
                        conditionInput.name = `items[${index}][condition]`;
                        conditionInput.value = item.new_condition;
                        form.appendChild(conditionInput);
                    });

                    document.body.appendChild(form);
                    form.submit();
                } catch (e) {
                    console.error(e);
                    this.error = 'An unexpected error occurred. Please try again.';
                    this.processing = false;
                }
            }
        }" x-show="open" style="display: none;" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-md transition-opacity" x-show="open"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="if(!processing) open = false">
        </div>

        <!-- Modal Panel -->
        <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] ring-1 ring-black/5 transform transition-all"
            x-show="open" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95">

            <!-- Modal Header -->
            <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                                <line x1="12" y1="22.08" x2="12" y2="12" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Receive Items</h3>
                            <p class="text-sm font-medium text-gray-500">RMA: <span x-text="rma"
                                    class="font-mono text-gray-700"></span></p>
                        </div>
                    </div>
                </div>
                <button @click="if(!processing) open = false"
                    class="text-gray-400 hover:text-gray-900 bg-white hover:bg-gray-100 p-2 rounded-full transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <!-- Error Message -->
            <div x-show="error" class="px-8 pt-6 pb-0" style="display: none;">
                <div
                    class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <span x-text="error"></span>
                </div>
            </div>

            <!-- Items List -->
            <div class="p-8 space-y-4 overflow-y-auto overflow-x-hidden custom-scrollbar flex-1 bg-white">
                <template x-for="(item, index) in items" :key="item.id">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 p-5 rounded-2xl border border-gray-100 bg-white hover:border-gray-200 hover:shadow-sm transition-all group">
                        <!-- Product Info -->
                        <div class="flex items-center gap-4 flex-1">
                            <div
                                class="w-14 h-14 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center flex-shrink-0 text-gray-300">
                                <template x-if="item.image">
                                    <img :src="item.image" class="w-full h-full object-cover rounded-xl" alt="Product">
                                </template>
                                <template x-if="!item.image">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                    </svg>
                                </template>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 text-sm" x-text="item.name"></h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider"
                                        x-text="item.sku || 'N/A'"></span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                    <span class="text-xs text-gray-500 font-medium">Qty: <span x-text="item.quantity"
                                            class="text-gray-900"></span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Condition Selector -->
                        <div class="flex flex-col gap-1.5 sm:text-right min-w-[180px]">
                            <label class="text-[10px] font-bold uppercase text-gray-400 tracking-wider">Condition
                                Received</label>
                            <div class="relative">
                                <select x-model="item.new_condition"
                                    class="w-full appearance-none pl-4 pr-10 py-2.5 rounded-xl text-sm font-bold border-gray-200 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 transition-all cursor-pointer"
                                    :class="item.new_condition === 'sellable' ? 'text-emerald-700 bg-emerald-50/50 border-emerald-100' : 'text-red-700 bg-red-50/50 border-red-100'">
                                    <option value="sellable">✅ Sellable (Restock)</option>
                                    <option value="damaged">❌ Damaged (Scrap)</option>
                                </select>
                                <div
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="items.length === 0" style="display: none;" class="text-center py-10">
                    <p class="text-gray-500">No items found for this return.</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-6 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 shrink-0">
                <button @click="if(!processing) open = false"
                    class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-900 hover:bg-white border border-transparent hover:border-gray-200 rounded-xl transition-all disabled:opacity-50"
                    :disabled="processing">
                    Cancel
                </button>
                <button @click="submit()" :disabled="processing"
                    class="px-8 py-3 text-sm font-bold bg-gray-900 text-white rounded-xl hover:bg-black hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 flex items-center gap-2 shadow-lg shadow-gray-900/20">
                    <svg x-show="!processing" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m5 12 7-7 7 7" />
                        <path d="M12 19V5" />
                    </svg>
                    <svg x-show="processing" class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                    </svg>
                    <span x-text="processing ? 'Processing...' : 'Confirm Receipt'"></span>
                </button>
            </div>
        </div>
    </div>
@endsection