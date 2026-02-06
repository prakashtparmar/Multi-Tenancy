@extends('layouts.app')

@section('title', 'System Reports')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">System Reports</h1>

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('central.reports.export') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Report Type -->
                <div>
                    <label for="report_type" class="block text-sm font-medium text-gray-700 mb-2">Select Report Type</label>
                    <select name="report_type" id="report_type" required
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                        <option value="" disabled selected>-- Choose a Report --</option>
                        <option value="orders">Orders Report</option>
                        <option value="inventory">Inventory / Stock Report</option>
                        <option value="customers">Customers Report</option>
                        <option value="interactions">Customer Interactions Report</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Choose the type of data you wish to export.</p>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date
                            (Optional)</label>
                        <input type="date" name="start_date" id="start_date"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date
                            (Optional)</label>
                        <input type="date" name="end_date" id="end_date"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                    </div>
                </div>

                <!-- Format -->
                <div>
                    <label for="format" class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" class="form-radio text-indigo-600" name="format" value="csv" checked>
                            <span class="ml-2">CSV</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" class="form-radio text-indigo-600" name="format" value="xlsx">
                            <span class="ml-2">Excel (.xlsx)</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" class="form-radio text-indigo-600" name="format" value="pdf">
                            <span class="ml-2">PDF</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Generate & Download Report
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection