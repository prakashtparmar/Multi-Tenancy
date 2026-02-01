<aside 
    class="fixed inset-y-0 left-0 z-50 flex flex-col border-r border-sidebar-border/50 bg-sidebar/95 backdrop-blur-2xl transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)] md:translate-x-0 group/sidebar shadow-2xl shadow-primary/5 dark:shadow-black/50"
    style="position: fixed;"
    :class="{
        'w-72': !sidebarCollapsed,
        'w-[4.5rem]': sidebarCollapsed,
        '-translate-x-full': !mobileMenuOpen && window.innerWidth < 768,
        'translate-x-0': mobileMenuOpen && window.innerWidth < 768
    }"
    @click.away="mobileMenuOpen = false"
>
    <!-- Logo Area -->
    <div class="h-20 flex items-center px-5 border-b border-sidebar-border/50 relative overflow-hidden shrink-0 group/logo">
        <div class="absolute inset-0 bg-gradient-to-r from-primary/10 via-primary/5 to-transparent opacity-0 group-hover/logo:opacity-100 transition-opacity duration-700"></div>
        
        <a href="{{ url('/dashboard') }}" class="flex items-center gap-3.5 relative z-10 w-full" :class="sidebarCollapsed ? 'justify-center' : ''">
            <div class="h-10 w-10 min-w-10 rounded-xl bg-gradient-to-br from-primary to-indigo-600 flex items-center justify-center shadow-lg shadow-primary/25 shrink-0 transition-all duration-500 group-hover/logo:scale-105 group-hover/logo:rotate-3 ring-1 ring-white/10">
                <!-- Premium Logo Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-white drop-shadow-md"><path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3"/></svg>
            </div>
            <div class="flex flex-col overflow-hidden transition-all duration-500 ease-out" 
                 :class="sidebarCollapsed ? 'w-0 opacity-0 absolute translate-x-10' : 'w-auto opacity-100 translate-x-0'">
                <span class="font-heading font-bold text-lg tracking-tight leading-none text-foreground group-hover/logo:text-primary transition-colors duration-300 whitespace-nowrap">
                    {{ tenant('id') ? ucfirst(tenant('id')) : 'Central Admin' }}
                </span>
                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.25em] mt-1 pl-0.5 whitespace-nowrap">Workspace</span>
            </div>
        </a>
    </div>
    
    <!-- Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar py-6 px-3 space-y-8">
        
        <!-- SECTION: OVERVIEW -->
        <div class="space-y-1">
             <div class="px-3 mb-2 transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
                <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">
                    Overview
                </h3>
            </div>

            <x-layout.nav-link title="Dashboard" url="/dashboard" :active="request()->is('dashboard')">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                </x-slot>
            </x-layout.nav-link>

            <x-layout.nav-link title="Analytics" url="#" :active="false">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                </x-slot>
            </x-layout.nav-link>
        </div>

        <!-- SECTION: COMMERCE -->
        <div class="space-y-1">
            <div class="px-3 mb-2 transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
                <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">
                    Commerce
                </h3>
            </div>
            
            <!-- 1. CATALOG -->
            <x-layout.nav-collapsible title="Catalog" :active="request()->is('products*') || request()->is('central/products*') || request()->is('categories*') || request()->is('collections*')" :items="[
                [
                    'title' => 'Products', 
                    'url' => tenant() ? route('tenant.products.index') : route('central.products.index'),
                    'active' => request()->routeIs('tenant.products.*') || request()->routeIs('central.products.*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'m7.5 4.27 9 5.15\'/><path d=\'M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z\'/><path d=\'m3.3 7 8.7 5 8.7-5\'/><path d=\'M12 22V12\'/></svg>'
                ],
                [
                    'title' => 'Categories', 
                    'url' => tenant() ? route('tenant.categories.index') : route('central.categories.index'),
                    'active' => request()->is('categories*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M3 6h18\'/><path d=\'M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2\'/><rect width=\'18\' height=\'14\' x=\'3\' y=\'6\' rx=\'2\'/></svg>'
                ],
                [
                    'title' => 'Collections', 
                    'url' => tenant() ? route('tenant.collections.index') : route('central.collections.index'),
                    'active' => request()->is('collections*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'m16 6 4 14\'/><path d=\'M12 6v14\'/><path d=\'M8 8v12\'/><path d=\'M4 4v16\'/></svg>'
                ]
            ]">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                </x-slot>
            </x-layout.nav-collapsible>

            <!-- 2. SALES -->
            <x-layout.nav-collapsible title="Sales" :active="request()->is('orders*') || request()->is('central/orders*') || request()->is('invoices*') || request()->is('shipments*') || request()->is('returns*')" :items="[
                [
                    'title' => 'Orders', 
                    'url' => tenant() ? route('tenant.orders.index') : route('central.orders.index'),
                    'active' => request()->routeIs('tenant.orders.*') || request()->routeIs('central.orders.*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><rect width=\'16\' height=\'20\' x=\'4\' y=\'2\' rx=\'2\'/><path d=\'M9 22v-4h6v4\'/><path d=\'M8 6h.01\'/><path d=\'M16 6h.01\'/><path d=\'M12 6h.01\'/><path d=\'M12 10h.01\'/><path d=\'M12 14h.01\'/><path d=\'M16 10h.01\'/><path d=\'M16 14h.01\'/><path d=\'M8 10h.01\'/><path d=\'M8 14h.01\'/></svg>'
                ],
                [
                    'title' => 'Invoices', 
                    'url' => tenant() ? route('tenant.invoices.index') : route('central.invoices.index'),
                    'active' => request()->routeIs('tenant.invoices.*') || request()->routeIs('central.invoices.*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z\'/><polyline points=\'14 2 14 8 20 8\'/><path d=\'M16 13H8\'/><path d=\'M16 17H8\'/><path d=\'M10 9H8\'/></svg>'
                ],
                [
                    'title' => 'Shipments', 
                    'url' => tenant() ? route('tenant.shipments.index') : route('central.shipments.index'),
                    'active' => request()->routeIs('tenant.shipments.*') || request()->routeIs('central.shipments.*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M10 17h4V5H2v12h3\'/><path d=\'M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5\'/><path d=\'M14 17h1\'/><circle cx=\'7.5\' cy=\'17.5\' r=\'2.5\'/><circle cx=\'17.5\' cy=\'17.5\' r=\'2.5\'/></svg>'
                ],
                [
                    'title' => 'Returns', 
                    'url' => tenant() ? route('tenant.returns.index') : route('central.returns.index'),
                    'active' => request()->routeIs('tenant.returns.*') || request()->routeIs('central.returns.*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M9 14 2 9l7-5\'/><path d=\'M20 20v-7a4 4 0 0 0-4-4H2\'/></svg>'
                ]
            ]">
                 <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                </x-slot>
            </x-layout.nav-collapsible>
            
            <!-- 3. CUSTOMERS -->
             <x-layout.nav-collapsible title="Customers" :active="request()->is('customers*')" :items="[
                [
                    'title' => 'All Customers', 
                    'url' => '/customers', 'active' => request()->is('customers'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2\'/><circle cx=\'9\' cy=\'7\' r=\'4\'/><path d=\'M22 21v-2a4 4 0 0 0-3-3.87\'/><path d=\'M16 3.13a4 4 0 0 1 0 7.75\'/></svg>'
                ],
                [
                    'title' => 'Add Customer', 
                    'url' => '/customers/create', 'active' => request()->is('customers/create'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2\'/><circle cx=\'9\' cy=\'7\' r=\'4\'/><line x1=\'19\' x2=\'19\' y1=\'8\' y2=\'14\'/><line x1=\'22\' x2=\'16\' y1=\'11\' y2=\'11\'/></svg>'
                ],
                [
                    'title' => 'Segments', 
                    'url' => '#',
                    'active' => false,
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M10 21.9a10 10 0 0 0 9.9-9.9\'/><path d=\'M2 12a10 10 0 0 1 7-9.4\'/><path d=\'M22 12A10 10 0 0 0 12 2v10Z\'/></svg>'
                ],
            ]">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </x-slot>
            </x-layout.nav-collapsible>
        </div>

        <!-- SECTION: OPERATIONS -->
        <div class="space-y-1">
             <div class="px-3 mb-2 transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
                <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">
                    Operations
                </h3>
            </div>

             <!-- PROCUREMENT -->
             <x-layout.nav-collapsible title="Procurement" :active="request()->is('purchase-orders*') || request()->is('suppliers*') || request()->is('warehouses*') || request()->is('inventory*')" :items="[
                [
                    'title' => 'Purchase Orders', 
                    'url' => tenant() ? route('tenant.purchase-orders.index') : route('central.purchase-orders.index'),
                    'active' => request()->routeIs('tenant.purchase-orders.*') || request()->routeIs('central.purchase-orders.*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z\'/><path d=\'M3 6h18\'/><path d=\'M16 10a4 4 0 0 1-8 0\'/></svg>'
                ],
                [
                    'title' => 'Inventory', 
                    'url' => tenant() ? route('tenant.inventory.index') : route('central.inventory.index'),
                    'active' => request()->routeIs('tenant.inventory.*') || request()->routeIs('central.inventory.*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z\'/></svg>'
                ],
                [
                    'title' => 'Suppliers', 
                    'url' => tenant() ? route('tenant.suppliers.index') : route('central.suppliers.index'),
                    'active' => request()->routeIs('tenant.suppliers.*') || request()->routeIs('central.suppliers.*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2z\'/><path d=\'M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1\'/></svg>'
                ],
                [
                    'title' => 'Warehouses', 
                    'url' => tenant() ? route('tenant.warehouses.index') : route('central.warehouses.index'),
                    'active' => request()->routeIs('tenant.warehouses.*') || request()->routeIs('central.warehouses.*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0\'/><circle cx=\'12\' cy=\'10\' r=\'3\'/></svg>'
                ],
            ]">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </x-slot>
            </x-layout.nav-collapsible>
        </div>

        <!-- SECTION: ORGANIZATION -->
        <div class="space-y-1">
             <div class="px-3 mb-2 transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
                <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">
                    System
                </h3>
            </div>
            
             <x-layout.nav-collapsible title="Access Control" :active="request()->is('users*') || request()->is('roles*') || request()->is('permissions*')" :items="[
                [
                    'title' => 'Users', 
                    'url' => '/users', 'active' => request()->is('users*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2\'/><circle cx=\'9\' cy=\'7\' r=\'4\'/><path d=\'M22 21v-2a4 4 0 0 0-3-3.87\'/><path d=\'M16 3.13a4 4 0 0 1 0 7.75\'/></svg>'
                ],
                [
                    'title' => 'Roles', 
                    'url' => '/roles', 'active' => request()->is('roles*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'M9 3v18\'/></svg>'
                ],
                [
                    'title' => 'Permissions', 
                    'url' => '/permissions', 'active' => request()->is('permissions*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10\'/></svg>'
                ],
            ]">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </x-slot>
            </x-layout.nav-collapsible>
            
            @if(!tenant())
            <x-layout.nav-link title="Tenant Workspaces" url="/tenants" :active="request()->is('tenants*')">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                </x-slot>
            </x-layout.nav-link>
            @endif
            
            <x-layout.nav-collapsible title="Settings" :active="request()->is('settings*') || request()->is('activity-logs*')" :items="[
                [
                    'title' => 'General', 
                    'url' => '/settings', 'active' => request()->is('settings'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.72V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.17a2 2 0 0 1 1-1.74l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z\'/><circle cx=\'12\' cy=\'12\' r=\'3\'/></svg>'
                ],
                [
                    'title' => 'Audit Logs', 
                    'url' => '/activity-logs', 'active' => request()->is('activity-logs*'),
                    'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z\'/><polyline points=\'14 2 14 8 20 8\'/><path d=\'M12 18h.01\'/><path d=\'M12 14h.01\'/><path d=\'M12 10h.01\'/></svg>'
                ],
            ]">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.72V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.17a2 2 0 0 1 1-1.74l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                </x-slot>
            </x-layout.nav-collapsible>
        </div>

    </div>

    <!-- User Profile Footer -->
    <div class="p-3 border-t border-sidebar-border/50 bg-white/50 dark:bg-zinc-900/50 backdrop-blur-md shrink-0" x-data="{ userMenuOpen: false }" @click.away="userMenuOpen = false">
        <div class="relative">
             <button @click="userMenuOpen = !userMenuOpen" class="flex w-full items-center gap-3.5 rounded-xl p-2.5 transition-all duration-300 hover:bg-sidebar-accent/50 group/user border border-transparent hover:border-sidebar-border/50" :class="sidebarCollapsed ? 'justify-center' : ''">
                <div class="relative h-9 w-9 shrink-0">
                    <div class="h-full w-full rounded-xl bg-gradient-to-tr from-zinc-600 to-zinc-500 flex items-center justify-center text-white font-bold text-xs ring-2 ring-white/20 shadow-sm group-hover/user:scale-105 transition-transform duration-300">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <!-- Online Status Dot -->
                    <span class="absolute bottom-[-1px] right-[-1px] h-3 w-3 rounded-full border-2 border-white dark:border-zinc-900 bg-emerald-500 shadow-sm"></span>
                </div>
                
                <div class="flex flex-col overflow-hidden text-start transition-all duration-300 ease-in-out" :class="sidebarCollapsed ? 'w-0 opacity-0 hidden' : 'w-full opacity-100'">
                    <span class="truncate text-sm font-semibold leading-none text-foreground group-hover/user:text-primary transition-colors">{{ auth()->user()->name ?? 'User' }}</span>
                    <span class="truncate text-[10px] text-muted-foreground mt-1">{{ auth()->user()->email ?? '' }}</span>
                </div>
                
                <svg x-show="!sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-auto size-4 text-muted-foreground/50 group-hover/user:text-muted-foreground transition-all duration-300" :class="userMenuOpen ? 'rotate-180' : ''"><path d="m18 15-6-6-6 6"/></svg>
            </button>

            <!-- Menu Dropdown -->
             <div 
                x-show="userMenuOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                class="absolute bottom-full left-0 mb-4 w-64 rounded-2xl border border-border/50 bg-popover/80 backdrop-blur-2xl p-2 shadow-2xl shadow-black/20 ring-1 ring-white/10 z-[100] origin-bottom-left max-h-[60vh] overflow-y-auto custom-scrollbar"
            >
                <div class="px-3 py-3 mb-2 bg-muted/30 rounded-xl border border-white/5">
                     <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Signed in as</p>
                     <p class="text-xs font-medium truncate text-foreground mt-0.5">{{ auth()->user()->email ?? '' }}</p>
                </div>
                
                <div class="space-y-0.5">
                    <a href="#" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition-all hover:bg-primary/5 hover:text-primary font-medium group/item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4 text-muted-foreground group-hover/item:text-primary transition-colors"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Profile & Account
                    </a>
                    <a href="#" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition-all hover:bg-primary/5 hover:text-primary font-medium group/item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4 text-muted-foreground group-hover/item:text-primary transition-colors"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        Help & Support
                    </a>
                </div>
                
                <div class="h-px bg-border/50 my-2 mx-1"></div>
                
                <form method="POST" action="{{ tenant() ? request()->getSchemeAndHttpHost() . '/logout' : config('app.url') . '/logout' }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition-all hover:bg-red-500/10 text-red-500/90 hover:text-red-600 font-semibold group/logout">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4 transition-transform group-hover/logout:translate-x-1"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>
