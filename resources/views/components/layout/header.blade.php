<header
    class="sticky top-0 z-40 flex h-20 w-full items-center justify-between border-b border-zinc-200/50 dark:border-white/10 bg-white/70 dark:bg-zinc-950/80 px-6 backdrop-blur-3xl transition-all duration-500 ease-in-out shadow-[0_4px_30px_rgba(0,0,0,0.03)] group/header">

    <!-- Premium Ambient Glow -->
    <div class="absolute inset-0 z-[-1] overflow-hidden pointer-events-none">
        <div
            class="absolute top-0 left-1/4 w-[500px] h-full bg-primary/5 blur-[80px] opacity-50 transform -translate-y-1/2 rounded-full transition-opacity duration-700 group-hover/header:opacity-80">
        </div>
        <div
            class="absolute top-0 right-1/4 w-[400px] h-full bg-purple-500/5 blur-[100px] opacity-20 transform -translate-y-1/2 rounded-full transition-opacity duration-700 group-hover/header:opacity-40">
        </div>
    </div>

    <!-- Left Side: Nav & Branding -->
    <div class="flex items-center gap-6">
        <button
            class="group flex items-center justify-center rounded-2xl p-2.5 text-muted-foreground hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-foreground hover:shadow-inner transition-all duration-300 active:scale-95 focus-visible:outline-none focus:ring-2 focus:ring-primary/20 backdrop-blur-md border border-transparent hover:border-zinc-200 dark:hover:border-white/20"
            @click="toggleSidebar()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                class="size-5 transition-transform duration-500 group-hover:rotate-180">
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
                    {{ tenant() ? ucfirst(tenant('id')) : 'Home' }}
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
            <div x-data="headerCustomerSearch({
                        searchUrl: '{{ tenant() ? route('tenant.api.search.customers') : route('central.api.search.customers') }}',
                        storeUrl: '{{ tenant() ? route('tenant.api.customers.store-quick') : route('central.api.customers.store-quick') }}',
                        orderUrl: '{{ tenant() ? route('tenant.orders.create') : route('central.orders.create') }}'
                    })">
                <!-- Search Trigger Button -->
                <button @click="openSearchModal()"
                    class="group flex items-center justify-center rounded-2xl p-2.5 text-muted-foreground hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-foreground hover:shadow-inner transition-all duration-300 active:scale-95 focus-visible:outline-none focus:ring-2 focus:ring-primary/20 backdrop-blur-md border border-transparent hover:border-zinc-200 dark:hover:border-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="size-5">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <span class="sr-only">Search</span>
                </button>

                <!-- Search Modal (Teleported) -->
                <template x-teleport="body">
                    <div x-show="searchOpen" x-transition.opacity.duration.300ms @keydown.escape.window="searchOpen = false"
                        class="fixed inset-0 z-[9999] flex items-start justify-center pt-20 bg-zinc-950/80 backdrop-blur-sm p-4"
                        style="display: none;">

                        <div class="bg-white dark:bg-zinc-900 w-full max-w-2xl rounded-[32px] shadow-2xl border border-white/10 overflow-hidden flex flex-col max-h-[80vh] animate-in slide-in-from-top-4 fade-in duration-300"
                            @click.away="searchOpen = false">

                            <!-- Search Input Header -->
                            <div class="p-4 border-b border-border/50 relative">
                                <div class="relative flex items-center">
                                    <div class="absolute left-4 text-muted-foreground">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="size-5">
                                            <circle cx="11" cy="11" r="8" />
                                            <path d="m21 21-4.3-4.3" />
                                        </svg>
                                    </div>
                                    <input type="text" x-model="customerQuery" @input.debounce.300ms="searchCustomers()"
                                        x-ref="searchInput" placeholder="Scan Registry: Enter name, mobile, or code..."
                                        class="flex h-12 w-full rounded-2xl bg-secondary/50 dark:bg-zinc-800/50 pl-12 pr-12 text-base font-medium focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all placeholder:text-muted-foreground/50" />
                                    <button @click="searchOpen = false"
                                        class="absolute right-3 p-1 rounded-full hover:bg-black/5 dark:hover:bg-white/10 text-muted-foreground transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="size-5">
                                            <path d="M18 6 6 18" />
                                            <path d="m6 6 12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Results Area -->
                            <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar min-h-[200px]">
                                <!-- Active Results Header -->
                                <div x-show="customerResults.length > 0"
                                    class="px-4 py-2 flex items-center justify-between text-xs font-bold text-muted-foreground uppercase tracking-wider">
                                    <span>Results</span>
                                    <span x-text="customerResults.length + ' Matches'"></span>
                                </div>

                                <!-- Loading State -->
                                <template x-if="loading">
                                    <div class="flex flex-col items-center justify-center py-12 space-y-4">
                                        <div
                                            class="size-8 border-4 border-primary/30 border-t-primary rounded-full animate-spin">
                                        </div>
                                        <span
                                            class="text-xs font-bold text-muted-foreground animate-pulse">Searching...</span>
                                    </div>
                                </template>

                                <!-- Results List -->
                                <template x-for="cust in customerResults" :key="cust.id">
                                    <div @click="selectCustomer(cust)"
                                        class="group flex items-center gap-4 p-3 rounded-2xl hover:bg-secondary/50 dark:hover:bg-white/5 cursor-pointer transition-all duration-200 border border-transparent hover:border-black/5 dark:hover:border-white/10">
                                        <div
                                            class="size-12 rounded-xl bg-gradient-to-br from-primary to-purple-600 text-white flex items-center justify-center font-bold text-lg shadow-lg">
                                            <span x-text="cust.first_name.charAt(0)"></span>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <h4 class="font-bold text-foreground group-hover:text-primary transition-colors"
                                                    x-text="cust.first_name + ' ' + (cust.last_name || '')"></h4>
                                                <span
                                                    class="text-xs font-mono font-bold bg-primary/10 text-primary px-1.5 py-0.5 rounded"
                                                    x-text="cust.customer_code"></span>
                                            </div>
                                            <div
                                                class="flex items-center gap-3 mt-1 text-xs text-muted-foreground font-medium">
                                                <span class="flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path
                                                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                                    </svg>
                                                    <span x-text="cust.mobile"></span>
                                                </span>
                                                <span class="w-1 h-1 rounded-full bg-border"></span>
                                                <span class="uppercase" x-text="cust.type"></span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] font-bold uppercase text-muted-foreground/70">Balance</p>
                                            <p class="font-mono font-bold text-sm"
                                                :class="cust.outstanding_balance > 0 ? 'text-rose-500' : 'text-emerald-500'"
                                                x-text="'₹' + parseFloat(cust.outstanding_balance || 0).toLocaleString()">
                                            </p>
                                        </div>
                                    </div>
                                </template>

                                <!-- No Results / Register Prompt -->
                                <template x-if="!loading && customerResults.length === 0 && customerQuery.length >= 2">
                                    <div class="text-center py-10 px-4">
                                        <div
                                            class="size-16 rounded-3xl bg-secondary/50 flex items-center justify-center mx-auto mb-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="size-8 text-muted-foreground">
                                                <circle cx="12" cy="12" r="10" />
                                                <line x1="12" y1="8" x2="12" y2="12" />
                                                <line x1="12" y1="16" x2="12.01" y2="16" />
                                            </svg>
                                        </div>
                                        <h3 class="font-bold text-foreground">No Customer Found</h3>
                                        <p class="text-xs text-muted-foreground mt-1 mb-6">Search term didn't match any
                                            records.</p>
                                        <button @click="openCreateCustomerModal()"
                                            class="w-full py-3 rounded-xl bg-primary text-primary-foreground font-bold text-sm shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                                            Register New Customer
                                        </button>
                                    </div>
                                </template>

                                <!-- Initial Prompt -->
                                <div x-show="!loading && customerResults.length === 0 && customerQuery.length < 2"
                                    class="text-center py-12 text-muted-foreground">
                                    <p class="text-sm font-medium">Type to search customers...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

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
                                <div class="grid grid-cols-3 gap-4">
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
                                            class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em] group-focus-within/field:text-primary transition-colors">Middle
                                            Name</label>
                                        <input type="text" x-model="newCustomer.middle_name"
                                            class="w-full h-12 bg-secondary/30 dark:bg-white/5 border border-white/10 rounded-2xl px-5 text-sm font-bold placeholder:text-muted-foreground/30 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all font-sans"
                                            placeholder="" />
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
                    function headerCustomerSearch(routes) {
                        return {
                            customerQuery: '',
                            customerResults: [],
                            searchOpen: false, // New state for search modal
                            loading: false,
                            showModal: false,
                            saving: false,
                            error: '',
                            newCustomer: { first_name: '', middle_name: '', last_name: '', mobile: '', email: '', type: 'farmer' },

                            openSearchModal() {
                                this.searchOpen = true;
                                this.$nextTick(() => {
                                    this.$refs.searchInput.focus();
                                });
                            },

                            async searchCustomers() {
                                if (this.customerQuery.length < 2) {
                                    this.customerResults = [];
                                    return;
                                }
                                this.loading = true;
                                try {
                                    const url = `${routes.searchUrl}?q=${this.customerQuery}`;
                                    let res = await fetch(url);
                                    if (!res.ok) throw new Error('Search failed');
                                    this.customerResults = await res.json();
                                } catch (e) { console.error(e); }
                                finally { this.loading = false; }
                            },

                            selectCustomer(cust) {
                                window.location.href = `${routes.orderUrl}?customer_id=${cust.id}&reset=1`;
                            },

                            openCreateCustomerModal() {
                                this.searchOpen = false; // Close search modal
                                this.showModal = true;
                                this.error = '';
                                this.newCustomer = {
                                    first_name: '',
                                    middle_name: '',
                                    last_name: '',
                                    mobile: '',
                                    email: '',
                                    type: 'farmer'
                                };
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

                                // Auto-generate display_name
                                const f = this.newCustomer.first_name.trim();
                                const m = this.newCustomer.middle_name ? this.newCustomer.middle_name.trim() : '';
                                const l = this.newCustomer.last_name ? this.newCustomer.last_name.trim() : '';
                                const payload = {
                                    ...this.newCustomer,
                                    display_name: [f, m, l].filter(Boolean).join(' ')
                                };

                                try {
                                    const url = routes.storeUrl;
                                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                    if (!csrfToken) {
                                        this.error = 'Security token missing. Refresh page.';
                                        this.saving = false;
                                        return;
                                    }

                                    let res = await fetch(url, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': csrfToken
                                        },
                                        body: JSON.stringify(payload)
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

            <!-- Premium Notifications -->
            <div class="relative" x-data="{
                open: false,
                notifications: [],
                unreadCount: 0,
                loading: false,
                async fetchNotifications() {
                    this.loading = true;
                    try {
                        const url = `{{ tenant() ? route('tenant.notifications.index') : route('central.notifications.index') }}`;
                        let res = await fetch(url);
                        if (res.ok) {
                            this.notifications = await res.json();
                            this.unreadCount = this.notifications.filter(n => !n.read_at).length;
                        }
                    } catch (e) { console.error(e); }
                    finally { this.loading = false; }
                },
                async markAllRead() {
                    try {
                        const url = `{{ tenant() ? route('tenant.notifications.read-all') : route('central.notifications.read-all') }}`;
                        await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                            }
                        });
                        this.notifications.forEach(n => n.read_at = new Date().toISOString());
                        this.unreadCount = 0;
                    } catch (e) { console.error(e); }
                }
            }" x-init="fetchNotifications()" @click.away="open = false" @keydown.escape.window="open = false">

                <button @click="open = !open"
                    class="group relative inline-flex items-center justify-center rounded-xl size-10 text-muted-foreground hover:bg-white/50 dark:hover:bg-white/10 hover:text-primary transition-all duration-300 active:scale-90">
                    <span x-show="unreadCount > 0" x-transition
                        class="absolute -top-1 -right-1 min-w-[20px] h-5 flex items-center justify-center px-1 rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-[0_0_8px_rgba(244,63,94,0.6)] animate-pulse border-2 border-white dark:border-zinc-950"
                        x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        class="size-5 relative z-10 transition-all group-hover:rotate-[15deg] group-hover:scale-110 active:scale-95">
                        <path d="M6 8a6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                    </svg>
                </button>

                <!-- Dropdown Panel -->
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    class="absolute right-0 mt-3 w-80 sm:w-96 md:w-[450px] rounded-3xl border border-white/20 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-2xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.3)] z-50 overflow-hidden ring-1 ring-black/5">

                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-border/50 bg-white/5 dark:bg-white/2">
                        <h3 class="text-xs font-black uppercase tracking-widest">Feed</h3>
                        <button @click="markAllRead()" x-show="unreadCount > 0"
                            class="text-[10px] font-bold text-primary hover:text-primary/70 underline underline-offset-4 decoration-primary/30 transition-all">
                            Mark All Read
                        </button>
                    </div>

                    <div class="max-h-[70vh] overflow-y-auto p-2 space-y-1 no-scrollbar">
                        <template x-if="notifications.length === 0">
                            <div
                                class="flex flex-col items-center justify-center py-10 text-center space-y-4 opacity-50 italic">
                                <div
                                    class="size-16 rounded-full bg-muted/50 flex items-center justify-center shadow-inner">
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
                        </template>

                        <template x-for="note in notifications" :key="note.id">
                            <div class="group relative flex gap-4 p-4 rounded-2xl hover:bg-secondary/20 dark:hover:bg-white/5 transition-all duration-300 border border-transparent hover:border-black/5 dark:hover:border-white/5 select-text"
                                :class="{ 'bg-primary/5': !note.read_at }">

                                <div class="shrink-0 pt-1"
                                    x-html="note.data.icon || '<svg class=\'size-5 text-primary\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\' /></svg>'">
                                </div>

                                <div class="flex-1 space-y-1.5">
                                    <div class="flex items-start justify-between gap-4">
                                        <h4 class="text-sm font-bold text-foreground leading-tight"
                                            x-text="note.data.title"></h4>
                                        <span
                                            class="text-[10px] font-medium text-muted-foreground whitespace-nowrap shrink-0"
                                            x-text="note.created_at"></span>
                                    </div>
                                    <p class="text-xs text-muted-foreground leading-relaxed font-medium"
                                        x-text="note.data.message"></p>
                                </div>

                                <div x-show="!note.read_at"
                                    class="absolute top-4 right-2 size-2 rounded-full bg-rose-500 shadow-sm ring-2 ring-white dark:ring-zinc-900">
                                </div>
                            </div>
                        </template>
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