<x-app-layout>
    <div class="min-h-screen bg-gray-50/50 py-12" x-data="{ 
        tab: 'basic',
        taxClassId: '{{ old('tax_class_id') }}',
        taxRate: {{ old('tax_rate', 0) ?: 0 }},
        taxClasses: [
            @foreach($taxClasses as $tc)
            { 
                id: '{{ $tc->id }}', 
                rate: {{ $tc->rates->first() ? $tc->rates->first()->rate : 0 }} 
            },
            @endforeach
        ],
        updateTax() {
            const selected = this.taxClasses.find(tc => tc.id == this.taxClassId);
            this.taxRate = selected ? selected.rate : 0;
        }
    }" x-init="updateTax()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900">
                        {{ __('Create Central Product') }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">Add a new master product to the central catalog.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        Cancel
                    </a>
                </div>
            </div>

            <form action="{{ route('central.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    
                    <!-- Left Sidebar - Navigation Pills -->
                    <div class="lg:col-span-1">
                        <nav class="space-y-1 bg-white p-2 rounded-xl shadow-sm border border-gray-100 sticky top-6">
                            @foreach([
                                'basic' => ['label' => 'Basic Info', 'icon' => 'heroicon-o-information-circle'],
                                'pricing' => ['label' => 'Pricing & Stock', 'icon' => 'heroicon-o-currency-dollar'],
                                'agri' => ['label' => 'Agriculture Details', 'icon' => 'heroicon-o-beaker'],
                                'seo' => ['label' => 'SEO & Settings', 'icon' => 'heroicon-o-globe-alt'],
                                'media' => ['label' => 'Media', 'icon' => 'heroicon-o-photo'],
                            ] as $key => $item)
                            <button type="button" 
                                @click="tab = '{{ $key }}'"
                                :class="{ 'bg-indigo-50 text-indigo-700 font-semibold ring-1 ring-indigo-200': tab === '{{ $key }}', 'text-gray-600 hover:bg-gray-50 hover:text-gray-900': tab !== '{{ $key }}' }"
                                class="group flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out">
                                <span :class="{ 'text-indigo-500': tab === '{{ $key }}', 'text-gray-400 group-hover:text-gray-500': tab !== '{{ $key }}' }" class="mr-3 flex-shrink-0 h-5 w-5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($key === 'basic') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        @elseif($key === 'pricing') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        @elseif($key === 'agri') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                        @elseif($key === 'seo') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                        @elseif($key === 'media') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path> @endif
                                    </svg>
                                </span>
                                {{ $item['label'] }}
                            </button>
                            @endforeach
                        </nav>
                        
                        <div class="mt-6 hidden lg:block">
                            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5">
                                Save Product
                            </button>
                        </div>
                    </div>

                    <!-- Right Content Area -->
                    <div class="lg:col-span-3">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden min-h-[500px]">
                            
                            <!-- Basic Info Tab -->
                            <div x-show="tab === 'basic'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-6 sm:p-8 space-y-8">
                                <h3 class="text-lg font-semibold text-gray-900 border-b pb-4">Basic Information</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                                        <input type="text" name="name" required value="{{ old('name') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU <span class="text-xs text-gray-500 font-normal">(Unique)</span></label>
                                        <input type="text" name="sku" value="{{ old('sku') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Type <span class="text-red-500">*</span></label>
                                        <select name="type" required
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                            <option value="simple" {{ old('type') == 'simple' ? 'selected' : '' }}>Simple Product</option>
                                            <option value="variable" {{ old('type') == 'variable' ? 'selected' : '' }}>Variable Product</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Type <span class="text-red-500">*</span></label>
                                        <select name="unit_type" required
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                            @foreach(['kg' => 'Kilogram (kg)', 'g' => 'Gram (g)', 'l' => 'Liter (l)', 'ml' => 'Milliliter (ml)', 'piece' => 'Piece', 'pack' => 'Pack/Bundle', 'bottle' => 'Bottle', 'bag' => 'Bag'] as $val => $label)
                                                <option value="{{ $val }}" {{ old('unit_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Packing Size</label>
                                        <input type="text" name="packing_size" value="{{ old('packing_size') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                        <select name="category_id"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                        <select name="brand_id"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                            <option value="">Select Brand</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing & Stock Tab (With Tax Logic) -->
                            <div x-show="tab === 'pricing'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-6 sm:p-8 space-y-8" style="display: none;">
                                <h3 class="text-lg font-semibold text-gray-900 border-b pb-4">Pricing & Inventory</h3>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    
                                    <!-- Price Cards -->
                                    <div class="bg-indigo-50/50 rounded-xl p-4 border border-indigo-100">
                                        <label class="block text-sm font-medium text-indigo-900 mb-1">Selling Price <span class="text-red-500">*</span></label>
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-indigo-500 sm:text-sm font-bold">$</span>
                                            </div>
                                            <input type="number" step="0.01" name="price" required value="{{ old('price') }}"
                                                class="block w-full rounded-lg border-indigo-300 pl-8 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                                                placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price') }}"
                                                class="block w-full rounded-lg border-gray-300 pl-8 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 bg-white"
                                                placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">MRP</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" step="0.01" name="mrp" value="{{ old('mrp') }}"
                                                class="block w-full rounded-lg border-gray-300 pl-8 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 bg-white"
                                                placeholder="0.00">
                                        </div>
                                    </div>

                                    <!-- Tax Section (Dropdown) -->
                                    <div class="md:col-span-3 bg-white rounded-xl p-4 border border-gray-200 shadow-sm max-w-md mx-auto w-full">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tax Class</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <select name="tax_class_id" x-model="taxClassId" @change="updateTax()"
                                                class="block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-3">
                                                <option value="">None (0%)</option>
                                                @foreach($taxClasses as $tc)
                                                    <option value="{{ $tc->id }}" {{ old('tax_class_id') == $tc->id ? 'selected' : '' }}>
                                                        {{ $tc->name }} ({{ $tc->rates->first() ? $tc->rates->first()->rate : 0 }}%)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="hidden" name="tax_rate" :value="taxRate">

                                        <!-- Indian Tax Breakdown -->
                                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs" x-show="taxRate > 0" x-transition>
                                            <div class="bg-blue-50 text-blue-700 px-2 py-1 rounded border border-blue-100 text-center">
                                                <span class="block font-semibold">SGST</span>
                                                <span x-text="(taxRate / 2).toFixed(2) + '%'"></span>
                                            </div>
                                            <div class="bg-purple-50 text-purple-700 px-2 py-1 rounded border border-purple-100 text-center">
                                                <span class="block font-semibold">CGST</span>
                                                <span x-text="(taxRate / 2).toFixed(2) + '%'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">HSN Code</label>
                                        <input type="text" name="hsn_code" value="{{ old('hsn_code') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Is Taxable?</label>
                                        <select name="is_taxable"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                            <option value="1" {{ old('is_taxable') == '1' ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ old('is_taxable') == '0' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="border-t border-gray-100 pt-6">
                                    <h4 class="text-sm font-medium text-gray-900 mb-4 uppercase tracking-wider">Inventory Control</h4>
                                    
                                     <div class="flex items-center space-x-6 mb-6">
                                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-indigo-500 transition-all">
                                            <input type="radio" name="manage_stock" value="1" class="sr-only" checked>
                                            <span class="block text-sm font-medium text-gray-900">Track Stock</span>
                                        </label>

                                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-gray-400 transition-all opacity-70">
                                            <input type="radio" name="manage_stock" value="0" class="sr-only">
                                            <span class="block text-sm font-medium text-gray-900">Don't Track</span>
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <!-- Warehouse Opening Stock -->
                                        <div class="col-span-1 md:col-span-2 bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                                            <h5 class="text-sm font-semibold text-indigo-900 mb-3">Initial Stock Allocation</h5>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Warehouse</label>
                                                    <select name="warehouse_id"
                                                        class="block w-full rounded-lg border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-3">
                                                        <option value="">-- Select Warehouse --</option>
                                                        @foreach($warehouses as $warehouse)
                                                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                                {{ $warehouse->name }} ({{ $warehouse->code }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Opening Stock Qty</label>
                                                    <input type="number" step="0.001" name="opening_stock" value="{{ old('opening_stock', 0) }}"
                                                        class="block w-full rounded-lg border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-3"
                                                        placeholder="0.00">
                                                </div>
                                            </div>
                                            <p class="text-xs text-indigo-600 mt-2 flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                                This will create an initial stock entry in the selected warehouse.
                                            </p>
                                        </div>

                                        <!-- Alerts -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Order Qty</label>
                                            <input type="number" step="1" name="min_order_qty" value="{{ old('min_order_qty', 1) }}"
                                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Reorder Level</label>
                                            <input type="number" step="1" name="reorder_level" value="{{ old('reorder_level', 0) }}"
                                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Agri Tab -->
                            <div x-show="tab === 'agri'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-6 sm:p-8 space-y-8" style="display: none;">
                                <h3 class="text-lg font-semibold text-gray-900 border-b pb-4">Crop Science & Specifications</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Technical Name</label>
                                        <input type="text" name="technical_name" value="{{ old('technical_name') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Application Method</label>
                                        <input type="text" name="application_method" value="{{ old('application_method') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Harvest Date</label>
                                        <input type="date" name="harvest_date" value="{{ old('harvest_date') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                                        <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                </div>
                            </div>

                            <!-- SEO & Settings Tab -->
                            <div x-show="tab === 'seo'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-6 sm:p-8 space-y-8" style="display: none;">
                                <h3 class="text-lg font-semibold text-gray-900 border-b pb-4">SEO & Visibility</h3>

                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                                        <input type="text" name="meta_title" value="{{ old('meta_title') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                                        <input type="text" name="meta_description" value="{{ old('meta_description') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                </div>
                            </div>

                            <!-- Media Tab -->
                            <div x-show="tab === 'media'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-6 sm:p-8 space-y-8" style="display: none;">
                                <h3 class="text-lg font-semibold text-gray-900 border-b pb-4">Product Images</h3>
                                
                                <div class="space-y-6">
                                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Images (First will be main image)</label>
                                        <input type="file" name="images[]" accept="image/*" multiple
                                            class="block w-full text-sm text-gray-500
                                            file:mr-4 file:py-2.5 file:px-4
                                            file:rounded-full file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-indigo-50 file:text-indigo-700
                                            hover:file:bg-indigo-100">
                                        <p class="text-xs text-gray-500 mt-2">Select multiple images to create a gallery.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mobile Sticky Action -->
                        <div class="mt-8 lg:hidden">
                            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                                Save Product
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>