<header
    class="sticky top-0 z-40 flex h-20 w-full items-center justify-between border-b border-white/10 bg-white/70 dark:bg-zinc-950/70 px-6 backdrop-blur-2xl transition-all duration-500 ease-in-out shadow-[0_4px_30px_rgba(0,0,0,0.03)] group/header">

    <!-- Premium Ambient Glow -->
    <div class="absolute inset-0 z-[-1] overflow-hidden pointer-events-none">
        <div
            class="absolute top-0 left-1/4 w-[500px] h-full bg-primary/5 blur-[80px] opacity-50 transform -translate-y-1/2 rounded-full transition-opacity duration-700 group-hover/header:opacity-80">
        </div>
        <div
            class="absolute top-0 right-1/4 w-[400px] h-full bg-purple-500/5 blur-[100px] opacity-30 transform -translate-y-1/2 rounded-full transition-opacity duration-700 group-hover/header:opacity-60">
        </div>
    </div>

    <!-- Left Side: Nav & Branding -->
    <div class="flex items-center gap-6">
        <button
            class="group flex items-center justify-center rounded-2xl p-2.5 text-muted-foreground hover:bg-white/10 dark:hover:bg-white/5 hover:text-foreground hover:shadow-inner transition-all duration-300 active:scale-90 focus-visible:outline-none focus:ring-2 focus:ring-primary/20 backdrop-blur-md border border-transparent hover:border-white/20"
            @click="toggleSidebar()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                class="size-5 transition-transform duration-300 group-hover:rotate-180">
                <path d="M4 6h16M4 12h16M14 18h6" />
            </svg>
            <span class="sr-only">Toggle Sidebar</span>
        </button>

        <div class="h-8 w-px bg-gradient-to-b from-transparent via-border/50 to-transparent"></div>

        <!-- Premium Breadcrumbs -->
        <nav class="hidden md:flex items-center gap-2">
            <div
                class="flex items-center p-1 bg-secondary/20 dark:bg-white/5 rounded-xl border border-white/10 backdrop-blur-md shadow-sm">
                <a href="#"
                    class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider text-muted-foreground hover:text-primary hover:bg-white/50 dark:hover:bg-white/10 transition-all">
                    <div
                        class="size-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary-rgb),0.5)]">
                    </div>
                    {{ tenant() ? ucfirst(tenant('id')) : 'Master Platform' }}
                </a>

                <div class="px-1 opacity-20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="size-4">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </div>

                @can('dashboard view')
                    <a href="/dashboard"
                        class="group flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all {{ request()->is('dashboard') ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/20' : 'text-muted-foreground hover:text-foreground hover:bg-white/50 dark:hover:bg-white/10' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="size-3.5 transition-transform group-hover:scale-110">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                        Dashboard
                    </a>
                @endcan
            </div>
        </nav>
    </div>

    <!-- Right Side: Search & User Actions -->
    <div class="flex items-center gap-3 md:gap-6">

        <!-- Premium Customer Search "Command Center" style -->
        @can('customers view')
            <div class="hidden lg:block lg:flex-1 lg:max-w-3xl relative" x-data="headerCustomerSearch()">
                <div class="relative group/search">
                    <div
                        class="absolute -inset-1 bg-gradient-to-r from-primary/30 via-purple-500/30 to-primary/30 rounded-[24px] blur-md opacity-0 group-focus-within/search:opacity-100 transition-all duration-700">
                    </div>
                    <div class="relative flex flex-col">
                        <div class="relative flex items-center">
                            <div
                                class="absolute left-5 pointer-events-none text-muted-foreground group-focus-within/search:text-primary transition-colors duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="size-5">
                                    <circle cx="11" cy="11" r="8" />
                                    <path d="m21 21-4.3-4.3" />
                                </svg>
                            </div>
                            <input type="text" x-model="customerQuery" @input.debounce.300ms="searchCustomers()"
                                @focus="open = true" placeholder="Scan Registry: Enter name, mobile, or code..."
                                class="flex h-14 w-full rounded-[20px] border border-white/20 dark:border-white/10 bg-white/40 dark:bg-zinc-900/40 pl-14 pr-20 text-base font-medium shadow-sm ring-1 ring-black/5 backdrop-blur-3xl transition-all placeholder:text-muted-foreground/40 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white dark:focus:bg-zinc-900 focus:shadow-2xl" />
                            <div class="absolute right-4 flex items-center gap-2">
                                <div
                                    class="hidden md:flex items-center gap-1.5 px-2.5 py-1.5 bg-white/50 dark:bg-white/5 border border-white/20 rounded-xl text-[10px] font-black text-muted-foreground backdrop-blur-md">
                                    <span class="text-xs opacity-50">⌘</span>
                                    <span>K</span>
                                </div>
                            </div>
                        </div>

                        <!-- Search Results Dropdown -->
                        <div x-show="open && (customerQuery.length >= 2 || customerResults.length > 0)"
                            @click.away="open = false" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            class="absolute top-[calc(100%+16px)] left-0 right-0 bg-white/95 dark:bg-zinc-950/95 border border-white/20 dark:border-white/10 rounded-[32px] shadow-[0_30px_80px_-20px_rgba(0,0,0,0.5)] backdrop-blur-3xl z-50 overflow-hidden max-h-[600px] flex flex-col"
                            style="display: none;">

                            <div
                                class="px-6 py-5 border-b border-white/5 flex items-center justify-between bg-white/5 dark:bg-white/2">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex size-2 rounded-full bg-primary animate-pulse shadow-[0_0_10px_rgba(var(--primary-rgb),0.5)]"></span>
                                    <h4 class="text-xs font-black uppercase tracking-[0.2em] text-muted-foreground/80">
                                        Active Results</h4>
                                </div>
                                <div
                                    class="flex items-center gap-2 px-3 py-1 rounded-full bg-secondary/50 dark:bg-white/5 border border-white/10">
                                    <span class="text-[10px] text-muted-foreground font-bold"
                                        x-text="customerResults.length + ' Matches'"></span>
                                </div>
                            </div>

                            <div class="flex-1 overflow-y-auto p-4 space-y-2 no-scrollbar">
                                <template x-if="loading">
                                    <div class="flex flex-col items-center justify-center py-20 space-y-5">
                                        <div class="relative size-12">
                                            <div class="absolute inset-0 border-4 border-primary/20 rounded-full"></div>
                                            <div
                                                class="absolute inset-0 border-4 border-primary border-t-transparent rounded-full animate-spin">
                                            </div>
                                        </div>
                                        <span
                                            class="text-xs font-black uppercase tracking-[0.3em] text-muted-foreground animate-pulse">Filtering
                                            Ledger</span>
                                    </div>
                                </template>

                                <template x-for="cust in customerResults" :key="cust.id">
                                    <div @click="selectCustomer(cust)"
                                        class="group relative flex items-center justify-between p-5 rounded-[24px] hover:bg-white/50 dark:hover:bg-white/5 transition-all duration-300 cursor-pointer overflow-hidden border border-transparent hover:border-white/20 hover:shadow-xl group-hover:scale-[1.01]">
                                        <div
                                            class="flex items-center gap-5 relative z-10 transition-transform group-hover:translate-x-1">
                                            <div
                                                class="size-14 rounded-2xl bg-gradient-to-br from-primary via-primary/90 to-purple-600 text-white flex items-center justify-center font-black text-xl shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 ring-2 ring-white/10">
                                                <span x-text="cust.first_name.charAt(0)"></span><span
                                                    x-text="(cust.last_name || '').charAt(0)"></span>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-3">
                                                    <p class="font-black text-base text-foreground group-hover:text-primary transition-colors"
                                                        x-text="cust.first_name + ' ' + (cust.last_name || '')"></p>
                                                    <span
                                                        class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest border border-primary/10"
                                                        x-text="cust.customer_code"></span>
                                                </div>
                                                <div class="flex items-center gap-4 mt-1.5 opacity-70">
                                                    <p
                                                        class="text-xs text-muted-foreground font-bold flex items-center gap-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2.5" stroke-linecap="round"
                                                            stroke-linejoin="round" class="size-3">
                                                            <path
                                                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                                        </svg>
                                                        <span x-text="cust.mobile"></span>
                                                    </p>
                                                    <div class="size-1 rounded-full bg-muted-foreground/30"></div>
                                                    <p class="text-xs text-muted-foreground font-bold uppercase tracking-tighter"
                                                        x-text="cust.type"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-8 relative z-10 transition-all">
                                            <div class="text-right flex flex-col items-end">
                                                <p
                                                    class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1.5">
                                                    Outstanding</p>
                                                <p class="font-mono text-sm font-black px-3 py-1 rounded-xl border border-white/10 group-hover:border-primary/20 group-hover:bg-primary/5 transition-all shadow-inner"
                                                    :class="cust.outstanding_balance > 0 ? 'text-rose-500 bg-rose-500/5' : 'text-emerald-500 bg-emerald-500/5'">
                                                    Rs <span
                                                        x-text="parseFloat(cust.outstanding_balance || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                                                </p>
                                            </div>
                                            <div
                                                class="size-10 rounded-full bg-primary text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-500 transform translate-x-8 group-hover:translate-x-0 shadow-lg shadow-primary/30">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                                    stroke-linecap="round" stroke-linejoin="round" class="size-5">
                                                    <path d="M5 12h14" />
                                                    <path d="m12 5 7 7-7 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!loading && customerResults.length === 0 && customerQuery.length >= 2">
                                    <div class="text-center py-16 px-6">
                                        <div
                                            class="size-20 bg-muted/30 rounded-[32px] flex items-center justify-center mx-auto mb-6 shadow-inner ring-1 ring-white/10">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="size-10 text-muted-foreground/40">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                                <circle cx="9" cy="7" r="4" />
                                                <circle cx="19" cy="11" r="3" />
                                                <path d="M19 8v6" />
                                                <path d="M22 11h-6" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-foreground italic">Anonymous Customer?</h3>
                                        <p
                                            class="text-sm text-muted-foreground mt-2 mb-8 leading-relaxed max-w-[240px] mx-auto">
                                            This phantom digit doesn't haunt our halls yet. Shall we add them?</p>
                                        <button @click="openCreateCustomerModal()"
                                            class="w-full inline-flex items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-primary to-purple-600 px-6 py-4 text-xs font-black uppercase tracking-[0.1em] text-white shadow-[0_10px_30px_-5px_rgba(var(--primary-rgb),0.3)] hover:shadow-[0_15px_40px_-5px_rgba(var(--primary-rgb),0.5)] hover:-translate-y-1 active:scale-[0.98] transition-all duration-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                                stroke-linecap="round" stroke-linejoin="round" class="size-4">
                                                <path d="M5 12h14" />
                                                <path d="M12 5v14" />
                                            </svg>
                                            Register Record
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Premeum Registration Modal (Teleported to Body to avoid Header Clipping) -->
                <template x-teleport="body">
                    <div x-show="showModal" x-transition.opacity.duration.400ms
                        class="fixed inset-0 z-[9999] flex items-center justify-center bg-zinc-950/80 backdrop-blur-md p-4"
                        style="display: none;">
                        <div class="bg-white dark:bg-zinc-900 w-full max-w-lg rounded-[40px] shadow-[0_0_100px_rgba(0,0,0,0.5)] border border-white/10 overflow-hidden flex flex-col max-h-[90vh] animate-in zoom-in-95 fade-in duration-500 ease-out"
                            @click.away="showModal = false">
                            <div class="px-10 pt-10 pb-6 relative overflow-hidden">
                                <div class="absolute -top-20 -right-20 size-60 bg-primary/20 blur-[80px] rounded-full">
                                </div>
                                <div class="relative z-10">
                                    <h3 class="font-black text-3xl tracking-tighter">Fast Enrollment</h3>
                                    <p class="text-sm text-muted-foreground font-medium mt-1">Creating a new identity in the
                                        current ledger.</p>
                                </div>
                                <button @click="showModal = false"
                                    class="absolute top-8 right-8 text-muted-foreground hover:text-foreground p-2 hover:bg-white/10 rounded-full transition-all duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="size-6">
                                        <path d="M18 6 6 18" />
                                        <path d="m6 6 12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="px-10 pb-10 overflow-y-auto space-y-8 custom-scrollbar">
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="space-y-2 group/field">
                                        <label
                                            class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em] group-focus-within/field:text-primary transition-colors">First
                                            Name</label>
                                        <input type="text" x-model="newCustomer.first_name"
                                            class="w-full h-12 bg-secondary/30 dark:bg-white/5 border border-white/10 rounded-2xl px-5 text-sm font-bold placeholder:text-muted-foreground/30 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all font-sans"
                                            placeholder="Rahul" />
                                    </div>
                                    <div class="space-y-2 group/field">
                                        <label
                                            class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em] group-focus-within/field:text-primary transition-colors">Last
                                            Name</label>
                                        <input type="text" x-model="newCustomer.last_name"
                                            class="w-full h-12 bg-secondary/30 dark:bg-white/5 border border-white/10 rounded-2xl px-5 text-sm font-bold placeholder:text-muted-foreground/30 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all font-sans"
                                            placeholder="Sharma" />
                                    </div>
                                </div>

                                <div class="space-y-2 group/field">
                                    <label
                                        class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em] group-focus-within/field:text-primary transition-colors">Mobile
                                        Identity (Verify)</label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-5 top-1/2 -translate-y-1/2 text-primary font-black text-sm">+91</span>
                                        <input type="text" x-model="newCustomer.mobile"
                                            class="w-full h-14 bg-secondary/30 dark:bg-white/5 border border-white/10 rounded-[20px] pl-16 pr-5 text-lg font-black tracking-widest focus:outline-none focus:ring-4 focus:ring-primary/10 focus:bg-white dark:focus:bg-zinc-800 transition-all font-sans" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div class="space-y-2 group/field">
                                        <label
                                            class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em]">Contact
                                            Email</label>
                                        <input type="email" x-model="newCustomer.email"
                                            class="w-full h-12 bg-secondary/30 dark:bg-white/5 border border-white/10 rounded-2xl px-5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all font-sans"
                                            placeholder="name@domain.com" />
                                    </div>
                                    <div class="space-y-2 group/field">
                                        <label
                                            class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em]">Classification</label>
                                        <select x-model="newCustomer.type"
                                            class="w-full h-12 bg-secondary/30 dark:bg-white/5 border border-white/10 rounded-2xl px-5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all font-sans">
                                            <option value="farmer">🏢 Farmer / Individual</option>
                                            <option value="buyer">💼 Corporate Buyer</option>
                                            <option value="dealer">📦 Retail Dealer</option>
                                            <option value="vendor">🚚 Service Vendor</option>
                                        </select>
                                    </div>
                                </div>

                                <div x-show="error" x-transition
                                    class="p-5 rounded-3xl bg-rose-500/10 border border-rose-500/20 text-rose-500 text-xs font-bold leading-relaxed shadow-inner"
                                    x-text="error"></div>
                            </div>

                            <div class="px-10 py-8 border-t border-border/50 bg-muted/20 flex justify-end gap-4">
                                <button @click="showModal = false"
                                    class="px-6 py-3 text-xs font-black uppercase tracking-widest text-muted-foreground hover:text-foreground transition-all">Discard</button>
                                <button @click="createCustomer()" :disabled="saving"
                                    class="inline-flex items-center justify-center gap-3 rounded-[22px] bg-gradient-to-r from-primary to-purple-600 px-8 py-4 text-xs font-black uppercase tracking-widest text-white shadow-2xl hover:-translate-y-1 active:scale-95 transition-all duration-500 disabled:opacity-50">
                                    <span x-text="saving ? 'AUTHENTICATING...' : 'ENROLL & CONTINUE'"></span>
                                    <svg x-show="!saving" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                        stroke-linecap="round" stroke-linejoin="round" class="size-4">
                                        <path d="m9 18 6-6-6-6" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <script>
                    function headerCustomerSearch() {
                        return {
                            customerQuery: '',
                            customerResults: [],
                            open: false,
                            loading: false,
                            showModal: false,
                            saving: false,
                            error: '',
                            newCustomer: { first_name: '', last_name: '', mobile: '', email: '', type: 'farmer' },

                            async searchCustomers() {
                                if (this.customerQuery.length < 2) {
                                    this.customerResults = [];
                                    return;
                                }
                                this.loading = true;
                                try {
                                    const url = `{{ tenant() ? route('tenant.api.search.customers') : route('central.api.search.customers') }}?q=${this.customerQuery}`;
                                    let res = await fetch(url);
                                    if (!res.ok) throw new Error('Search failed');
                                    this.customerResults = await res.json();
                                    this.open = true;
                                } catch (e) { console.error(e); }
                                finally { this.loading = false; }
                            },

                            selectCustomer(cust) {
                                const baseUrl = `{{ tenant() ? route('tenant.orders.create') : route('central.orders.create') }}`;
                                window.location.href = `${baseUrl}?customer_id=${cust.id}&reset=1`;
                            },

                            openCreateCustomerModal() {
                                this.open = false;
                                this.showModal = true;
                                this.error = '';
                                this.newCustomer = { first_name: '', last_name: '', mobile: '', email: '', type: 'farmer' };
                                if (/^\d+$/.test(this.customerQuery)) {
                                    this.newCustomer.mobile = this.customerQuery;
                                } else {
                                    this.newCustomer.first_name = this.customerQuery;
                                }
                            },

                            async createCustomer() {
                                if (!this.newCustomer.first_name || !this.newCustomer.mobile) {
                                    this.error = 'Legal name and mobile hash required.';
                                    return;
                                }
                                this.saving = true;
                                this.error = '';
                                try {
                                    const url = `{{ tenant() ? route('tenant.api.customers.store-quick') : route('central.api.customers.store-quick') }}`;
                                    let res = await fetch(url, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                        },
                                        body: JSON.stringify(this.newCustomer)
                                    });
                                    let data = await res.json();
                                    if (data.success) {
                                        this.selectCustomer(data.customer);
                                    } else {
                                        this.error = data.message || 'System rejection. Contact core admin.';
                                    }
                                } catch (e) {
                                    console.error(e);
                                    this.error = 'Network failure in ledger sync.';
                                } finally {
                                    this.saving = false;
                                }
                            }
                        }
                    }
                </script>
            </div>
        @endcan

        <!-- Premium Action Group -->
        <div
            class="flex items-center gap-1.5 p-1 bg-secondary/20 dark:bg-white/5 border border-white/10 rounded-2xl shadow-inner backdrop-blur-md">
            <!-- Theme Toggle -->
            <x-layout.theme-toggle />

            <!-- Premeum Notifications -->
            <div class="relative" x-data="{ open: false }" @click.away="open = false"
                @keydown.escape.window="open = false">
                <button @click="open = !open"
                    class="group relative inline-flex items-center justify-center rounded-xl size-10 text-muted-foreground hover:bg-white/50 dark:hover:bg-white/10 hover:text-primary transition-all duration-300 active:scale-90">
                    <span
                        class="absolute top-2 right-2 size-2 rounded-full bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.6)] animate-pulse"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        class="size-5 relative z-10 transition-all group-hover:rotate-[15deg] group-active:scale-110">
                        <path d="M6 8a6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                    </svg>
                </button>

                <!-- Dropdown Panel -->
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    class="absolute right-0 mt-3 w-80 sm:w-96 rounded-3xl border border-white/20 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-2xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.3)] z-50 overflow-hidden ring-1 ring-black/5">
                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-border/50 bg-white/5 dark:bg-white/2">
                        <h3 class="text-xs font-black uppercase tracking-widest">Feed</h3>
                        <button
                            class="text-[10px] font-bold text-primary hover:text-primary/70 underline underline-offset-4 decoration-primary/30 transition-all">Clear
                            All</button>
                    </div>

                    <div class="max-h-[70vh] overflow-y-auto p-4 space-y-2 no-scrollbar">
                        <div
                            class="flex flex-col items-center justify-center py-10 text-center space-y-4 opacity-50 italic">
                            <div class="size-16 rounded-full bg-muted/50 flex items-center justify-center shadow-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"
                                    stroke-linejoin="round" class="size-8 text-muted-foreground/50">
                                    <path d="M6 8a6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold uppercase tracking-widest">Digital Silence</p>
                                <p class="text-[10px] opacity-70">The system ledger is currently quiet.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Premeum Settings -->
            @can('settings view')
                <a href="/settings"
                    class="group relative inline-flex items-center justify-center rounded-xl size-10 text-muted-foreground hover:bg-white/50 dark:hover:bg-white/10 hover:text-primary transition-all duration-300 active:scale-90 {{ request()->is('settings*') ? 'text-primary bg-primary/10' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        class="size-5 relative z-10 transition-transform duration-700 group-hover:rotate-180">
                        <path
                            d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.72V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.17a2 2 0 0 1 1-1.74l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </a>
            @endcan
        </div>

        <div class="h-8 w-px bg-gradient-to-b from-transparent via-border/50 to-transparent mx-2"></div>

        <x-layout.user-dropdown />
    </div>
</header>