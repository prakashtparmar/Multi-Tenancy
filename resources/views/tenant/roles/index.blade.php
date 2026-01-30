@extends('layouts.app')

@section('content')
<div class="flex flex-1 flex-col animate-in fade-in duration-500">
    
    <!-- Header (Glassmorphic) -->
    <div class="relative z-30 flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-6 py-6 lg:px-8 border-b border-border/40 bg-background/60 backdrop-blur-xl supports-[backdrop-filter]:bg-background/40">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent font-heading">
                Roles & Access
            </h1>
            <p class="text-sm text-muted-foreground font-medium">
                Define and manage security checkpoints for your team.
            </p>
        </div>
        
        <a href="{{ $createUrl }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            <span>Create New Role</span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="p-6 lg:p-8 space-y-8">
        
        <!-- Roles Grid -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($roles as $role)
            <div class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-border/50 bg-card/50 backdrop-blur-sm p-6 shadow-sm transition-all duration-300 hover:shadow-xl hover:border-primary/20 hover:-translate-y-1">
                
                <!-- Background Gradient Glow -->
                <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                <div class="relative space-y-6">
                    <!-- Card Header -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <!-- Role Icon/Avatar -->
                            <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-primary/10 to-primary/5 border border-primary/10 flex items-center justify-center shrink-0 shadow-inner">
                                <span class="text-lg font-bold text-primary">{{ substr($role->name, 0, 1) }}</span>
                            </div>
                            
                            <div class="space-y-0.5">
                                <h3 class="font-bold text-lg leading-none tracking-tight text-foreground group-hover:text-primary transition-colors duration-200">{{ ucfirst($role->name) }}</h3>
                                <div class="flex items-center text-xs text-muted-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    {{ $role->users_count }} members
                                </div>
                            </div>
                        </div>

                        <!-- Actions Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                             <button @click="open = !open" class="opacity-0 group-hover:opacity-100 transition-opacity h-8 w-8 inline-flex items-center justify-center rounded-lg hover:bg-secondary text-muted-foreground hover:text-foreground">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                             </button>
                             
                             <div x-show="open" 
                                  x-transition:enter="transition ease-out duration-100"
                                  x-transition:enter-start="opacity-0 scale-95"
                                  x-transition:enter-end="opacity-100 scale-100"
                                  x-transition:leave="transition ease-in duration-75"
                                  class="absolute right-0 top-full z-50 mt-2 min-w-[160px] rounded-xl border border-border/50 bg-popover/95 p-1 text-popover-foreground shadow-xl backdrop-blur-xl"
                                  style="display: none;">
                                @if($role->name !== 'super_admin') <!-- Guard critical roles -->
                                    <form action="{{ route($routePrefix . '.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Delete this role? Users attached will lose these permissions.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs font-medium text-destructive hover:bg-destructive/10 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                            Delete Role
                                        </button>
                                    </form>
                                @else
                                    <div class="px-2 py-1.5 text-xs text-muted-foreground italic text-center">System Role</div>
                                @endif
                             </div>
                        </div>
                    </div>

                    <!-- Permissions Preview -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            <span>Capabilities</span>
                            <span class="bg-secondary/50 px-1.5 py-0.5 rounded text-[10px]">{{ $role->permissions->count() }} Total</span>
                        </div>
                        
                        <div class="flex flex-wrap gap-1.5 min-h-[50px]">
                             @forelse($role->permissions->take(6) as $permission)
                                <span class="inline-flex items-center rounded-md bg-secondary/40 border border-border/30 px-2 py-1 text-[11px] font-medium text-secondary-foreground transition-colors group-hover:border-primary/20 group-hover:bg-primary/5">
                                    {{ $permission->name }}
                                </span>
                            @empty
                                <span class="text-xs text-muted-foreground/60 italic py-1">No special permissions assigned.</span>
                            @endforelse
                            @if($role->permissions->count() > 6)
                                <span class="inline-flex items-center rounded-md bg-secondary/60 px-2 py-1 text-[11px] font-medium text-secondary-foreground">
                                    +{{ $role->permissions->count() - 6 }} more
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="relative mt-8 pt-4 border-t border-border/40">
                    @php
                        $editUrl = route($routePrefix . '.roles.edit', $role);
                        $currentHost = Request::getHttpHost();
                        $editUrl = preg_replace('#^https?://[^/]+#', (Request::secure() ? 'https://' : 'http://') . $currentHost, $editUrl);
                    @endphp
                    <a href="{{ $editUrl }}" class="w-full inline-flex items-center justify-center rounded-xl bg-background border border-input py-2 text-sm font-medium shadow-sm transition-all hover:bg-accent hover:text-accent-foreground hover:border-primary/30 group-hover:shadow-md">
                        Manage Configurations
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 transition-transform group-hover:translate-x-1"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full py-16 text-center border-2 border-dashed border-border/50 rounded-3xl bg-muted/10">
                 <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-muted/50 mb-4 ring-1 ring-border/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-8 text-muted-foreground"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                 </div>
                 <h3 class="text-lg font-bold text-foreground">No roles configured</h3>
                 <p class="mt-2 text-sm text-muted-foreground max-w-sm mx-auto">Create roles to define what users are allowed to access and perform within the system.</p>
                 <a href="{{ $createUrl }}" class="mt-6 inline-flex items-center text-primary font-medium hover:underline">
                     Create your first role &rarr;
                 </a>
            </div>
            @endforelse
        </div>

        @if($roles->hasPages())
        <div class="border-t border-border/40 pt-6">
            {{ $roles->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
