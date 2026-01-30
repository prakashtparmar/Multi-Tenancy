@extends('layouts.app')

@section('content')
<div class="p-6 lg:p-8 max-w-5xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-heading tracking-tight">Activity Logs</h1>
            <p class="text-muted-foreground text-sm">Audit trail of all system activities and events.</p>
        </div>
    </div>

    <!-- Timeline Activity Log -->
    <div class="relative pl-6 border-l border-border/40 space-y-8">
        
        @forelse($activities as $activity)
        <div class="relative group">
            <!-- Timeline Dot -->
            <div class="absolute -left-[29px] top-1.5 h-3.5 w-3.5 rounded-full border-2 border-background bg-muted-foreground/30 group-hover:bg-primary group-hover:scale-125 transition-all"></div>
            
            <div class="rounded-xl border border-border/40 bg-card p-4 shadow-sm group-hover:shadow-md transition-shadow">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        @if($activity->causer)
                            <div class="flex items-center gap-2">
                                <div class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary">
                                    {{ substr($activity->causer->name, 0, 2) }}
                                </div>
                                <span class="font-semibold text-sm">{{ $activity->causer->name }}</span>
                            </div>
                        @else
                            <span class="font-semibold text-sm italic text-muted-foreground">System</span>
                        @endif
                        
                        <span class="text-muted-foreground text-xs">•</span>
                        <span class="text-sm text-foreground">{{ $activity->description }}</span>
                    </div>
                    
                    <span class="text-xs text-muted-foreground whitespace-nowrap" title="{{ $activity->created_at }}">
                        {{ $activity->created_at->diffForHumans() }}
                    </span>
                </div>
                
                <div class="flex flex-col gap-2 text-xs text-muted-foreground bg-muted/20 p-2 rounded-md font-mono">
                    <div class="flex items-center gap-2">
                        <span class="uppercase tracking-wider opacity-70">Event:</span>
                        <span class="inline-flex items-center rounded-sm bg-background px-1.5 py-0.5 border border-border/50 text-foreground">
                            {{ $activity->event }}
                        </span>
                    </div>
                    
                    @if($activity->subject_type)
                    <div class="flex items-center gap-2">
                        <span class="uppercase tracking-wider opacity-70">Subject:</span>
                        <span>{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</span>
                    </div>
                    @endif
                    
                    @if($activity->properties && $activity->properties->count() > 0)
                    <div class="mt-1 border-t border-border/30 pt-1">
                        <span class="uppercase tracking-wider opacity-70 block mb-1">Changes:</span>
                        <pre class="overflow-x-auto whitespace-pre-wrap">{{ $activity->properties }}</pre>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="py-12 text-center text-muted-foreground italic">
            No activity logs found.
        </div>
        @endforelse

    </div>

    @if($activities->hasPages())
    <div class="pt-4">
        {{ $activities->links() }}
    </div>
    @endif
</div>
@endsection
