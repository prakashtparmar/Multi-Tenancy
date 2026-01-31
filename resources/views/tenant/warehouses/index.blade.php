<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Central Warehouses') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-lg font-bold">Warehouses</h3>
                        <a href="{{ route('tenant.warehouses.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Add Warehouse
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @forelse($warehouses as $wh)
                            <div class="border rounded-lg p-4 shadow-sm hover:shadow-md transition">
                                <h4 class="font-bold text-lg mb-2">{{ $wh->name }}</h4>
                                <p class="text-gray-600 text-sm mb-1">Code: <span class="font-mono bg-gray-100 px-1">{{ $wh->code }}</span></p>
                                <p class="text-gray-600 text-sm mb-4">{{ $wh->address ?? 'No address' }}</p>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs {{ $wh->is_active ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $wh->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <a href="{{ route('tenant.warehouses.edit', $wh) }}" class="text-blue-600 text-sm hover:underline">Edit</a>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 text-center text-gray-500 py-8">
                                No warehouses found.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
