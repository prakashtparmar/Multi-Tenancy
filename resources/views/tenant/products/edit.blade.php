<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Product: ') }} {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ tab: 'basic' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Tabs Navigation -->
            <div class="mb-6 bg-white shadow-sm sm:rounded-lg flex overflow-x-auto">
                <button @click="tab = 'basic'"
                    :class="{ 'border-b-2 border-indigo-500 text-indigo-600': tab === 'basic', 'text-gray-500 hover:text-gray-700': tab !== 'basic' }"
                    class="px-6 py-4 font-medium text-sm focus:outline-none transition-colors">
                    Basic Info
                </button>
                <button @click="tab = 'pricing'"
                    :class="{ 'border-b-2 border-indigo-500 text-indigo-600': tab === 'pricing', 'text-gray-500 hover:text-gray-700': tab !== 'pricing' }"
                    class="px-6 py-4 font-medium text-sm focus:outline-none transition-colors">
                    Pricing & Stock
                </button>
                <button @click="tab = 'agri'"
                    :class="{ 'border-b-2 border-indigo-500 text-indigo-600': tab === 'agri', 'text-gray-500 hover:text-gray-700': tab !== 'agri' }"
                    class="px-6 py-4 font-medium text-sm focus:outline-none transition-colors">
                    Agriculture Details
                </button>
                <button @click="tab = 'seo'"
                    :class="{ 'border-b-2 border-indigo-500 text-indigo-600': tab === 'seo', 'text-gray-500 hover:text-gray-700': tab !== 'seo' }"
                    class="px-6 py-4 font-medium text-sm focus:outline-none transition-colors">
                    SEO & Settings
                </button>
                <button @click="tab = 'media'"
                    :class="{ 'border-b-2 border-indigo-500 text-indigo-600': tab === 'media', 'text-gray-500 hover:text-gray-700': tab !== 'media' }"
                    class="px-6 py-4 font-medium text-sm focus:outline-none transition-colors">
                    Media
                </button>
            </div>

            <form action="{{ route('tenant.products.update', $product->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <!-- Basic Info Tab -->
                    <div x-show="tab === 'basic'" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Product Name *</label>
                                <input type="text" name="name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    required value="{{ old('name', $product->name) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">SKU (Unique)</label>
                                <input type="text" name="sku"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('sku', $product->sku) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Barcode / EAN</label>
                                <input type="text" name="barcode"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('barcode', $product->barcode) }}">
                            </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Unit Type *</label>
                                <select name="unit_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    required>
                                    @foreach(['kg', 'g', 'l', 'ml', 'piece', 'pack', 'bottle', 'bag'] as $type)
                                        <option value="{{ $type }}" {{ old('unit_type', $product->unit_type) == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Packing Size</label>
                                <input type="text" name="packing_size" placeholder="e.g. 500g, 1L"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('packing_size', $product->packing_size) }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Category</label>
                                <select name="category_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">None</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Brand</label>
                                <select name="brand_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">None</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>

                    <!-- Pricing & Stock Tab -->
                    <div x-show="tab === 'pricing'" class="space-y-6" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Selling Price *</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" step="0.01" name="price"
                                        class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        placeholder="0.00" required value="{{ old('price', $product->price) }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cost Price</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" step="0.01" name="cost_price"
                                        class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        placeholder="0.00" value="{{ old('cost_price', $product->cost_price) }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <input type="number" step="0.01" name="tax_rate"
                                        class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        placeholder="0.00" value="{{ old('tax_rate', $product->tax_rate) }}">
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500 sm:text-sm">%</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">HSN Code</label>
                                <input type="text" name="hsn_code"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('hsn_code', $product->hsn_code) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Is Taxable?</label>
                                <select name="is_taxable"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="1" {{ old('is_taxable', $product->is_taxable) ? 'selected' : '' }}>
                                        Yes</option>
                                    <option value="0" {{ !old('is_taxable', $product->is_taxable) ? 'selected' : '' }}>
                                        No</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t pt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Inventory Management</label>
                                <div class="mt-2 space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="manage_stock" value="1"
                                            class="text-indigo-600 focus:ring-indigo-500" {{ old('manage_stock', $product->manage_stock) ? 'checked' : '' }}>
                                        <span class="ml-2">Track Stock</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="manage_stock" value="0"
                                            class="text-indigo-600 focus:ring-indigo-500" {{ !old('manage_stock', $product->manage_stock) ? 'checked' : '' }}>
                                        <span class="ml-2">Don't Track</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Stock On Hand</label>
                                <input type="number" step="0.001" name="stock_on_hand"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('stock_on_hand', $product->stock_on_hand) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Min Order Qty</label>
                                <input type="number" step="1" name="min_order_qty"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('min_order_qty', $product->min_order_qty) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Reorder Level</label>
                                <input type="number" step="1" name="reorder_level"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('reorder_level', $product->reorder_level) }}">
                            </div>
                        </div>

                        <!-- WMS: Dimensions & Weight -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-t pt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                                <input type="number" step="0.001" name="weight"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('weight', $product->weight) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Length (cm)</label>
                                <input type="number" step="0.1" name="dimensions[length]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('dimensions.length', $product->dimensions['length'] ?? '') }}">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700">Width (cm)</label>
                                <input type="number" step="0.1" name="dimensions[width]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('dimensions.width', $product->dimensions['width'] ?? '') }}">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700">Height (cm)</label>
                                <input type="number" step="0.1" name="dimensions[height]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('dimensions.height', $product->dimensions['height'] ?? '') }}">
                            </div>
                        </div>

                        <!-- Discount -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t pt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Default Discount Type</label>
                                <select name="default_discount_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="fixed" {{ old('default_discount_type', $product->default_discount_type) == 'fixed' ? 'selected' : '' }}>Fixed Amount
                                    </option>
                                    <option value="percent" {{ old('default_discount_type', $product->default_discount_type) == 'percent' ? 'selected' : '' }}>Percentage
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Discount Value</label>
                                <input type="number" step="0.01" name="default_discount_value"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('default_discount_value', $product->default_discount_value ?? 0) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Agri Tab -->
                    <div x-show="tab === 'agri'" class="space-y-6" style="display: none;">
                        <h3 class="text-lg font-medium text-gray-900">Crop Science & Specifications</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Technical Name</label>
                                <input type="text" name="technical_name" placeholder="e.g. Imidacloprid 17.8% SL"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('technical_name', $product->technical_name) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Application Method</label>
                                <input type="text" name="application_method"
                                    placeholder="e.g. Foliar Spray, Soil Drench"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('application_method', $product->application_method) }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Harvest Date (if
                                    applicable)</label>
                                <input type="date" name="harvest_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('harvest_date', optional($product->harvest_date)->format('Y-m-d')) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                <input type="date" name="expiry_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('expiry_date', optional($product->expiry_date)->format('Y-m-d')) }}">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Target Crops (Comma separated)</label>
                            <input type="text" name="target_crops" placeholder="e.g. Wheat, Rice, Cotton"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                value="{{ old('target_crops', is_array($product->target_crops) ? implode(', ', $product->target_crops) : $product->target_crops) }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Target Pests (Comma separated)</label>
                            <input type="text" name="target_pests" placeholder="e.g. Aphids, Bollworm"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                value="{{ old('target_pests', is_array($product->target_pests) ? implode(', ', $product->target_pests) : $product->target_pests) }}">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pre-Harvest Interval (PHI)</label>
                                <input type="text" name="pre_harvest_interval" placeholder="e.g. 15 Days"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('pre_harvest_interval', $product->pre_harvest_interval) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Shelf Life</label>
                                <input type="text" name="shelf_life" placeholder="e.g. 2 Years"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('shelf_life', $product->shelf_life) }}">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Usage Instructions</label>
                            <textarea name="usage_instructions" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('usage_instructions', $product->usage_instructions) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Origin / Farm</label>
                                <input type="text" name="origin"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('origin', $product->origin) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Is Organic?</label>
                                <select name="is_organic"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="0" {{ !old('is_organic', $product->is_organic) ? 'selected' : '' }}>
                                        No</option>
                                    <option value="1" {{ old('is_organic', $product->is_organic) ? 'selected' : '' }}>
                                        Yes</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cert. Number</label>
                                <input type="text" name="certification_number"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('certification_number', $product->certification_number) }}">
                            </div>
                        </div>

                        <div class="mt-6 border-t pt-4">
                            <label class="block text-sm font-medium text-gray-700">Certificate File (PDF/Image)</label>
                            @if($product->certificate_url)
                                <div class="mb-2">
                                    <a href="{{ asset('storage/' . $product->certificate_url) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 text-sm">View Current Certificate</a>
                                </div>
                            @endif
                            <input type="file" name="certificate_url" accept=".pdf,image/*"
                                class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100">
                        </div>
                    </div>

                    <!-- SEO & Settings Tab -->
                    <div x-show="tab === 'seo'" class="space-y-6" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Meta Title</label>
                                <input type="text" name="meta_title"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('meta_title', $product->meta_title) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                                <input type="text" name="meta_description"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    value="{{ old('meta_description', $product->meta_description) }}">
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <label class="flex items-center space-x-3 mb-2">
                                <input type="checkbox" name="is_featured" value="1"
                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                    {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                                <span class="text-gray-900 font-medium">Featured Product (Highlight in store)</span>
                            </label>

                            <label class="flex items-center space-x-3">
                                <input type="checkbox" name="is_active" value="1"
                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                    {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <span class="text-gray-900 font-medium">Active (Visible in Store)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Media Tab -->
                    <div x-show="tab === 'media'" class="space-y-6" style="display: none;">
                         <div>
                            <label class="block text-sm font-medium text-gray-700">Main Product Image</label>
                            @if($product->image_url)
                                <div class="mt-2 mb-4">
                                     <img src="{{ $product->image_url }}" alt="Current Image" class="h-32 w-32 object-cover rounded border">
                                </div>
                            @endif
                            <input type="file" name="image" accept="image/*"
                                class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100">
                            <p class="text-xs text-gray-500 mt-1">Upload to replace the current image.</p>
                        </div>

                        <div class="border-t pt-6">
                            <label class="block text-sm font-medium text-gray-700">Gallery Images</label>
                            @if($product->images->where('is_primary', false)->count() > 0)
                                <div class="mt-2 mb-4 flex space-x-2 overflow-x-auto">
                                    @foreach($product->images->where('is_primary', false) as $img)
                                         <img src="{{ asset('storage/' . $img->image_path) }}" class="h-20 w-20 object-cover rounded border">
                                    @endforeach
                                </div>
                            @endif
                            <input type="file" name="gallery[]" accept="image/*" multiple
                                class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100">
                             <p class="text-xs text-gray-500 mt-1">Select images to append to the gallery.</p>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-end border-t pt-6">
                        <x-primary-button class="ml-3">
                            {{ __('Update Product') }}
                        </x-primary-button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</x-app-layout>