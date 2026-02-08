@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto space-y-8 p-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('central.products.index') }}"
                    class="group flex h-10 w-10 items-center justify-center rounded-xl bg-background border border-border/50 shadow-sm transition-all hover:bg-accent hover:scale-105 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-muted-foreground group-hover:text-foreground transition-colors">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </a>
                <div>
                    <h1
                        class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                        Add New Product</h1>
                    <p class="text-muted-foreground text-sm mt-1">Configure global catalog items with agriculture-specific
                        metadata.</p>
                </div>
            </div>
        </div>

        <!-- Main Form Grid -->
        <form action="{{ route('central.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Core Info -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- General Information Card -->
                    <div class="rounded-2xl border border-border/40 bg-card/40 backdrop-blur-xl shadow-sm p-8 space-y-6">
                        <div class="flex items-center gap-3 border-b border-border/40 pb-4 mb-6">
                            <div class="h-8 w-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 2H2v10h20V2z" />
                                    <path d="M12 22V12" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold">General Information</h2>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold tracking-tight">Product Name <span
                                        class="text-destructive">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}"
                                    placeholder="e.g. Organic Wheat Seeds"
                                    class="flex h-11 w-full rounded-xl border-border/50 bg-background/50 px-4 py-2 text-sm ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary disabled:cursor-not-allowed disabled:opacity-50"
                                    required>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-semibold tracking-tight">SKU (Stock Keeping Unit)</label>
                                <input type="text" name="sku" value="{{ old('sku') }}" placeholder="e.g. WH-ORG-001"
                                    class="flex h-11 w-full rounded-xl border-border/50 bg-background/50 px-4 py-2 text-sm ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary font-mono uppercase">
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold tracking-tight">Category <span
                                        class="text-destructive">*</span></label>
                                <select name="category_id"
                                    class="flex h-11 w-full rounded-xl border-border/50 bg-background/50 px-4 py-2 text-sm ring-offset-background transition-all focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary appearance-none cursor-pointer">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-semibold tracking-tight">Brand / Manufacturer</label>
                                <select name="brand_id"
                                    class="flex h-11 w-full rounded-xl border-border/50 bg-background/50 px-4 py-2 text-sm ring-offset-background transition-all focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary appearance-none cursor-pointer">
                                    <option value="">Select Brand (Optional)</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Stock Card -->
                    <div class="rounded-2xl border border-border/40 bg-card/40 backdrop-blur-xl shadow-sm p-8 space-y-6">
                        <div class="flex items-center gap-3 border-b border-border/40 pb-4 mb-6">
                            <div
                                class="h-8 w-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <line x1="12" y1="1" x2="12" y2="23" />
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold">Pricing & Inventory</h2>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold tracking-tight">Standard Price <span
                                        class="text-destructive">*</span></label>
                                <div class="relative group">
                                    <span
                                        class="absolute left-4 top-3 text-muted-foreground font-medium group-focus-within:text-primary transition-colors text-sm">Rs</span>
                                    <input type="number" step="0.01" name="price" value="{{ old('price') }}"
                                        class="flex h-11 w-full rounded-xl border-border/50 bg-background/50 pl-10 pr-4 py-2 text-sm font-bold ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary"
                                        required>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-semibold tracking-tight">Technical/Unit Type <span
                                        class="text-destructive">*</span></label>
                                <select name="unit_type"
                                    class="flex h-11 w-full rounded-xl border-border/50 bg-background/50 px-4 py-2 text-sm ring-offset-background transition-all focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary appearance-none cursor-pointer">
                                    <option value="kg" {{ old('unit_type') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                    <option value="ton" {{ old('unit_type') == 'ton' ? 'selected' : '' }}>Ton</option>
                                    <option value="litre" {{ old('unit_type') == 'litre' ? 'selected' : '' }}>Litre (L)
                                    </option>
                                    <option value="piece" {{ old('unit_type') == 'piece' ? 'selected' : '' }}>Piece (Unit)
                                    </option>
                                    <option value="packet" {{ old('unit_type') == 'packet' ? 'selected' : '' }}>Packet
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2 border-t border-border/20 pt-6">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold tracking-tight">Discount Type</label>
                                <select name="default_discount_type"
                                    class="flex h-11 w-full rounded-xl border-border/50 bg-background/50 px-4 py-2 text-sm ring-offset-background transition-all focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary appearance-none cursor-pointer">
                                    <option value="fixed" {{ old('default_discount_type') == 'fixed' ? 'selected' : '' }}>
                                        Fixed Amount (Rs)</option>
                                    <option value="percent" {{ old('default_discount_type') == 'percent' ? 'selected' : '' }}>
                                        Percentage (%)</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold tracking-tight">Discount Value</label>
                                <input type="number" step="0.01" name="default_discount_value"
                                    value="{{ old('default_discount_value', 0) }}"
                                    class="flex h-11 w-full rounded-xl border-border/50 bg-background/50 px-4 py-2 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary">
                            </div>
                        </div>
                    </div>

                    <!-- Agriculture Specifics Card -->
                    <div class="rounded-2xl border border-border/40 bg-card/40 backdrop-blur-xl shadow-sm p-8 space-y-6">
                        <div class="flex items-center gap-3 border-b border-border/40 pb-4 mb-6">
                            <div class="h-8 w-8 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z" />
                                    <path d="M2 21c0-3 1.85-5.36 5.08-6C10.9 14.36 12 12 12 12" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold">Agriculture Metadata</h2>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold tracking-tight">Harvest Date</label>
                                <input type="date" name="harvest_date" value="{{ old('harvest_date') }}"
                                    class="flex h-11 w-full rounded-xl border-border/50 bg-background/50 px-4 py-2 text-sm ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold tracking-tight">Expiry Date</label>
                                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                                    class="flex h-11 w-full rounded-xl border-border/50 bg-background/50 px-4 py-2 text-sm ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary">
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold tracking-tight">Origin (Farm/Region)</label>
                                <input type="text" name="origin" value="{{ old('origin') }}"
                                    placeholder="e.g. Nashik Valley, MH"
                                    class="flex h-11 w-full rounded-xl border-border/50 bg-background/50 px-4 py-2 text-sm ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold tracking-tight">Certification #</label>
                                <input type="text" name="certification_number" value="{{ old('certification_number') }}"
                                    placeholder="e.g. APEDA-12345"
                                    class="flex h-11 w-full rounded-xl border-border/50 bg-background/50 px-4 py-2 text-sm ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary">
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-3 p-4 rounded-xl bg-emerald-500/5 border border-emerald-500/10 transition-all hover:bg-emerald-500/10">
                            <input type="checkbox" name="is_organic" value="1" id="is_organic" {{ old('is_organic') ? 'checked' : '' }}
                                class="h-5 w-5 rounded-lg border-emerald-500/30 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                            <label for="is_organic"
                                class="text-sm font-bold text-emerald-700 cursor-pointer flex items-center gap-2">
                                Organic Certified Product
                                <span
                                    class="text-[10px] bg-emerald-500 text-white px-1.5 py-0.5 rounded uppercase tracking-tighter">Premium</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sidebar settings -->
                <div class="space-y-8">
                    <!-- Status/Type Card -->
                    <div class="rounded-2xl border border-border/40 bg-card/40 backdrop-blur-xl shadow-sm p-6 space-y-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold tracking-tight">Product Type</label>
                            <select name="type"
                                class="flex h-11 w-full rounded-xl border-border/50 bg-background/50 px-4 py-2 text-sm ring-offset-background transition-all focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary appearance-none cursor-pointer">
                                <option value="simple" {{ old('type') == 'simple' ? 'selected' : '' }}>Simple Product</option>
                                <option value="variable" {{ old('type') == 'variable' ? 'selected' : '' }}>Variable Product
                                    (Variants)</option>
                            </select>
                            <p class="text-[10px] text-muted-foreground px-1 italic">Variants allow multiple sizes/colors.
                            </p>
                        </div>
                    </div>

                    <!-- Media Card -->
                    <div class="rounded-2xl border border-border/40 bg-card/40 backdrop-blur-xl shadow-sm p-6 space-y-4">
                        <label class="text-sm font-semibold tracking-tight">Product Media</label>
                        <div
                            class="group relative flex flex-col items-center justify-center w-full h-44 border-2 border-dashed rounded-2xl cursor-pointer bg-background/30 hover:bg-muted/50 border-border/60 hover:border-primary/40 transition-all duration-300">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                                <div
                                    class="h-10 w-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary mb-3 group-hover:scale-110 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="17 8 12 3 7 8" />
                                        <line x1="12" y1="3" x2="12" y2="15" />
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-foreground">Upload Images</p>
                                <p class="text-[10px] text-muted-foreground mt-1 uppercase tracking-widest">PNG, JPG up to
                                    5MB</p>
                            </div>
                            <input id="dropzone-file" type="file" name="images[]" multiple
                                class="absolute inset-0 opacity-0 cursor-pointer" />
                        </div>
                    </div>

                    <!-- SEO Card -->
                    <div class="rounded-2xl border border-border/40 bg-card/40 backdrop-blur-xl shadow-sm p-6 space-y-4">
                        <h3 class="text-sm font-semibold tracking-tight flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="text-muted-foreground">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
                            SEO Metadata
                        </h3>
                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-muted-foreground/70 uppercase tracking-wider">Meta
                                    Title</label>
                                <input type="text" name="meta_title" value="{{ old('meta_title') }}"
                                    placeholder="Page Title"
                                    class="flex h-10 w-full rounded-lg border-border/50 bg-background/50 px-3 py-2 text-xs focus-visible:ring-2 focus-visible:ring-primary/20 outline-none transition-all">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-muted-foreground/70 uppercase tracking-wider">Meta
                                    Description</label>
                                <textarea name="meta_description" placeholder="SEO Description"
                                    class="flex w-full rounded-lg border-border/50 bg-background/50 px-3 py-1.5 text-xs h-20 focus-visible:ring-2 focus-visible:ring-primary/20 outline-none transition-all">{{ old('meta_description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Errors -->
            @if ($errors->any())
                <div
                    class="p-4 rounded-2xl bg-destructive/5 text-destructive border border-destructive/10 animate-in shake duration-500">
                    <div class="flex items-center gap-3 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <span class="font-bold text-sm">Please correct the following errors:</span>
                    </div>
                    <ul class="list-disc pl-11 text-xs space-y-1 font-medium opacity-80">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Action Bar -->
            <div
                class="flex flex-col sm:flex-row items-center justify-between gap-4 p-8 rounded-2xl bg-card border border-border/40 shadow-xl shadow-black/5 backdrop-blur-xl">
                <div class="flex items-center gap-3">
                    <div class="h-2 w-2 rounded-full bg-primary animate-pulse"></div>
                    <span class="text-xs font-semibold text-muted-foreground uppercase tracking-widest">Global Catalog Sync
                        active</span>
                </div>
                <div class="flex items-center gap-4 w-full sm:w-auto">
                    <a href="{{ route('central.products.index') }}"
                        class="inline-flex h-12 flex-1 sm:flex-initial items-center justify-center px-6 rounded-xl border border-border font-semibold text-sm transition-all hover:bg-accent active:scale-95">
                        Discard Changes
                    </a>
                    <button type="submit"
                        class="inline-flex h-12 flex-1 sm:flex-initial items-center justify-center px-8 rounded-xl bg-primary text-primary-foreground font-bold text-sm shadow-lg shadow-primary/30 transition-all hover:bg-primary/90 hover:scale-[1.02] active:scale-95">
                        Publish Product
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection