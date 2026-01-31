@extends('layouts.app')

@section('content')
<div id="warehouses-page-wrapper" class="flex flex-1 flex-col space-y-8 p-8 animate-in fade-in duration-500">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                {{ __('Warehouses') }}
            </h1>
            <p class="text-muted-foreground text-sm">Manage inventory storage locations.</p>
        </div>
        
        <div class="flex items-center gap-3">
             <a href="{{ route('central.warehouses.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                <span>Add Warehouse</span>
            </a>
        </div>
    </div>

    <!-- Grid Layout for Warehouses -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($warehouses as $wh)
            <div class="group relative overflow-hidden rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:border-primary/20 hover:-translate-y-1">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg text-foreground">{{ $wh->name }}</h4>
                            <span class="text-xs font-mono text-muted-foreground bg-muted/50 px-1.5 py-0.5 rounded">{{ $wh->code }}</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-semibold {{ $wh->is_active ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-destructive/10 text-destructive border border-destructive/20' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $wh->is_active ? 'bg-emerald-500' : 'bg-destructive' }}"></span>
                        {{ $wh->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                
                <div class="space-y-4">
                    <div class="text-sm text-muted-foreground">
                        <p class="line-clamp-2">{{ $wh->address ?? 'No address provided' }}</p>
                    </div>
                    
                    <div class="pt-4 border-t border-border/40 flex justify-end">
                        <a href="{{ route('central.warehouses.edit', $wh) }}" class="text-sm font-medium text-primary hover:underline underline-offset-4 decoration-primary/30 hover:decoration-primary transition-all">
                            Edit Configuration &rarr;
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full p-16 text-center rounded-2xl border border-border/40 bg-muted/10 border-dashed">
                <div class="mx-auto h-12 w-12 text-muted-foreground/50 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4 8 4v14"/><path d="M17 21v-8H7v8"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-foreground">No warehouses configured</h3>
                <p class="text-muted-foreground">Get started by adding your first storage location.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
