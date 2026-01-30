<aside 
    class="fixed inset-y-0 left-0 z-50 flex flex-col border-r border-border/40 bg-sidebar/80 backdrop-blur-xl transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] md:translate-x-0 group/sidebar shadow-2xl shadow-primary/5 dark:shadow-none bg-gradient-to-b from-background/80 to-background/40"
    :class="{
        'w-72': !sidebarCollapsed,
        'w-[4.5rem]': sidebarCollapsed,
        '-translate-x-full': !mobileMenuOpen && window.innerWidth < 768,
        'translate-x-0': mobileMenuOpen && window.innerWidth < 768
    }"
    @click.away="mobileMenuOpen = false"
>
    <!-- Logo Area -->
    <div class="h-16 flex items-center px-4 border-b border-border/40 relative overflow-hidden shrink-0">
        <div class="absolute inset-0 bg-gradient-to-r from-primary/10 to-transparent opacity-0 group-hover/sidebar:opacity-100 transition-opacity duration-500"></div>
        
        <a href="{{ url('/dashboard') }}" class="flex items-center gap-3 relative z-10 w-full" :class="sidebarCollapsed ? 'justify-center' : ''">
            <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-primary to-purple-600 flex items-center justify-center shadow-lg shadow-primary/25 shrink-0 transition-transform group-hover/sidebar:scale-110 duration-300 ring-1 ring-white/10">
                <!-- Premium Logo Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-white"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <div class="flex flex-col overflow-hidden transition-all duration-300" 
                 :class="sidebarCollapsed ? 'w-0 opacity-0 absolute' : 'w-auto opacity-100'">
                <span class="font-heading font-bold text-lg tracking-tight leading-none text-foreground">
                    {{ tenant('id') ? ucfirst(tenant('id')) : 'Central Admin' }}
                </span>
                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-0.5">Workspace</span>
            </div>
        </a>
    </div>
    
    <!-- Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar py-4 px-3 space-y-6">
        
        <!-- SECTION: OVERVIEW -->
        <div>
            <div class="px-2 mb-2 transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
                <h3 class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/60 pl-1">
                    Overview
                </h3>
            </div>
            
            <div class="space-y-1">
                <x-layout.nav-link title="Dashboard" url="/dashboard" :active="request()->is('dashboard')">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    </x-slot>
                </x-layout.nav-link>

                <x-layout.nav-link title="Analytics" url="#">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                    </x-slot>
                </x-layout.nav-link>
            </div>
        </div>

        <!-- SECTION: MANAGEMENT -->
        <div>
            <div class="px-2 mb-2 transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
                <h3 class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/60 pl-1">
                    Management
                </h3>
            </div>
            
            <div class="space-y-1">
                <!-- Catalog -->
                <x-layout.nav-collapsible title="Catalog" :active="request()->is('catalog*')" :items="[
                    ['title' => 'Products', 'url' => '#', 'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'16\' height=\'16\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'m7.5 4.27 9 5.15\'/><path d=\'M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z\'/><path d=\'m3.3 7 8.7 5 8.7-5\'/><path d=\'M12 22V12\'/></svg>'],
                    ['title' => 'Categories', 'url' => '#'],
                    ['title' => 'Collections', 'url' => '#'],
                    ['title' => 'Inventory', 'url' => '#'],
                ]">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    </x-slot>
                </x-layout.nav-collapsible>

                <!-- Sales -->
                <x-layout.nav-collapsible title="Sales & Orders" :active="request()->is('sales*')" :items="[
                    ['title' => 'All Orders', 'url' => '#'],
                    ['title' => 'Invoices', 'url' => '#'],
                    ['title' => 'Shipments', 'url' => '#'],
                    ['title' => 'Returns', 'url' => '#'],
                ]">
                     <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    </x-slot>
                </x-layout.nav-collapsible>

                <!-- Customers (Shared) -->
                <x-layout.nav-collapsible title="Customers" :active="request()->is('customers*')" :items="[
                    ['title' => 'All Customers', 'url' => '/customers', 'active' => request()->is('customers') || request()->is('customers/*/edit') || request()->is('customers/*')],
                    ['title' => 'Add Customer', 'url' => '/customers/create', 'active' => request()->is('customers/create')],
                    ['title' => 'Segments', 'url' => '#'],
                ]">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </x-slot>
                </x-layout.nav-collapsible>
            </div>
        </div>
        
        <!-- SECTION: CONTENT -->
        <div>
            <div class="px-2 mb-2 transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
                <h3 class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/60 pl-1">
                     Online Store
                </h3>
            </div>
             <div class="space-y-1">
                <x-layout.nav-collapsible title="Storefront" :active="request()->is('storefront*')" :items="[
                    ['title' => 'Themes', 'url' => '#'],
                    ['title' => 'Blog Posts', 'url' => '#'],
                    ['title' => 'Navigation', 'url' => '#'],
                ]">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2v0a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12v0a2 2 0 0 1-2-2V7"/></svg>
                    </x-slot>
                </x-layout.nav-collapsible>
            </div>
        </div>

        <!-- SECTION: ORGANIZATION -->
        <div>
            <div class="px-2 mb-2 transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
                <h3 class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/60 pl-1">
                    Organization
                </h3>
            </div>
            
            <div class="space-y-1">
                 <x-layout.nav-collapsible title="Team & Access" :active="request()->is('users*') || request()->is('roles*') || request()->is('permissions*')" :items="[
                    ['title' => 'Users', 'url' => '/users', 'active' => request()->is('users*')],
                    ['title' => 'Roles & Policies', 'url' => '/roles', 'active' => request()->is('roles*')],
                    ['title' => 'Permissions', 'url' => '/permissions', 'active' => request()->is('permissions*')],
                ]">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                    </x-slot>
                </x-layout.nav-collapsible>
                
                @if(!tenant())
                <x-layout.nav-link title="Tenant Workspaces" url="/tenants" :active="request()->is('tenants*')">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                    </x-slot>
                </x-layout.nav-link>
                @endif
                
                <x-layout.nav-link title="Audit Logs" url="/activity-logs" :active="request()->is('activity-logs*')">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                    </x-slot>
                </x-layout.nav-link>
            </div>
        </div>
        
         <!-- SECTION: SETTINGS -->
        <div>
            <div class="px-2 mb-2 transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
                <h3 class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/60 pl-1">
                    System
                </h3>
            </div>
            
            <div class="space-y-1">
                <x-layout.nav-collapsible title="Settings" :active="request()->is('settings*')" :items="[
                    ['title' => 'General', 'url' => '/settings', 'active' => request()->is('settings')],
                    ['title' => 'Billing & Plans', 'url' => '#'],
                    ['title' => 'Notifications', 'url' => '#'],
                ]">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </x-slot>
                </x-layout.nav-collapsible>
            </div>
        </div>

    </div>

    <!-- User Profile Footer -->
    <div class="p-3 border-t border-border/40 bg-sidebar/50 backdrop-blur-sm shrink-0" x-data="{ userMenuOpen: false }" @click.away="userMenuOpen = false">
        <div class="relative">
             <button 
                @click="userMenuOpen = !userMenuOpen"
                class="flex w-full items-center gap-3 rounded-xl p-2 transition-all duration-200 hover:bg-sidebar-accent outline-none group border border-transparent hover:border-border/30"
                :class="sidebarCollapsed ? 'justify-center' : ''"
            >
                <div class="relative h-9 w-9 shrink-0">
                    <div class="h-full w-full rounded-lg bg-gradient-to-tr from-gray-700 to-gray-600 flex items-center justify-center text-white font-bold text-xs ring-2 ring-background group-hover:ring-primary/50 transition-all">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <!-- Online Status Dot -->
                    <span class="absolute bottom-[-2px] right-[-2px] h-3 w-3 rounded-full border-2 border-background bg-emerald-500"></span>
                </div>
                
                <div class="flex flex-col overflow-hidden text-start transition-all duration-300" :class="sidebarCollapsed ? 'w-0 opacity-0 hidden' : 'w-full opacity-100'">
                    <span class="truncate text-sm font-semibold leading-none">{{ auth()->user()->name ?? 'User' }}</span>
                    <span class="truncate text-[10px] text-muted-foreground mt-1">{{ auth()->user()->email ?? '' }}</span>
                </div>
                
                <svg x-show="!sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-auto size-4 text-muted-foreground/50 group-hover:text-muted-foreground transition-colors" :class="userMenuOpen ? 'rotate-180' : ''"><path d="m18 15-6-6-6 6"/></svg>
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
                class="absolute bottom-full left-0 mb-3 w-64 rounded-2xl border border-border/50 bg-popover/95 backdrop-blur-xl p-1.5 shadow-2xl shadow-black/20 ring-1 ring-black/5 z-[100] origin-bottom-left max-h-[60vh] overflow-y-auto custom-scrollbar"
            >
                <div class="px-3 py-3 mb-1 bg-muted/40 rounded-xl border border-border/30">
                     <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Signed in as</p>
                     <p class="text-sm font-bold truncate text-foreground mt-0.5">{{ auth()->user()->email ?? '' }}</p>
                </div>
                
                <a href="#" class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4 text-muted-foreground"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Profile & Account
                </a>
                <a href="#" class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4 text-muted-foreground"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                    Help & Support
                </a>
                
                <div class="h-px bg-border/50 my-1 mx-2"></div>
                
                <form method="POST" action="{{ tenant() ? request()->getSchemeAndHttpHost() . '/logout' : config('app.url') . '/logout' }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-sm transition-colors hover:bg-red-500/10 text-red-500 font-semibold group/logout">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4 transition-transform group-hover/logout:translate-x-1"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>
