@extends('layouts.app')

@section('content')
<div class="flex flex-1 flex-col p-6 lg:p-8 space-y-8 animate-in fade-in duration-500">
    
    <!-- Hero Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-heading font-extrabold tracking-tight text-foreground">Workspaces</h1>
            <p class="text-muted-foreground mt-1">Manage and access all your organization scenarios.</p>
        </div>
        <div class="flex items-center gap-3">
             <div class="relative group">
                <input type="text" placeholder="Search workspaces..." class="pl-10 pr-4 py-2 rounded-xl border border-input bg-background/50 backdrop-blur-sm focus:ring-2 focus:ring-primary/20 focus:border-primary w-64 transition-all outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground group-hover:text-primary transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
             </div>
             <a href="{{ config('app.url') }}/tenants/create" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 transition-all hover:scale-105 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Create Workspace
            </a>
        </div>
    </div>

    <!-- Tenants Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($tenants as $tenant)
        <div class="group relative flex flex-col rounded-3xl border border-border/50 bg-card p-6 shadow-sm hover:shadow-xl hover:border-primary/20 transition-all duration-300 hover:-translate-y-1">
            
            <div class="flex items-start justify-between mb-4">
                <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-primary/10 to-purple-500/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform duration-300">
                    <span class="font-heading font-bold text-lg">{{ strtoupper(substr($tenant->id, 0, 1)) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <form action="/tenants/{{ $tenant->id }}/toggle-status" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset transition-all cursor-pointer hover:opacity-80
                            {{ $tenant->status === 'active' 
                                ? 'bg-emerald-500/10 text-emerald-600 ring-emerald-500/20' 
                                : 'bg-zinc-500/10 text-zinc-600 ring-zinc-500/20' 
                            }}">
                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full {{ $tenant->status === 'active' ? 'bg-emerald-500' : 'bg-zinc-500' }}"></span>
                            {{ ucfirst($tenant->status) }}
                        </button>
                    </form>
                    <button class="text-muted-foreground hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                    </button>
                </div>

            </div>

            <div class="mb-6">
                <h3 class="text-xl font-bold font-heading mb-1 text-foreground group-hover:text-primary transition-colors">{{ ucfirst($tenant->id) }}</h3>
                <div class="flex flex-col gap-1 text-xs text-muted-foreground">
                    @foreach($tenant->domains as $domain)
                    <div class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        <span class="font-mono">{{ $domain->domain }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-auto pt-4 border-t border-border/50 flex items-center justify-between">
                <div class="flex -space-x-2 overflow-hidden">
                    <div class="inline-block h-6 w-6 rounded-full ring-2 ring-card bg-gray-200"></div>
                    <div class="inline-block h-6 w-6 rounded-full ring-2 ring-card bg-gray-300"></div>
                    <div class="inline-block h-6 w-6 rounded-full ring-2 ring-card bg-gray-400"></div>
                </div>
                
                @if($tenant->domains->first())
                <a href="http://{{ $tenant->domains->first()->domain }}/login" target="_blank" class="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:text-primary/80 transition-colors">
                    Access
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 group-hover:translate-x-1 transition-transform"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
                @else
                <button disabled class="inline-flex items-center gap-1 text-sm font-semibold text-muted-foreground/50 cursor-not-allowed">
                    No Domain
                </button>
                @endif
            </div>
            
        </div>
        @endforeach

        <!-- Add New Placeholder -->
            <a href="{{ config('app.url') }}/tenants/create" class="group relative flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-border/50 bg-card/30 p-6 hover:bg-card/50 hover:border-primary/50 transition-all duration-300 min-h-[200px]">
            <div class="h-16 w-16 rounded-full bg-muted flex items-center justify-center mb-4 group-hover:bg-primary/10 group-hover:scale-110 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-muted-foreground group-hover:text-primary"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            </div>
            <h3 class="font-heading font-semibold text-foreground">Create New Workspace</h3>
            <p class="text-sm text-muted-foreground mt-1">Setup density, database & domain.</p>
        </a>
    </div>
</div>
@endsection
