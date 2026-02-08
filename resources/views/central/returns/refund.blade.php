<x-app-layout>
    <x-slot name="header">
        <h2 class="font-heading font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Process Refund') }} <span class="text-gray-400">#{{ $orderReturn->rma_number }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">

                <form action="{{ route('central.returns.refund.store', $orderReturn) }}" method="POST">
                    @csrf

                    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-900">Refund Summary</h3>
                        <p class="text-sm text-gray-500">Finalize the refund amount for this return.</p>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Summary -->
                        <div class="bg-blue-50/50 rounded-lg p-4 border border-blue-100">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-600">Original Order Total:</span>
                                <span
                                    class="font-semibold text-gray-900">${{ number_format($orderReturn->order->total_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-blue-800 font-medium">Suggested Refund (Based on
                                    Items):</span>
                                <!-- Simplified calculation for demo -->
                                <span class="font-bold text-blue-900 text-lg">
                                    ${{ number_format($orderReturn->items->sum(fn($i) => $i->quantity_received * $i->product->price), 2) }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Refund Amount ($)</label>
                            <input type="number" name="refunded_amount" step="0.01"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-lg font-mono placeholder-gray-400"
                                placeholder="0.00" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                            <textarea name="notes" rows="3"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm placeholder-gray-400"></textarea>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-100 gap-3">
                            <a href="{{ route('central.returns.show', $orderReturn) }}"
                                class="inline-flex justify-center rounded-lg border border-gray-300 bg-white py-2.5 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none transition-all">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex justify-center rounded-lg border border-transparent bg-purple-600 py-2.5 px-6 text-sm font-medium text-white shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all">
                                Issue Refund
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>