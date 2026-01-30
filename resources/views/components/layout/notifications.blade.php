<div x-data="{ open: false }" class="relative">
    <x-ui.button variant="ghost" size="icon" class="relative rounded-xl hover:bg-primary/10 hover:text-primary transition-all active:scale-95 group" @click="open = !open">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell size-5 group-hover:shake transition-transform"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
        <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-destructive border-2 border-background animate-pulse"></span>
    </x-ui.button>
    
    <div x-show="open" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         @click.away="open = false" 
         class="absolute right-0 mt-3 w-80 rounded-2xl border bg-popover/95 backdrop-blur-md text-popover-foreground shadow-2xl outline-none z-50 overflow-hidden ring-1 ring-black/5"
    >
        <div class="flex flex-col space-y-1 p-5 border-b bg-muted/30">
            <h3 class="font-bold leading-none tracking-tight">Notifications</h3>
            <p class="text-xs text-muted-foreground mt-1">You have <span class="text-primary font-bold">3 unread</span> messages.</p>
        </div>
        <div class="max-h-[400px] overflow-y-auto p-2 no-scrollbar">
            <div class="flex flex-col gap-1">
                @php
                    $notifications = [
                        ['title' => 'New task assigned', 'desc' => 'You have been assigned to the "Port to Laravel" project.', 'time' => '2 hours ago', 'icon' => 'bg-blue-500', 'unread' => true],
                        ['title' => 'Server Alert', 'desc' => 'High memory usage detected on main production server.', 'time' => '5 hours ago', 'icon' => 'bg-amber-500', 'unread' => true],
                        ['title' => 'Deployment Success', 'desc' => 'Version 2.4.0 has been successfully deployed to staging.', 'time' => 'Yesterday', 'icon' => 'bg-emerald-500', 'unread' => false],
                    ];
                @endphp
                @foreach($notifications as $notif)
                <div class="flex items-start gap-3 rounded-xl p-3 transition-all hover:bg-accent cursor-pointer group relative">
                    <div class="mt-1.5 h-2 w-2 rounded-full {{ $notif['icon'] }} {{ $notif['unread'] ? 'ring-4 ring-'.$notif['icon'].'/20 shadow-[0_0_10px_rgba(59,130,246,0.5)]' : 'opacity-40' }}"></div>
                    <div class="grid gap-1">
                        <p class="text-sm font-bold leading-none flex items-center justify-between">
                            {{ $notif['title'] }}
                            @if($notif['unread'])
                            <span class="text-[9px] bg-primary/10 text-primary px-1.5 py-0.5 rounded-full uppercase font-black">New</span>
                            @endif
                        </p>
                        <p class="text-xs text-muted-foreground leading-snug line-clamp-2">{{ $notif['desc'] }}</p>
                        <p class="text-[10px] text-muted-foreground/60 mt-1 font-medium">{{ $notif['time'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="p-3 border-t bg-muted/10">
            <x-ui.button variant="ghost" class="w-full text-xs font-bold uppercase tracking-widest h-9 hover:bg-primary hover:text-primary-foreground transition-all">Mark all as read</x-ui.button>
        </div>
    </div>
</div>

<style>
@keyframes shake {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(10deg); }
  75% { transform: rotate(-10deg); }
}
.group:hover .group-hover\:shake {
  animation: shake 0.5s ease-in-out infinite;
}
</style>
