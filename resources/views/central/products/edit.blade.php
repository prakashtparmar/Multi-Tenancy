<x-app-layout>
    <div class="min-h-screen bg-gray-50/50 py-12" x-data="{ 
        tab: 'basic',
        taxClassId: '{{ old('tax_class_id', $product->tax_class_id) }}',
        taxRate: {{ old('tax_rate', $product->tax_rate) ?: 0 }},
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
                        {{ __('Edit Central Product') }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">Update global catalog details for <span class="font-semibold text-gray-900">{{ $product->name }}</span>.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('central.products.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        Cancel
                    </a>
                </div>
            </div>

            <form action="{{ route('central.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
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
                                Save Changes
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
                                        <input type="text" name="name" required value="{{ old('name', $product->name) }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU <span class="text-xs text-gray-500 font-normal">(Unique)</span></label>
                                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Type <span class="text-red-500">*</span></label>
                                        <select name="type" required
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                            <option value="simple" {{ old('type', $product->type) == 'simple' ? 'selected' : '' }}>Simple Product</option>
                                            <option value="variable" {{ old('type', $product->type) == 'variable' ? 'selected' : '' }}>Variable Product</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Type <span class="text-red-500">*</span></label>
                                        <select name="unit_type" required
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                            @foreach(['kg' => 'Kilogram (kg)', 'g' => 'Gram (g)', 'l' => 'Liter (l)', 'ml' => 'Milliliter (ml)', 'piece' => 'Piece', 'pack' => 'Pack/Bundle', 'bottle' => 'Bottle', 'bag' => 'Bag'] as $val => $label)
                                                <option value="{{ $val }}" {{ old('unit_type', $product->unit_type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Packing Size</label>
                                        <input type="text" name="packing_size" value="{{ old('packing_size', $product->packing_size) }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                        <select name="category_id"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                        <select name="brand_id"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                            <option value="">Select Brand</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
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
                                            <input type="number" step="0.01" name="price" required value="{{ old('price', $product->price) }}"
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
                                            <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}"
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
                                            <input type="number" step="0.01" name="mrp" value="{{ old('mrp', $product->mrp) }}"
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
                                                    <option value="{{ $tc->id }}" {{ old('tax_class_id', $product->tax_class_id) == $tc->id ? 'selected' : '' }}>
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
                                        <input type="text" name="hsn_code" value="{{ old('hsn_code', $product->hsn_code) }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Is Taxable?</label>
                                        <select name="is_taxable"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                            <option value="1" {{ old('is_taxable', $product->is_taxable) == '1' ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ old('is_taxable', $product->is_taxable) == '0' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Is Active?</label>
                                        <select name="is_active"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                            <option value="1" {{ old('is_active', $product->is_active) == '1' ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ old('is_active', $product->is_active) == '0' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="border-t border-gray-100 pt-6">
                                    <h4 class="text-sm font-medium text-gray-900 mb-4 uppercase tracking-wider">Inventory Control</h4>
                                    
                                     <div class="flex items-center space-x-6 mb-6">
                                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-indigo-500 transition-all">
                                            <input type="radio" name="manage_stock" value="1" class="sr-only" {{ old('manage_stock', $product->manage_stock) ? 'checked' : '' }}>
                                            <span class="block text-sm font-medium text-gray-900">Track Stock</span>
                                        </label>

                                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-gray-400 transition-all opacity-70">
                                            <input type="radio" name="manage_stock" value="0" class="sr-only" {{ !old('manage_stock', $product->manage_stock) ? 'checked' : '' }}>
                                            <span class="block text-sm font-medium text-gray-900">Don't Track</span>
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="col-span-1 md:col-span-3">
                                        <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                                            <div class="flex items-center justify-between mb-4">
                                                <h5 class="text-sm font-semibold text-gray-900">Current Stock by Warehouse</h5>
                                                <a href="{{ route('central.inventory.index', ['search' => $product->sku]) }}" target="_blank"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-gray-300 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                                                    Manage Inventory
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                                                    </svg>
                                                </a>
                                            </div>

                                            @if($product->stocks->count() > 0)
                                                <div class="overflow-x-auto rounded-lg border border-gray-200">
                                                    <table class="min-w-full divide-y divide-gray-200 bg-white">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warehouse</th>
                                                                <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                                <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Reserved</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-200">
                                                            @foreach($product->stocks as $stock)
                                                                <tr>
                                                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 font-medium">
                                                                        {{ $stock->warehouse->name ?? 'Unknown' }}
                                                                    </td>
                                                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-right text-gray-700">
                                                                        {{ floatval($stock->quantity) }}
                                                                    </td>
                                                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-right text-gray-500">
                                                                        {{ floatval($stock->reserve_quantity) }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            <tr class="bg-gray-50 font-semibold">
                                                                <td class="px-3 py-2 text-sm text-gray-900">Total Global Stock</td>
                                                                <td class="px-3 py-2 text-right text-sm text-indigo-700">
                                                                    {{ floatval($product->stock_on_hand) }}
                                                                </td>
                                                                <td class="px-3 py-2"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center py-6 bg-white rounded-lg border border-dashed border-gray-300">
                                                    <p class="text-sm text-gray-500">No stock records found.</p>
                                                    <span class="text-xs text-gray-400">Use "Manage Inventory" to add stock.</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Order Qty</label>
                                            <input type="number" step="1" name="min_order_qty" value="{{ old('min_order_qty', $product->min_order_qty) }}"
                                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Reorder Level</label>
                                            <input type="number" step="1" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level) }}"
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
                                        <input type="text" name="technical_name" value="{{ old('technical_name', $product->technical_name) }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Application Method</label>
                                        <input type="text" name="application_method" value="{{ old('application_method', $product->application_method) }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Harvest Date</label>
                                        <input type="date" name="harvest_date" value="{{ old('harvest_date', $product->harvest_date?->format('Y-m-d')) }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                                        <input type="date" name="expiry_date" value="{{ old('expiry_date', $product->expiry_date?->format('Y-m-d')) }}"
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
                                        <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                                        <input type="text" name="meta_description" value="{{ old('meta_description', $product->meta_description) }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50 focus:bg-white transition-colors py-2.5 px-3">
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="is_featured" value="1" id="is_featured" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="is_featured" class="ml-2 block text-sm text-gray-900">Featured Product</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Media Tab -->
                            <div x-show="tab === 'media'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-6 sm:p-8 space-y-8" style="display: none;">
                                <h3 class="text-lg font-semibold text-gray-900 border-b pb-4">Product Images</h3>
                                
                                <div class="space-y-6">
                                    <!-- Existing Images -->
                                    @if($product->images->count() > 0)
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                                            @foreach($product->images as $image)
                                                <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200">
                                                    <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-full object-cover" alt="Product Image">
                                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                        <label class="flex items-center gap-2 cursor-pointer bg-red-600 text-white px-3 py-1.5 rounded-full text-xs font-bold uppercase hover:bg-red-700 transition-all">
                                                            <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" class="accent-current h-4 w-4">
                                                            Delete
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Add New Images</label>
                                        <input type="file" name="images[]" accept="image/*" multiple
                                            class="block w-full text-sm text-gray-500
                                            file:mr-4 file:py-2.5 file:px-4
                                            file:rounded-full file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-indigo-50 file:text-indigo-700
                                            hover:file:bg-indigo-100">
                                        <p class="text-xs text-gray-500 mt-2">Select multiple images to upload.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mobile Sticky Action -->
                        <div class="mt-8 lg:hidden">
                            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>