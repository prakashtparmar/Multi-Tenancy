@props([
    'title',
    'url' => '#',
    'icon' => null,
    'badge' => null,
    'active' => false,
])

<div class="group/menu-item relative">
    <a href="{{ $url }}" 
       class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-start text-sm outline-none transition-all hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2 active:bg-sidebar-accent active:text-sidebar-accent-foreground {{ $active ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground' : 'text-sidebar-foreground' }}"
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
    </a>
</div>
