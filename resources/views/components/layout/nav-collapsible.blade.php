@props([
    'title',
    'icon' => null,
    'badge' => null,
    'active' => false,
    'items' => [],
])

<div x-data="{ 
    open: {{ $active ? 'true' : 'false' }}, 
    top: 0, 
    hoverOpen: false, 
    closeTimer: null 
}" 
     @mouseenter="clearTimeout(closeTimer); hoverOpen = true; top = $el.getBoundingClientRect().top"
     @mouseleave="closeTimer = setTimeout(() => hoverOpen = false, 150)"
     class="group/collapsible relative">
    <button @click="open = !open"
            class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-xl p-2.5 text-start text-sm outline-none transition-all duration-300 hover:bg-primary/5 hover:text-primary focus-visible:ring-2 active:bg-primary/10 {{ $active ? 'font-bold text-primary border border-primary/10 bg-primary/5' : 'text-sidebar-foreground border border-transparent' }}"
            :class="sidebarCollapsed ? 'justify-center p-2' : ''"
    >
        @if($active)
             <div class="absolute left-[-12px] top-1/2 -translate-y-1/2 w-1.5 h-6 bg-primary rounded-r-full shadow-[2px_0_8px_rgba(var(--primary-rgb),0.4)] z-50"></div>
        @endif
        @if($icon)
            <div class="shrink-0">
                {!! $icon !!}
            </div>
        @endif
        
        <span x-show="!sidebarCollapsed" x-transition.opacity class="truncate">{{ $title }}</span>
        
        @if($badge)
            <div x-show="!sidebarCollapsed" class="ml-auto flex h-5 min-w-5 items-center justify-center rounded-md bg-primary px-1 text-[10px] font-medium text-primary-foreground tabular-nums select-none">
                {{ $badge }}
            </div>
        @endif

        <svg x-show="!sidebarCollapsed" 
             xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
             class="lucide lucide-chevron-right ml-auto size-4 transition-transform duration-200"
             :class="open ? 'rotate-90' : ''"
        >
            <path d="m9 18 6-6-6-6"/>
        </svg>
    </button>

    <!-- Submenu (Original - Expanded State) -->
    <div x-show="open && !sidebarCollapsed" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="ml-3.5 mt-1 flex flex-col gap-1 border-l border-sidebar-border px-2.5 py-0.5"
    >
        @foreach($items as $item)
            <a href="{{ $item['url'] ?? '#' }}" 
               class="flex h-9 items-center gap-2.5 rounded-lg px-3 text-sm text-sidebar-foreground hover:bg-primary/5 hover:text-primary transition-all duration-300 {{ ($item['active'] ?? false) ? 'bg-primary/5 font-bold text-primary border border-primary/10' : 'border border-transparent' }}">
                @if($item['icon'] ?? null)
                    {!! $item['icon'] !!}
                @endif
                <span class="truncate">{{ $item['title'] }}</span>
            </a>
        @endforeach
    </div>

    <!-- Flyout Menu (New - Collapsed State) -->
    <div x-show="sidebarCollapsed && hoverOpen"
         x-cloak
         @mouseenter="clearTimeout(closeTimer); hoverOpen = true"
         @mouseleave="closeTimer = setTimeout(() => hoverOpen = false, 150)"
         style="display: none;"
         :style="'display: ' + (sidebarCollapsed && hoverOpen ? 'block' : 'none') + '; top: ' + top + 'px'"
         class="fixed left-[4.5rem] z-[50] min-w-[12rem] overflow-hidden rounded-r-lg border-l-2 border-primary bg-popover shadow-lg ring-1 ring-border animate-in fade-in zoom-in-95 duration-200"
    >
        <div class="border-b border-border bg-muted/40 px-3 py-2">
            <h4 class="font-medium text-sm text-foreground">{{ $title }}</h4>
        </div>
        <div class="p-1">
            @foreach($items as $item)
                <a href="{{ $item['url'] ?? '#' }}" 
                   class="flex w-full cursor-pointer items-center rounded-md p-2 text-sm font-medium transition-colors hover:bg-primary/10 hover:text-primary {{ ($item['active'] ?? false) ? 'bg-primary/10 text-primary' : 'text-muted-foreground' }}">
                    <span class="truncate">{{ $item['title'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
