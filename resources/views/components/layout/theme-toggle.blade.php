<div x-data="{ open: false }" class="relative" @keydown.escape.prevent="open = false" @click.outside="open = false">
    <button @click="open = !open" class="group relative inline-flex items-center justify-center rounded-xl size-10 text-muted-foreground hover:bg-white/50 dark:hover:bg-white/10 hover:text-primary transition-all duration-300 active:scale-90">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="size-5 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="absolute size-5 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
        <span class="sr-only">Toggle theme</span>
    </button>

    <div x-show="open" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-[0.98]"
         class="absolute right-0 mt-3 w-64 rounded-[32px] border border-white/20 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-3xl shadow-[0_30px_70px_-15px_rgba(0,0,0,0.5)] z-50 overflow-hidden ring-1 ring-black/5">
        
        <div class="p-6 pb-2 transition-all">
            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 mb-4 px-1">Interface Mode</h4>
            <div class="grid grid-cols-3 gap-2 bg-secondary/20 dark:bg-white/5 p-1 rounded-2xl border border-white/10 backdrop-blur-md">
                <button @click="theme = 'light'" 
                    class="group flex flex-col items-center justify-center rounded-xl py-3 gap-2 transition-all duration-300"
                    :class="theme === 'light' ? 'bg-white dark:bg-zinc-800 text-primary shadow-lg ring-1 ring-black/5' : 'text-muted-foreground hover:text-foreground hover:bg-white/50 dark:hover:bg-white/10'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                    <span class="text-[9px] font-black uppercase tracking-tighter">Day</span>
                </button>
                <button @click="theme = 'dark'" 
                    class="group flex flex-col items-center justify-center rounded-xl py-3 gap-2 transition-all duration-300"
                    :class="theme === 'dark' ? 'bg-white dark:bg-zinc-800 text-primary shadow-lg ring-1 ring-black/5' : 'text-muted-foreground hover:text-foreground hover:bg-white/50 dark:hover:bg-white/10'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                    <span class="text-[9px] font-black uppercase tracking-tighter">Night</span>
                </button>
                <button @click="theme = 'system'" 
                    class="group flex flex-col items-center justify-center rounded-xl py-3 gap-2 transition-all duration-300"
                    :class="theme === 'system' ? 'bg-white dark:bg-zinc-800 text-primary shadow-lg ring-1 ring-black/5' : 'text-muted-foreground hover:text-foreground hover:bg-white/50 dark:hover:bg-white/10'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><rect width="18" height="12" x="3" y="4" rx="2"/><path d="M7 20h10"/><path d="M12 16v4"/></svg>
                    <span class="text-[9px] font-black uppercase tracking-tighter">Auto</span>
                </button>
            </div>
        </div>

        <div class="h-px bg-gradient-to-r from-transparent via-border/50 to-transparent mx-6 my-2"></div>

        <div class="p-6 pt-2">
            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 mb-4 px-1">Identity Color</h4>
            <div class="grid grid-cols-3 gap-4 px-1">
                <template x-for="c in ['zinc', 'blue', 'violet', 'rose', 'orange', 'green']" :key="c">
                     <button @click="colorTheme = c" class="group relative flex items-center justify-center p-1 rounded-2xl transition-all hover:bg-secondary" :title="c">
                        <div class="size-10 rounded-xl shadow-lg transition-all border-2 border-transparent group-hover:scale-110" 
                             :class="{
                                'bg-zinc-950 dark:bg-white': c === 'zinc',
                                'bg-blue-500': c === 'blue',
                                'bg-violet-500': c === 'violet',
                                'bg-rose-500': c === 'rose',
                                'bg-orange-500': c === 'orange',
                                'bg-green-500': c === 'green',
                                'ring-2 ring-primary ring-offset-4 dark:ring-offset-zinc-950': colorTheme === c
                             }">
                        </div>
                        <div x-show="colorTheme === c" class="absolute inset-0 flex items-center justify-center text-white pointer-events-none" :class="c==='zinc' ? 'dark:text-black' : ''">
                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="M20 6 9 17l-5-5"/></svg>
                        </div>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
