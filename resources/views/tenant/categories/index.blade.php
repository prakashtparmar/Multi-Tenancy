@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-heading tracking-tight text-foreground">Categories</h1>
            <p class="text-muted-foreground text-sm mt-1">Manage your product categories and hierarchy.</p>
        </div>
        
        <x-ui.button href="{{ route('tenant.categories.create') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Add Category
        </x-ui.button>
    </div>

    <!-- Stats Grid (Optional) -->
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-border/50 bg-card/50 backdrop-blur-sm p-4 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-2 bg-primary/10 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><rect width="18" height="14" x="3" y="6" rx="2"/></svg>
                </div>
                <div>
                    <div class="text-sm font-medium text-muted-foreground">Total Categories</div>
                    <div class="text-2xl font-bold">{{ $categories->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="rounded-xl border border-border/50 bg-card/50 backdrop-blur-sm shadow-sm overflow-hidden">
        <div class="p-4 border-b border-border/50 flex gap-4">
            <div class="relative max-w-sm w-full">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" placeholder="Search categories..." class="w-full h-9 pl-9 rounded-lg bg-background/50 border border-border/50 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-muted-foreground uppercase bg-muted/30">
                    <tr>
                        <th class="px-6 py-3 font-medium">Name & Description</th>
                        <th class="px-6 py-3 font-medium">Parent</th>
                        <th class="px-6 py-3 font-medium">Products</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/50">
                    @forelse($categories as $category)
                    <tr class="hover:bg-muted/10 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="font-medium text-foreground">{{ $category->name }}</div>
                            <div class="text-xs text-muted-foreground mt-0.5 truncate max-w-xs">{{ $category->description ?? 'No description' }}</div>
                        </td>
                         <td class="px-6 py-4">
                            @if($category->parent)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-muted/50 text-xs font-medium text-muted-foreground border border-border/50">
                                    {{ $category->parent->name }}
                                </span>
                            @else
                                <span class="text-muted-foreground/50 text-xs italic">Root Category</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 text-xs font-medium">
                                {{ $category->products_count }} items
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($category->is_active ?? true)
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
                                <a href="{{ route('tenant.categories.edit', $category) }}" class="p-2 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground transition-all" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                </a>
                                <form action="{{ route('tenant.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');" class="inline">
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
                        <td colspan="5" class="px-6 py-12 text-center text-muted-foreground">
                            <div class="flex flex-col items-center gap-2">
                                <div class="p-3 bg-muted/30 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><rect width="18" height="14" x="3" y="6" rx="2"/></svg>
                                </div>
                                <p class="font-medium">No categories found</p>
                                <p class="text-xs">Get started by adding your first category.</p>
                                <div class="mt-2">
                                     <x-ui.button href="{{ route('tenant.categories.create') }}" variant="outline" size="sm">
                                        Add Category
                                    </x-ui.button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($categories->hasPages())
        <div class="px-6 py-4 border-t border-border/50">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
