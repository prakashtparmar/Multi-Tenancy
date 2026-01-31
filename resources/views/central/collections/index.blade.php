@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-heading tracking-tight text-foreground">Collections</h1>
            <p class="text-muted-foreground text-sm mt-1">Group products into collections for marketing campaigns.</p>
        </div>
        
        <x-ui.button href="{{ route('central.collections.create') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Add Collection
        </x-ui.button>
    </div>

    <!-- Data Table -->
    <div class="rounded-xl border border-border/50 bg-card/50 backdrop-blur-sm shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border/50 bg-muted/20">
             <div class="relative max-w-sm w-full">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" placeholder="Search collections..." class="w-full h-9 pl-9 rounded-lg bg-background border border-border/50 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-muted-foreground uppercase bg-muted/40">
                    <tr>
                        <th class="px-6 py-3 font-medium">Name & Description</th>
                        <th class="px-6 py-3 font-medium">Slug</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/50">
                    @forelse($collections as $collection)
                    <tr class="hover:bg-muted/10 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="font-medium text-foreground">{{ $collection->name }}</div>
                            @if($collection->description)
                                <div class="text-xs text-muted-foreground mt-0.5 truncate max-w-xs">{{ $collection->description }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-muted-foreground font-mono text-xs">
                           {{ $collection->slug }}
                        </td>
                        <td class="px-6 py-4">
                            @if($collection->is_active ?? true)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-medium border border-emerald-500/10">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-red-500/10 text-red-600 dark:text-red-400 text-xs font-medium border border-red-500/10">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                             <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('central.collections.edit', $collection) }}" class="p-2 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground transition-all" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                </a>
                                <form action="{{ route('central.collections.destroy', $collection) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this collection?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg hover:bg-red-500/10 text-muted-foreground hover:text-red-600 transition-all" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-muted-foreground">
                            <div class="flex flex-col items-center gap-2">
                                <div class="p-3 bg-muted/30 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 6 4 14"/><path d="M12 6v14"/><path d="M8 8v12"/><path d="M4 4v16"/></svg>
                                </div>
                                <p class="font-medium">No collections found</p>
                                <p class="text-xs">Create your first collection to group products.</p>
                                <div class="mt-2">
                                     <x-ui.button href="{{ route('central.collections.create') }}" variant="outline" size="sm">
                                        Add Collection
                                    </x-ui.button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($collections->hasPages())
        <div class="px-6 py-4 border-t border-border/50">
            {{ $collections->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
