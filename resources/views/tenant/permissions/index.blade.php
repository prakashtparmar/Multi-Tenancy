@extends('layouts.app')

@section('content')
<div class="flex flex-1 flex-col animate-in fade-in duration-500">
    
    <!-- Header (Glassmorphic) -->
    <div class="relative z-30 flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-6 py-6 lg:px-8 border-b border-border/40 bg-background/60 backdrop-blur-xl supports-[backdrop-filter]:bg-background/40">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent font-heading">
                Platform Permissions
            </h1>
            <p class="text-sm text-muted-foreground font-medium">
                Manage granular access controls and capability definitions.
            </p>
        </div>
        
        <a href="{{ route($routePrefix . '.permissions.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            <span>Add Capability</span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="p-6 lg:p-8 space-y-8">
        
        <!-- Permissions Table Card -->
        <div class="rounded-2xl border border-border/50 bg-card/50 backdrop-blur-xl shadow-sm overflow-hidden">
            
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b border-border/40 transition-colors hover:bg-muted/30 bg-muted/20">
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px] w-1/3">Permission Name</th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px]">Assigned Roles</th>
                            <th class="h-12 px-6 text-left align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px] w-[120px]">Guard</th>
                            <th class="h-12 px-6 text-right align-middle font-medium text-muted-foreground/70 uppercase tracking-wider text-[11px] w-[100px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0 text-sm">
                        @forelse($permissions as $permission)
                        <tr class="group border-b border-border/40 transition-all duration-200 hover:bg-muted/40 hover:shadow-inner">
                            <td class="p-6 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-secondary/50 border border-border/50 flex items-center justify-center text-muted-foreground">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                                    </div>
                                    <span class="font-semibold text-foreground tracking-tight">{{ $permission->name }}</span>
                                </div>
                            </td>
                            <td class="p-6 align-middle">
                                <div class="flex flex-wrap gap-1.5">
                                     @forelse($permission->roles->take(4) as $role)
                                        <span class="inline-flex items-center rounded-md border border-border/40 bg-background px-2 py-0.5 text-xs font-medium text-muted-foreground shadow-sm">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-muted-foreground/40 italic">Unassigned</span>
                                    @endforelse
                                    @if($permission->roles->count() > 4)
                                        <span class="inline-flex items-center rounded-md bg-secondary/50 px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">
                                            +{{ $permission->roles->count() - 4 }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-6 align-middle">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-mono text-muted-foreground bg-muted/50 border border-border/50">
                                    {{ $permission->guard_name }}
                                </span>
                            </td>
                            <td class="p-6 align-middle text-right">
                                 <div class="flex items-center justify-end gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route($routePrefix . '.permissions.edit', $permission) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-all hover:text-foreground hover:bg-accent focus:outline-none focus:ring-2 focus:ring-ring">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </a>
                                    <form action="{{ route($routePrefix . '.permissions.destroy', $permission) }}" method="POST" onsubmit="return confirm('Delete this permission?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-all hover:text-destructive hover:bg-destructive/10 focus:outline-none focus:ring-2 focus:ring-destructive/20">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-16 text-center">
                                <div class="flex flex-col items-center justify-center text-muted-foreground/50">
                                    <div class="h-12 w-12 rounded-xl bg-muted/30 flex items-center justify-center mb-4 ring-1 ring-border/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-foreground">No permissions found</p>
                                    <p class="text-xs mt-1 max-w-xs mx-auto">Start by adding a new capability to the system.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($permissions->hasPages())
            <div class="border-t border-border/40 p-4 bg-muted/20">
                {{ $permissions->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
