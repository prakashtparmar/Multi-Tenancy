@props([
    'title',
    'url' => '#',
    'icon' => null,
    'badge' => null,
    'active' => false,
])

<div class="group/menu-item relative">
    <a href="{{ $url }}" 
       class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-xl p-2.5 text-start text-sm outline-none transition-all duration-300 hover:bg-primary/5 hover:text-primary focus-visible:ring-2 active:bg-primary/10 {{ $active ? 'bg-primary/10 font-bold text-primary shadow-sm border border-primary/10' : 'text-sidebar-foreground border border-transparent' }}"
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
    </a>
</div>
