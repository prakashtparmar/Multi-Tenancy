<div x-data="{ open: false }" class="relative" @keydown.escape.prevent="open = false" @click.outside="open = false">
    <button @click="open = !open" class="inline-flex items-center justify-center rounded-xl p-2 text-muted-foreground hover:bg-secondary hover:text-foreground transition-all duration-200 active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="absolute size-5 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
        <span class="sr-only">Toggle theme</span>
    </button>

    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-56 rounded-xl border border-border/50 bg-popover p-2 text-popover-foreground shadow-xl shadow-black/5 outline-none z-50">
        
        <!-- Mode Toggle -->
        <div class="space-y-1 mb-2">
            <div class="px-2 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2 mt-1">Mode</div>
            <div class="grid grid-cols-3 gap-1 bg-muted/40 p-1 rounded-lg">
                <button @click="theme = 'light'" 
                    class="flex items-center justify-center rounded-md py-1.5 text-sm font-medium transition-all"
                    :class="theme === 'light' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                </button>
                <button @click="theme = 'dark'" 
                    class="flex items-center justify-center rounded-md py-1.5 text-sm font-medium transition-all"
                    :class="theme === 'dark' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                </button>
                <button @click="theme = 'system'" 
                    class="flex items-center justify-center rounded-md py-1.5 text-sm font-medium transition-all"
                    :class="theme === 'system' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                </button>
            </div>
        </div>

        <div class="h-px bg-border/50 my-2"></div>

        <!-- Color Toggle -->
        <div class="space-y-1">
            <div class="px-2 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Theme</div>
            <div class="grid grid-cols-4 gap-2 px-1">
                <button @click="colorTheme = 'zinc'" class="group relative flex items-center justify-center rounded-full p-0.5 hover:bg-muted transition-colors">
                    <span class="h-6 w-6 rounded-full bg-zinc-950 dark:bg-zinc-100 border border-zinc-200 dark:border-zinc-800"></span>
                    <span x-show="colorTheme === 'zinc'" class="absolute inset-0 flex items-center justify-center text-white dark:text-black">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                </button>

                <button @click="colorTheme = 'blue'" class="group relative flex items-center justify-center rounded-full p-0.5 hover:bg-muted transition-colors">
                    <span class="h-6 w-6 rounded-full bg-blue-600 border border-blue-200 dark:border-blue-800"></span>
                    <span x-show="colorTheme === 'blue'" class="absolute inset-0 flex items-center justify-center text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                </button>

                <button @click="colorTheme = 'violet'" class="group relative flex items-center justify-center rounded-full p-0.5 hover:bg-muted transition-colors">
                    <span class="h-6 w-6 rounded-full bg-violet-600 border border-violet-200 dark:border-violet-800"></span>
                    <span x-show="colorTheme === 'violet'" class="absolute inset-0 flex items-center justify-center text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                </button>

                <button @click="colorTheme = 'rose'" class="group relative flex items-center justify-center rounded-full p-0.5 hover:bg-muted transition-colors">
                    <span class="h-6 w-6 rounded-full bg-rose-600 border border-rose-200 dark:border-rose-800"></span>
                    <span x-show="colorTheme === 'rose'" class="absolute inset-0 flex items-center justify-center text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                </button>

                <button @click="colorTheme = 'orange'" class="group relative flex items-center justify-center rounded-full p-0.5 hover:bg-muted transition-colors">
                    <span class="h-6 w-6 rounded-full bg-orange-600 border border-orange-200 dark:border-orange-800"></span>
                    <span x-show="colorTheme === 'orange'" class="absolute inset-0 flex items-center justify-center text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                </button>

                <button @click="colorTheme = 'green'" class="group relative flex items-center justify-center rounded-full p-0.5 hover:bg-muted transition-colors">
                    <span class="h-6 w-6 rounded-full bg-green-600 border border-green-200 dark:border-green-800"></span>
                    <span x-show="colorTheme === 'green'" class="absolute inset-0 flex items-center justify-center text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
