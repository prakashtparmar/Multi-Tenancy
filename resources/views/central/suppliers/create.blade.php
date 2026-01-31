<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Supplier') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('central.suppliers.store') }}" method="POST" class="p-6 text-gray-900">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company Name</label>
                            <input type="text" name="company_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                            <input type="text" name="contact_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Currency</label>
                            <input type="text" name="currency" value="USD" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        
                        <!-- Agri Profile -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Farm Size (Acres)</label>
                            <input type="number" step="0.01" name="farm_size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Primary Crop</label>
                            <input type="text" name="primary_crop" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="e.g. Wheat, Corn">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Verification Status</label>
                            <select name="verification_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="unverified">Unverified</option>
                                <option value="verified">Verified</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                             <input type="checkbox" name="is_active" value="1" id="is_active" checked class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                             <label for="is_active" class="ml-2 block text-sm text-gray-900">Active Supplier</label>
                        </div>
                    
                    <div class="flex justify-end pt-6">
                        <button type="submit" class="bg-gray-800 text-white font-bold py-2 px-4 rounded hover:bg-gray-700">
                            Save Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
