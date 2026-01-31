<header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-white/5 bg-white/80 dark:bg-zinc-900/80 px-4 backdrop-blur-xl transition-all duration-300 ease-in-out lg:px-6 shadow-[0_1px_30px_0px_rgba(0,0,0,0.02)]">
    
    <!-- Ambient Glow -->
    <div class="absolute inset-0 z-[-1] overflow-hidden pointer-events-none">
        <div class="absolute top-0 left-1/4 w-64 h-full bg-primary/5 blur-[50px] transform -translate-y-1/2"></div>
    </div>

    <div class="flex items-center gap-4">
        <button class="inline-flex items-center justify-center rounded-xl p-2 text-muted-foreground hover:bg-secondary hover:text-foreground transition-all duration-200 active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" @click="toggleSidebar()">
            <!-- Sidebar Toggle Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>
            <span class="sr-only">Toggle Sidebar</span>
        </button>
        
        <div class="h-6 w-px bg-border/50"></div>
        
        <!-- Breadcrumbs -->
        <nav class="hidden md:flex items-center space-x-1 text-sm font-medium">
            <a href="#" class="text-muted-foreground hover:text-foreground transition-colors px-2 py-1 rounded-lg hover:bg-muted/50 flex items-center gap-2">
                <div class="h-1.5 w-1.5 rounded-full bg-primary/50"></div>
                {{ tenant() ? ucfirst(tenant('id')) : 'Platform' }}
            </a>
            <!-- Chevron Right Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-3.5 text-muted-foreground/40"><path d="m9 18 6-6-6-6"/></svg>
            <a href="/dashboard" class="px-2 py-1 rounded-lg transition-all {{ request()->is('dashboard') ? 'bg-primary/5 text-primary font-semibold shadow-sm ring-1 ring-primary/10' : 'text-muted-foreground hover:text-foreground hover:bg-muted/50' }}">
                Dashboard
            </a>
        </nav>
    </div>

    <!-- Right Side Actions -->
    <div class="flex items-center gap-2 sm:gap-4">
        
        <!-- Global Search -->
        <div class="hidden lg:block lg:flex-1 lg:max-w-sm relative group">
            <div class="absolute inset-0 bg-gradient-to-r from-primary/20 to-purple-500/20 rounded-xl blur opacity-0 group-focus-within:opacity-100 transition-opacity duration-500"></div>
            <div class="relative flex items-center">
                <!-- Search Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 size-4 text-muted-foreground group-focus-within:text-primary transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" placeholder="Search anything..." class="flex h-10 w-full rounded-xl border border-input/50 bg-secondary/30 px-10 py-2 text-sm shadow-sm transition-all placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary/50 focus-visible:bg-background" />
                <div class="absolute right-2 flex items-center gap-1">
                     <kbd class="pointer-events-none hidden h-5 select-none items-center gap-1 rounded border bg-muted px-1.5 font-mono text-[10px] font-medium opacity-100 sm:flex text-muted-foreground">
                        <span class="text-xs">⌘</span>K
                    </kbd>
                </div>
            </div>
        </div>
        
        
        <!-- Theme Toggle -->
        <x-layout.theme-toggle />

        <!-- Notifications -->
        <!-- Notifications -->
        <div class="relative" x-data="{ open: false }" @click.away="open = false" @keydown.escape.window="open = false">
            <button @click="open = !open" class="relative inline-flex items-center justify-center rounded-xl p-2 text-muted-foreground hover:bg-secondary hover:text-foreground transition-all duration-200 active:scale-95 group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20">
                <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-background animate-pulse"></span>
                <div class="absolute inset-0 rounded-xl bg-primary/5 scale-0 group-hover:scale-100 transition-transform duration-300"></div>
                <!-- Bell Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5 relative z-10"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
            </button>

            <!-- Dropdown Panel -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                 class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl border border-border/50 bg-background/95 backdrop-blur-xl shadow-xl ring-1 ring-black/5 z-50 overflow-hidden" 
                 style="display: none;">
                
                <div class="flex items-center justify-between px-4 py-3 border-b border-border/50 bg-muted/30">
                    <h3 class="font-heading font-semibold text-sm">Notifications</h3>
                    <button class="text-xs text-primary hover:text-primary/80 font-medium transition-colors">Mark all as read</button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto p-2 space-y-1 scrollbar-thin scrollbar-thumb-border">
                    <!-- Empty State for now -->
                     <div class="flex flex-col items-center justify-center py-8 text-center">
                        <div class="h-12 w-12 rounded-full bg-muted/50 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-muted-foreground/50"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        </div>
                        <p class="text-sm font-medium text-foreground">No new notifications</p>
                        <p class="text-xs text-muted-foreground mt-1">We'll let you know when something arrives.</p>
                    </div>
                </div>
                
                <div class="p-2 border-t border-border/50 bg-muted/30">
                    <a href="#" class="flex items-center justify-center w-full rounded-lg py-2 text-xs font-medium text-muted-foreground hover:bg-background hover:text-foreground hover:shadow-sm transition-all border border-transparent hover:border-border/50">
                        View all activity
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Settings -->
        <a href="/settings" class="inline-flex items-center justify-center rounded-xl p-2 text-muted-foreground hover:bg-secondary hover:text-foreground transition-all duration-200 active:scale-95 group relative {{ request()->is('settings*') ? 'text-primary bg-primary/5' : '' }}">
            <div class="absolute inset-0 rounded-xl bg-primary/5 scale-0 group-hover:scale-100 transition-transform duration-300"></div>
            <!-- Settings Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5 relative z-10 group-hover:rotate-45 transition-transform duration-500"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.72V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.17a2 2 0 0 1 1-1.74l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>

        <div class="h-6 w-px bg-border/50"></div>

        <x-layout.user-dropdown />
    </div>
</header>
