<x-app-layout>
    <div x-data="orderWizard(@js($products), @js($preSelectedCustomer))" class="flex flex-col min-h-screen bg-muted/5">

        <!-- MANDATORY INTERACTION TAGGING MODAL -->
        <template x-teleport="body">
            <div x-show="showTaggingModal"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-zinc-950/80 backdrop-blur-lg p-4"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100">

                <div class="bg-white dark:bg-zinc-900 w-full max-w-lg rounded-[32px] shadow-[0_0_80px_rgba(0,0,0,0.5)] border border-white/10 overflow-hidden flex flex-col animate-in zoom-in-95 duration-300 ease-out max-h-[90vh]"
                    @click.away="if(!taggingLoading) showTaggingModal = false">
                    <div class="flex-1 overflow-y-auto p-8 relative custom-scrollbar">
                        <div class="absolute -top-10 -right-10 size-40 bg-primary/20 blur-[60px] rounded-full"></div>

                        <div class="relative z-10 flex items-center gap-4 mb-6">
                            <div
                                class="size-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-black text-2xl tracking-tighter">Interaction Outcome</h3>
                                <p class="text-xs text-muted-foreground uppercase tracking-widest font-bold">Mandatory
                                    Tagging Required</p>
                            </div>
                        </div>

                        <div class="p-6 rounded-2xl bg-muted/30 border border-border/50 mb-8">
                            <p class="text-sm font-medium text-foreground/80 leading-relaxed">
                                You are closing an active session for <span class="text-primary font-black"
                                    x-text="selectedCustomer?.first_name + ' ' + (selectedCustomer?.last_name || '')"></span>.
                                To ensure high-quality CRM data, please select why this visit did not result in an
                                order.
                            </p>
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-3">
                                <label
                                    class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em] ml-1">Reason
                                    for no order</label>
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
                                <label
                                    class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em] ml-1">Additional
                                    Notes (Optional)</label>
                                <textarea x-model="tagNotes"
                                    class="w-full bg-secondary/20 dark:bg-white/5 border border-border rounded-2xl p-4 text-sm font-medium placeholder:text-muted-foreground/30 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all min-h-[100px]"
                                    placeholder="Provide more context..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div
                        class="p-8 border-t border-border flex gap-4 bg-white dark:bg-zinc-900 shadow-[0_-10px_40px_rgba(0,0,0,0.05)]">
                        <button @click="showTaggingModal = false" :disabled="taggingLoading"
                            class="flex-1 h-14 rounded-2xl border border-border text-xs font-black uppercase tracking-widest hover:bg-secondary/50 transition-all disabled:opacity-50">
                            Stay Back
                        </button>
                        <button @click="submitTagging()" :disabled="taggingLoading"
                            class="flex-[2] h-14 rounded-2xl bg-primary text-primary-foreground text-xs font-black uppercase tracking-widest shadow-xl shadow-primary/30 hover:shadow-primary/40 transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3">
                            <span x-show="taggingLoading"
                                class="animate-spin size-4 border-2 border-white/20 border-t-white rounded-full"></span>
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
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100">

                <div class="bg-white dark:bg-zinc-900 w-full max-w-2xl rounded-[32px] shadow-2xl border border-white/10 overflow-hidden flex flex-col animate-in zoom-in-95 duration-300 ease-out max-h-[90vh]"
                    @click.away="showProductDetailsModal = false">
                    <div class="flex-1 overflow-y-auto custom-scrollbar relative">
                        <!-- Top Banner / Image -->
                        <div class="h-64 relative bg-muted group">
                            <img :src="detailedProduct?.image_url"
                                class="size-full object-contain p-8 bg-zinc-50 dark:bg-zinc-800/50"
                                onerror="this.src='https://placehold.co/800x400?text=Product+Image'">
                            <button @click="showProductDetailsModal = false"
                                class="absolute top-6 right-6 p-2 bg-white/20 hover:bg-white/40 backdrop-blur-lg rounded-full text-white transition-all shadow-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M18 6 6 18" />
                                    <path d="m6 6 12 12" />
                                </svg>
                            </button>
                            <div class="absolute bottom-6 left-6 flex gap-2">
                                <span
                                    class="px-3 py-1 bg-primary text-primary-foreground rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg"
                                    x-text="detailedProduct?.category"></span>
                                <template x-if="detailedProduct?.is_organic">
                                    <span
                                        class="px-3 py-1 bg-emerald-500 text-white rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg flex items-center gap-1">
                                        <svg class="size-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                        </svg> 100% Organic
                                    </span>
                                </template>
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-1"
                                        x-text="detailedProduct?.brand || 'Generic'"></p>
                                    <h3 class="text-3xl font-black tracking-tight" x-text="detailedProduct?.name"></h3>
                                    <p class="text-xs font-mono text-muted-foreground mt-1"
                                        x-text="'SKU: ' + (detailedProduct?.sku || 'N/A')"></p>
                                </div>
                                <div class="text-right">
                                    <p
                                        class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-1">
                                        Our Price</p>
                                    <p class="text-3xl font-black text-primary tracking-tighter">Rs <span
                                            x-text="parseFloat(detailedProduct?.price || 0).toFixed(2)"></span></p>
                                    <p class="text-[10px] font-bold text-muted-foreground"
                                        x-text="'per ' + (detailedProduct?.unit_type || 'kg')"></p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-8">
                                <div class="p-4 rounded-2xl bg-muted/30 border border-border/50">
                                    <p
                                        class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-2">
                                        Available Stock</p>
                                    <p class="text-xl font-black"
                                        :class="detailedProduct?.stock_on_hand > 0 ? 'text-foreground' : 'text-destructive'">
                                        <span x-text="detailedProduct?.stock_on_hand || 0"></span>
                                        <span class="text-sm font-bold text-muted-foreground ml-1 font-mono"
                                            x-text="detailedProduct?.unit_type"></span>
                                    </p>
                                </div>
                                <div class="p-4 rounded-2xl bg-muted/30 border border-border/50">
                                    <p
                                        class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-2">
                                        Product Origin</p>
                                    <p class="text-sm font-bold truncate"
                                        x-text="detailedProduct?.origin || 'Global Market'"></p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h4
                                    class="text-[10px] font-black text-muted-foreground uppercase tracking-widest flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                                        <polyline points="16 6 12 2 8 6" />
                                        <line x1="12" y1="2" x2="12" y2="15" />
                                    </svg>
                                    Product Description
                                </h4>
                                <div class="text-sm leading-relaxed text-muted-foreground font-medium bg-muted/20 p-6 rounded-[24px]"
                                    x-text="detailedProduct?.description || 'No detailed description provided for this product.'">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 border-t border-border flex gap-4 bg-muted/10">
                        <x-ui.button @click="showProductDetailsModal = false" variant="outline"
                            class="flex-1 h-14 rounded-2xl">
                            Close Details
                        </x-ui.button>
                        <template x-if="detailedProduct?.stock_on_hand > 0">
                            <x-ui.button @click="addToCart(detailedProduct); showProductDetailsModal = false"
                                class="flex-[2] h-14 rounded-2xl shadow-xl shadow-primary/20">
                                Add to Order (Rs <span
                                    x-text="parseFloat(detailedProduct?.price || 0).toFixed(2)"></span>)
                            </x-ui.button>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- Header / Progress Bar -->
        <div
            class="flex-none bg-background border-b border-border px-8 py-4 flex items-center justify-between z-20 shadow-sm sticky top-0">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">New Order</h1>
                <p class="text-xs text-muted-foreground">Create a new sales order in 3 simple steps</p>
            </div>

            <div class="flex items-center gap-4">
                <!-- Step 1 Indicator -->
                <div @click="goToStep(1)" class="flex items-center gap-2 transition-colors duration-300 cursor-pointer"
                    :class="step >= 1 ? 'text-primary' : 'text-muted-foreground'">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300 shadow-sm"
                            :class="step >= 1 ? 'border-primary bg-primary text-primary-foreground shadow-primary/25' : 'border-border bg-background'">
                            <span x-show="step > 1">✓</span>
                            <span x-show="step <= 1">1</span>
                        </div>
                    </div>
                    <span class="hidden sm:inline font-medium text-sm">Customer</span>
                </div>
                <div class="w-12 h-0.5 rounded-full transition-colors duration-300"
                    :class="step >= 2 ? 'bg-primary' : 'bg-border'"></div>

                <!-- Step 2 Indicator -->
                <div @click="goToStep(2)" class="flex items-center gap-2 transition-colors duration-300 cursor-pointer"
                    :class="step >= 2 ? 'text-primary' : 'text-muted-foreground'">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300 shadow-sm"
                        :class="step >= 2 ? 'border-primary bg-primary text-primary-foreground shadow-primary/25' : 'border-border bg-background'">
                        <span x-show="step > 2">✓</span>
                        <span x-show="step <= 2">2</span>
                    </div>
                    <span class="hidden sm:inline font-medium text-sm">Products</span>
                </div>
                <div class="w-12 h-0.5 rounded-full transition-colors duration-300"
                    :class="step >= 3 ? 'bg-primary' : 'bg-border'"></div>

                <!-- Step 3 Indicator -->
                <div @click="goToStep(3)" class="flex items-center gap-2 transition-colors duration-300 cursor-pointer"
                    :class="step >= 3 ? 'text-primary' : 'text-muted-foreground'">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300 shadow-sm"
                        :class="step >= 3 ? 'border-primary bg-primary text-primary-foreground shadow-primary/25' : 'border-border bg-background'">
                        3
                    </div>
                    <span class="hidden sm:inline font-medium text-sm">Review</span>
                </div>
            </div>

            <x-ui.button variant="ghost" @click="requestCloseSession()" size="sm"
                class="text-muted-foreground hover:text-destructive">
                Cancel
            </x-ui.button>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 w-full relative">
            <div class="p-6">
                <div class="max-w-7xl mx-auto h-full">

                    <!-- STEP 1: CUSTOMER SELECTION / VERIFICATION -->
                    <div x-show="step === 1" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="flex flex-col items-center justify-center max-w-4xl mx-auto min-h-[500px] w-full">

                        <!-- Mode A: Search (Shown if no customer selected) -->
                        <template x-if="!selectedCustomer">
                            <div class="w-full max-w-3xl relative isolate">
                                <!-- Ambient Glow -->
                                <div
                                    class="absolute -top-40 -left-40 size-96 bg-primary/20 blur-[100px] rounded-full opacity-50 pointer-events-none">
                                </div>
                                <div
                                    class="absolute -bottom-40 -right-40 size-96 bg-purple-500/20 blur-[100px] rounded-full opacity-50 pointer-events-none">
                                </div>

                                <div
                                    class="relative bg-white/80 dark:bg-zinc-900/80 backdrop-blur-2xl border border-white/20 dark:border-white/5 rounded-[40px] shadow-2xl overflow-hidden">
                                    <div class="p-10 md:p-14 text-center">
                                        <div
                                            class="inline-flex items-center justify-center p-4 bg-primary/10 text-primary rounded-3xl mb-8 ring-1 ring-primary/20 shadow-lg shadow-primary/10">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                                <circle cx="9" cy="7" r="4" />
                                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                            </svg>
                                        </div>

                                        <h2 class="text-3xl md:text-4xl font-black tracking-tight text-foreground mb-4">
                                            Start New Order</h2>
                                        <p class="text-muted-foreground text-lg mb-10 max-w-md mx-auto leading-relaxed">
                                            Search the registry to identify the customer, or enroll a new profile
                                            instantly.</p>

                                        <div class="relative group max-w-xl mx-auto">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                                                <svg class="h-6 w-6 text-muted-foreground group-focus-within:text-primary transition-colors duration-300"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            </div>
                                            <input type="text" x-model="customerQuery"
                                                @input.debounce.300ms="searchCustomers()"
                                                placeholder="Search by Name, Mobile, or Code..."
                                                class="w-full h-16 pl-16 pr-6 rounded-2xl border border-border/50 bg-secondary/30 dark:bg-zinc-800/50 focus:bg-white dark:focus:bg-zinc-900 focus:ring-4 focus:ring-primary/10 focus:border-primary/50 transition-all text-xl font-medium placeholder:text-muted-foreground/50 shadow-inner"
                                                autofocus>

                                            <!-- Spinner -->
                                            <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none opacity-0 transition-opacity duration-300"
                                                :class="{'opacity-100': false}">
                                                <!-- Replace false with loading state if available -->
                                                <svg class="animate-spin h-5 w-5 text-primary"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Search Results -->
                                    <div class="border-t border-border/50 bg-muted/10">
                                        <div class="max-h-[360px] overflow-y-auto custom-scrollbar p-2 space-y-2">
                                            <template x-if="customerQuery.length < 2 && customerResults.length === 0">
                                                <div class="py-12 flex flex-col items-center justify-center opacity-40">
                                                    <div
                                                        class="text-sm font-bold uppercase tracking-widest text-muted-foreground">
                                                        Waiting for input</div>
                                                </div>
                                            </template>

                                            <template x-for="cust in customerResults" :key="cust.id">
                                                <div @click="selectCustomer(cust, false)"
                                                    class="group flex items-center gap-5 p-4 mx-2 rounded-2xl hover:bg-white dark:hover:bg-white/5 border border-transparent hover:border-black/5 dark:hover:border-white/10 cursor-pointer transition-all duration-200">

                                                    <div
                                                        class="size-14 rounded-xl bg-gradient-to-br from-primary to-purple-600 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-primary/20 group-hover:scale-105 transition-transform duration-300">
                                                        <span x-text="cust.first_name.charAt(0)"></span><span
                                                            x-text="(cust.last_name || '').charAt(0)"></span>
                                                    </div>

                                                    <div class="flex-1 text-left">
                                                        <div class="flex items-center justify-between">
                                                            <h4 class="font-black text-lg text-foreground group-hover:text-primary transition-colors"
                                                                x-text="cust.first_name + ' ' + (cust.last_name || '')">
                                                            </h4>
                                                            <span
                                                                class="text-[10px] font-black uppercase tracking-widest bg-muted text-muted-foreground px-2 py-0.5 rounded-md"
                                                                x-text="cust.type"></span>
                                                        </div>
                                                        <div
                                                            class="flex items-center gap-3 mt-1 text-sm text-muted-foreground font-medium">
                                                            <span class="flex items-center gap-1.5">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                                    height="14" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <path
                                                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                                                </svg>
                                                                <span x-text="cust.mobile"></span>
                                                            </span>
                                                            <span class="w-1 h-1 rounded-full bg-border"></span>
                                                            <span class="font-mono opacity-70"
                                                                x-text="cust.customer_code"></span>
                                                        </div>
                                                    </div>

                                                    <div class="text-right">
                                                        <p
                                                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-0.5">
                                                            Balance</p>
                                                        <p class="font-mono font-black text-base"
                                                            :class="cust.outstanding_balance > 0 ? 'text-rose-500' : 'text-emerald-500'">
                                                            Rs <span x-text="cust.outstanding_balance || '0.00'"></span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </template>

                                            <div x-show="customerQuery.length > 2 && customerResults.length === 0"
                                                class="text-center py-10">
                                                <div
                                                    class="size-16 rounded-3xl bg-muted flex items-center justify-center mx-auto mb-4">
                                                    <svg class="size-8 text-muted-foreground" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <circle cx="11" cy="11" r="8"></circle>
                                                        <path d="m21 21-4.3-4.3"></path>
                                                    </svg>
                                                </div>
                                                <h3 class="font-bold text-foreground">No matches found</h3>
                                                <p class="text-sm text-muted-foreground mb-6">Can't find this customer
                                                    in the registry.</p>
                                                <button @click="openCreateCustomerModal()"
                                                    class="inline-flex items-center gap-2.5 bg-primary text-primary-foreground text-sm font-bold uppercase tracking-wider px-8 py-4 rounded-xl hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/20">
                                                    <svg class="size-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" stroke-width="3">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                    Register New Profile
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Mode B: Customer Details (Shown if customer selected) -->
                        <template x-if="selectedCustomer">
                            <div
                                class="w-full max-w-6xl mx-auto animate-in fade-in slide-in-from-bottom-6 duration-700 space-y-8">

                                <!-- Top Navigation & Actions -->
                                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                                    <button @click="requestCloseSession()"
                                        class="group flex items-center gap-3 text-xs font-black uppercase tracking-widest text-muted-foreground hover:text-rose-500 transition-colors px-5 py-3 rounded-2xl hover:bg-rose-500/10">
                                        <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        Switch Profile
                                    </button>

                                    <button @click="step = 2"
                                        class="w-full md:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-primary to-indigo-600 text-white font-black text-xs uppercase tracking-widest shadow-xl shadow-primary/25 hover:shadow-primary/40 hover:-translate-y-1 active:scale-95 transition-all flex items-center justify-center gap-3 group">
                                        Proceed to Catalog
                                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Premium Profile Card -->
                                <div
                                    class="relative overflow-hidden rounded-[40px] bg-white/50 dark:bg-zinc-900/50 backdrop-blur-xl border border-white/20 dark:border-white/5 shadow-[0_20px_50px_rgba(0,0,0,0.05)] isolate">
                                    <!-- Glow Effects -->
                                    <div
                                        class="absolute -top-32 -right-32 size-96 bg-primary/10 blur-[80px] rounded-full pointer-events-none">
                                    </div>
                                    <div
                                        class="absolute -bottom-32 -left-32 size-96 bg-purple-500/10 blur-[80px] rounded-full pointer-events-none">
                                    </div>

                                    <div
                                        class="relative z-10 p-8 md:p-10 flex flex-col md:flex-row gap-8 items-center md:items-start">
                                        <!-- Avatar -->
                                        <div class="relative shrink-0 group/avatar cursor-default">
                                            <div
                                                class="size-32 rounded-[2.5rem] bg-gradient-to-br from-primary to-indigo-600 flex items-center justify-center text-white text-5xl font-black shadow-2xl shadow-primary/30 rotate-3 group-hover/avatar:rotate-6 transition-all duration-500 ease-out border-4 border-white/20">
                                                <span x-text="selectedCustomer.first_name.charAt(0)"></span><span
                                                    x-text="(selectedCustomer.last_name || '').charAt(0)"></span>
                                            </div>
                                            <div
                                                class="absolute -bottom-2 -right-2 px-4 py-1.5 bg-zinc-900/90 backdrop-blur-md text-white border border-white/10 rounded-full shadow-lg flex items-center gap-2">
                                                <div
                                                    class="size-2 rounded-full bg-emerald-500 animate-[pulse_2s_infinite]">
                                                </div>
                                                <span
                                                    class="text-[10px] font-black uppercase tracking-widest">Verified</span>
                                            </div>
                                        </div>

                                        <!-- Main Info -->
                                        <div class="flex-1 min-w-0 text-center md:text-left space-y-2 pt-2">
                                            <div class="flex items-center justify-center md:justify-start gap-3 mb-2">
                                                <span
                                                    class="px-3 py-1 bg-primary/10 text-primary rounded-lg text-[10px] font-black uppercase tracking-[0.2em] border border-primary/10"
                                                    x-text="selectedCustomer.type || 'Standard'"></span>
                                                <span class="font-mono text-xs text-muted-foreground/60 font-bold"
                                                    x-text="selectedCustomer.customer_code"></span>
                                            </div>

                                            <h2 class="text-4xl md:text-6xl font-black tracking-tighter text-foreground truncate"
                                                x-text="selectedCustomer.first_name + ' ' + (selectedCustomer.last_name || '')">
                                            </h2>

                                            <div
                                                class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-sm text-muted-foreground font-medium pt-1">
                                                <span
                                                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/50 border border-border/50">
                                                    <svg class="size-4 text-primary" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                    </svg>
                                                    <span x-text="selectedCustomer.mobile"></span>
                                                </span>
                                                <span
                                                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/50 border border-border/50"
                                                    x-show="selectedCustomer.company_name">
                                                    <svg class="size-4 text-primary" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                    </svg>
                                                    <span x-text="selectedCustomer.company_name"></span>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Quick Actions -->
                                        <div
                                            class="flex flex-col gap-3 shrink-0 w-full md:w-auto p-4 bg-white/40 dark:bg-black/20 rounded-[28px] border border-white/20 backdrop-blur-md">
                                            <button @click="editCustomerDetails()"
                                                class="flex items-center justify-center gap-3 px-6 py-3.5 rounded-2xl bg-white dark:bg-zinc-800 hover:bg-primary/5 border border-transparent hover:border-primary/20 font-bold text-xs uppercase tracking-wider transition-all shadow-sm">
                                                Edit Profile
                                            </button>
                                            <button @click="openAddressModal('billing')"
                                                class="flex items-center justify-center gap-3 px-6 py-3.5 rounded-2xl bg-white dark:bg-zinc-800 hover:bg-primary/5 border border-transparent hover:border-primary/20 font-bold text-xs uppercase tracking-wider transition-all shadow-sm">
                                                Manage Addresses
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Key Metrics -->
                                    <div
                                        class="grid grid-cols-2 md:grid-cols-4 divide-x divide-black/5 dark:divide-white/5 border-t border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02]">
                                        <div class="p-6 text-center hover:bg-white/10 transition-colors">
                                            <p
                                                class="text-[10px] font-black uppercase tracking-[0.2em] opacity-50 mb-1">
                                                Balance</p>
                                            <p class="text-2xl font-black font-mono tracking-tight"
                                                :class="selectedCustomer.outstanding_balance > 0 ? 'text-rose-500' : 'text-emerald-600'">
                                                Rs <span x-text="selectedCustomer.outstanding_balance || 0"></span>
                                            </p>
                                        </div>
                                        <div class="p-6 text-center hover:bg-white/10 transition-colors">
                                            <p
                                                class="text-[10px] font-black uppercase tracking-[0.2em] opacity-50 mb-1">
                                                Credit Limit</p>
                                            <p class="text-2xl font-black font-mono tracking-tight opacity-80">Rs <span
                                                    x-text="selectedCustomer.credit_limit || 0"></span></p>
                                        </div>
                                        <div class="p-6 text-center hover:bg-white/10 transition-colors">
                                            <p
                                                class="text-[10px] font-black uppercase tracking-[0.2em] opacity-50 mb-1">
                                                Orders</p>
                                            <p class="text-2xl font-black font-mono tracking-tight text-primary"
                                                x-text="selectedCustomer.orders_count || 0"></p>
                                        </div>
                                        <div class="p-6 text-center hover:bg-white/10 transition-colors">
                                            <p
                                                class="text-[10px] font-black uppercase tracking-[0.2em] opacity-50 mb-1">
                                                Farm Size</p>
                                            <p class="text-2xl font-black font-mono tracking-tight opacity-80"
                                                x-text="(selectedCustomer.land_area || '-') + ' ' + (selectedCustomer.land_unit || '')">
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- (Removed duplicate external detail sections) -->

                                <!-- Customer Insights (Scrollable & Sticky) -->
                                <div
                                    class="bg-card border border-border/50 rounded-[32px] shadow-sm overflow-hidden flex flex-col mt-6 h-[700px] isolate">
                                    <!-- Sticky Tab Header -->
                                    <div
                                        class="flex items-center border-b border-border/50 bg-white/80 dark:bg-zinc-900/80 px-8 backdrop-blur-xl sticky top-0 z-20 shrink-0">
                                        <button @click="activeTab = 'timeline'"
                                            class="mr-8 py-5 text-sm font-black uppercase tracking-widest border-b-2 transition-all flex items-center gap-2.5"
                                            :class="activeTab === 'timeline' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground opacity-60 hover:opacity-100'">
                                            <div class="size-2 rounded-full transition-colors"
                                                :class="activeTab === 'timeline' ? 'bg-primary animate-pulse' : 'bg-transparent'">
                                            </div>
                                            Activity
                                        </button>
                                        <button @click="activeTab = 'profile'"
                                            class="mr-8 py-5 text-sm font-black uppercase tracking-widest border-b-2 transition-all flex items-center gap-2.5"
                                            :class="activeTab === 'profile' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground opacity-60 hover:opacity-100'">
                                            <div class="size-2 rounded-full transition-colors"
                                                :class="activeTab === 'profile' ? 'bg-primary animate-pulse' : 'bg-transparent'">
                                            </div>
                                            Full Profile
                                        </button>
                                        <button @click="activeTab = 'orders'"
                                            class="py-5 text-sm font-black uppercase tracking-widest border-b-2 transition-all flex items-center gap-2.5"
                                            :class="activeTab === 'orders' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground opacity-60 hover:opacity-100'">
                                            <div class="size-2 rounded-full transition-colors"
                                                :class="activeTab === 'orders' ? 'bg-primary animate-pulse' : 'bg-transparent'">
                                            </div>
                                            Orders History
                                        </button>
                                    </div>

                                    <!-- Scrollable Content Area -->
                                    <div
                                        class="flex-1 overflow-y-auto custom-scrollbar bg-secondary/5 dark:bg-black/20 p-0 relative scroll-smooth">

                                        <!-- TIMELINE TAB -->
                                        <div x-show="activeTab === 'timeline'"
                                            class="p-8 md:p-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
                                            <div class="max-w-4xl mx-auto space-y-10 relative">
                                                <!-- Continuous Line -->
                                                <div
                                                    class="absolute left-[19px] top-4 bottom-4 w-[2px] bg-gradient-to-b from-primary/50 via-border to-transparent">
                                                </div>

                                                <template x-if="activityLoading">
                                                    <div class="py-20 text-center">
                                                        <div
                                                            class="size-12 border-4 border-primary/20 border-t-primary rounded-full animate-spin mx-auto mb-4">
                                                        </div>
                                                        <p
                                                            class="text-xs font-black uppercase tracking-widest text-muted-foreground animate-pulse">
                                                            Syncing Timeline...</p>
                                                    </div>
                                                </template>

                                                <template x-for="event in activityEvents" :key="event.id + event.kind">
                                                    <div class="relative pl-14 group">
                                                        <!-- Timeline Node -->
                                                        <div class="absolute left-0 top-1 size-10 rounded-full border-[3px] border-background flex items-center justify-center shadow-lg z-10 transition-transform group-hover:scale-110"
                                                            :class="event.kind === 'order' ? 'bg-indigo-500 text-white shadow-indigo-500/30' : 'bg-emerald-500 text-white shadow-emerald-500/30'">
                                                            <template x-if="event.kind === 'order'">
                                                                <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                                                    stroke="currentColor" stroke-width="2.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                                                </svg>
                                                            </template>
                                                            <template x-if="event.kind === 'interaction'">
                                                                <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                                                    stroke="currentColor" stroke-width="2.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                                </svg>
                                                            </template>
                                                        </div>

                                                        <!-- Card -->
                                                        <div
                                                            class="bg-white dark:bg-zinc-900 border border-border/50 rounded-[24px] p-6 shadow-sm hover:shadow-xl transition-all duration-300 group-hover:-translate-y-1">

                                                            <!-- Order Event -->
                                                            <template x-if="event.kind === 'order'">
                                                                <div>
                                                                    <div
                                                                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 pb-4 border-b border-border/30">
                                                                        <div class="flex items-center gap-3">
                                                                            <span
                                                                                class="px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-600 text-[10px] font-black uppercase tracking-widest">New
                                                                                Order</span>
                                                                            <span
                                                                                class="text-xs text-muted-foreground font-medium"
                                                                                x-text="event.placed_at"></span>
                                                                        </div>
                                                                        <div class="text-right">
                                                                            <span
                                                                                class="text-2xl font-black font-mono tracking-tight text-foreground"
                                                                                x-text="'Rs ' + event.grand_total"></span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="flex items-center justify-between">
                                                                        <div>
                                                                            <h4
                                                                                class="text-lg font-bold text-foreground flex items-center gap-2">
                                                                                <span
                                                                                    x-text="event.order_number"></span>
                                                                                <span
                                                                                    class="size-1.5 rounded-full bg-border"></span>
                                                                                <span
                                                                                    class="text-xs text-muted-foreground uppercase font-medium"
                                                                                    x-text="event.status"></span>
                                                                            </h4>
                                                                        </div>

                                                                        <!-- Mini Item Previews -->
                                                                        <div class="flex items-center -space-x-2">
                                                                            <template
                                                                                x-for="item in (event.items || []).slice(0,4)"
                                                                                :key="item.id">
                                                                                <img :src="item.product?.image_url || 'https://placehold.co/50x50'"
                                                                                    class="size-8 rounded-full border-2 border-white dark:border-zinc-900 object-cover bg-white"
                                                                                    :title="item.product?.name">
                                                                            </template>
                                                                            <div x-show="(event.item_count || 0) > 4"
                                                                                class="size-8 rounded-full border-2 border-white dark:border-zinc-900 bg-muted flex items-center justify-center text-[10px] font-bold text-muted-foreground">
                                                                                <span
                                                                                    x-text="'+' + ((event.item_count || 0) - 4)"></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </template>

                                                            <!-- Interaction Event -->
                                                            <template x-if="event.kind === 'interaction'">
                                                                <div>
                                                                    <div class="flex items-center justify-between mb-3">
                                                                        <div class="flex items-center gap-3">
                                                                            <span
                                                                                class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 text-[10px] font-black uppercase tracking-widest"
                                                                                x-text="event.interaction_type"></span>
                                                                            <span
                                                                                class="text-xs text-muted-foreground font-medium"
                                                                                x-text="event.created_at"></span>
                                                                        </div>
                                                                        <div
                                                                            class="flex items-center gap-1.5 text-xs font-bold text-foreground">
                                                                            <div
                                                                                class="size-6 rounded-full bg-gradient-to-tr from-zinc-200 to-zinc-100 flex items-center justify-center text-[8px] border border-white/20">
                                                                                <span
                                                                                    x-text="(event.user_name || 'S').charAt(0)"></span>
                                                                            </div>
                                                                            <span
                                                                                x-text="event.user_name || 'System'"></span>
                                                                        </div>
                                                                    </div>
                                                                    <p class="text-sm font-medium leading-relaxed text-muted-foreground/80 bg-secondary/30 p-4 rounded-2xl italic border border-transparent hover:border-border/50 transition-colors"
                                                                        x-text="'&ldquo;' + event.notes + '&rdquo;'">
                                                                    </p>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>

                                                <template x-if="!activityLoading && activityEvents.length === 0">
                                                    <div class="py-24 text-center opacity-50">
                                                        <div
                                                            class="size-20 rounded-full bg-muted mx-auto mb-4 flex items-center justify-center">
                                                            <svg class="size-8 text-muted-foreground" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="1.5"
                                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </div>
                                                        <h3 class="text-lg font-bold">Timeline Empty</h3>
                                                        <p class="text-sm">No activity recorded for this timeline yet.
                                                        </p>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- FULL PROFILE TAB -->
                                        <div x-show="activeTab === 'profile'"
                                            class="p-8 md:p-12 animate-in fade-in zoom-in-95 duration-300">
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                                                <!-- Card 1: Personal Identity -->
                                                <div
                                                    class="bg-white dark:bg-zinc-900 rounded-[24px] border border-border/50 p-6 shadow-sm hover:shadow-md transition-all">
                                                    <div
                                                        class="flex items-center gap-3 mb-6 pb-4 border-b border-border/40">
                                                        <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
                                                            <svg class="size-5" fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                            </svg>
                                                        </div>
                                                        <h4
                                                            class="text-sm font-black uppercase tracking-widest text-foreground">
                                                            Identity</h4>
                                                    </div>

                                                    <div class="space-y-5">
                                                        <div>
                                                            <p
                                                                class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">
                                                                Full Name</p>
                                                            <p class="text-base font-bold text-foreground"
                                                                x-text="selectedCustomer.first_name + ' ' + (selectedCustomer.last_name || '')">
                                                            </p>
                                                        </div>
                                                        <div>
                                                            <p
                                                                class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">
                                                                Mobile Number</p>
                                                            <p class="text-base font-mono font-medium text-foreground tracking-wide"
                                                                x-text="selectedCustomer.mobile || '-'"></p>
                                                        </div>
                                                        <div>
                                                            <p
                                                                class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">
                                                                Email Address</p>
                                                            <p class="text-sm font-medium text-foreground break-all"
                                                                x-text="selectedCustomer.email || 'Not Provided'"></p>
                                                        </div>
                                                        <div>
                                                            <p
                                                                class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">
                                                                Aadhaar (Last 4)</p>
                                                            <p class="text-sm font-mono font-bold text-foreground bg-muted/30 inline-block px-2 py-1 rounded"
                                                                x-text="selectedCustomer.aadhaar_last4 ? 'XXXX XXXX ' + selectedCustomer.aadhaar_last4 : 'Not Linked'">
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Card 2: Business & Financial -->
                                                <div
                                                    class="bg-white dark:bg-zinc-900 rounded-[24px] border border-border/50 p-6 shadow-sm hover:shadow-md transition-all">
                                                    <div
                                                        class="flex items-center gap-3 mb-6 pb-4 border-b border-border/40">
                                                        <div class="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-600">
                                                            <svg class="size-5" fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                            </svg>
                                                        </div>
                                                        <h4
                                                            class="text-sm font-black uppercase tracking-widest text-foreground">
                                                            Business & Credit</h4>
                                                    </div>

                                                    <div class="space-y-5">
                                                        <div>
                                                            <p
                                                                class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">
                                                                Company / Shop Name</p>
                                                            <p class="text-base font-bold text-foreground"
                                                                x-text="selectedCustomer.company_name || 'Individual'">
                                                            </p>
                                                        </div>
                                                        <div class="grid grid-cols-2 gap-4">
                                                            <div>
                                                                <p
                                                                    class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">
                                                                    GST Number</p>
                                                                <p class="text-sm font-mono font-medium text-foreground"
                                                                    x-text="selectedCustomer.gst_number || '-'"></p>
                                                            </div>
                                                            <div>
                                                                <p
                                                                    class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">
                                                                    PAN Number</p>
                                                                <p class="text-sm font-mono font-medium text-foreground"
                                                                    x-text="selectedCustomer.pan_number || '-'"></p>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <p
                                                                class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">
                                                                Credit Limit</p>
                                                            <div class="flex items-center justify-between">
                                                                <p class="text-lg font-black font-mono text-foreground">
                                                                    Rs <span
                                                                        x-text="selectedCustomer.credit_limit || 0"></span>
                                                                </p>
                                                                <span
                                                                    class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-500/10 px-2 py-1 rounded"
                                                                    x-show="selectedCustomer.credit_valid_till">Valid:
                                                                    <span
                                                                        x-text="selectedCustomer.credit_valid_till"></span></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Card 3: Farm Profile (Conditional) -->
                                                <div class="bg-white dark:bg-zinc-900 rounded-[24px] border border-border/50 p-6 shadow-sm hover:shadow-md transition-all"
                                                    x-show="selectedCustomer.type === 'farmer'">
                                                    <div
                                                        class="flex items-center gap-3 mb-6 pb-4 border-b border-border/40">
                                                        <div
                                                            class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-600">
                                                            <svg class="size-5" fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </div>
                                                        <h4
                                                            class="text-sm font-black uppercase tracking-widest text-foreground">
                                                            Farm Profile</h4>
                                                    </div>

                                                    <div class="space-y-5">
                                                        <div>
                                                            <p
                                                                class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">
                                                                Land Holding</p>
                                                            <p class="text-2xl font-black text-emerald-600"
                                                                x-text="(selectedCustomer.land_area || '0') + ' ' + (selectedCustomer.land_unit || '')">
                                                            </p>
                                                        </div>
                                                        <div>
                                                            <p
                                                                class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">
                                                                Irrigation Type</p>
                                                            <p class="text-sm font-bold text-foreground"
                                                                x-text="selectedCustomer.irrigation_type || 'Surrender / Rainfed'">
                                                            </p>
                                                        </div>
                                                        <div>
                                                            <p
                                                                class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-3">
                                                                Crop Portfolio</p>
                                                            <div class="flex flex-wrap gap-2">
                                                                <template
                                                                    x-for="crop in (Array.isArray(selectedCustomer.crops) ? selectedCustomer.crops : [])">
                                                                    <span
                                                                        class="px-2.5 py-1 rounded-lg bg-orange-500/10 text-orange-700 text-xs font-bold border border-orange-500/20"
                                                                        x-text="crop"></span>
                                                                </template>
                                                                <span
                                                                    x-show="!selectedCustomer.crops || selectedCustomer.crops.length === 0"
                                                                    class="text-xs text-muted-foreground italic">No
                                                                    crops listed</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Card 4: Address Book (Full Width on Mobile, Span 2 on Desktop) -->
                                                <div
                                                    class="md:col-span-2 lg:col-span-3 bg-white dark:bg-zinc-900 rounded-[24px] border border-border/50 p-6 shadow-sm hover:shadow-md transition-all">
                                                    <div
                                                        class="flex items-center justify-between mb-6 pb-4 border-b border-border/40">
                                                        <div class="flex items-center gap-3">
                                                            <div class="p-2.5 rounded-xl bg-rose-500/10 text-rose-600">
                                                                <svg class="size-5" fill="none" viewBox="0 0 24 24"
                                                                    stroke="currentColor" stroke-width="2">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                </svg>
                                                            </div>
                                                            <h4
                                                                class="text-sm font-black uppercase tracking-widest text-foreground">
                                                                Address Book</h4>
                                                        </div>
                                                        <button @click="openAddressModal('billing')"
                                                            class="text-xs font-bold uppercase tracking-wider text-primary hover:underline hover:text-primary/80 transition-colors">
                                                            + Add New Address
                                                        </button>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                        <template x-for="addr in selectedCustomer.addresses"
                                                            :key="addr.id">
                                                            <div class="group relative p-4 rounded-xl bg-secondary/20 border border-border/50 hover:bg-white hover:border-primary/20 hover:shadow-lg transition-all cursor-pointer"
                                                                @click="editAddress(addr)">
                                                                <div class="flex items-start justify-between mb-2">
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="font-black text-sm text-foreground"
                                                                            x-text="addr.label || 'Home'"></span>
                                                                        <span x-show="addr.is_default"
                                                                            class="px-1.5 py-0.5 rounded bg-primary text-primary-foreground text-[8px] font-black uppercase tracking-wider">Default</span>
                                                                    </div>
                                                                    <svg class="size-4 text-muted-foreground group-hover:text-primary transition-colors"
                                                                        fill="none" viewBox="0 0 24 24"
                                                                        stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </div>
                                                                <p
                                                                    class="text-xs text-muted-foreground font-medium leading-relaxed">
                                                                    <span x-text="addr.address_line1"></span>
                                                                    <span x-show="addr.address_line2">, <span
                                                                            x-text="addr.address_line2"></span></span>
                                                                    <br>
                                                                    <span x-text="addr.village"></span>, <span
                                                                        x-text="addr.district"></span>
                                                                    <br>
                                                                    <span x-text="addr.state"></span> - <span
                                                                        x-text="addr.pincode"></span>
                                                                </p>
                                                            </div>
                                                        </template>

                                                        <template
                                                            x-if="!selectedCustomer.addresses || selectedCustomer.addresses.length === 0">
                                                            <div
                                                                class="col-span-full py-8 text-center border-2 border-dashed border-border/50 rounded-xl bg-secondary/10">
                                                                <p
                                                                    class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2">
                                                                    No addresses saved</p>
                                                                <p class="text-xs text-muted-foreground opacity-70">Add
                                                                    an address to speed up checkout.</p>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ORDERS HISTORY TAB -->
                                        <div x-show="activeTab === 'orders'"
                                            class="p-0 animate-in fade-in slide-in-from-right-4 duration-300">
                                            <div class="relative">
                                                <template
                                                    x-if="activityEvents.filter(e => e.kind === 'order').length === 0">
                                                    <div class="py-24 text-center">
                                                        <div
                                                            class="size-20 rounded-full bg-indigo-50 dark:bg-indigo-900/20 mx-auto mb-4 flex items-center justify-center">
                                                            <svg class="size-8 text-indigo-500" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="1.5"
                                                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                                            </svg>
                                                        </div>
                                                        <h3 class="font-bold text-foreground">No Order History</h3>
                                                        <p class="text-sm text-muted-foreground">This customer hasn't
                                                            placed any orders yet.</p>
                                                    </div>
                                                </template>

                                                <div class="divide-y divide-border/50">
                                                    <template
                                                        x-for="order in activityEvents.filter(e => e.kind === 'order')"
                                                        :key="order.id">
                                                        <div
                                                            class="group p-6 hover:bg-secondary/20 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-6 cursor-default">

                                                            <div class="flex items-start gap-4">
                                                                <div
                                                                    class="mt-1 size-12 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0">
                                                                    <!-- Cart Icon -->
                                                                    <svg class="size-6" fill="none" viewBox="0 0 24 24"
                                                                        stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                                                    </svg>
                                                                </div>
                                                                <div>
                                                                    <div class="flex items-center gap-3 mb-1">
                                                                        <h4 class="text-lg font-black text-foreground"
                                                                            x-text="order.order_number"></h4>
                                                                        <span
                                                                            class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-secondary text-foreground"
                                                                            x-text="order.status"></span>
                                                                    </div>
                                                                    <div
                                                                        class="flex items-center gap-4 text-xs font-bold text-muted-foreground uppercase tracking-wider">
                                                                        <span class="flex items-center gap-1">
                                                                            <svg class="size-3.5" fill="none"
                                                                                viewBox="0 0 24 24"
                                                                                stroke="currentColor">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                            </svg>
                                                                            <span x-text="order.placed_at"></span>
                                                                        </span>
                                                                        <span class="flex items-center gap-1">
                                                                            <svg class="size-3.5" fill="none"
                                                                                viewBox="0 0 24 24"
                                                                                stroke="currentColor">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                                            </svg>
                                                                            <span
                                                                                x-text="order.item_count + ' Items'"></span>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="flex items-center gap-8 pl-16 md:pl-0">
                                                                <!-- Ordered Items List -->
                                                                <div class="hidden md:block">
                                                                    <div class="flex flex-col gap-1">
                                                                        <template x-for="item in order.items"
                                                                            :key="item.id">
                                                                            <div
                                                                                class="flex items-center gap-2 text-xs text-muted-foreground">
                                                                                <span class="font-bold text-foreground"
                                                                                    x-text="item.quantity + 'x'"></span>
                                                                                <span
                                                                                    x-text="item.product?.name || 'Unknown Product'"></span>
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </div>

                                                                <div class="text-right">
                                                                    <p
                                                                        class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-0.5">
                                                                        Total Amount</p>
                                                                    <p class="text-xl font-black font-mono text-foreground"
                                                                        x-text="'Rs ' + order.grand_total"></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ORDERS TAB -->
                                <div x-show="activeTab === 'orders'"
                                    class="p-6 md:p-8 animate-in fade-in slide-in-from-right-2">
                                    <div class="space-y-4">
                                        <template x-for="order in activity.orders" :key="order.id">
                                            <div
                                                class="flex flex-col p-4 bg-background border border-border rounded-xl gap-3">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs"
                                                            x-text="'#' + order.id"></div>
                                                        <div>
                                                            <p class="font-bold text-foreground"
                                                                x-text="order.order_number"></p>
                                                            <p class="text-xs text-muted-foreground"
                                                                x-text="order.placed_at"></p>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="font-bold font-mono">Rs <span
                                                                x-text="order.grand_total"></span></p>
                                                        <span
                                                            class="px-2 py-0.5 rounded-full bg-muted text-[10px] uppercase font-bold text-muted-foreground"
                                                            x-text="order.status"></span>
                                                    </div>
                                                </div>
                                                <!-- Items List -->
                                                <div class="border-t border-border/50 pt-3 px-1">
                                                    <template x-for="item in order.items" :key="item.id">
                                                        <div
                                                            class="flex items-center justify-between text-xs py-1 text-muted-foreground">
                                                            <span x-text="item.product?.name || 'Unknown Item'"></span>
                                                            <span class="font-mono font-bold text-foreground"
                                                                x-text="'x' + item.quantity"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="activity.orders.length === 0">
                                            <div class="text-center py-12 text-muted-foreground">
                                                No past orders found.
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                    </div>



                </div>
                </template>
            </div>

            <!-- Create Customer Modal -->
            <div x-show="showCreateCustomerModal" x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
                style="display: none;">
                <div class="bg-card w-full max-w-2xl rounded-2xl shadow-2xl relative border border-border animate-in fade-in zoom-in-95 duration-200 overflow-hidden flex flex-col max-h-[90vh]"
                    @click.away="showCreateCustomerModal = false">

                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-border flex justify-between items-center bg-muted/30">
                        <div>
                            <h3 class="text-xl font-bold"
                                x-text="newCustomer.id ? 'Edit Customer Details' : 'Add New Customer'"></h3>
                            <p class="text-sm text-muted-foreground"
                                x-text="newCustomer.id ? 'Update the customer profile details.' : 'Fill in the details to create a new customer profile.'">
                            </p>
                        </div>
                        <button @click="showCreateCustomerModal = false"
                            class="text-muted-foreground hover:text-foreground transition-colors p-2 hover:bg-muted rounded-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Scrollable Content -->
                    <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                        <form @submit.prevent>

                            <!-- Section: Identity -->
                            <div class="mb-6">
                                <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-3">
                                    Identity & Contact</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5">First Name <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" x-model="newCustomer.first_name" @input="updateDisplayName()"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                                            placeholder="e.g. Rahul">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5">Last Name</label>
                                        <input type="text" x-model="newCustomer.last_name" @input="updateDisplayName()"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                                            placeholder="e.g. Sharma">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5">Display Name</label>
                                        <input type="text" x-model="newCustomer.display_name"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                                            placeholder="e.g. Rahul Sharma (Farmer)">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5">Source</label>
                                        <select x-model="newCustomer.source"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                                            <option value="">Select Source</option>
                                            <option value="Walk-in">Walk-in</option>
                                            <option value="Referral">Referral</option>
                                            <option value="Campaign">Campaign</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5">Mobile Number <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" x-model="newCustomer.mobile"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                                            placeholder="10-digit number">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5">Alt. Phone</label>
                                        <input type="text" x-model="newCustomer.phone_number_2"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                                            placeholder="Optional">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5">Relative Phone</label>
                                        <input type="text" x-model="newCustomer.relative_phone"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                                            placeholder="Optional">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5">Email (Optional)</label>
                                        <input type="email" x-model="newCustomer.email"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                                            placeholder="email@example.com">
                                    </div>
                                </div>
                            </div>

                            <div class="h-px bg-border/50 my-6"></div>

                            <!-- Section: Classification -->
                            <div class="mb-6">
                                <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-3">
                                    Classification</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5">Customer Type</label>
                                        <select x-model="newCustomer.type"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                                            <option value="farmer">Farmer</option>
                                            <option value="buyer">Buyer</option>
                                            <option value="dealer">Dealer</option>
                                            <option value="vendor">Vendor</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5">Category</label>
                                        <select x-model="newCustomer.category"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                                            <option value="individual">Individual</option>
                                            <option value="business">Business</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="h-px bg-border/50 my-6"></div>

                            <!-- Section: Business / Agriculture -->
                            <div class="mb-2">
                                <div x-show="newCustomer.type === 'farmer'">
                                    <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-3">
                                        Agriculture Details</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-1.5">Land Area</label>
                                            <input type="number" step="0.01" x-model="newCustomer.land_area"
                                                class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1.5">Unit</label>
                                            <select x-model="newCustomer.land_unit"
                                                class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                                                <option value="acre">Acre</option>
                                                <option value="hectare">Hectare</option>
                                                <option value="guntha">Guntha</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1.5">Irrigation
                                                Type</label>
                                            <select x-model="newCustomer.irrigation_type"
                                                class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                                                <option value="">Select Type</option>
                                                <option value="Drip">Drip</option>
                                                <option value="Sprinkler">Sprinkler</option>
                                                <option value="Flood">Flood</option>
                                                <option value="Rainfed">Rainfed</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1.5">Crops </label>
                                            <div
                                                class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto p-2 border border-zinc-200 dark:border-zinc-700 rounded-lg custom-scrollbar">
                                                <template x-for="crop in cropsList" :key="crop">
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="checkbox" :value="crop" x-model="newCustomer.crops"
                                                            class="w-3.5 h-3.5 rounded border-border text-primary focus:ring-primary/20">
                                                        <span class="text-xs" x-text="crop"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="newCustomer.type !== 'farmer'">
                                    <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-3">
                                        Business Information</h4>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium mb-1.5">Company Name</label>
                                        <input type="text" x-model="newCustomer.company_name"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-1.5">GST Number</label>
                                            <input type="text" x-model="newCustomer.gst_number"
                                                class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm uppercase">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1.5">PAN Number</label>
                                            <input type="text" x-model="newCustomer.pan_number"
                                                class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm uppercase">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="h-px bg-border/50 my-6"></div>

                            <!-- Section: Financial & KYC -->
                            <div class="mb-6">
                                <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-3">
                                    Financial & Status</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5">Credit Limit
                                            (Rs)</label>
                                        <input type="number" step="0.01" x-model="newCustomer.credit_limit"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5">Credit Valid
                                            Till</label>
                                        <input type="date" x-model="newCustomer.credit_valid_till"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5">Aadhaar Last 4</label>
                                        <input type="text" maxlength="4" x-model="newCustomer.aadhaar_last4"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                                            placeholder="XXXX">
                                    </div>
                                    <div class="flex items-center gap-4 mt-6">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" x-model="newCustomer.kyc_completed"
                                                class="w-4 h-4 rounded border-border text-primary focus:ring-primary/20">
                                            <span class="text-sm font-medium">KYC Completed</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" x-model="newCustomer.is_blacklisted"
                                                class="w-4 h-4 rounded border-border text-red-500 focus:ring-red-500/20">
                                            <span class="text-sm font-medium text-red-500">Blacklisted</span>
                                        </label>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium mb-1.5">Internal Notes</label>
                                        <textarea x-model="newCustomer.internal_notes" rows="2"
                                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                                            placeholder="Private specific notes..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Error Display -->
                            <div x-show="newCustomerError" x-transition
                                class="mt-4 text-destructive text-sm bg-destructive/10 p-3 rounded-lg flex items-start gap-2 border border-destructive/20">
                                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                                <span x-text="newCustomerError"></span>
                            </div>
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-border bg-muted/30 flex justify-end gap-3 flex-none">
                        <button @click="showCreateCustomerModal = false"
                            class="px-5 py-2.5 text-sm font-medium text-muted-foreground hover:bg-background rounded-lg transition-colors border border-transparent hover:border-border">Cancel</button>
                        <button @click="createCustomer()"
                            class="px-6 py-2.5 text-sm font-bold bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 shadow-lg shadow-primary/25 transition-all hover:-translate-y-0.5 active:translate-y-0 active:scale-95 flex items-center gap-2">
                            <span x-text="newCustomer.id ? 'Save Changes' : 'Create & Select'"></span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 2: PRODUCT SELECTION -->
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
                class="flex gap-6 h-full items-start">

                <!-- Left: Product Catalog -->
                <div class="flex-1 flex flex-col h-full min-h-0">
                    <!-- Search & Filter Bar -->
                    <div class="flex gap-4 mb-6 sticky top-0 z-10 bg-muted/5 pt-1 backdrop-blur-sm">
                        <div class="relative flex-1 group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-muted-foreground group-focus-within:text-primary transition-colors"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" x-model="productQuery" @input.debounce.300ms="searchProducts()"
                                placeholder="Search products..."
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-border bg-card shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        </div>
                        <select
                            class="rounded-xl border border-border bg-card px-4 py-2 pr-8 text-sm shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"
                            @change="productQuery=$event.target.value; searchProducts()">
                            <option value="">All Categories</option>
                            @foreach(\App\Models\Category::where('is_active', true)->get() as $cat)
                                <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Product Table -->
                    <div
                        class="flex-1 overflow-hidden border border-border rounded-xl bg-card shadow-sm flex flex-col mb-6">
                        <div class="overflow-y-auto custom-scrollbar flex-1">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-muted/50 sticky top-0 z-10 backdrop-blur-md shadow-sm">
                                    <tr>
                                        <th
                                            class="p-4 text-xs font-bold text-muted-foreground uppercase tracking-wider w-20">
                                            Image</th>
                                        <th
                                            class="p-4 text-xs font-bold text-muted-foreground uppercase tracking-wider">
                                            Product Details</th>
                                        <th
                                            class="p-4 text-xs font-bold text-muted-foreground uppercase tracking-wider text-center">
                                            Stock</th>
                                        <th
                                            class="p-4 text-xs font-bold text-muted-foreground uppercase tracking-wider text-right">
                                            Price</th>
                                        <th
                                            class="p-4 text-xs font-bold text-muted-foreground uppercase tracking-wider text-right w-40">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <template x-for="product in paginatedProducts" :key="product.id">
                                        <tr class="group hover:bg-muted/30 transition-colors">
                                            <!-- Image -->
                                            <td class="p-4">
                                                <div
                                                    class="w-16 h-16 rounded-lg bg-muted overflow-hidden border border-border shadow-sm group-hover:scale-105 transition-transform">
                                                    <img :src="product.image_url" class="w-full h-full object-cover"
                                                        onerror="this.src='https://placehold.co/100x100?text=IMG'">
                                                </div>
                                            </td>

                                            <!-- Details -->
                                            <td class="p-4">
                                                <div class="flex flex-col">
                                                    <span class="text-xs text-primary font-semibold mb-0.5"
                                                        x-text="product.category || 'General'"></span>
                                                    <span class="font-bold text-foreground text-base"
                                                        x-text="product.name"></span>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="text-[10px] text-muted-foreground font-mono"
                                                            x-text="product.sku"></span>
                                                        <button type="button"
                                                            @click="detailedProduct = product; showProductDetailsModal = true"
                                                            class="text-[10px] text-primary hover:underline font-bold uppercase tracking-wider">
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
                                                        <span
                                                            x-text="product.stock_on_hand > 0 ? 'In Stock' : 'Out of Stock'"></span>
                                                    </span>
                                                    <span x-show="product.stock_on_hand > 0"
                                                        class="text-xs text-muted-foreground mt-1 font-mono">
                                                        <span x-text="product.stock_on_hand"></span> <span
                                                            x-text="product.unit_type"></span>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Price -->
                                            <td class="p-4 text-right">
                                                <span class="font-bold text-lg text-foreground font-mono">Rs
                                                    <span x-text="parseFloat(product.price).toFixed(2)"></span></span>
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
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </template>

                                                    <template x-if="isInCart(product.id)">
                                                        <div
                                                            class="flex items-center bg-background rounded-lg border border-primary/30 p-1 shadow-sm ring-2 ring-primary/5">
                                                            <button @click="updateCartQty(product.id, -1)"
                                                                class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-muted text-foreground transition-colors font-bold text-lg">-</button>
                                                            <span
                                                                class="w-10 text-center font-bold text-primary font-mono"
                                                                x-text="getCartQty(product.id)"></span>
                                                            <button @click="updateCartQty(product.id, 1)"
                                                                class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-muted text-foreground transition-colors font-bold text-lg"
                                                                :disabled="getCartQty(product.id) >= product.stock_on_hand"
                                                                :class="getCartQty(product.id) >= product.stock_on_hand ? 'opacity-30 cursor-not-allowed' : ''">+</button>
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
                                        <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="font-medium">No products found</p>
                                    <p class="text-sm opacity-70">Try modifying your search query</p>
                                </div>
                            </template>
                        </div>
                        <!-- Pagination Controls -->
                        <div class="p-4 border-t border-border bg-muted/50 flex items-center justify-between"
                            x-show="productResults.length > 0">
                            <span class="text-xs text-muted-foreground">
                                Showing <span x-text="(currentPage - 1) * itemsPerPage + 1"></span> - <span
                                    x-text="Math.min(currentPage * itemsPerPage, productResults.length)"></span>
                                of <span x-text="productResults.length"></span>
                            </span>
                            <div class="flex items-center gap-2">
                                <button @click="prevPage" :disabled="currentPage === 1"
                                    class="p-1 px-3 rounded-lg border border-border bg-card hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed text-xs font-medium">Prev</button>
                                <span class="text-xs font-medium text-foreground">Page <span
                                        x-text="currentPage"></span> of <span x-text="totalPages"></span></span>
                                <button @click="nextPage" :disabled="currentPage === totalPages"
                                    class="p-1 px-3 rounded-lg border border-border bg-card hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed text-xs font-medium">Next</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Cart Sidebar (Glassmorphism) -->
                <div class="w-96 flex-none hidden lg:block h-full pb-6">
                    <!-- Customer Intelligence Panel -->
                    <div class="mb-4 bg-card/80 backdrop-blur-xl border border-border/50 rounded-2xl shadow-xl overflow-hidden ring-1 ring-black/5"
                        x-data="{ showHistory: false }" x-show="selectedCustomer"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <button @click="showHistory = !showHistory"
                            class="w-full flex justify-between items-center p-4 bg-gradient-to-r from-blue-500/5 to-transparent hover:bg-blue-500/10 transition-colors">
                            <span class="font-bold text-sm text-primary flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Last 5 Orders
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] bg-primary/10 text-primary px-2 py-0.5 rounded-full font-mono"
                                    x-text="orderHistory.length"></span>
                                <svg class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                                    :class="showHistory ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </button>
                        <div x-show="showHistory"
                            class="p-0 border-t border-border/50 max-h-60 overflow-y-auto custom-scrollbar">
                            <!-- Loader -->
                            <div x-show="orderHistoryLoading" class="text-center py-6 text-muted-foreground">
                                <span class="animate-spin inline-block mr-2">⏳</span> Loading history...
                            </div>

                            <!-- List -->
                            <template x-for="hist in orderHistory" :key="hist.id">
                                <div
                                    class="flex justify-between items-center p-3 border-b border-border/30 last:border-0 hover:bg-muted/30 transition-colors">
                                    <div>
                                        <div class="font-bold text-xs text-foreground" x-text="hist.order_number"></div>
                                        <div class="text-[10px] text-muted-foreground" x-text="hist.placed_at">
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-xs">Rs <span
                                                x-text="hist.grand_total.toFixed(2)"></span></div>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium" :class="{
                                                        'bg-green-100 text-green-700': hist.status === 'Completed' || hist.status === 'Delivered',
                                                        'bg-blue-100 text-blue-700': hist.status === 'Confirmed' || hist.status === 'Processing',
                                                        'bg-yellow-100 text-yellow-700': hist.status === 'Pending' || hist.status === 'Scheduled',
                                                        'bg-red-100 text-red-700': hist.status === 'Cancelled'
                                                    }" x-text="hist.status"></span>
                                    </div>
                                </div>
                            </template>
                            <div x-show="!orderHistoryLoading && orderHistory.length === 0"
                                class="text-center py-4 text-xs text-muted-foreground">No order history found.
                            </div>
                        </div>
                    </div>

                    <div
                        class="sticky top-0 bg-card/80 backdrop-blur-xl border border-border/50 rounded-2xl shadow-xl flex flex-col h-full max-h-[calc(100vh-140px)] ring-1 ring-black/5">
                        <!-- Cart Header -->
                        <div
                            class="p-5 border-b border-border/50 bg-gradient-to-b from-primary/5 to-transparent rounded-t-2xl">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-bold text-lg flex items-center gap-2">
                                    <div class="bg-primary/10 p-1.5 rounded-lg text-primary">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z">
                                            </path>
                                        </svg>
                                    </div>
                                    Current Order
                                </h3>
                                <span class="text-xs font-mono bg-muted px-2 py-1 rounded text-muted-foreground"
                                    x-text="cart.length + ' Items'"></span>
                            </div>
                            <div
                                class="flex items-center gap-2 text-sm text-muted-foreground bg-background/50 p-2 rounded-lg border border-border/50">
                                <div
                                    class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0">
                                    <span
                                        x-text="selectedCustomer ? selectedCustomer.first_name.charAt(0) : '?'"></span>
                                </div>
                                <span class="truncate"
                                    x-text="selectedCustomer ? 'For: ' + selectedCustomer.first_name + ' ' + selectedCustomer.last_name : 'No Customer Selected'"></span>
                                <button type="button" @click="goToStep(1)"
                                    class="text-[10px] text-primary hover:underline ml-auto shrink-0 font-bold uppercase transition-all">Change</button>
                            </div>
                        </div>

                        <!-- Cart Items -->
                        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                            <template x-if="cart.length === 0">
                                <div
                                    class="h-full flex flex-col items-center justify-center text-muted-foreground space-y-4 opacity-60">
                                    <svg class="w-16 h-16 text-muted-foreground/30" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                    </svg>
                                    <p class="text-sm font-medium">Your cart is empty</p>
                                </div>
                            </template>
                            <template x-for="(item, idx) in cart" :key="item.product_id">
                                <div
                                    class="group relative flex gap-3 p-3 rounded-xl bg-background border border-border hover:border-primary/30 transition-all shadow-sm">
                                    <div
                                        class="w-12 h-12 rounded-lg bg-muted flex items-center justify-center overflow-hidden shrink-0">
                                        <img :src="item.image_url" class="w-full h-full object-cover"
                                            onerror="this.src='https://placehold.co/100x100?text=IMG'">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-sm text-foreground truncate" x-text="item.name"></p>
                                        <div class="flex flex-col gap-1 mt-1">
                                            <div class="flex items-center justify-between">
                                                <p class="text-xs text-muted-foreground">Rs <span
                                                        x-text="item.price.toFixed(2)"></span> x <span
                                                        x-text="item.quantity"></span></p>
                                                <div class="flex items-center gap-2">
                                                    <button @click="updateCartQty(item.product_id, -1)"
                                                        class="text-muted-foreground hover:text-primary px-1 font-bold">-</button>
                                                    <span class="text-xs font-bold w-4 text-center"
                                                        x-text="item.quantity"></span>
                                                    <button @click="updateCartQty(item.product_id, 1)"
                                                        class="text-muted-foreground hover:text-primary px-1 font-bold"
                                                        :disabled="item.quantity >= item.max_stock">+</button>
                                                </div>
                                            </div>

                                            <!-- Product Discount -->
                                            <div class="flex items-center gap-2 mt-1">
                                                <select x-model="item.discount_type"
                                                    class="text-[10px] bg-muted/50 border-none rounded p-1 focus:ring-0">
                                                    <option value="fixed">Rs Off</option>
                                                    <option value="percent">% Off</option>
                                                </select>
                                                <input type="number" x-model="item.discount_value"
                                                    class="w-12 text-[10px] bg-muted/50 border-none rounded p-1 focus:ring-0 text-right"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right flex flex-col justify-between">
                                        <div class="flex flex-col">
                                            <template x-if="item.discount_value > 0">
                                                <span class="text-[10px] text-muted-foreground line-through">Rs
                                                    <span
                                                        x-text="(item.price * item.quantity).toFixed(2)"></span></span>
                                            </template>
                                            <p class="font-bold text-sm"
                                                :class="item.discount_value > 0 ? 'text-primary' : 'text-foreground'">
                                                Rs <span
                                                    x-text="((item.price * item.quantity) - (item.discount_type === 'percent' ? (item.price * item.quantity * item.discount_value / 100) : item.discount_value)).toFixed(2)"></span>
                                            </p>
                                        </div>
                                        <button @click="removeFromCart(idx)"
                                            class="text-[10px] text-red-500 hover:text-red-600 font-medium uppercase tracking-wide opacity-0 group-hover:opacity-100 transition-opacity">Remove</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Cart Footer -->
                        <div class="p-5 border-t border-border bg-muted/20 rounded-b-xl space-y-3">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span class="font-medium">Rs <span x-text="subTotal.toFixed(2)"></span></span>
                            </div>
                            <template x-if="cartDiscountTotal > 0">
                                <div class="flex justify-between items-center text-xs text-primary">
                                    <span>Product Discounts</span>
                                    <span>- Rs <span x-text="cartDiscountTotal.toFixed(2)"></span></span>
                                </div>
                            </template>
                            <div class="flex justify-between items-end pt-2 border-t border-border/50">
                                <span class="text-muted-foreground text-sm font-bold">Grand Total</span>
                                <span class="font-bold text-2xl text-primary text-foreground">Rs <span
                                        x-text="grandTotal.toFixed(2)"></span></span>
                            </div>
                            <button @click="if(cart.length > 0) step = 3" :disabled="cart.length === 0"
                                class="w-full bg-primary text-primary-foreground py-3.5 rounded-xl font-bold hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2 group">
                                <span>Proceed to Review</span>
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: REVIEW & SUBMIT -->
            <div x-show="step === 3" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                <form action="{{ route('central.orders.store') }}" method="POST" id="orderForm"
                    class="max-w-6xl mx-auto" @submit.prevent="submitOrder">
                    @csrf
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
                                <div
                                    class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none">
                                </div>
                                <div
                                    class="flex flex-col md:flex-row justify-between md:items-center gap-4 relative z-10">
                                    <div>
                                        <div
                                            class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1">
                                            Customer</div>
                                        <h3 class="font-bold text-lg text-foreground flex items-center gap-2">
                                            <span
                                                x-text="selectedCustomer?.first_name + ' ' + (selectedCustomer?.last_name || '')"></span>
                                            <span x-show="selectedCustomer?.type"
                                                class="px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] border border-primary/20 uppercase"
                                                x-text="selectedCustomer?.type"></span>
                                        </h3>
                                        <div class="flex items-center gap-4 mt-2 text-sm text-muted-foreground">
                                            <span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                                    </path>
                                                </svg> <span x-text="selectedCustomer?.mobile"></span></span>
                                        </div>
                                    </div>
                                    <div class="flex gap-3">
                                        <button type="button" @click="editCustomerDetails()"
                                            class="text-sm font-semibold text-primary hover:text-primary/80 transition-colors flex items-center gap-1 bg-primary/10 px-3 py-1.5 rounded-lg border border-primary/20">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                            Edit Details
                                        </button>
                                        <button type="button" @click="step = 1"
                                            class="text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1 px-3 py-1.5">
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
                                        <h4 class="text-sm font-bold text-muted-foreground uppercase tracking-wider">
                                            Billing Address</h4>
                                        <button type="button" @click.prevent="openAddressModal('billing')"
                                            class="text-xs font-semibold text-primary hover:underline flex items-center gap-1 z-20 relative">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add New
                                        </button>
                                    </div>

                                    <div class="grid gap-3">
                                        <template
                                            x-if="!selectedCustomer?.addresses || selectedCustomer.addresses.length === 0">
                                            <div
                                                class="p-4 border border-dashed border-border rounded-lg text-center text-sm text-muted-foreground bg-muted/30">
                                                No addresses found. Please add one.
                                            </div>
                                        </template>

                                        <template x-for="addr in selectedCustomer?.addresses" :key="addr.id">
                                            <label class="cursor-pointer relative block">
                                                <input type="radio" name="billing_address_id" :value="addr.id"
                                                    x-model="order.billing_address_id" class="peer sr-only">
                                                <div
                                                    class="p-3 rounded-lg border border-border bg-card peer-checked:border-primary peer-checked:ring-1 peer-checked:ring-primary peer-checked:bg-primary/5 transition-all w-full text-left group hover:border-primary/50">
                                                    <div class="flex justify-between items-start mb-1">
                                                        <span class="font-bold text-sm flex items-center gap-2">
                                                            <span x-text="addr.label || 'Address'"></span>
                                                            <span x-show="addr.is_default"
                                                                class="text-[10px] bg-primary text-primary-foreground px-1.5 py-0.5 rounded">Default</span>
                                                        </span>
                                                        <div class="flex gap-2 transition-opacity z-20 relative">
                                                            <button type="button"
                                                                @click.prevent.stop="editAddress(addr)"
                                                                class="text-xs text-primary hover:underline font-medium px-2 py-1 rounded hover:bg-primary/10">Edit</button>
                                                        </div>
                                                    </div>
                                                    <p class="text-xs text-muted-foreground leading-relaxed">
                                                        <span x-text="addr.address_line1"></span><span
                                                            x-show="addr.address_line2">, <span
                                                                x-text="addr.address_line2"></span></span><br>
                                                        <span x-text="addr.village || addr.city"></span>, <span
                                                            x-text="addr.state"></span> - <span
                                                            x-text="addr.pincode"></span>
                                                    </p>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                <!-- Shipping Address -->
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-bold text-muted-foreground uppercase tracking-wider">
                                            Shipping Address</h4>
                                        <div class="flex items-center gap-3">
                                            <label
                                                class="flex items-center gap-1.5 text-xs text-muted-foreground cursor-pointer select-none">
                                                <input type="checkbox" x-model="order.same_as_billing"
                                                    class="rounded border-border text-primary focus:ring-primary/20 w-3.5 h-3.5">
                                                Same as Billing
                                            </label>
                                            <button type="button" @click.prevent="openAddressModal('shipping')"
                                                class="text-xs font-semibold text-primary hover:underline flex items-center gap-1 z-20 relative">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Add New
                                            </button>
                                        </div>
                                    </div>

                                    <div x-show="!order.same_as_billing" class="grid gap-3 relative">
                                        <template
                                            x-if="!selectedCustomer?.addresses || selectedCustomer.addresses.length === 0">
                                            <div
                                                class="p-4 border border-dashed border-border rounded-lg text-center text-sm text-muted-foreground bg-muted/30">
                                                No addresses found.
                                            </div>
                                        </template>

                                        <template x-for="addr in selectedCustomer?.addresses" :key="'ship-'+addr.id">
                                            <label class="cursor-pointer relative block">
                                                <input type="radio" name="shipping_address_id" :value="addr.id"
                                                    x-model="order.shipping_address_id" class="peer sr-only">
                                                <div
                                                    class="p-3 rounded-lg border border-border bg-card peer-checked:border-primary peer-checked:ring-1 peer-checked:ring-primary peer-checked:bg-primary/5 transition-all w-full text-left group hover:border-primary/50">
                                                    <div class="flex justify-between items-start mb-1">
                                                        <span class="font-bold text-sm flex items-center gap-2">
                                                            <span x-text="addr.label || 'Address'"></span>
                                                        </span>
                                                        <div class="flex gap-2 transition-opacity z-20 relative">
                                                            <button type="button"
                                                                @click.prevent.stop="editAddress(addr)"
                                                                class="text-xs text-primary hover:underline font-medium px-2 py-1 rounded hover:bg-primary/10">Edit</button>
                                                        </div>
                                                    </div>
                                                    <p class="text-xs text-muted-foreground leading-relaxed">
                                                        <span x-text="addr.address_line1"></span><span
                                                            x-show="addr.address_line2">, <span
                                                                x-text="addr.address_line2"></span></span><br>
                                                        <span x-text="addr.village || addr.city"></span>, <span
                                                            x-text="addr.state"></span> - <span
                                                            x-text="addr.pincode"></span>
                                                    </p>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                    <div x-show="order.same_as_billing"
                                        class="p-4 rounded-lg bg-muted/30 border border-dashed border-border text-center flex flex-col items-center justify-center py-8">
                                        <svg class="w-8 h-8 text-muted-foreground/30 mb-2" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <p class="text-sm text-muted-foreground font-medium">Shipping address
                                            same as billing</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-card border border-border rounded-xl shadow-sm p-6">
                                <h3 class="font-semibold text-lg mb-6 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                    Ordered Items
                                </h3>
                                <div class="overflow-hidden rounded-lg border border-border">
                                    <table class="w-full text-sm">
                                        <thead
                                            class="bg-muted/50 text-muted-foreground text-xs uppercase font-semibold">
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
                                                            <img :src="item.image_url"
                                                                class="w-10 h-10 rounded object-cover bg-muted"
                                                                onerror="this.src='https://placehold.co/50x50'">
                                                            <span class="font-medium text-foreground"
                                                                x-text="item.name"></span>
                                                        </div>
                                                    </td>
                                                    <td class="px-5 py-4 text-right">Rs <span
                                                            x-text="item.price.toFixed(2)"></span></td>
                                                    <td class="px-5 py-4 text-center font-mono" x-text="item.quantity">
                                                    </td>
                                                    <td class="px-5 py-4 text-right font-bold text-foreground">
                                                        Rs <span
                                                            x-text="(item.price * item.quantity).toFixed(2)"></span>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div
                                class="bg-card border border-border rounded-xl shadow-sm p-6 transition-all hover:shadow-md">
                                <h3 class="font-semibold text-lg mb-6 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Order Type & Schedule
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-4">
                                        <label class="text-sm font-medium text-muted-foreground block">Is this a
                                            future order?</label>
                                        <div class="flex items-center gap-6">
                                            <label class="flex items-center gap-2 cursor-pointer group">
                                                <input type="radio" name="is_future_order_radio" value="0"
                                                    x-model="order.is_future_order"
                                                    @change="order.is_future_order = false" class="peer sr-only">
                                                <div
                                                    class="w-5 h-5 rounded-full border-2 border-border flex items-center justify-center peer-checked:border-primary peer-checked:bg-primary transition-all group-hover:border-primary/50 shadow-sm">
                                                    <div
                                                        class="w-1.5 h-1.5 rounded-full bg-white opacity-0 peer-checked:opacity-100">
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-sm font-semibold text-muted-foreground peer-checked:text-foreground transition-colors">Immediate
                                                    Order</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer group">
                                                <input type="radio" name="is_future_order_radio" value="1"
                                                    x-model="order.is_future_order"
                                                    @change="order.is_future_order = true" class="peer sr-only">
                                                <div
                                                    class="w-5 h-5 rounded-full border-2 border-border flex items-center justify-center peer-checked:border-primary peer-checked:bg-primary transition-all group-hover:border-primary/50 shadow-sm">
                                                    <div
                                                        class="w-1.5 h-1.5 rounded-full bg-white opacity-0 peer-checked:opacity-100">
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-sm font-semibold text-muted-foreground peer-checked:text-foreground transition-colors">Future/Scheduled
                                                    Order</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div x-show="order.is_future_order"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-2"
                                        x-transition:enter-end="opacity-100 translate-y-0" class="space-y-2">
                                        <label class="block text-sm font-medium text-muted-foreground">Scheduled
                                            Date & Time <span class="text-red-500">*</span></label>
                                        <div class="relative group">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-4 h-4 text-muted-foreground group-focus-within:text-primary transition-colors"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <input type="datetime-local" x-model="order.scheduled_at"
                                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-border bg-background shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm"
                                                :required="order.is_future_order">
                                        </div>
                                        <p class="text-[10px] text-muted-foreground italic">Order will be
                                            processed at the selected time.</p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-card border border-border rounded-xl shadow-sm p-6 transition-all hover:shadow-md">
                                <h3 class="font-semibold text-lg mb-6 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    Logistics & Notes
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-muted-foreground mb-2">Fulfillment
                                            Warehouse</label>
                                        <select name="warehouse_id"
                                            class="w-full rounded-lg border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-1 focus:ring-primary py-2.5 transition-all"
                                            required>
                                            @foreach($warehouses as $wh)
                                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- <div>
                                             <label class="block text-sm font-medium text-muted-foreground mb-2">Order ID (Auto-generated)</label>
                                             <input type="text" name="order_number" value="ORD-{{ strtoupper(\Illuminate\Support\Str::random(10)) }}" class="w-full bg-muted/50 border border-border rounded-lg text-muted-foreground font-mono" readonly>
                                        </div> -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-muted-foreground mb-2">Internal
                                            Notes</label>
                                        <textarea name="notes" rows="3"
                                            placeholder="Add any special instructions for this order..."
                                            class="w-full rounded-lg border-border bg-background shadow-sm focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Summary Sidebar -->
                        <div class="space-y-6">
                            <div class="bg-card border border-border rounded-xl shadow-lg p-6 sticky top-24">
                                <h3 class="font-bold text-lg mb-4">Payment & Confirmation</h3>

                                <div class="space-y-3 pb-4 border-b border-border mb-4">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-muted-foreground">Gross Amount</span>
                                        <span class="font-medium">Rs <span x-text="subTotal.toFixed(2)"></span></span>
                                    </div>
                                    <template x-if="cartDiscountTotal > 0">
                                        <div class="flex justify-between items-center text-sm text-primary">
                                            <span>Product Discounts</span>
                                            <span>- Rs <span x-text="cartDiscountTotal.toFixed(2)"></span></span>
                                        </div>
                                    </template>

                                    <!-- Order Level Discount -->
                                    <div class="pt-2">
                                        <label
                                            class="block text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-2">Order
                                            Discount</label>
                                        <div class="flex gap-2">
                                            <select x-model="order.discount_type"
                                                class="text-xs bg-muted/50 border-border rounded-lg p-2 focus:ring-0">
                                                <option value="fixed">Fixed Rs</option>
                                                <option value="percent">% Off</option>
                                            </select>
                                            <input type="number" x-model="order.discount_value"
                                                class="flex-1 text-sm bg-muted/50 border-border rounded-lg p-2 focus:ring-0 text-right"
                                                placeholder="0.00">
                                        </div>
                                    </div>

                                    <template x-if="orderDiscountAmount > 0">
                                        <div class="flex justify-between items-center text-sm text-primary pt-2">
                                            <span>Order Discount</span>
                                            <span>- Rs <span x-text="orderDiscountAmount.toFixed(2)"></span></span>
                                        </div>
                                    </template>

                                    <div class="flex justify-between items-center text-sm text-muted-foreground pt-2">
                                        <span>Tax (0%)</span>
                                        <span>Rs 0.00</span>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center text-2xl font-black text-primary mb-6">
                                    <span>Grand Total</span>
                                    <span>Rs <span x-text="grandTotal.toFixed(2)"></span></span>
                                </div>

                                <button type="button" @click="submitOrder()" id="submitBtn"
                                    class="w-full bg-primary text-primary-foreground py-4 rounded-xl font-bold shadow-xl shadow-primary/30 hover:bg-primary/90 transition-transform active:scale-[0.98] flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Place Order
                                </button>
                                <button type="button" @click="step = 2"
                                    class="w-full mt-4 text-sm text-center text-muted-foreground hover:text-foreground hover:underline transition-colors">
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
    <div x-show="showAddressModal" class="fixed inset-0 z-50 flex items-center justify-center px-4"
        style="display: none;">

        <div class="fixed inset-0 bg-background/80 backdrop-blur-sm transition-opacity"
            @click="showAddressModal = false"></div>

        <div
            class="relative bg-card rounded-2xl shadow-xl w-full max-w-lg border border-border p-6 overflow-hidden transform transition-all">
            <div class="absolute top-4 right-4">
                <button @click="showAddressModal = false"
                    class="text-muted-foreground hover:text-foreground transition-colors p-1 rounded-md hover:bg-muted">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-bold text-foreground"
                    x-text="addressForm.id ? 'Edit Address' : 'Add New Address'"></h3>
                <p class="text-sm text-muted-foreground">Enter address details for delivery or billing.</p>
            </div>

            <div class="space-y-4 relative">
                <div>
                    <label class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">Label <span
                            class="text-red-500">*</span></label>
                    <input type="text" x-model="addressForm.label"
                        class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                        placeholder="e.g. Home, Farm, Warehouse">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">Line 1
                            <span class="text-red-500">*</span></label>
                        <input type="text" x-model="addressForm.address_line1"
                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                            placeholder="House/Plot No">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">Line
                            2</label>
                        <input type="text" x-model="addressForm.address_line2"
                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                            placeholder="Street/Area">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">Pincode
                            <span class="text-red-500">*</span></label>
                        <input type="text" id="modal_pincode" x-model="addressForm.pincode"
                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                            placeholder="6-digit code">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">Post
                            Office</label>
                        <input type="text" id="modal_post_office" x-model="addressForm.post_office" readonly
                            tabindex="0"
                            class="w-full cursor-pointer rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                            placeholder="Select Post Office">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">Village/City</label>
                        <input type="text" id="modal_village" x-model="addressForm.village"
                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                            placeholder="Type to search">
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">Taluka</label>
                        <input type="text" id="modal_taluka" x-model="addressForm.taluka"
                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                            placeholder="Type to search">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">District</label>
                        <input type="text" id="modal_district" x-model="addressForm.district"
                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                            placeholder="Type to search">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5 focus:text-primary transition-colors">State
                            <span class="text-red-500">*</span></label>
                        <input type="text" id="modal_state" x-model="addressForm.state"
                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                            placeholder="Type to search">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="addressForm.is_default"
                            class="rounded border-border text-primary focus:ring-primary/20 bg-background w-4 h-4">
                        <span class="text-sm">Set as Default</span>
                    </label>
                </div>

                <!-- Address Lookup Dropdown -->
                <div id="addressDropdown"
                    class="hidden absolute bg-card border border-border rounded-lg shadow-xl max-h-60 overflow-y-auto z-50"
                    style="min-width: 200px;"></div>

                <div x-show="addressForm.error" class="text-sm text-red-500 bg-red-500/10 p-2 rounded-lg"
                    x-text="addressForm.error"></div>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="showAddressModal = false"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-muted hover:bg-muted/80 text-foreground transition-colors">Cancel</button>
                    <button @click="saveAddress()"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                        <span x-text="addressForm.loading ? 'Saving...' : 'Save Address'"></span>
                    </button>
                </div>
            </div>
        </div>

        <script>
            function orderWizard(initialProducts, preSelectedCustomer) {
                return {
                    step: 1, // Always start at Step 1 (Customer Profile) to show the dashboard

                    // Order State
                    order: {
                        billing_address_id: null,
                        shipping_address_id: null,
                        same_as_billing: true,
                        is_future_order: false,
                        scheduled_at: '',
                        payment_method: 'cash',
                        shipping_method: 'standard',
                        discount_type: 'fixed',
                        discount_value: 0
                    },

                    // Customer State
                    customerQuery: '',
                    customerResults: [],
                    selectedCustomer: preSelectedCustomer || null,
                    showCreateCustomerModal: false,
                    cropsList: ['Cotton', 'Paddy', 'Wheat', 'Soybean', 'Chilli', 'Maize', 'Tur', 'Gram', 'Groundnut', 'Sugarcane', 'Banana', 'Vegetables'],
                    newCustomer: {
                        display_name: '',
                        first_name: '',
                        last_name: '',
                        mobile: '',
                        phone_number_2: '',
                        relative_phone: '',
                        email: '',
                        source: '',
                        type: 'farmer',
                        category: 'individual',
                        company_name: '',
                        gst_number: '',
                        pan_number: '',
                        land_area: '',
                        land_unit: 'acre',
                        irrigation_type: '',
                        crops: [],
                        credit_limit: '',
                        credit_valid_till: '',
                        aadhaar_last4: '',
                        kyc_completed: false,
                        internal_notes: '',
                        is_active: true,
                        is_blacklisted: false
                    },
                    newCustomerError: '',

                    // State Management
                    isRestoring: false,

                    // Address State
                    showAddressModal: false,
                    addressForm: {
                        id: null,
                        label: '',
                        address_line1: '',
                        address_line2: '',
                        village: '',
                        taluka: '',
                        district: '',
                        post_office: '',
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

                    interactionType: 'order_dropped',

                    // Activity State
                    activity: { orders: [], interactions: [] },
                    activityLoading: false,
                    activeTab: 'timeline',

                    // Order History State (Legacy + Activity)
                    orderHistory: [],
                    orderHistoryLoading: false,

                    // Product Pagination State
                    currentPage: 1,
                    itemsPerPage: 6,

                    get paginatedProducts() {
                        const start = (this.currentPage - 1) * this.itemsPerPage;
                        const end = start + this.itemsPerPage;
                        return this.productResults.slice(start, end);
                    },

                    get activityEvents() {
                        const orders = (this.activity?.orders || []).map(o => ({ ...o, kind: 'order', sortDate: new Date(o.date) }));
                        const interactions = (this.activity?.interactions || []).map(i => ({ ...i, kind: 'interaction', sortDate: new Date(i.date) }));
                        return [...orders, ...interactions].sort((a, b) => b.sortDate - a.sortDate);
                    },

                    get totalPages() {
                        return Math.ceil(this.productResults.length / this.itemsPerPage);
                    },

                    nextPage() {
                        if (this.currentPage < this.totalPages) this.currentPage++;
                    },

                    prevPage() {
                        if (this.currentPage > 1) this.currentPage--;
                    },

                    goToPage(page) {
                        this.currentPage = page;
                    },

                    fetchCustomerActivity(customerId) {
                        if (!customerId) {
                            this.orderHistory = [];
                            this.activity = { orders: [], interactions: [] };
                            return;
                        }
                        this.activityLoading = true;
                        // Also set legacy loading
                        this.orderHistoryLoading = true;

                        fetch(`{{ route('central.api.search.customer-activity') }}?customer_id=${customerId}`)
                            .then(res => res.json())
                            .then(data => {
                                this.activity = {
                                    orders: data.orders || [],
                                    interactions: data.interactions || []
                                };
                                // Maintain legacy orderHistory for Step 2 panel
                                this.orderHistory = data.orders || [];
                                this.activityLoading = false;
                                this.orderHistoryLoading = false;
                            })
                            .catch(err => {
                                console.error('Failed to fetch activity', err);
                                this.activityLoading = false;
                                this.orderHistoryLoading = false;
                            });
                    },

                    // Legacy alias if needed, or just replace calls
                    fetchOrderHistory(customerId) {
                        this.fetchCustomerActivity(customerId);
                    },

                    init() {
                        this.isRestoring = true;

                        // 1. Check for Reset Parameter
                        const urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.has('reset')) {
                            localStorage.removeItem('order_wizard_state');
                            // CLEANUP: Remove reset param from URL so refresh doesn't trigger it again
                            const newUrl = window.location.pathname;
                            window.history.replaceState({}, document.title, newUrl);

                            if (!preSelectedCustomer) {
                                this.step = 1;
                                this.selectedCustomer = null;
                                this.cart = [];
                                this.isRestoring = false;
                                return;
                            }
                        }

                        // 1b. Check for Customer Query (Code/Mobile) via URL
                        if (urlParams.has('customer_query') && !preSelectedCustomer) {
                            this.customerQuery = urlParams.get('customer_query');
                            this.searchCustomers().then(() => {
                                if (this.customerResults.length > 0) {
                                    this.selectCustomer(this.customerResults[0]);
                                }
                            });
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

                                // Restore customer if not forced by backend
                                if (!preSelectedCustomer) {
                                    // SAFELY restore selected customer if available in storage
                                    if (parsed.selectedCustomer && parsed.selectedCustomer.id) {
                                        this.selectedCustomer = parsed.selectedCustomer;

                                        // Defer history fetch to next tick to avoid init race conditions
                                        this.$nextTick(() => {
                                            if (typeof this.fetchOrderHistory === 'function') {
                                                this.fetchOrderHistory(this.selectedCustomer.id);
                                            }
                                        });
                                    }
                                }

                                // Restore Step (prioritize saved step over defaults)
                                if (parsed.step) {
                                    this.step = parseInt(parsed.step);
                                }

                                this.cart = parsed.cart || [];

                                // Merge order object
                                this.order = {
                                    billing_address_id: null,
                                    shipping_address_id: null,
                                    same_as_billing: true,
                                    ...this.order,
                                    ...parsed.order
                                };

                                // Restore other UI states
                                this.customerQuery = parsed.customerQuery || '';
                                this.productQuery = parsed.productQuery || '';

                                // Validate Consistency: If on Step 2/3 but no customer, fallback to 1
                                if (this.step > 1 && !this.selectedCustomer) {
                                    this.step = 1;
                                }
                                // If on Step 3 but empty cart, fallback to 1 (or 2)
                                if (this.step === 3 && this.cart.length === 0) {
                                    this.step = this.selectedCustomer ? 2 : 1;
                                }

                            } catch (e) {
                                console.error('Failed to restore state', e);
                                localStorage.removeItem('order_wizard_state');
                            }
                        }

                        // Allow UI to settle before enabling saveState
                        this.$nextTick(() => {
                            this.isRestoring = false;
                        });

                        // 2. Setup Watchers for Persistence
                        const save = () => { if (!this.isRestoring) this.saveState(); };

                        this.$watch('step', save);
                        this.$watch('selectedCustomer', save);
                        this.$watch('cart', save);
                        this.$watch('order', save);

                        // Deep watchers for nested object changes
                        this.$watch('cart', save, { deep: true });
                        this.$watch('order', save, { deep: true });
                        this.$watch('customerQuery', save);
                        this.$watch('productQuery', save);
                    },

                    saveState() {
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

                    // Helper to open modal with pre-filled data
                    openCreateCustomerModal() {
                        this.cropsList = ['Cotton', 'Paddy', 'Wheat', 'Soybean', 'Chilli', 'Maize', 'Tur', 'Gram', 'Groundnut', 'Sugarcane', 'Banana', 'Vegetables'];

                        this.newCustomer = {
                            id: null,
                            display_name: '',
                            first_name: '',
                            last_name: '',
                            mobile: '',
                            phone_number_2: '',
                            relative_phone: '',
                            email: '',
                            source: '',
                            type: 'farmer',
                            category: 'individual',
                            company_name: '',
                            gst_number: '',
                            pan_number: '',
                            land_area: '',
                            land_unit: 'acre',
                            irrigation_type: '',
                            crops: [],
                            credit_limit: '',
                            credit_valid_till: '',
                            aadhaar_last4: '',
                            kyc_completed: false,
                            internal_notes: '',
                            is_active: true,
                            is_blacklisted: false
                        };

                        if (/^\d+$/.test(this.customerQuery)) {
                            this.newCustomer.mobile = this.customerQuery;
                        }
                        else if (this.customerQuery.length > 0) {
                            this.newCustomer.first_name = this.customerQuery;
                            this.updateDisplayName();
                        }

                        this.showCreateCustomerModal = true;
                    },

                    updateDisplayName() {
                        const first = this.newCustomer.first_name || '';
                        const last = this.newCustomer.last_name || '';
                        this.newCustomer.display_name = (first + ' ' + last).trim();
                    },

                    async createCustomer() {
                        this.newCustomerError = '';
                        if (!this.newCustomer.first_name || !this.newCustomer.mobile) {
                            this.newCustomerError = 'First Name and Mobile Number are required.';
                            return;
                        }

                        // Prepare payload
                        let payload = { ...this.newCustomer };

                        // Ensure crops is an array (it should be from checkboxes, but safety first)
                        if (!Array.isArray(payload.crops)) {
                            payload.crops = [];
                        }

                        try {
                            const response = await fetch("{{ route('central.api.customers.store-quick') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify(payload)
                            });

                            const data = await response.json().catch(() => null);

                            if (response.ok && data && data.success) {
                                this.selectedCustomer = data.customer;
                                this.showCreateCustomerModal = false;
                                setTimeout(() => {
                                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: this.newCustomer.id ? 'Customer updated successfully.' : 'Customer created successfully.' } }));
                                }, 300);
                            } else {
                                this.newCustomerError = (data && data.message) || response.statusText || 'Failed to save customer. Server returned ' + response.status;
                            }
                        } catch (error) {
                            console.error(error);
                            this.newCustomerError = 'Network error: ' + error.message;
                        }
                    },

                    editCustomerDetails() {
                        if (!this.selectedCustomer) return;

                        this.newCustomer = {
                            id: this.selectedCustomer.id,
                            display_name: this.selectedCustomer.display_name,
                            first_name: this.selectedCustomer.first_name,
                            last_name: this.selectedCustomer.last_name,
                            mobile: this.selectedCustomer.mobile,
                            phone_number_2: this.selectedCustomer.phone_number_2,
                            relative_phone: this.selectedCustomer.relative_phone,
                            email: this.selectedCustomer.email,
                            source: this.selectedCustomer.source,
                            type: this.selectedCustomer.type,
                            category: this.selectedCustomer.category || 'individual',
                            company_name: this.selectedCustomer.company_name,
                            gst_number: this.selectedCustomer.gst_number,
                            pan_number: this.selectedCustomer.pan_number,
                            land_area: this.selectedCustomer.land_area,
                            land_unit: this.selectedCustomer.land_unit || 'acre',
                            irrigation_type: this.selectedCustomer.irrigation_type,
                            crops: this.selectedCustomer.crops || [],
                            credit_limit: this.selectedCustomer.credit_limit,
                            credit_valid_till: this.selectedCustomer.credit_valid_till,
                            aadhaar_last4: this.selectedCustomer.aadhaar_last4,
                            kyc_completed: !!this.selectedCustomer.kyc_completed,
                            internal_notes: this.selectedCustomer.internal_notes,
                            is_active: this.selectedCustomer.is_active !== 0,
                            is_blacklisted: this.selectedCustomer.is_blacklisted === 1
                        };

                        this.showCreateCustomerModal = true;
                    },

                    // API Calls
                    async searchCustomers() {
                        if (this.customerQuery.length < 2) return;
                        try {
                            let res = await fetch(`{{ route('central.api.search.customers') }}?q=${this.customerQuery}&_t=${Date.now()}`);
                            if (!res.ok) throw new Error('Network response was not ok');
                            this.customerResults = await res.json();
                        } catch (e) {
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
                        } catch (e) {
                            console.error(e);
                            this.newCustomerError = 'Server error occurred.';
                        }
                    },

                    selectCustomer(cust, shouldNextStep = false) {
                        this.selectedCustomer = cust;
                        this.fetchOrderHistory(cust.id);
                        // Auto-select Default Addresses
                        if (cust.addresses && cust.addresses.length > 0) {
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
                            let routeUrl = "{{ route('central.customers.interaction', ['customer' => 'CUSTOMER_ID']) }}";
                            let res = await fetch(routeUrl.replace('CUSTOMER_ID', this.selectedCustomer.id), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    outcome: this.tagOutcome,
                                    notes: this.tagNotes,
                                    type: this.interactionType || 'order_dropped',
                                    close_session: true,
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
                            taluka: '',
                            district: '',
                            post_office: '',
                            state: 'Maharashtra',
                            pincode: '',
                            is_default: false,
                            type: type || 'shipping',
                            loading: false,
                            error: ''
                        };
                        this.showAddressModal = true;

                        // Setup auto-complete after modal is shown
                        this.$nextTick(() => {
                            this.setupAddressAutoComplete();
                        });
                    },

                    editAddress(addr) {
                        this.addressForm = {
                            customer_id: this.selectedCustomer.id,
                            id: addr.id,
                            label: addr.label || '',
                            address_line1: addr.address_line1 || '',
                            address_line2: addr.address_line2 || '',
                            village: addr.village || addr.city || '',
                            taluka: addr.taluka || '',
                            district: addr.district || '',
                            post_office: addr.post_office || '',
                            state: addr.state || 'Maharashtra',
                            pincode: addr.pincode || '',
                            is_default: addr.is_default || false,
                            type: 'both',
                            loading: false,
                            error: ''
                        };
                        this.showAddressModal = true;

                        // Setup auto-complete after modal is shown
                        this.$nextTick(() => {
                            this.setupAddressAutoComplete();
                        });
                    },

                    // Village Lookup Logic - Enhanced to match customer create page
                    setupAddressAutoComplete() {
                        const fields = ['pincode', 'post_office', 'village', 'taluka', 'district', 'state'];
                        const dropdown = document.getElementById('addressDropdown');

                        if (!dropdown) return;

                        let activeField = null;
                        let debounceTimer = null;
                        let preventBlurClose = false;

                        const setAll = (data) => {
                            Object.keys(data).forEach(key => {
                                if (key === 'pincode') this.addressForm.pincode = data[key] ?? this.addressForm.pincode;
                                if (key === 'post_office') this.addressForm.post_office = data[key] ?? this.addressForm.post_office;
                                if (key === 'village') this.addressForm.village = data[key] ?? this.addressForm.village;
                                if (key === 'taluka') this.addressForm.taluka = data[key] ?? this.addressForm.taluka;
                                if (key === 'district') this.addressForm.district = data[key] ?? this.addressForm.district;
                                if (key === 'state') this.addressForm.state = data[key] ?? this.addressForm.state;
                            });
                        };

                        const hideDropdown = (force = false) => {
                            if (preventBlurClose && !force) return;
                            dropdown.classList.add('hidden');
                            dropdown.innerHTML = '';
                        };

                        const showDropdown = (list, anchor) => {
                            dropdown.innerHTML = '';
                            dropdown.classList.remove('hidden');

                            // Position dropdown relative to input
                            dropdown.style.minWidth = anchor.offsetWidth + 'px';
                            dropdown.style.left = anchor.offsetLeft + 'px';
                            dropdown.style.top = (anchor.offsetTop + anchor.offsetHeight + 4) + 'px';

                            list.forEach(item => {
                                const option = document.createElement('div');
                                option.className = 'px-3 py-2 cursor-pointer hover:bg-muted text-sm border-b border-border/50 last:border-0';
                                option.innerHTML = `
                                <div class="font-medium">${item.label || ''}</div>
                            `;

                                option.addEventListener('mousedown', e => {
                                    e.preventDefault();
                                    setAll(item.data);
                                    hideDropdown(true);
                                });

                                dropdown.appendChild(option);
                            });
                        };

                        const lookup = (query, anchor) => {
                            fetch(`{{ url('/api/village-lookup') }}?${query}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                                .then(res => res.ok ? res.json() : null)
                                .then(res => {
                                    if (!res || !res.found) {
                                        hideDropdown();
                                        return;
                                    }

                                    if (res.mode === 'single') {
                                        setAll(res.data);
                                        hideDropdown();
                                    }

                                    if (res.mode === 'multiple') {
                                        showDropdown(res.list, anchor);
                                    }
                                })
                                .catch(err => {
                                    console.error('Lookup error:', err);
                                    hideDropdown();
                                });
                        };

                        fields.forEach(id => {
                            const el = document.getElementById('modal_' + id);
                            if (!el) return;

                            const isReadonly = el.hasAttribute('readonly');

                            // For typing fields
                            el.addEventListener('input', e => {
                                if (isReadonly) return;

                                const value = e.target.value.trim();
                                activeField = el;

                                clearTimeout(debounceTimer);
                                debounceTimer = setTimeout(() => {
                                    if (id === 'pincode' && value.length < 6) return;
                                    if (id !== 'pincode' && value.length < 2) return;

                                    lookup(`${id}=${encodeURIComponent(value)}`, el);
                                }, 300);
                            });

                            // For readonly dropdown fields (Post Office)
                            el.addEventListener('focus', () => {
                                activeField = el;

                                if (isReadonly) {
                                    const baseValue =
                                        document.getElementById('modal_pincode')?.value ||
                                        document.getElementById('modal_village')?.value ||
                                        '';

                                    if (baseValue.length >= 2) {
                                        lookup(`post_office=${encodeURIComponent(baseValue)}`, el);
                                    }
                                }
                            });

                            el.addEventListener('click', () => {
                                if (isReadonly) {
                                    el.dispatchEvent(new Event('focus'));
                                }
                            });

                            el.addEventListener('blur', () => {
                                setTimeout(() => hideDropdown(), 150);
                            });
                        });
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
                            // order_number: document.querySelector('[name="order_number"]')?.value,
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
                            if (loadingBtn) {
                                loadingBtn.disabled = true;
                                loadingBtn.innerHTML = '<span class="animate-spin mr-2">⏳</span> Processing...';
                            }

                            let res = await fetch(`{{ route('central.orders.store') }}`, {
                                method: 'POST',
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
                                if (loadingBtn) {
                                    loadingBtn.disabled = false;
                                    loadingBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Place Order';
                                }
                                return;
                            }

                            // Handle Generic Failures (500, etc)
                            if (!res.ok || (data && data.success === false)) {
                                throw new Error(data.message || res.statusText || 'Server Error');
                            }

                            // Success - Redirect to Customer Profile (Step 1)
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Order placed successfully!' } }));

                            this.interactionType = 'order_placed';
                            this.cart = []; // Clear cart
                            this.step = 1; // Go back to customer profile
                            this.fetchOrderHistory(this.selectedCustomer.id); // Refresh history

                            if (loadingBtn) {
                                loadingBtn.disabled = false;
                                loadingBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Place Order';
                            }

                        } catch (e) {
                            console.error(e);
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Error: ' + e.message } }));
                            if (loadingBtn) {
                                loadingBtn.disabled = false;
                                loadingBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Place Order';
                            }
                        }
                    },

                    async saveAddress() {
                        this.addressForm.loading = true;
                        this.addressForm.error = '';

                        if (!this.addressForm.address_line1 || !this.addressForm.pincode) {
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
                                if (this.addressForm.id === this.order.billing_address_id) {
                                    // Forced reactivity update if needed
                                } if (!this.order.billing_address_id) {
                                    this.order.billing_address_id = data.address.id;
                                }

                                this.showAddressModal = false;
                                setTimeout(() => {
                                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: this.addressForm.id ? 'Address updated successfully.' : 'Address added successfully.' } }));
                                }, 300);
                            } else {
                                this.addressForm.error = data.message || 'Failed to save address';
                            }
                        } catch (e) {
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
                        } catch (e) { console.error(e); }
                    },

                    addToCart(product) {
                        if (product.stock_on_hand <= 0) return;
                        let existing = this.cart.find(i => i.product_id === product.id);
                        if (existing) {
                            if (existing.quantity < product.stock_on_hand) {
                                existing.quantity++;
                            }
                        } else {
                            this.cart.push({
                                product_id: product.id,
                                name: product.name,
                                price: parseFloat(product.price),
                                image_url: product.image_url,
                                quantity: 1,
                                max_stock: product.stock_on_hand,
                                discount_type: product.default_discount_type || 'fixed',
                                discount_value: parseFloat(product.default_discount_value || 0)
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
                            display_name: '',
                            first_name: '',
                            last_name: '',
                            mobile: '',
                            phone_number_2: '',
                            relative_phone: '',
                            email: '',
                            source: '',
                            type: 'farmer',
                            category: 'individual',
                            company_name: '',
                            gst_number: '',
                            pan_number: '',
                            land_area: '',
                            land_unit: 'acre',
                            irrigation_type: '',
                            crops: [],
                            credit_limit: '',
                            credit_valid_till: '',
                            aadhaar_last4: '',
                            kyc_completed: false,
                            internal_notes: '',
                            is_active: true,
                            is_blacklisted: false
                        };

                        if (/^\d+$/.test(this.customerQuery)) {
                            this.newCustomer.mobile = this.customerQuery;
                        }
                        else if (this.customerQuery.length > 0) {
                            this.newCustomer.first_name = this.customerQuery;
                            this.newCustomer.display_name = this.customerQuery;
                        }

                        this.showCreateCustomerModal = true;
                    },

                    editCustomerDetails() {
                        if (!this.selectedCustomer) return;

                        this.newCustomer = {
                            id: this.selectedCustomer.id,
                            display_name: this.selectedCustomer.display_name,
                            first_name: this.selectedCustomer.first_name,
                            last_name: this.selectedCustomer.last_name,
                            mobile: this.selectedCustomer.mobile,
                            phone_number_2: this.selectedCustomer.phone_number_2,
                            relative_phone: this.selectedCustomer.relative_phone,
                            email: this.selectedCustomer.email,
                            source: this.selectedCustomer.source,
                            type: this.selectedCustomer.type,
                            category: this.selectedCustomer.category || 'individual',
                            company_name: this.selectedCustomer.company_name,
                            gst_number: this.selectedCustomer.gst_number,
                            pan_number: this.selectedCustomer.pan_number,
                            land_area: this.selectedCustomer.land_area,
                            land_unit: this.selectedCustomer.land_unit || 'acre',
                            irrigation_type: this.selectedCustomer.irrigation_type,
                            crops: this.selectedCustomer.crops || [],
                            credit_limit: this.selectedCustomer.credit_limit,
                            credit_valid_till: this.selectedCustomer.credit_valid_till,
                            aadhaar_last4: this.selectedCustomer.aadhaar_last4,
                            kyc_completed: !!this.selectedCustomer.kyc_completed,
                            internal_notes: this.selectedCustomer.internal_notes,
                            is_active: this.selectedCustomer.is_active !== 0,
                            is_blacklisted: this.selectedCustomer.is_blacklisted === 1
                        };

                        this.showCreateCustomerModal = true;
                    },

                    get subTotal() {
                        return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                    },

                    get cartDiscountTotal() {
                        return this.cart.reduce((sum, item) => {
                            let discount = 0;
                            if (item.discount_type === 'percent') {
                                discount = (item.price * item.quantity) * (item.discount_value / 100);
                            } else {
                                discount = item.discount_value;
                            }
                            return sum + parseFloat(discount || 0);
                        }, 0);
                    },

                    get orderDiscountAmount() {
                        let totalAfterCartDiscounts = this.subTotal - this.cartDiscountTotal;
                        if (this.order.discount_type === 'percent') {
                            return totalAfterCartDiscounts * (this.order.discount_value / 100);
                        }
                        return parseFloat(this.order.discount_value || 0);
                    },

                    get grandTotal() {
                        return (this.subTotal - this.cartDiscountTotal - this.orderDiscountAmount);
                    }
                }
            }
        </script>


    </div>
</x-app-layout>