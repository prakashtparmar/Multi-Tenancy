@extends('layouts.app')

@section('content')
    <div class="flex flex-1 flex-col space-y-8 p-6 md:p-8 max-w-7xl mx-auto w-full animate-in fade-in duration-500">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-1">
                <h1
                    class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                    Return Processing (Central)
                </h1>
                <p class="text-muted-foreground text-sm">
                    Receive returned items and update inventory.
                </p>
            </div>
            <div>
                <a href="{{ route('central.processing.orders.index') }}"
                    class="px-4 py-2 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/80 transition-colors">
                    Back to Orders
                </a>
            </div>
        </div>

        <!-- Returns Table -->
        <div
            class="rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl shadow-lg shadow-black/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-muted/50 text-muted-foreground font-medium border-b border-border/40">
                        <tr>
                            <th class="px-6 py-4">RMA #</th>
                            <th class="px-6 py-4">Order #</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Items</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/30">
                        @forelse($returns as $return)
                            <tr class="group hover:bg-muted/30 transition-colors">
                                <td class="px-6 py-4 font-mono font-medium text-foreground">
                                    {{ $return->rma_number }}
                                    <div class="text-xs text-muted-foreground">{{ $return->created_at->format('M d, H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-muted-foreground">
                                    {{ $return->order->order_number }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $return->order->customer->first_name }}
                                        {{ $return->order->customer->last_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold border 
                                                {{ $return->status === 'approved' ? 'bg-blue-500/10 text-blue-600 border-blue-500/20' : '' }}
                                                {{ $return->status === 'received' ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : '' }}
                                            ">
                                        {{ ucfirst($return->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <ul class="list-disc pl-4 text-xs text-muted-foreground">
                                        @foreach($return->items as $item)
                                            <li>{{ $item->quantity }}x {{ $item->product->name }} ({{ $item->condition }})</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($return->status === 'approved')
                                        <form action="{{ route('central.processing.returns.receive', $return) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary text-primary-foreground text-xs rounded-md hover:bg-primary/90 transition-colors">
                                                Receive Items
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-muted-foreground">Processed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                    No approved returns waiting for receipt.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-border/40">
                {{ $returns->links() }}
            </div>
        </div>
    </div>
@endsection