@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('central.products.index') }}" class="p-2 rounded-lg hover:bg-muted text-muted-foreground transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold font-heading tracking-tight text-foreground">Add Product</h1>
            <p class="text-muted-foreground text-sm">Create a new global product.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="rounded-xl border border-border/50 bg-card/50 backdrop-blur-sm shadow-sm overflow-hidden p-6">
        <form action="{{ route('central.products.store') }}" method="POST" class="space-y-6" enctype="multipart/form-data">
            @csrf

            <!-- Name & SKU -->
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Product Name</label>
                    <input type="text" name="name" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" placeholder="e.g. Wireless Headphones" required>
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">SKU</label>
                    <input type="text" name="sku" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                </div>
            </div>

            <!-- Price & Category -->
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                     <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Price</label>
                     <div class="relative">
                        <span class="absolute left-3 top-2.5 text-muted-foreground">$</span>
                        <input type="number" step="0.01" name="price" class="flex h-10 w-full rounded-md border border-input bg-background pl-7 pr-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                    </div>
                </div>

                 <div class="space-y-2">
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Category</label>
                    <select name="category_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Product Images -->
            <div class="space-y-2">
                <label class="text-sm font-medium leading-none">Product Images</label>
                <div class="flex items-center justify-center w-full">
                    <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer hover:bg-muted/50 transition-colors border-border">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-2 text-muted-foreground" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                            </svg>
                            <p class="text-sm text-muted-foreground"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                            <p class="text-xs text-muted-foreground">SVG, PNG, JPG or GIF (MAX. 2MB)</p>
                        </div>
                        <input id="dropzone-file" type="file" name="images[]" multiple class="hidden" />
                    </label>
                </div>
            </div>

            <!-- Agriculture Fields -->
            <div class="grid gap-6 md:grid-cols-2">
                 <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Product Type</label>
                    <select name="type" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                        <option value="simple">Simple Product</option>
                        <option value="variable">Variable Product (Sizes/Colors)</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Unit Type</label>
                    <select name="unit_type" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                        <option value="kg">Kilogram (kg)</option>
                        <option value="ton">Ton</option>
                        <option value="crate">Crate</option>
                        <option value="bundle">Bundle</option>
                        <option value="piece">Piece</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Harvest Date</label>
                    <input type="date" name="harvest_date" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Expiry Date</label>
                    <input type="date" name="expiry_date" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Origin (Farm/Region)</label>
                    <input type="text" name="origin" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="e.g. California Valley">
                </div>
                 <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Certification Number</label>
                    <input type="text" name="certification_number" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Organic/GAP Cert #">
                </div>
            </div>

             <div class="flex items-center gap-2">
                <input type="checkbox" name="is_organic" value="1" id="is_organic" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                <label for="is_organic" class="text-sm font-medium leading-none">Is Organic Certified?</label>
            </div>

            <!-- SEO -->
             <div class="space-y-4 pt-4 border-t">
                <h3 class="font-semibold">SEO Metadata</h3>
                 <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Meta Title</label>
                    <input type="text" name="meta_title" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="SEO Title">
                </div>
                 <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Meta Description</label>
                    <textarea name="meta_description" class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm h-20" placeholder="SEO Description"></textarea>
                </div>
            </div>

            <!-- Global Errors -->
             @if ($errors->any())
                <div class="p-3 rounded-lg bg-red-500/10 text-red-600 border border-red-500/20 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="pt-4 flex justify-end gap-3">
                <x-ui.button type="button" variant="outline" href="{{ route('central.products.index') }}">Cancel</x-ui.button>
                <x-ui.button type="submit">Create Product</x-ui.button>
            </div>
        </form>
    </div>
</div>
@endsection
