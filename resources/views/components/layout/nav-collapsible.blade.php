@props([
    'title',
    'icon' => null,
    'badge' => null,
    'active' => false,
    'items' => [],
])

<div x-data="{ open: {{ $active ? 'true' : 'false' }} }" class="group/collapsible relative">
    <button @click="open = !open"
            class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-start text-sm outline-none transition-all hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2 active:bg-sidebar-accent active:text-sidebar-accent-foreground {{ $active ? 'font-medium text-sidebar-accent-foreground' : 'text-sidebar-foreground' }}"
            :class="sidebarCollapsed ? 'justify-center p-2' : ''"
    >
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

    <div x-show="open && !sidebarCollapsed" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="ml-3.5 mt-1 flex flex-col gap-1 border-l border-sidebar-border px-2.5 py-0.5"
    >
        @foreach($items as $item)
            <a href="{{ $item['url'] ?? '#' }}" 
               class="flex h-7 items-center gap-2 rounded-md px-2 text-sm text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground transition-all {{ ($item['active'] ?? false) ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground' : '' }}">
                @if($item['icon'] ?? null)
                    {!! $item['icon'] !!}
                @endif
                <span class="truncate">{{ $item['title'] }}</span>
            </a>
        @endforeach
    </div>
</div>
