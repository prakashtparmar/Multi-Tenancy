<div x-data="{ open: false }" class="relative flex flex-col p-2">
    <button @click="open = !open && !sidebarCollapsed"
            class="flex items-center gap-2 rounded-lg p-2 transition-all hover:bg-sidebar-accent hover:text-sidebar-accent-foreground data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
            :class="sidebarCollapsed ? 'justify-center p-2' : ''"
    >
        <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-command size-4"><path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3"/></svg>
        </div>
        <div class="grid flex-1 text-left text-sm leading-tight overflow-hidden transition-all duration-300" :class="sidebarCollapsed ? 'w-0 opacity-0' : 'w-full opacity-100'">
            <span class="truncate font-semibold text-sidebar-foreground">{{ tenant('id') ? ucfirst(tenant('id')) : 'Central Platform' }}</span>
            <span class="truncate text-xs text-muted-foreground">{{ auth()->user()->name ?? 'Administrator' }}</span>
        </div>
        <svg x-show="!sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevrons-up-down ml-auto size-4 text-muted-foreground"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
    </button>

    <div x-show="open" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.away="open = false" 
         class="absolute left-2 top-14 w-56 rounded-md border bg-popover text-popover-foreground shadow-md outline-none z-50 overflow-hidden"
    >
        <div class="p-2 text-xs text-muted-foreground">Teams</div>
        <div class="flex flex-col gap-1 p-1">
            @php
                $currentTeam = tenant('id') ? ucfirst(tenant('id')) : 'Central Platform';
                $plan = tenant('id') ? 'Workspace' : 'Management';
            @endphp
            <div class="flex items-center gap-2 rounded-md p-2 transition-colors hover:bg-muted cursor-pointer bg-muted/50">
                <div class="flex size-6 items-center justify-center rounded-sm border bg-primary text-primary-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 shrink-0"><path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3"/></svg>
                </div>
                <div class="flex flex-col flex-1 overflow-hidden">
                    <span class="text-sm font-medium truncate">{{ $currentTeam }}</span>
                    <span class="text-[10px] text-muted-foreground">{{ $plan }}</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3 text-primary"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
        </div>
        <div class="border-t p-1 mt-1">
            <div class="flex items-center gap-2 rounded-md p-2 transition-colors hover:bg-muted cursor-pointer">
                <div class="flex size-6 items-center justify-center rounded-sm border bg-background">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="M12 5v14M5 12h14"/></svg>
                </div>
                <span class="text-sm font-medium text-muted-foreground">Add team</span>
            </div>
        </div>
    </div>
</div>
