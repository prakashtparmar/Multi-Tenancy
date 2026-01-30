@extends('layouts.app')

@section('content')
<div class="flex flex-1 flex-col space-y-8 p-6 md:p-8 max-w-2xl mx-auto w-full animate-in fade-in duration-500">
    
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ route($routePrefix . '.permissions.index') }}" class="group flex h-10 w-10 items-center justify-center rounded-xl bg-background border border-border/50 shadow-sm transition-all hover:bg-accent hover:text-accent-foreground hover:scale-105 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-hover:text-foreground transition-colors"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                    {{ isset($permission) ? 'Edit Permission' : 'Create Permission' }}
                </h1>
                <p class="text-muted-foreground text-sm">
                    Define granular access capabilities for the system.
                </p>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl shadow-lg shadow-black/5 overflow-hidden">
        
        <!-- Decoration Line -->
        <div class="h-1 w-full bg-gradient-to-r from-primary/20 via-primary/50 to-primary/20"></div>

        <form action="{{ isset($permission) ? route($routePrefix . '.permissions.update', $permission) : route($routePrefix . '.permissions.store') }}" 
              method="POST" 
              class="p-6 md:p-8 space-y-8">
            @csrf
            @if(isset($permission))
                @method('PUT')
            @endif
            
            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none text-foreground/80" for="name">
                        Permission Name <span class="text-destructive">*</span>
                    </label>
                    <div class="relative group">
                        <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        </span>
                        <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                               id="name" name="name" placeholder="e.g. content.create" required 
                               value="{{ old('name', $permission->name ?? '') }}">
                    </div>
                    <p class="text-[0.8rem] text-muted-foreground">
                        Recommended format: <code>resource.action</code> (e.g. <code>users.edit</code>, <code>reports.view</code>).
                    </p>
                    @error('name') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-border/40">
                <a href="{{ route($routePrefix . '.permissions.index') }}" class="inline-flex items-center justify-center rounded-xl bg-muted px-4 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted/80 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>{{ isset($permission) ? 'Save Permission' : 'Create Permission' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
