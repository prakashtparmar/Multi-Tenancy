@section('title', 'Edit Order #' . $orderData->order_number)

<x-app-layout>
    <div x-data="orderWizard({{ $products->toJson() }}, {{ $orderData->customer->toJson() }}, {{ $orderData->toJson() }})" class="flex flex-1 flex-col p-8 space-y-8 animate-in fade-in duration-700">
        
        <!-- MANDATORY INTERACTION TAGGING MODAL -->
        <template x-teleport="body">
            <div x-show="showTaggingModal" 
                 class="fixed inset-0 z-[100] flex items-center justify-center bg-zinc-950/80 backdrop-blur-lg p-4" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                
                <div class="bg-white dark:bg-zinc-900 w-full max-w-lg rounded-[32px] shadow-[0_0_80px_rgba(0,0,0,0.5)] border border-white/10 overflow-hidden flex flex-col animate-in zoom-in-95 duration-300 ease-out max-h-[90vh]" @click.away="if(!taggingLoading) showTaggingModal = false">
                    <div class="flex-1 overflow-y-auto p-8 relative custom-scrollbar">
                        <div class="absolute -top-10 -right-10 size-40 bg-primary/20 blur-[60px] rounded-full"></div>
                        
                        <div class="relative z-10 flex items-center gap-4 mb-6">
                            <div class="size-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-black text-2xl tracking-tighter">Interaction Outcome</h3>
                                <p class="text-xs text-muted-foreground uppercase tracking-widest font-bold">Mandatory Tagging Required</p>
                            </div>
                        </div>

                        <div class="p-6 rounded-2xl bg-muted/30 border border-border/50 mb-8">
                            <p class="text-sm font-medium text-foreground/80 leading-relaxed">
                                You are closing an active session for <span class="text-primary font-black" x-text="selectedCustomer?.first_name + ' ' + (selectedCustomer?.last_name || '')"></span>. 
                                To ensure high-quality CRM data, please select why this visit did not result in an order.
                            </p>
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em] ml-1">Reason for no order</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <template x-for="opt in [
                                        {id: 'enquiry', label: 'Enquiry Only'},
                                        {id: 'pricing', label: 'Price Too High'},
                                        {id: 'stock', label: 'Out of Stock'},
                                        {id: 'follow_up', label: 'Need Follow-up'},
                                        {id: 'comparison', label: 'Comparing Market'},
                                        {id: 'other', label: 'Other Reason'}
                                    ]">
                                        <button @click="tagOutcome = opt.id" 
                                                :class="tagOutcome === opt.id ? 'bg-primary text-primary-foreground border-primary shadow-lg shadow-primary/20 scale-[1.02]' : 'bg-secondary/20 hover:bg-secondary/40 border-border text-muted-foreground'"
                                                class="p-4 rounded-xl text-xs font-black uppercase tracking-widest border transition-all duration-300 text-center">
                                            <span x-text="opt.label"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em] ml-1">Additional Notes (Optional)</label>
                                <textarea x-model="tagNotes" 
                                          class="w-full bg-secondary/20 dark:bg-white/5 border border-border rounded-2xl p-4 text-sm font-medium placeholder:text-muted-foreground/30 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all min-h-[100px]" 
                                          placeholder="Provide more context..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 border-t border-border flex gap-4 bg-white dark:bg-zinc-900 shadow-[0_-10px_40px_rgba(0,0,0,0.05)]">
                        <button @click="showTaggingModal = false" :disabled="taggingLoading" class="flex-1 h-14 rounded-2xl border border-border text-xs font-black uppercase tracking-widest hover:bg-secondary/50 transition-all disabled:opacity-50">
                            Stay Back
                        </button>
                        <button @click="submitTagging()" :disabled="taggingLoading" class="flex-[2] h-14 rounded-2xl bg-primary text-primary-foreground text-xs font-black uppercase tracking-widest shadow-xl shadow-primary/30 hover:shadow-primary/40 transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3">
                            <span x-show="taggingLoading" class="animate-spin size-4 border-2 border-white/20 border-t-white rounded-full"></span>
                            <span x-text="taggingLoading ? 'Logging...' : 'Confirm & Close Segment'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- PRODUCT DETAILS MODAL -->
        <template x-teleport="body">
            <div x-show="showProductDetailsModal" 
                 class="fixed inset-0 z-[110] flex items-center justify-center bg-zinc-950/80 backdrop-blur-md p-4" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                
                <div class="bg-white dark:bg-zinc-900 w-full max-w-2xl rounded-[32px] shadow-2xl border border-white/10 overflow-hidden flex flex-col animate-in zoom-in-95 duration-300 ease-out max-h-[90vh]" @click.away="showProductDetailsModal = false">
                    <div class="flex-1 overflow-y-auto custom-scrollbar relative">
                        <!-- Top Banner / Image -->
                        <div class="h-64 relative bg-muted group">
                            <img :src="detailedProduct?.image_url" class="size-full object-contain p-8 bg-zinc-50 dark:bg-zinc-800/50" onerror="this.src='https://placehold.co/800x400?text=Product+Image'">
                            <button @click="showProductDetailsModal = false" class="absolute top-6 right-6 p-2 bg-white/20 hover:bg-white/40 backdrop-blur-lg rounded-full text-white transition-all shadow-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                            <div class="absolute bottom-6 left-6 flex gap-2">
                                <span class="px-3 py-1 bg-primary text-primary-foreground rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg" x-text="detailedProduct?.category"></span>
                                <template x-if="detailedProduct?.is_organic">
                                    <span class="px-3 py-1 bg-emerald-500 text-white rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg flex items-center gap-1">
                                        <svg class="size-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg> 100% Organic
                                    </span>
                                </template>
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-1" x-text="detailedProduct?.brand || 'Generic'"></p>
                                    <h3 class="text-3xl font-black tracking-tight" x-text="detailedProduct?.name"></h3>
                                    <p class="text-xs font-mono text-muted-foreground mt-1" x-text="'SKU: ' + (detailedProduct?.sku || 'N/A')"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-1">Our Price</p>
                                    <p class="text-3xl font-black text-primary tracking-tighter">Rs <span x-text="parseFloat(detailedProduct?.price || 0).toFixed(2)"></span></p>
                                    <p class="text-[10px] font-bold text-muted-foreground" x-text="'per ' + (detailedProduct?.unit_type || 'kg')"></p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-8">
                                <div class="p-4 rounded-2xl bg-muted/30 border border-border/50">
                                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-2">Available Stock</p>
                                    <p class="text-xl font-black" :class="detailedProduct?.stock_on_hand > 0 ? 'text-foreground' : 'text-destructive'">
                                        <span x-text="detailedProduct?.stock_on_hand || 0"></span>
                                        <span class="text-sm font-bold text-muted-foreground ml-1 font-mono" x-text="detailedProduct?.unit_type"></span>
                                    </p>
                                </div>
                                <div class="p-4 rounded-2xl bg-muted/30 border border-border/50">
                                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-2">Product Origin</p>
                                    <p class="text-sm font-bold truncate" x-text="detailedProduct?.origin || 'Global Market'"></p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h4 class="text-[10px] font-black text-muted-foreground uppercase tracking-widest flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                                    Product Description
                                </h4>
                                <div class="text-sm leading-relaxed text-muted-foreground font-medium bg-muted/20 p-6 rounded-[24px]" x-text="detailedProduct?.description || 'No detailed description provided for this product.'"></div>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 border-t border-border flex gap-4 bg-muted/10">
                        <x-ui.button @click="showProductDetailsModal = false" variant="outline" class="flex-1 h-14 rounded-2xl">
                            Close Details
                        </x-ui.button>
                        <template x-if="detailedProduct?.stock_on_hand > 0">
                            <x-ui.button @click="addToCart(detailedProduct); showProductDetailsModal = false" class="flex-[2] h-14 rounded-2xl shadow-xl shadow-primary/20">
                                Add to Order (Rs <span x-text="parseFloat(detailedProduct?.price || 0).toFixed(2)"></span>)
                            </x-ui.button>
                        </template>
                    </div>
                </div>
            </div>
        </template>
        
        <!-- Header / Progress Bar -->
        <div class="flex-none bg-background border-b border-border px-8 py-4 flex items-center justify-between z-20 shadow-sm sticky top-0">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">New Order</h1>
                <p class="text-xs text-muted-foreground">Create a new sales order in 3 simple steps</p>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Step 1 Indicator -->
                <div @click="goToStep(1)" class="flex items-center gap-2 transition-colors duration-300 cursor-pointer" :class="step >= 1 ? 'text-primary' : 'text-muted-foreground'">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300 shadow-sm"
                             :class="step >= 1 ? 'border-primary bg-primary text-primary-foreground shadow-primary/25' : 'border-border bg-background'">
                            <span x-show="step > 1">✓</span>
                            <span x-show="step <= 1">1</span>
                        </div>
                    </div>
                    <span class="hidden sm:inline font-medium text-sm">Customer</span>
                </div>
                <div class="w-12 h-0.5 rounded-full transition-colors duration-300" :class="step >= 2 ? 'bg-primary' : 'bg-border'"></div>
                
                <!-- Step 2 Indicator -->
                <div @click="goToStep(2)" class="flex items-center gap-2 transition-colors duration-300 cursor-pointer" :class="step >= 2 ? 'text-primary' : 'text-muted-foreground'">
                     <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300 shadow-sm"
                         :class="step >= 2 ? 'border-primary bg-primary text-primary-foreground shadow-primary/25' : 'border-border bg-background'">
                         <span x-show="step > 2">✓</span>
                         <span x-show="step <= 2">2</span>
                    </div>
                    <span class="hidden sm:inline font-medium text-sm">Products</span>
                </div>
                <div class="w-12 h-0.5 rounded-full transition-colors duration-300" :class="step >= 3 ? 'bg-primary' : 'bg-border'"></div>
                
                <!-- Step 3 Indicator -->
                <div @click="goToStep(3)" class="flex items-center gap-2 transition-colors duration-300 cursor-pointer" :class="step >= 3 ? 'text-primary' : 'text-muted-foreground'">
                     <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300 shadow-sm"
                         :class="step >= 3 ? 'border-primary bg-primary text-primary-foreground shadow-primary/25' : 'border-border bg-background'">
                        3
                    </div>
                    <span class="hidden sm:inline font-medium text-sm">Review</span>
                </div>
            </div>

            <x-ui.button variant="ghost" @click="requestCloseSession()" size="sm" class="text-muted-foreground hover:text-destructive">
                Cancel
            </x-ui.button>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 w-full relative">
            <div class="p-6">
                <div class="max-w-7xl mx-auto h-full">
                    
                    <!-- STEP 1: CUSTOMER SELECTION / VERIFICATION -->
                    <div x-show="step === 1" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="flex flex-col items-center justify-center max-w-4xl mx-auto min-h-[500px] w-full">
                        
                        <!-- Mode A: Search (Shown if no customer selected) -->
                        <template x-if="!selectedCustomer">
                            <div class="w-full max-w-2xl bg-card bg-white dark:bg-zinc-900 border border-border rounded-2xl shadow-lg p-10 text-center relative overflow-hidden">
                                <!-- Background Decoration -->
                                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-primary/50 to-primary"></div>
                                
                                <h2 class="text-2xl font-bold mb-2 text-foreground">Select Customer</h2>
                                <p class="text-muted-foreground mb-8">Search to start a new order. You can add a new customer if needed.</p>
                                
                                <div class="relative max-w-lg mx-auto mb-6 group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-muted-foreground group-focus-within:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <input type="text" 
                                           x-model="customerQuery" 
                                           @input.debounce.300ms="searchCustomers()"
                                           placeholder="Search by Name, Mobile, or Code..." 
                                           class="w-full pl-11 pr-4 py-4 rounded-xl border-2 border-border bg-background/50 focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-lg shadow-sm"
                                           autofocus>
                                </div>

                                <!-- Search Results -->
                                <div class="max-h-[300px] overflow-y-auto custom-scrollbar text-left rounded-xl border border-border bg-background/50 shadow-inner p-2 min-h-[100px]">
                                    <template x-if="customerQuery.length < 2 && customerResults.length === 0">
                                        <div class="h-full flex flex-col items-center justify-center text-muted-foreground py-8 opacity-60">
                                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            <span class="text-sm">Start typing to find customers</span>
                                        </div>
                                    </template>

                                    <template x-for="cust in customerResults" :key="cust.id">
                                        <div @click="selectCustomer(cust, false)" 
                                             class="group flex items-center justify-between p-3 rounded-lg hover:bg-primary/5 border border-transparent hover:border-primary/20 cursor-pointer transition-all mb-1">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary/10 to-primary/20 text-primary flex items-center justify-center font-bold text-lg shadow-sm group-hover:scale-105 transition-transform">
                                                    <span x-text="cust.first_name.charAt(0)"></span><span x-text="(cust.last_name || '').charAt(0)"></span>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-foreground text-lg leading-tight" x-text="cust.first_name + ' ' + (cust.last_name || '')"></p>
                                                    <p class="text-sm text-muted-foreground" x-text="cust.mobile + ' • ' + cust.customer_code"></p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                 <div class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Balance</div>
                                                 <div class="font-mono font-medium" :class="cust.outstanding_balance > 0 ? 'text-red-600' : 'text-green-600'">
                                                    Rs <span x-text="cust.outstanding_balance || '0.00'"></span>
                                                 </div>
                                            </div>
                                        </div>
                                    </template>
                                   
                                    <div x-show="customerQuery.length > 2 && customerResults.length === 0" class="text-center py-6">
                                        <p class="text-muted-foreground mb-3">No customers found.</p>
                                        <button @click="openCreateCustomerModal()" class="inline-flex items-center gap-2 text-sm bg-primary text-primary-foreground px-5 py-2.5 rounded-full hover:bg-primary/90 transition-transform active:scale-95 shadow-lg shadow-primary/20">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            Create New Customer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Mode B: Customer Details (Shown if customer selected) -->
                        <template x-if="selectedCustomer">
                            <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-6 animate-in fade-in slide-in-from-bottom-5">
                                <!-- Left Column: Customer Profile Card -->
                                <div class="md:col-span-2 space-y-6">
                                    <div class="bg-card border border-border rounded-2xl shadow-xl overflow-hidden">
                                        <div class="bg-gradient-to-r from-primary/10 to-transparent p-8 border-b border-border flex items-center gap-6">
                                            <div class="w-20 h-20 rounded-2xl bg-primary flex items-center justify-center text-primary-foreground text-3xl font-bold shadow-lg shadow-primary/30">
                                                <span x-text="selectedCustomer.first_name.charAt(0)"></span><span x-text="(selectedCustomer.last_name || '').charAt(0)"></span>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between mb-1">
                                                    <h2 class="text-3xl font-bold text-foreground" x-text="selectedCustomer.first_name + ' ' + (selectedCustomer.last_name || '')"></h2>
                                                    <span class="px-3 py-1 bg-emerald-500/10 text-emerald-600 rounded-full text-xs font-bold uppercase tracking-widest border border-emerald-500/20">Active</span>
                                                </div>
                                                <p class="text-muted-foreground flex items-center gap-3">
                                                    <span class="flex items-center gap-1 font-mono bg-muted/50 px-2 py-0.5 rounded" x-text="selectedCustomer.customer_code"></span>
                                                    <span>•</span>
                                                    <span x-text="selectedCustomer.mobile"></span>
                                                    <span>•</span>
                                                    <span class="capitalize" x-text="selectedCustomer.type || 'farmer'"></span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="p-8 grid grid-cols-2 gap-8">
                                            <div>
                                                <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-widest mb-4">Contact Information</h3>
                                                <div class="space-y-4 text-sm">
                                                    <div class="flex items-center gap-3 text-foreground">
                                                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                                        <span x-text="selectedCustomer.email || 'No email provided'"></span>
                                                    </div>
                                                    <div class="flex items-center gap-3 text-foreground">
                                                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                                        <span x-text="selectedCustomer.mobile"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-widest mb-4">Finance & Credit</h3>
                                                <div class="space-y-4">
                                                    <div class="flex justify-between items-end border-b border-border/50 pb-2">
                                                        <span class="text-sm text-muted-foreground">Outstanding</span>
                                                        <span class="text-xl font-bold font-mono" :class="selectedCustomer.outstanding_balance > 0 ? 'text-red-600' : 'text-emerald-600'">
                                                            Rs <span x-text="selectedCustomer.outstanding_balance || '0.00'"></span>
                                                        </span>
                                                    </div>
                                                    <div class="flex justify-between items-end">
                                                        <span class="text-sm text-muted-foreground">Credit Limit</span>
                                                        <span class="text-sm font-bold text-foreground">Rs <span x-text="selectedCustomer.credit_limit || '0.00'"></span></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="bg-card border border-border rounded-2xl p-6 shadow-lg">
                                            <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-widest mb-4">Default Billing Address</h3>
                                            <template x-if="selectedCustomer.addresses && selectedCustomer.addresses.length > 0">
                                                <div class="text-sm space-y-1">
                                                    <p class="font-bold text-foreground" x-text="selectedCustomer.addresses[0].address_line1"></p>
                                                    <p class="text-muted-foreground" x-text="selectedCustomer.addresses[0].address_line2"></p>
                                                    <p class="text-muted-foreground" x-text="(selectedCustomer.addresses[0].village || '') + (selectedCustomer.addresses[0].state ? ', ' + selectedCustomer.addresses[0].state : '')"></p>
                                                    <p class="font-mono text-xs mt-2 bg-muted px-2 py-1 rounded inline-block" x-text="selectedCustomer.addresses[0].pincode"></p>
                                                </div>
                                            </template>
                                            <template x-if="!selectedCustomer.addresses || selectedCustomer.addresses.length === 0">
                                                <p class="text-sm text-muted-foreground italic">No address found. Please add an address to proceed.</p>
                                            </template>
                                        </div>
                                         <div class="bg-card border border-border rounded-2xl p-6 shadow-lg">
                                            <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-widest mb-4">Agriculture Details</h3>
                                            <div class="space-y-3">
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-muted-foreground">Land Area</span>
                                                    <span class="font-bold capitalize" x-text="(selectedCustomer.land_area || '0') + ' ' + (selectedCustomer.land_unit || 'acre')"></span>
                                                </div>
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-muted-foreground">Main Crops</span>
                                                    <span class="font-bold" x-text="(selectedCustomer.crops?.primary?.join(', ')) || 'None'"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: Verification & Actions -->
                                <div class="space-y-6">
                                    <div class="bg-primary/5 border border-primary/20 rounded-2xl p-8 flex flex-col h-full shadow-inner">
                                        <h3 class="text-lg font-bold text-foreground mb-4">Customer Verified?</h3>
                                        <p class="text-sm text-muted-foreground mb-8">Please confirm the customer details. You can change the customer if needed.</p>
                                        
                                        <div class="mt-auto space-y-3">
                                            <button @click="requestCloseSession()" class="w-full bg-background border border-border text-foreground hover:bg-muted py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                                                Change Customer
                                            </button>
                                            <button @click="step = 2" class="w-full bg-primary text-primary-foreground py-4 rounded-xl font-bold hover:bg-primary/90 shadow-lg shadow-primary/25 transition-all hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 group">
                                                Next: Select Products
                                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Create Customer Modal -->
                    <div x-show="showCreateCustomerModal" 
                         x-transition.opacity
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" 
                         style="display: none;">
                        <div class="bg-card w-full max-w-2xl rounded-2xl shadow-2xl relative border border-border animate-in fade-in zoom-in-95 duration-200 overflow-hidden flex flex-col max-h-[90vh]" @click.away="showCreateCustomerModal = false">
                            
                            <!-- Header -->
                            <div class="px-6 py-5 border-b border-border flex justify-between items-center bg-muted/30">
                                <div>
                                    <h3 class="text-xl font-bold" x-text="newCustomer.id ? 'Edit Customer Details' : 'Add New Customer'"></h3>
                                    <p class="text-sm text-muted-foreground" x-text="newCustomer.id ? 'Update the customer profile details.' : 'Fill in the details to create a new customer profile.'"></p>
                                </div>
                                <button @click="showCreateCustomerModal = false" class="text-muted-foreground hover:text-foreground transition-colors p-2 hover:bg-muted rounded-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                            
                            <!-- Scrollable Content -->
                            <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                                <form @submit.prevent>
                                    
                                    <!-- Section: Identity -->
                                    <div class="mb-6">
                                        <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-3">Identity & Contact</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium mb-1.5">First Name <span class="text-red-500">*</span></label>
                                                <input type="text" x-model="newCustomer.first_name" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm" placeholder="e.g. Rahul">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1.5">Last Name</label>
                                                <input type="text" x-model="newCustomer.last_name" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm" placeholder="e.g. Sharma">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1.5">Mobile Number <span class="text-red-500">*</span></label>
                                                <input type="text" x-model="newCustomer.mobile" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm" placeholder="10-digit number">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1.5">Email (Optional)</label>
                                                <input type="email" x-model="newCustomer.email" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm" placeholder="email@example.com">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="h-px bg-border/50 my-6"></div>

                                    <!-- Section: Classification -->
                                    <div class="mb-6">
                                        <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-3">Classification</h4>
                                         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium mb-1.5">Customer Type</label>
                                                <select x-model="newCustomer.type" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                                                    <option value="farmer">Farmer</option>
                                                    <option value="buyer">Buyer</option>
                                                    <option value="dealer">Dealer</option>
                                                    <option value="vendor">Vendor</option>
                                                </select>
                                            </div>
                                         </div>
                                    </div>

                                     <div class="h-px bg-border/50 my-6"></div>

                                    <!-- Section: Business / Agriculture -->
                                    <div class="mb-2">
                                        <div x-show="newCustomer.type === 'farmer'">
                                            <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-3">Agriculture Details</h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium mb-1.5">Land Area</label>
                                                    <input type="number" step="0.01" x-model="newCustomer.land_area" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium mb-1.5">Unit</label>
                                                     <select x-model="newCustomer.land_unit" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                                                        <option value="acre">Acre</option>
                                                        <option value="hectare">Hectare</option>
                                                        <option value="guntha">Guntha</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div x-show="newCustomer.type !== 'farmer'">
                                            <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-3">Business Information</h4>
                                            <div class="mb-4">
                                                <label class="block text-sm font-medium mb-1.5">Company Name</label>
                                                <input type="text" x-model="newCustomer.company_name" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium mb-1.5">GST Number</label>
                                                    <input type="text" x-model="newCustomer.gst_number" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm uppercase">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium mb-1.5">PAN Number</label>
                                                    <input type="text" x-model="newCustomer.pan_number" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm uppercase">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Error Display -->
                                    <div x-show="newCustomerError" x-transition class="mt-4 text-destructive text-sm bg-destructive/10 p-3 rounded-lg flex items-start gap-2 border border-destructive/20">
                                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <span x-text="newCustomerError"></span>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Footer -->
                            <div class="px-6 py-4 border-t border-border bg-muted/30 flex justify-end gap-3 flex-none">
                                <button @click="showCreateCustomerModal = false" class="px-5 py-2.5 text-sm font-medium text-muted-foreground hover:bg-background rounded-lg transition-colors border border-transparent hover:border-border">Cancel</button>
                                <button @click="createCustomer()" class="px-6 py-2.5 text-sm font-bold bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 shadow-lg shadow-primary/25 transition-all hover:-translate-y-0.5 active:translate-y-0 active:scale-95 flex items-center gap-2">
                                    <span x-text="newCustomer.id ? 'Save Changes' : 'Create & Select'"></span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: PRODUCT SELECTION -->
                    <div x-show="step === 2" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-x-4"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         class="flex gap-6 h-full items-start">
                        
                        <!-- Left: Product Catalog -->
                        <div class="flex-1 flex flex-col h-full min-h-0">
                            <!-- Search & Filter Bar -->
                            <div class="flex gap-4 mb-6 sticky top-0 z-10 bg-muted/5 pt-1 backdrop-blur-sm">
                                <div class="relative flex-1 group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-muted-foreground group-focus-within:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <input type="text"
                                           x-model="productQuery"
                                           @input.debounce.300ms="searchProducts()" 
                                           placeholder="Search products..." 
                                           class="w-full pl-10 pr-4 py-3 rounded-xl border border-border bg-card shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                </div>
                                <select class="rounded-xl border border-border bg-card px-4 py-2 pr-8 text-sm shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                        @change="productQuery=$event.target.value; searchProducts()">
                                    <option value="">All Categories</option>
                                    @foreach(\App\Models\Category::where('is_active', true)->get() as $cat)
                                        <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Product Table -->
                            <div class="flex-1 overflow-hidden border border-border rounded-xl bg-card shadow-sm flex flex-col mb-6">
                                <div class="overflow-y-auto custom-scrollbar flex-1">
                                    <table class="w-full text-left border-collapse">
                                        <thead class="bg-muted/50 sticky top-0 z-10 backdrop-blur-md shadow-sm">
                                            <tr>
                                                <th class="p-4 text-xs font-bold text-muted-foreground uppercase tracking-wider w-20">Image</th>
                                                <th class="p-4 text-xs font-bold text-muted-foreground uppercase tracking-wider">Product Details</th>
                                                <th class="p-4 text-xs font-bold text-muted-foreground uppercase tracking-wider text-center">Stock</th>
                                                <th class="p-4 text-xs font-bold text-muted-foreground uppercase tracking-wider text-right">Price</th>
                                                <th class="p-4 text-xs font-bold text-muted-foreground uppercase tracking-wider text-right w-40">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border">
                                            <template x-for="product in productResults" :key="product.id">
                                                <tr class="group hover:bg-muted/30 transition-colors">
                                                    <!-- Image -->
                                                    <td class="p-4">
                                                        <div class="w-16 h-16 rounded-lg bg-muted overflow-hidden border border-border shadow-sm group-hover:scale-105 transition-transform">
                                                            <img :src="product.image_url" 
                                                                 class="w-full h-full object-cover"
                                                                 onerror="this.src='https://placehold.co/100x100?text=IMG'">
                                                        </div>
                                                    </td>
                                                    
                                                    <!-- Details -->
                                                    <td class="p-4">
                                                        <div class="flex flex-col">
                                                            <span class="text-xs text-primary font-semibold mb-0.5" x-text="product.category || 'General'"></span>
                                                            <span class="font-bold text-foreground text-base" x-text="product.name"></span>
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <span class="text-[10px] text-muted-foreground font-mono" x-text="product.sku"></span>
                                                                <button type="button" @click="detailedProduct = product; showProductDetailsModal = true" class="text-[10px] text-primary hover:underline font-bold uppercase tracking-wider">
                                                                    View Details
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    
                                                    <!-- Stock -->
                                                    <td class="p-4 text-center">
                                                        <div class="inline-flex flex-col items-center">
                                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold border"
                                                                  :class="product.stock_on_hand > 0 
                                                                    ? 'bg-green-500/10 text-green-600 border-green-500/20' 
                                                                    : 'bg-red-500/10 text-red-600 border-red-500/20'">
                                                                <span x-text="product.stock_on_hand > 0 ? 'In Stock' : 'Out of Stock'"></span>
                                                            </span>
                                                            <span x-show="product.stock_on_hand > 0" class="text-xs text-muted-foreground mt-1 font-mono">
                                                                <span x-text="product.stock_on_hand"></span> <span x-text="product.unit_type"></span>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    
                                                    <!-- Price -->
                                                    <td class="p-4 text-right">
                                                        <span class="font-bold text-lg text-foreground font-mono">Rs <span x-text="parseFloat(product.price).toFixed(2)"></span></span>
                                                    </td>
                                                    
                                                    <!-- Action -->
                                                    <td class="p-4 text-right">
                                                        <div x-data="{ qty: 1 }" class="flex justify-end">
                                                            <template x-if="!isInCart(product.id)">
                                                                <button @click="addToCart(product)" 
                                                                        :disabled="product.stock_on_hand <= 0"
                                                                        class="rounded-lg px-4 py-2 font-semibold text-sm transition-all shadow-sm flex items-center gap-2 border border-transparent"
                                                                        :class="product.stock_on_hand > 0 ? 'bg-primary text-primary-foreground hover:bg-primary/90 hover:shadow-primary/20 hover:-translate-y-0.5 active:translate-y-0' : 'bg-muted text-muted-foreground cursor-not-allowed border-border opacity-50'">
                                                                    <span>Add</span>
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                                                </button>
                                                            </template>
                                                            
                                                            <template x-if="isInCart(product.id)">
                                                                 <div class="flex items-center bg-background rounded-lg border border-primary/30 p-1 shadow-sm ring-2 ring-primary/5">
                                                                    <button @click="updateCartQty(product.id, -1)" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-muted text-foreground transition-colors font-bold text-lg">-</button>
                                                                    <span class="w-10 text-center font-bold text-primary font-mono" x-text="getCartQty(product.id)"></span>
                                                                    <button @click="updateCartQty(product.id, 1)" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-muted text-foreground transition-colors font-bold text-lg" :disabled="getCartQty(product.id) >= product.stock_on_hand" :class="getCartQty(product.id) >= product.stock_on_hand ? 'opacity-30 cursor-not-allowed' : ''">+</button>
                                                                 </div>
                                                            </template>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                    
                                    <template x-if="productResults.length === 0">
                                        <div class="flex flex-col items-center justify-center py-20 text-muted-foreground">
                                            <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                            </div>
                                            <p class="font-medium">No products found</p>
                                            <p class="text-sm opacity-70">Try modifying your search query</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Cart Sidebar (Glassmorphism) -->
                        <div class="w-96 flex-none hidden lg:block h-full pb-6">
                            <div class="sticky top-0 bg-card/80 backdrop-blur-xl border border-border/50 rounded-2xl shadow-xl flex flex-col h-full max-h-[calc(100vh-140px)] ring-1 ring-black/5">
                                <!-- Cart Header -->
                                <div class="p-5 border-b border-border/50 bg-gradient-to-b from-primary/5 to-transparent rounded-t-2xl">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-bold text-lg flex items-center gap-2">
                                            <div class="bg-primary/10 p-1.5 rounded-lg text-primary">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                            </div>
                                            Current Order
                                        </h3>
                                        <span class="text-xs font-mono bg-muted px-2 py-1 rounded text-muted-foreground" x-text="cart.length + ' Items'"></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground bg-background/50 p-2 rounded-lg border border-border/50">
                                        <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0">
                                            <span x-text="selectedCustomer ? selectedCustomer.first_name.charAt(0) : '?'"></span>
                                        </div>
                                        <span class="truncate" x-text="selectedCustomer ? 'For: ' + selectedCustomer.first_name + ' ' + selectedCustomer.last_name : 'No Customer Selected'"></span>
                                        <button type="button" @click="goToStep(1)" class="text-[10px] text-primary hover:underline ml-auto shrink-0 font-bold uppercase transition-all">Change</button>
                                    </div>
                                </div>
                                
                                <!-- Cart Items -->
                                <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                                    <template x-if="cart.length === 0">
                                        <div class="h-full flex flex-col items-center justify-center text-muted-foreground space-y-4 opacity-60">
                                            <svg class="w-16 h-16 text-muted-foreground/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            <p class="text-sm font-medium">Your cart is empty</p>
                                        </div>
                                    </template>
                                    <template x-for="(item, idx) in cart" :key="item.product_id">
                                        <div class="group relative flex gap-3 p-3 rounded-xl bg-background border border-border hover:border-primary/30 transition-all shadow-sm">
                                            <div class="w-12 h-12 rounded-lg bg-muted flex items-center justify-center overflow-hidden shrink-0">
                                                <img :src="item.image_url" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/100x100?text=IMG'">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start">
                                                    <p class="font-medium text-sm text-foreground truncate" x-text="item.name"></p>
                                                    <p class="font-bold text-sm">
                                                        <span x-show="item.discount_value > 0" class="text-[10px] text-muted-foreground line-through mr-1" x-text="'Rs ' + (item.price * item.quantity).toFixed(2)"></span>
                                                        Rs <span x-text="((item.price * item.quantity) - (item.discount_type === 'percent' ? (item.price * item.quantity * item.discount_value / 100) : item.discount_value)).toFixed(2)"></span>
                                                    </p>
                                                </div>
                                                <div class="flex items-center justify-between mt-1">
                                                    <p class="text-[10px] text-muted-foreground">Rs <span x-text="item.price.toFixed(2)"></span> x <span x-text="item.quantity"></span></p>
                                                    <div class="flex items-center gap-2">
                                                        <button @click="updateCartQty(item.product_id, -1)" class="w-6 h-6 flex items-center justify-center rounded bg-muted hover:bg-muted/80 text-foreground transition-colors font-bold text-xs">-</button>
                                                        <span class="text-xs font-bold w-4 text-center" x-text="item.quantity"></span>
                                                        <button @click="updateCartQty(item.product_id, 1)" class="w-6 h-6 flex items-center justify-center rounded bg-muted hover:bg-muted/80 text-foreground transition-colors font-bold text-xs" :disabled="item.quantity >= item.max_stock">+</button>
                                                    </div>
                                                </div>
                                                <!-- Item Discount -->
                                                <div class="mt-2 flex items-center gap-2">
                                                    <select x-model="item.discount_type" class="text-[10px] h-6 rounded border-border bg-transparent px-1 focus:ring-primary/20">
                                                        <option value="fixed">Rs</option>
                                                        <option value="percent">%</option>
                                                    </select>
                                                    <input type="number" x-model="item.discount_value" 
                                                           class="h-6 w-16 text-[10px] rounded border border-border bg-transparent px-2 focus:outline-none focus:ring-2 focus:ring-primary/20" 
                                                           placeholder="Disc">
                                                </div>
                                            </div>
                                            <div class="absolute top-2 right-2">
                                                <button @click="removeFromCart(idx)" class="text-muted-foreground hover:text-red-500 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                
                                <!-- Cart Footer -->
                                <div class="p-5 border-t border-border bg-muted/20 rounded-b-xl space-y-4">
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-xs text-muted-foreground">
                                            <span>Subtotal</span>
                                            <span>Rs <span x-text="subTotal.toFixed(2)"></span></span>
                                        </div>
                                        <div class="flex justify-between text-xs text-primary" x-show="cartDiscountTotal > 0">
                                            <span>Product Discounts</span>
                                            <span>- Rs <span x-text="cartDiscountTotal.toFixed(2)"></span></span>
                                        </div>
                                        <div class="flex justify-between items-center bg-primary/5 p-2 rounded-lg border border-primary/20">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold uppercase">Order Disc</span>
                                                <select x-model="order.discount_type" class="text-[10px] h-6 rounded border-primary/20 bg-transparent px-1 focus:ring-primary/20">
                                                    <option value="fixed">Rs</option>
                                                    <option value="percent">%</option>
                                                </select>
                                            </div>
                                            <input type="number" x-model="order.discount_value" 
                                                   class="h-6 w-20 text-xs text-right rounded border border-primary/20 bg-transparent px-2 focus:outline-none focus:ring-2 focus:ring-primary/20 font-bold" 
                                                   placeholder="0.00">
                                        </div>
                                        <div class="pt-2 border-t border-border/50 flex justify-between items-end">
                                            <span class="text-muted-foreground text-sm font-bold uppercase tracking-widest">Grand Total</span>
                                            <span class="font-black text-2xl text-primary tracking-tighter">Rs <span x-text="grandTotal.toFixed(2)"></span></span>
                                        </div>
                                    </div>
                                    <button @click="if(cart.length > 0) step = 3" 
                                            :disabled="cart.length === 0"
                                            class="w-full bg-primary text-primary-foreground py-3.5 rounded-xl font-bold hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2 group">
                                        <span>Proceed to Review</span>
                                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: REVIEW & SUBMIT -->
                    <div x-show="step === 3" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100">
                         
                       <form action="{{ route('central.orders.update', $orderData->id) }}" method="POST" id="orderForm" class="max-w-6xl mx-auto" @submit.prevent="submitOrder">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="customer_id" :value="selectedCustomer?.id">
                        
                        <!-- Hidden inputs for array -->
                        <template x-for="(item, idx) in cart" :key="idx">
                            <div>
                                <input type="hidden" :name="'items['+idx+'][product_id]'" :value="item.product_id">
                                <input type="hidden" :name="'items['+idx+'][quantity]'" :value="item.quantity">
                                <input type="hidden" :name="'items['+idx+'][price]'" :value="item.price">
                            </div>
                        </template>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10">
                            <!-- Left: Order Details -->
                            <div class="lg:col-span-2 space-y-6">
                                
                                <div class="bg-card border border-border rounded-xl shadow-sm p-6 relative overflow-hidden">
                                    <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
                                    <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 relative z-10">
                                        <div>
                                            <div class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1">Customer</div>
                                            <h3 class="font-bold text-lg text-foreground flex items-center gap-2">
                                                <span x-text="selectedCustomer?.first_name + ' ' + (selectedCustomer?.last_name || '')"></span>
                                                <span x-show="selectedCustomer?.type" class="px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] border border-primary/20 uppercase" x-text="selectedCustomer?.type"></span>
                                            </h3>
                                            <div class="flex items-center gap-4 mt-2 text-sm text-muted-foreground">
                                                <span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg> <span x-text="selectedCustomer?.mobile"></span></span>
                                            </div>
                                        </div>
                                        <div class="flex gap-3">
                                             <button type="button" @click="editCustomerDetails()" class="text-sm font-semibold text-primary hover:text-primary/80 transition-colors flex items-center gap-1 bg-primary/10 px-3 py-1.5 rounded-lg border border-primary/20">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Edit Details
                                            </button>
                                            <button type="button" @click="step = 1" class="text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1 px-3 py-1.5">
                                                Change
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Address Selection -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Billing Address -->
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-bold text-muted-foreground uppercase tracking-wider">Billing Address</h4>
                                            <button type="button" @click.prevent="openAddressModal('billing')" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1 z-20 relative">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                Add New
                                            </button>
                                        </div>
                                        
                                        <div class="grid gap-3">
                                            <template x-if="!selectedCustomer?.addresses || selectedCustomer.addresses.length === 0">
                                                <div class="p-4 border border-dashed border-border rounded-lg text-center text-sm text-muted-foreground bg-muted/30">
                                                    No addresses found. Please add one.
                                                </div>
                                            </template>
                                            
                                            <template x-for="addr in selectedCustomer?.addresses" :key="addr.id">
                                                <label class="cursor-pointer relative block">
                                                    <input type="radio" name="billing_address_id" :value="addr.id" x-model="order.billing_address_id" class="peer sr-only">
                                                    <div class="p-3 rounded-lg border border-border bg-card peer-checked:border-primary peer-checked:ring-1 peer-checked:ring-primary peer-checked:bg-primary/5 transition-all w-full text-left group hover:border-primary/50">
                                                        <div class="flex justify-between items-start mb-1">
                                                            <span class="font-bold text-sm flex items-center gap-2">
                                                                <span x-text="addr.label || 'Address'"></span>
                                                                <span x-show="addr.is_default" class="text-[10px] bg-primary text-primary-foreground px-1.5 py-0.5 rounded">Default</span>
                                                            </span>
                                                            <div class="flex gap-2 transition-opacity z-20 relative">
                                                                <button type="button" @click.prevent.stop="editAddress(addr)" class="text-xs text-primary hover:underline font-medium px-2 py-1 rounded hover:bg-primary/10">Edit</button>
                                                            </div>
                                                        </div>
                                                        <p class="text-xs text-muted-foreground leading-relaxed">
                                                            <span x-text="addr.address_line1"></span><span x-show="addr.address_line2">, <span x-text="addr.address_line2"></span></span><br>
                                                            <span x-text="addr.village || addr.city"></span>, <span x-text="addr.state"></span> - <span x-text="addr.pincode"></span>
                                                        </p>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Shipping Address -->
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-bold text-muted-foreground uppercase tracking-wider">Shipping Address</h4>
                                            <div class="flex items-center gap-3">
                                                <label class="flex items-center gap-1.5 text-xs text-muted-foreground cursor-pointer select-none">
                                                    <input type="checkbox" x-model="order.same_as_billing" class="rounded border-border text-primary focus:ring-primary/20 w-3.5 h-3.5">
                                                    Same as Billing
                                                </label>
                                                <button type="button" @click.prevent="openAddressModal('shipping')" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1 z-20 relative">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                    Add New
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div x-show="!order.same_as_billing" class="grid gap-3 relative">
                                            <template x-if="!selectedCustomer?.addresses || selectedCustomer.addresses.length === 0">
                                                <div class="p-4 border border-dashed border-border rounded-lg text-center text-sm text-muted-foreground bg-muted/30">
                                                    No addresses found.
                                                </div>
                                            </template>

                                            <template x-for="addr in selectedCustomer?.addresses" :key="'ship-'+addr.id">
                                                <label class="cursor-pointer relative block">
                                                    <input type="radio" name="shipping_address_id" :value="addr.id" x-model="order.shipping_address_id" class="peer sr-only">
                                                    <div class="p-3 rounded-lg border border-border bg-card peer-checked:border-primary peer-checked:ring-1 peer-checked:ring-primary peer-checked:bg-primary/5 transition-all w-full text-left group hover:border-primary/50">
                                                        <div class="flex justify-between items-start mb-1">
                                                            <span class="font-bold text-sm flex items-center gap-2">
                                                                <span x-text="addr.label || 'Address'"></span>
                                                            </span>
                                                            <div class="flex gap-2 transition-opacity z-20 relative">
                                                                 <button type="button" @click.prevent.stop="editAddress(addr)" class="text-xs text-primary hover:underline font-medium px-2 py-1 rounded hover:bg-primary/10">Edit</button>
                                                            </div>
                                                        </div>
                                                        <p class="text-xs text-muted-foreground leading-relaxed">
                                                            <span x-text="addr.address_line1"></span><span x-show="addr.address_line2">, <span x-text="addr.address_line2"></span></span><br>
                                                            <span x-text="addr.village || addr.city"></span>, <span x-text="addr.state"></span> - <span x-text="addr.pincode"></span>
                                                        </p>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                        <div x-show="order.same_as_billing" class="p-4 rounded-lg bg-muted/30 border border-dashed border-border text-center flex flex-col items-center justify-center py-8">
                                            <svg class="w-8 h-8 text-muted-foreground/30 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <p class="text-sm text-muted-foreground font-medium">Shipping address same as billing</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-card border border-border rounded-xl shadow-sm p-6">
                                    <h3 class="font-semibold text-lg mb-6 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Ordered Items
                                    </h3>
                                    <div class="overflow-hidden rounded-lg border border-border">
                                        <table class="w-full text-sm">
                                            <thead class="bg-muted/50 text-muted-foreground text-xs uppercase font-semibold">
                                                <tr>
                                                    <th class="px-5 py-4 text-left">Product</th>
                                                    <th class="px-5 py-4 text-right">Unit Price</th>
                                                    <th class="px-5 py-4 text-center">Qty</th>
                                                    <th class="px-5 py-4 text-right">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-border/50 bg-background">
                                                <template x-for="item in cart" :key="item.product_id">
                                                    <tr>
                                                        <td class="px-5 py-4">
                                                            <div class="flex items-center gap-3">
                                                                <img :src="item.image_url" class="w-10 h-10 rounded object-cover bg-muted" onerror="this.src='https://placehold.co/50x50'">
                                                                <span class="font-medium text-foreground" x-text="item.name"></span>
                                                            </div>
                                                        </td>
                                                        <td class="px-5 py-4 text-right">Rs <span x-text="item.price.toFixed(2)"></span></td>
                                                        <td class="px-5 py-4 text-center font-mono" x-text="item.quantity"></td>
                                                        <td class="px-5 py-4 text-right font-bold text-foreground">Rs <span x-text="(item.price * item.quantity).toFixed(2)"></span></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="bg-card border border-border rounded-xl shadow-sm p-6 transition-all hover:shadow-md">
                                    <h3 class="font-semibold text-lg mb-6 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Order Type & Schedule
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="space-y-4">
                                            <label class="text-sm font-medium text-muted-foreground block">Is this a future order?</label>
                                            <div class="flex items-center gap-6">
                                                <label class="flex items-center gap-2 cursor-pointer group">
                                                    <input type="radio" name="is_future_order_radio" value="0" x-model="order.is_future_order" @change="order.is_future_order = false" class="peer sr-only">
                                                    <div class="w-5 h-5 rounded-full border-2 border-border flex items-center justify-center peer-checked:border-primary peer-checked:bg-primary transition-all group-hover:border-primary/50 shadow-sm">
                                                        <div class="w-1.5 h-1.5 rounded-full bg-white opacity-0 peer-checked:opacity-100"></div>
                                                    </div>
                                                    <span class="text-sm font-semibold text-muted-foreground peer-checked:text-foreground transition-colors">Immediate Order</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer group">
                                                    <input type="radio" name="is_future_order_radio" value="1" x-model="order.is_future_order" @change="order.is_future_order = true" class="peer sr-only">
                                                    <div class="w-5 h-5 rounded-full border-2 border-border flex items-center justify-center peer-checked:border-primary peer-checked:bg-primary transition-all group-hover:border-primary/50 shadow-sm">
                                                        <div class="w-1.5 h-1.5 rounded-full bg-white opacity-0 peer-checked:opacity-100"></div>
                                                    </div>
                                                    <span class="text-sm font-semibold text-muted-foreground peer-checked:text-foreground transition-colors">Future/Scheduled Order</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div x-show="order.is_future_order" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-2">
                                            <label class="block text-sm font-medium text-muted-foreground">Scheduled Date & Time <span class="text-red-500">*</span></label>
                                            <div class="relative group">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-4 h-4 text-muted-foreground group-focus-within:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                </div>
                                                <input type="datetime-local" x-model="order.scheduled_at" 
                                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-border bg-background shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm"
                                                       :required="order.is_future_order">
                                            </div>
                                            <p class="text-[10px] text-muted-foreground italic">Order will be processed at the selected time.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-card border border-border rounded-xl shadow-sm p-6 transition-all hover:shadow-md">
                                    <h3 class="font-semibold text-lg mb-6 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        Logistics & Notes
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                         <div>
                                            <label class="block text-sm font-medium text-muted-foreground mb-2">Fulfillment Warehouse</label>
                                            <select name="warehouse_id" class="w-full rounded-lg border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-1 focus:ring-primary py-2.5 transition-all" required>
                                                @foreach($warehouses as $wh)
                                                    <option value="{{ $wh->id }}" {{ $orderData->warehouse_id == $wh->id ? 'selected' : '' }}>{{ $wh->name }} ({{ $wh->code }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                         <div>
                                              <label class="block text-sm font-medium text-muted-foreground mb-2">Order ID</label>
                                              <input type="text" name="order_number" value="{{ $orderData->order_number }}" class="w-full bg-muted/50 border border-border rounded-lg text-muted-foreground font-mono" readonly>
                                         </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-muted-foreground mb-2">Internal Notes</label>
                                            <textarea name="notes" rows="3" placeholder="Add any special instructions for this order..." class="w-full rounded-lg border-border bg-background shadow-sm focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Summary Sidebar -->
                            <div class="space-y-6">
                                <div class="bg-card border border-border rounded-xl shadow-lg p-6 sticky top-24">
                                    <h3 class="font-bold text-lg mb-4">Payment & Confirmation</h3>
                                    
                                    <div class="space-y-3 mb-6">
                                        <div class="flex justify-between items-center text-sm font-medium">
                                            <span class="text-muted-foreground">Subtotal</span>
                                            <span class="text-foreground">Rs <span x-text="subTotal.toFixed(2)"></span></span>
                                        </div>
                                        <div class="flex justify-between items-center text-sm font-medium text-primary" x-show="cartDiscountTotal > 0">
                                            <span>Product Discounts</span>
                                            <span>- Rs <span x-text="cartDiscountTotal.toFixed(2)"></span></span>
                                        </div>
                                        <div class="flex justify-between items-center text-sm font-medium text-primary" x-show="orderDiscountAmount > 0">
                                            <span>Order Discount</span>
                                            <span>- Rs <span x-text="orderDiscountAmount.toFixed(2)"></span></span>
                                        </div>
                                        <div class="flex justify-between items-center text-sm font-medium text-muted-foreground">
                                            <span>Tax (0%)</span>
                                            <span>Rs 0.00</span>
                                        </div>
                                        <div class="h-px bg-border my-2"></div>
                                        <div class="flex justify-between items-center text-xl font-bold text-primary">
                                            <span>Grand Total</span>
                                            <span>Rs <span x-text="grandTotal.toFixed(2)"></span></span>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" id="submitBtn" class="w-full bg-primary text-primary-foreground py-4 rounded-xl font-bold shadow-xl shadow-primary/30 hover:bg-primary/90 transition-transform active:scale-[0.98] flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Update Order
                                    </button>
                                    <button type="button" @click="step = 2" class="w-full mt-4 text-sm text-center text-muted-foreground hover:text-foreground hover:underline transition-colors">
                                        Back to Products
                                    </button>
                                </div>
                            </div>
                        </div>
                       </form>
                    </div>

                </div>
            </div>
        </div>
        <!-- Address Modal (Create/Edit) -->
    <div x-show="showAddressModal" 
         class="fixed inset-0 z-50 flex items-center justify-center px-4" 
         style="display: none;">
        
        <div class="fixed inset-0 bg-background/80 backdrop-blur-sm transition-opacity" @click="showAddressModal = false"></div>

        <div class="relative bg-card rounded-2xl shadow-xl w-full max-w-lg border border-border p-6 overflow-hidden transform transition-all">
            <div class="absolute top-4 right-4">
                <button @click="showAddressModal = false" class="text-muted-foreground hover:text-foreground transition-colors p-1 rounded-md hover:bg-muted">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-bold text-foreground" x-text="addressForm.id ? 'Edit Address' : 'Add New Address'"></h3>
                <p class="text-sm text-muted-foreground">Enter address details for delivery or billing.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">Label <span class="text-red-500">*</span></label>
                    <input type="text" x-model="addressForm.label" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm" placeholder="e.g. Home, Farm, Warehouse">
                </div>

                <div class="grid grid-cols-2 gap-4">
                     <div>
                        <label class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">Line 1 <span class="text-red-500">*</span></label>
                        <input type="text" x-model="addressForm.address_line1" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm" placeholder="House/Plot No">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">Line 2</label>
                        <input type="text" x-model="addressForm.address_line2" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm" placeholder="Street/Area">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">Village/City</label>
                        <input type="text" x-model="addressForm.village" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm" placeholder="Village Name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">Pincode <span class="text-red-500">*</span></label>
                        <input type="text" x-model="addressForm.pincode" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm" placeholder="6-digit code">
                    </div>
                </div>
                 
                 <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">State <span class="text-red-500">*</span></label>
                        <input type="text" x-model="addressForm.state" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm" placeholder="State Name">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 mt-8 cursor-pointer">
                            <input type="checkbox" x-model="addressForm.is_default" class="rounded border-border text-primary focus:ring-primary/20 bg-background w-4 h-4">
                            <span class="text-sm">Set as Default</span>
                        </label>
                    </div>
                </div>

                <div x-show="addressForm.error" class="text-sm text-red-500 bg-red-500/10 p-2 rounded-lg" x-text="addressForm.error"></div>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="showAddressModal = false" class="px-4 py-2 rounded-lg text-sm font-medium bg-muted hover:bg-muted/80 text-foreground transition-colors">Cancel</button>
                    <button @click="saveAddress()" class="px-4 py-2 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                        <span x-text="addressForm.loading ? 'Saving...' : 'Save Address'"></span>
                    </button>
                </div>
            </div>
        </div>

    <script>
        function orderWizard(initialProducts, preSelectedCustomer, existingOrder) {
            return {
                step: existingOrder ? 2 : (preSelectedCustomer ? 2 : 1),
                isEdit: !!existingOrder,
                
                // Order State
                order: {
                    billing_address_id: null,
                    shipping_address_id: null,
                    same_as_billing: true,
                    is_future_order: false,
                    scheduled_at: ''
                },

                // Customer State
                customerQuery: '',
                customerResults: [],
                selectedCustomer: preSelectedCustomer || null,
                showCreateCustomerModal: false,
                newCustomer: { 
                    first_name: '', 
                    last_name: '', 
                    mobile: '', 
                    email: '',
                    type: 'farmer',
                    company_name: '',
                    gst_number: '',
                    pan_number: '',
                    land_area: '',
                    land_unit: 'acre'
                },
                newCustomerError: '',

                // Address State
                showAddressModal: false,
                addressForm: {
                    id: null,
                    label: '',
                    address_line1: '',
                    address_line2: '',
                    village: '',
                    state: 'Maharashtra',
                    pincode: '',
                    is_default: false,
                    loading: false,
                    error: ''
                },

                // Product State
                productQuery: '',
                productResults: initialProducts || [],

                // Cart State
                cart: [],

                // Product Details
                showProductDetailsModal: false,
                detailedProduct: null,

                // Tagging State
                showTaggingModal: false,
                taggingLoading: false,
                tagOutcome: '',
                tagNotes: '',

                init() {
                    // Initialize from existingOrder if available
                    if (this.isEdit) {
                        this.selectedCustomer = existingOrder.customer;
                        
                        this.order = { 
                            billing_address_id: existingOrder.billing_address_id, 
                            shipping_address_id: existingOrder.shipping_address_id, 
                            same_as_billing: existingOrder.billing_address_id === existingOrder.shipping_address_id,
                            is_future_order: existingOrder.is_future_order,
                            scheduled_at: existingOrder.scheduled_at ? existingOrder.scheduled_at.replace(' ', 'T').substring(0, 16) : '',
                            discount_type: existingOrder.discount_type || 'fixed',
                            discount_value: parseFloat(existingOrder.discount_value || 0)
                        };

                        this.cart = existingOrder.items.map(item => {
                            let p = initialProducts.find(prod => prod.id === item.product_id);
                            let currentStock = p ? parseFloat(p.stock_on_hand) : 0;
                            return {
                                product_id: item.product_id,
                                name: item.product_name,
                                price: parseFloat(item.unit_price),
                                image_url: item.product?.image_url || 'https://placehold.co/100x100?text=IMG',
                                quantity: parseFloat(item.quantity),
                                max_stock: currentStock + parseFloat(item.quantity),
                                discount_type: item.discount_type || 'fixed',
                                discount_value: parseFloat(item.discount_value || 0)
                            };
                        });
                        return; // Skip loading from localstorage for edit
                    }

                    // 1. Check for Reset Parameter
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('reset')) {
                        localStorage.removeItem('order_wizard_state');
                        if (!preSelectedCustomer) {
                            this.step = 1;
                            this.selectedCustomer = null;
                            this.cart = [];
                            return;
                        }
                    }

                    // 2. If we have a pre-selected customer, prioritize it
                    if (preSelectedCustomer) {
                        this.selectCustomer(preSelectedCustomer, false);
                    }

                    // 3. Restore State from LocalStorage
                    const saved = localStorage.getItem('order_wizard_state');
                    if (saved) {
                        try {
                            const parsed = JSON.parse(saved);
                            
                            // Only restore if not already set by preSelectedCustomer
                            if (!this.selectedCustomer) {
                                this.selectedCustomer = parsed.selectedCustomer || null;
                            }
                            
                            this.step = parsed.step || 1;
                            this.cart = parsed.cart || [];
                            
                            // Merge order object
                            this.order = { 
                                billing_address_id: null, 
                                shipping_address_id: null, 
                                same_as_billing: true,
                                ...this.order, // current (including address set by selectCustomer)
                                ...parsed.order 
                            };

                            this.showTaggingModal = false;
                            this.taggingLoading = false;
                            this.tagOutcome = '';
                            this.tagNotes = '';
                            
                            this.customerQuery = parsed.customerQuery || '';
                            this.productQuery = parsed.productQuery || '';

                            if(this.productQuery && this.productQuery.length > 0) {
                                this.searchProducts();
                            }
                        } catch(e) {
                            console.error('Failed to restore state', e);
                            localStorage.removeItem('order_wizard_state');
                        }
                    }

                    // 2. Setup Watchers for Persistence
                    this.$watch('step', () => this.saveState());
                    this.$watch('selectedCustomer', () => this.saveState());
                    this.$watch('cart', () => this.saveState()); 
                    this.$watch('order', () => this.saveState());
                    
                    // Deep watchers for nested object changes
                    this.$watch('cart', () => this.saveState(), { deep: true });
                    this.$watch('order', () => this.saveState(), { deep: true });
                    this.$watch('customerQuery', () => this.saveState());
                    this.$watch('productQuery', () => this.saveState());
                },

                saveState() {
                    if (this.isEdit) return; // Don't save draft for edit session
                    
                    const state = {
                        step: this.step,
                        selectedCustomer: this.selectedCustomer,
                        cart: this.cart,
                        order: this.order,
                        customerQuery: this.customerQuery,
                        productQuery: this.productQuery
                    };
                    localStorage.setItem('order_wizard_state', JSON.stringify(state));
                },

                // API Calls
                async searchCustomers() {
                    if (this.customerQuery.length < 2) return;
                    try {
                        let res = await fetch(`{{ route('central.api.search.customers') }}?q=${this.customerQuery}`);
                        if (!res.ok) throw new Error('Network response was not ok');
                        this.customerResults = await res.json();
                    } catch(e) { 
                        console.error(e); 
                    }
                },

                async createCustomer() {
                    this.newCustomerError = '';
                    if (!this.newCustomer.first_name || !this.newCustomer.mobile) {
                        this.newCustomerError = 'First Name and Mobile are required.';
                        return;
                    }
                    
                    try {
                        let res = await fetch(`{{ route('central.api.customers.store-quick') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.newCustomer)
                        });
                        
                        let data = await res.json();
                        if (data.success) {
                            // If editing (ID exists), do not redirect. If creating new, redirect.
                            let isEdit = !!this.newCustomer.id;
                            this.selectCustomer(data.customer, false);
                            this.showCreateCustomerModal = false;
                        } else {
                            this.newCustomerError = data.message || 'Failed to create customer.';
                        }
                    } catch(e) {
                        console.error(e);
                        this.newCustomerError = 'Server error occurred.';
                    }
                },

                selectCustomer(cust, shouldNextStep = false) {
                    this.selectedCustomer = cust;
                    // Auto-select Default Addresses
                    if(cust.addresses && cust.addresses.length > 0) {
                        let def = cust.addresses.find(a => a.is_default) || cust.addresses[0];
                        
                        // Preserve existing selection if just updating customer details and address exists
                        if (!this.order.billing_address_id || !cust.addresses.find(a => a.id === this.order.billing_address_id)) {
                             this.order.billing_address_id = def.id;
                        }
                        if (!this.order.shipping_address_id || !cust.addresses.find(a => a.id === this.order.shipping_address_id)) {
                             this.order.shipping_address_id = def.id;
                        }
                    } else {
                        // Reset if no addresses found (crucial for validation)
                        this.order.billing_address_id = null;
                        this.order.shipping_address_id = null;
                    }
                    
                    if (shouldNextStep) {
                        this.step = 2; // Move to next step
                    }
                },

                requestCloseSession() {
                    if (this.selectedCustomer) {
                        this.tagOutcome = '';
                        this.tagNotes = '';
                        this.showTaggingModal = true;
                    } else {
                        window.location.href = "{{ route('central.orders.create', ['reset' => 1]) }}";
                    }
                },

                async submitTagging() {
                    if (!this.tagOutcome) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Please select an outcome.' } }));
                        return;
                    }

                    this.taggingLoading = true;
                    try {
                        let res = await fetch(`{{ url('/api/customers') }}/${this.selectedCustomer.id}/interactions`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                outcome: this.tagOutcome,
                                notes: this.tagNotes,
                                type: 'order_dropped',
                                metadata: {
                                    cart_items: this.cart,
                                    last_step: this.step
                                }
                            })
                        });

                        let data = await res.json();
                        if (data.success) {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Interaction logged. Session closed.' } }));
                            localStorage.removeItem('order_wizard_state');
                            window.location.href = "{{ route('central.orders.create', ['reset' => 1]) }}";
                        }
                    } catch (e) {
                        console.error(e);
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Failed to log interaction.' } }));
                    } finally {
                        this.taggingLoading = false;
                    }
                },

                goToStep(targetStep) {
                    if (targetStep === 1) {
                         this.step = 1;
                    } else if (targetStep === 2) {
                        if (!this.selectedCustomer) {
                            alert('Please select a customer first.');
                            return;
                        }
                        this.step = 2;
                    } else if (targetStep === 3) {
                        if (!this.selectedCustomer) {
                             alert('Please select a customer first.');
                             this.step = 1; 
                             return;
                        }
                        if (this.cart.length === 0) {
                            alert('Please add products to cart first.');
                            return;
                        }
                        this.step = 3;
                    }
                },

                // Address Logic
                openAddressModal(type) {
                    this.addressForm = {
                        customer_id: this.selectedCustomer.id,
                        id: null,
                        label: '',
                        address_line1: '',
                        address_line2: '',
                        village: '',
                        state: 'Maharashtra',
                        pincode: '',
                        is_default: false,
                        type: type || 'shipping',
                        loading: false,
                        error: ''
                    };
                    this.showAddressModal = true;
                },

                editAddress(addr) {
                     this.addressForm = {
                        customer_id: this.selectedCustomer.id,
                        id: addr.id,
                        label: addr.label || '',
                        address_line1: addr.address_line1 || '',
                        address_line2: addr.address_line2 || '',
                        village: addr.village || addr.city || '',
                        state: addr.state || 'Maharashtra',
                        pincode: addr.pincode || '',
                        is_default: addr.is_default || false,
                        type: 'both',
                        loading: false,
                        error: ''
                    };
                    this.showAddressModal = true;
                },

                async submitOrder() {
                    // Validation
                    if (this.cart.length === 0) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Your cart is empty. Please add products to place an order.' } }));
                        return;
                    }

                    if (!this.order.billing_address_id) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Billing address is compulsory. Please select a billing address.' } }));
                        return;
                    }

                    if (!this.order.same_as_billing && !this.order.shipping_address_id) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Shipping address is compulsory. Please select a shipping address or verify "Same as Billing".' } }));
                        return;
                    }

                    // Prepare payload
                    const payload = {
                        customer_id: this.selectedCustomer.id,
                        warehouse_id: document.querySelector('[name="warehouse_id"]')?.value,
                        order_number: document.querySelector('[name="order_number"]')?.value,
                        notes: document.querySelector('[name="notes"]')?.value,
                        billing_address_id: this.order.billing_address_id,
                        shipping_address_id: this.order.shipping_address_id,
                        is_future_order: this.order.is_future_order,
                        scheduled_at: this.order.scheduled_at,
                        discount_type: this.order.discount_type,
                        discount_value: this.order.discount_value,
                        items: this.cart.map(item => ({
                            product_id: item.product_id,
                            quantity: item.quantity,
                            price: item.price,
                            discount_type: item.discount_type,
                            discount_value: item.discount_value
                        }))
                    };

                    const loadingBtn = document.getElementById('submitBtn');

                    try {
                        if(loadingBtn) {
                            loadingBtn.disabled = true;
                            loadingBtn.innerHTML = '<span class="animate-spin mr-2">⏳</span> Processing...';
                        }

                        let res = await fetch(this.isEdit ? `{{ url('/orders') }}/${existingOrder.id}` : `{{ route('central.orders.store') }}`, {
                            method: this.isEdit ? 'PATCH' : 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(payload)
                        });

                        let data = await res.json().catch(() => ({})); 

                        // Handle 422 Validation Errors
                        if (res.status === 422) {
                            let errors = Object.values(data.errors || {}).flat().join('\n');
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Validation Failed: ' + errors } }));
                            if(loadingBtn) {
                                loadingBtn.disabled = false;
                                loadingBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Place Order';
                            }
                            return;
                        }

                        // Handle Generic Failures (500, etc)
                        if (!res.ok || (data && data.success === false)) {
                             throw new Error(data.message || res.statusText || 'Server Error');
                        }

                        // Clear LocalStorage BEFORE redirecting so the wizard starts fresh
                        localStorage.removeItem('order_wizard_state');

                        // Handle Success Redirect
                        if (data.redirect_url) {
                             window.location.href = data.redirect_url;
                             return;
                        }

                        // Fallback Success
                        if (res.ok) {
                            window.location.href = res.url;
                        }

                    } catch (e) {
                         console.error(e);
                         window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Error: ' + e.message } }));
                         if(loadingBtn) {
                            loadingBtn.disabled = false;
                            loadingBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Place Order';
                        }
                    }
                },

                async saveAddress() {
                    this.addressForm.loading = true;
                    this.addressForm.error = '';

                    if(!this.addressForm.address_line1 || !this.addressForm.pincode) {
                        this.addressForm.error = 'Address Line 1 and Pincode are required.';
                        this.addressForm.loading = false;
                        return;
                    }

                    try {
                        let res = await fetch(`{{ route('central.api.addresses.store') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.addressForm)
                        });

                        let data = await res.json();
                        if (data.success) {
                            // Update customer addresses
                            this.selectedCustomer.addresses = data.all_addresses;
                            
                            // Verify selection
                            if(this.addressForm.id === this.order.billing_address_id) {
                                // Forced reactivity update if needed
                            } if (!this.order.billing_address_id) {
                                this.order.billing_address_id = data.address.id;
                            }

                            this.showAddressModal = false;
                        } else {
                            this.addressForm.error = data.message || 'Failed to save address';
                        }
                    } catch(e) {
                        console.error(e);
                        this.addressForm.error = 'Server Error';
                    } finally {
                        this.addressForm.loading = false;
                    }
                },

                async searchProducts() {
                    try {
                        let res = await fetch(`{{ route('central.api.search.products') }}?q=${this.productQuery}`);
                        this.productResults = await res.json();
                    } catch(e) { console.error(e); }
                },

                addToCart(product) {
                    if (product.stock_on_hand <= 1 && !this.isEdit) return; // Allow if edit or has stock
                    let existing = this.cart.find(i => i.product_id === product.id);
                    if (existing) {
                        existing.quantity++;
                    } else {
                        this.cart.push({
                            product_id: product.id,
                            name: product.name,
                            price: parseFloat(product.price),
                            image_url: product.image_url,
                            quantity: 1,
                            max_stock: product.stock_on_hand || 999,
                            discount_type: 'fixed',
                            discount_value: 0
                        });
                    }
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },

                isInCart(id) {
                    return this.cart.some(i => i.product_id === id);
                },

                getCartQty(id) {
                    let item = this.cart.find(i => i.product_id === id);
                    return item ? item.quantity : 0;
                },

                updateCartQty(id, change) {
                    let item = this.cart.find(i => i.product_id === id);
                    if (item) {
                        if (change > 0) {
                             if (item.quantity < item.max_stock) item.quantity += change;
                        } else {
                             item.quantity += change;
                        }
                        if (item.quantity <= 0) this.cart = this.cart.filter(i => i.product_id !== id);
                    }
                },

                // Helper to open modal with pre-filled data
                openCreateCustomerModal() {
                    this.newCustomer = { 
                        id: null,
                        first_name: '', 
                        last_name: '', 
                        mobile: '', 
                        email: '',
                        type: 'farmer',
                        company_name: '',
                        gst_number: '',
                        pan_number: '',
                        land_area: '',
                        land_unit: 'acre'
                    };
                    
                    if (/^\d+$/.test(this.customerQuery)) {
                        this.newCustomer.mobile = this.customerQuery;
                    } 
                    else if (this.customerQuery.length > 0) {
                        this.newCustomer.first_name = this.customerQuery;
                    }
                    
                    this.showCreateCustomerModal = true;
                },

                editCustomerDetails() {
                    if (!this.selectedCustomer) return;
                    
                    this.newCustomer = {
                        id: this.selectedCustomer.id,
                        first_name: this.selectedCustomer.first_name,
                        last_name: this.selectedCustomer.last_name,
                        mobile: this.selectedCustomer.mobile,
                        email: this.selectedCustomer.email,
                        type: this.selectedCustomer.type,
                        company_name: this.selectedCustomer.company_name,
                        gst_number: this.selectedCustomer.gst_number,
                        pan_number: this.selectedCustomer.pan_number,
                        land_area: this.selectedCustomer.land_area, // Be careful with nulls here if backend returns valid number
                        land_unit: this.selectedCustomer.land_unit || 'acre'
                    };
                    
                    this.showCreateCustomerModal = true;
                },

                get subTotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                get cartDiscountTotal() {
                    return this.cart.reduce((sum, item) => {
                        let d = 0;
                        if (item.discount_type === 'percent') {
                            d = (item.price * item.quantity) * (item.discount_value / 100);
                        } else {
                            d = parseFloat(item.discount_value || 0);
                        }
                        return sum + d;
                    }, 0);
                },

                get orderDiscountAmount() {
                    let netAfterCartDiscounts = this.subTotal - this.cartDiscountTotal;
                    if (this.order.discount_type === 'percent') {
                        return netAfterCartDiscounts * (this.order.discount_value / 100);
                    }
                    return parseFloat(this.order.discount_value || 0);
                },

                get grandTotal() {
                    let total = this.subTotal - this.cartDiscountTotal - this.orderDiscountAmount;
                    return Math.max(0, total);
                },

                get cartTotal() {
                    return this.grandTotal;
                }
            }
        }
    </script>

    
    </div>
</x-app-layout>
