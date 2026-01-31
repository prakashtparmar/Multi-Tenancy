<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Warehouse') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('central.warehouses.store') }}" method="POST" class="p-6 text-gray-900">
                    @csrf
                    <div class="grid grid-cols-1 gap-6 max-w-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Warehouse Name</label>
                            <input type="text" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Code (Unique)</label>
                            <input type="text" name="code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea name="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" rows="3"></textarea>
                        </div>
                        <div>
                             <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                <span class="ml-2">Active</span>
                            </label>
                        </div>
                        <div class="flex justify-end pt-4">
                            <button type="submit" class="bg-gray-800 text-white font-bold py-2 px-4 rounded hover:bg-gray-700">
                                Save Warehouse
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
