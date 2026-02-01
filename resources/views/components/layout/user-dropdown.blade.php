<div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
    <button @click="open = !open" class="group relative flex items-center gap-3 p-1 text-left rounded-2xl border border-white/10 bg-secondary/20 hover:bg-white/50 dark:hover:bg-white/10 transition-all duration-300 active:scale-95 focus:outline-none focus:ring-2 focus:ring-primary/20 backdrop-blur-md">
        <div class="relative size-10 rounded-xl bg-gradient-to-tr from-primary/20 to-purple-500/20 p-[1.5px] group-hover:rotate-[5deg] transition-all duration-500">
            <div class="flex h-full w-full items-center justify-center rounded-[10px] bg-white dark:bg-zinc-900 border border-white/20 text-xs font-black uppercase tracking-widest text-primary shadow-inner">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
            </div>
            <div class="absolute -bottom-1 -right-1 size-3 rounded-full bg-emerald-500 border-2 border-white dark:border-zinc-900 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
        </div>
        <div class="hidden xl:block pr-3">
            <p class="text-xs font-black uppercase tracking-[0.1em] text-foreground leading-tight">{{ auth()->user()->name ?? 'User' }}</p>
            <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-70 mt-0.5">{{ auth()->user()->role ?? 'Technician' }}</p>
        </div>
        <div class="hidden lg:block pr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="size-3 text-muted-foreground transition-transform duration-300" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
        </div>
    </button>
    
    <div x-show="open" 
         x-cloak
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-[0.98]"
         class="absolute right-0 mt-3 w-64 rounded-[32px] border border-white/20 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-3xl shadow-[0_30px_70px_-15px_rgba(0,0,0,0.5)] z-50 overflow-hidden ring-1 ring-black/5"
    >
        <div class="p-6 relative overflow-hidden">
            <div class="absolute -top-10 -right-10 size-32 bg-primary/10 blur-[40px] rounded-full"></div>
            <div class="relative z-10 space-y-1">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60">Session Registry</p>
                <p class="text-sm font-black text-foreground">{{ auth()->user()->name ?? 'User' }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[9px] font-black uppercase tracking-widest border border-primary/20 italic">Master Account</span>
                </div>
            </div>
        </div>

        <div class="h-px bg-gradient-to-r from-transparent via-border/50 to-transparent"></div>

        <div class="px-3 py-4 space-y-1">
            <a href="#" class="group flex items-center justify-between gap-3 px-4 py-3 rounded-2xl hover:bg-primary/5 transition-all text-sm font-bold text-muted-foreground hover:text-primary">
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-xl bg-secondary/50 flex items-center justify-center group-hover:text-primary transition-colors border border-transparent group-hover:border-primary/20">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    Profile Details
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="size-3.5 opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
            <a href="#" class="group flex items-center justify-between gap-3 px-4 py-3 rounded-2xl hover:bg-primary/5 transition-all text-sm font-bold text-muted-foreground hover:text-primary">
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-xl bg-secondary/50 flex items-center justify-center group-hover:text-primary transition-colors border border-transparent group-hover:border-primary/20">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/></svg>
                    </div>
                    Billing History
                </div>
            </a>
            <a href="/settings" class="group flex items-center justify-between gap-3 px-4 py-3 rounded-2xl hover:bg-primary/5 transition-all text-sm font-bold text-muted-foreground hover:text-primary">
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-xl bg-secondary/50 flex items-center justify-center group-hover:text-primary transition-colors border border-transparent group-hover:border-primary/20">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.72V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.17a2 2 0 0 1 1-1.74l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    System Core
                </div>
            </a>
        </div>

        <div class="h-px bg-gradient-to-r from-transparent via-border/50 to-transparent mx-4"></div>

        <div class="p-3">
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="group flex w-full items-center gap-3 px-4 py-4 rounded-[20px] bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white transition-all duration-300 font-black uppercase text-[10px] tracking-[0.2em] shadow-lg shadow-rose-500/0 hover:shadow-rose-500/20">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="size-4 mr-1 transition-transform group-hover:-translate-x-1"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    Close Session
                </button>
            </form>
        </div>
    </div>
</div>
