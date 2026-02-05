@extends('layouts.app')

@section('content')
<div id="invoices-page-wrapper" class="flex flex-1 flex-col space-y-8 p-8 animate-in fade-in duration-500">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                Invoices
            </h1>
            <p class="text-muted-foreground text-sm">
                Track customer payments and invoice statuses.
            </p>
        </div>

        <div class="flex items-center p-1 bg-muted/50 rounded-xl border border-border/50 backdrop-blur-sm">
            <a href="{{ route('central.invoices.index') }}"
               class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200
               {{ request('status') === null ? 'bg-background text-foreground shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-foreground hover:bg-background/50' }}">
                All Invoices
            </a>

            <div class="w-px h-4 bg-border/40 mx-1"></div>

            <a href="{{ route('central.invoices.index', ['status' => 'paid']) }}"
               class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200
               {{ request('status') === 'paid' ? 'bg-background text-emerald-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-emerald-600 hover:bg-background/50' }}">
                Paid
            </a>

            <div class="w-px h-4 bg-border/40 mx-1"></div>

            <a href="{{ route('central.invoices.index', ['status' => 'pending']) }}"
               class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200
               {{ request('status') === 'pending' ? 'bg-background text-amber-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-amber-600 hover:bg-background/50' }}">
                Pending
            </a>

            <div class="w-px h-4 bg-border/40 mx-1"></div>

            <a href="{{ route('central.invoices.index', ['status' => 'overdue']) }}"
               class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200
               {{ request('status') === 'overdue' ? 'bg-background text-red-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-red-600 hover:bg-background/50' }}">
                Overdue
            </a>
        </div>
    </div>

    <div id="invoices-table-container" x-data="{ selected: [] }">

        <div class="rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl shadow-sm overflow-hidden relative">

            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead>
                        <tr class="border-b border-border/40 bg-muted/20">
                            <th class="h-12 w-[50px] px-6"></th>
                            <th class="h-12 px-6 text-left text-xs font-medium uppercase text-muted-foreground">Invoice #</th>
                            <th class="h-12 px-6 text-left text-xs font-medium uppercase text-muted-foreground">Customer</th>
                            <th class="h-12 px-6 text-left text-xs font-medium uppercase text-muted-foreground">Due Date</th>
                            <th class="h-12 px-6 text-left text-xs font-medium uppercase text-muted-foreground">Amount</th>
                            <th class="h-12 px-6 text-left text-xs font-medium uppercase text-muted-foreground">Status</th>
                            <th class="h-12 px-6 text-right text-xs font-medium uppercase text-muted-foreground">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr class="border-b border-border/40 hover:bg-muted/40">
                                <td class="p-6"></td>

                                <td class="p-6">
                                    <div class="font-semibold">#{{ $invoice->invoice_number }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ $invoice->order ? 'Ord #' . $invoice->order->order_number : 'Manual' }}
                                    </div>
                                </td>

                                <td class="p-6">
                                    {{ $invoice->order->customer->name ?? 'Unknown' }}
                                </td>

                                <td class="p-6 text-xs text-muted-foreground">
                                    {{ $invoice->due_date?->format('M d, Y') ?? '-' }}
                                </td>

                                <td class="p-6 font-medium">
                                    ₹ {{ number_format($invoice->total_amount, 2) }}
                                </td>

                                <td class="p-6">
                                    @if($invoice->status === 'paid')
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700">Paid</span>
                                    @elseif($invoice->status === 'overdue')
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">Overdue</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700">Pending</span>
                                    @endif
                                </td>

                                <td class="p-6 text-right">
                                    <div class="relative inline-block" x-data="{ open: false }">
                                        <button @click="open = !open"
                                                class="h-8 w-8 rounded-lg hover:bg-muted flex items-center justify-center">
                                            ⋮
                                        </button>

                                        <div x-show="open" @click.away="open = false"
                                             class="absolute right-0 mt-2 w-44 rounded-lg border bg-white shadow-lg">
                                            <a href="{{ route('central.invoices.show', $invoice) }}"
                                               class="block px-4 py-2 text-sm hover:bg-muted">
                                                View Details
                                            </a>
                                            <a href="{{ route('central.invoices.pdf', $invoice) }}"
                                               class="block px-4 py-2 text-sm hover:bg-muted">
                                                Download Invoice PDF
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-16 text-center text-muted-foreground">
                                    No invoices found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="border-t border-border/40 p-4 bg-muted/20">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
