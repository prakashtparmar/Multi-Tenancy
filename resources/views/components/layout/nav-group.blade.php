@props([
    'title' => null,
])

<div class="relative flex w-full flex-col p-2 gap-1">
    @if($title)
        <div x-show="!sidebarCollapsed" x-transition.opacity class="flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 uppercase tracking-wider">
            {{ $title }}
        </div>
    @endif
    
    <div class="flex flex-col gap-1">
        {{ $slot }}
    </div>
</div>
