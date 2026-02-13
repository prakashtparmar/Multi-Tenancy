@extends('layouts.app')

@section('content')
    <div class="flex flex-1 flex-col space-y-8 p-8 animate-in fade-in duration-500">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-1">
                <h1
                    class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/60 bg-clip-text text-transparent">
                    Edit Warehouse: {{ $warehouse->name }}
                </h1>
                <p class="text-muted-foreground text-sm">Update the configuration and details for this storage location.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('central.warehouses.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm hover:bg-muted transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    Cancel
                </a>
            </div>
        </div>

        <!-- Main Form Section -->
        <div class="max-w-4xl">
            <div class="rounded-2xl border border-border/40 bg-card/60 backdrop-blur-xl shadow-sm overflow-hidden">
                <form action="{{ route('central.warehouses.update', $warehouse) }}" method="POST" class="p-8 space-y-8">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <!-- Warehouse Name -->
                        <div class="space-y-2">
                            <label for="name"
                                class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Warehouse
                                Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $warehouse->name) }}" required
                                class="flex h-11 w-full rounded-xl border border-border bg-background px-4 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                            @error('name') <p class="text-xs font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Warehouse Code -->
                        <div class="space-y-2">
                            <label for="code"
                                class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Unique Code</label>
                            <input type="text" name="code" id="code" value="{{ old('code', $warehouse->code) }}" required
                                class="flex h-11 w-full rounded-xl border border-border bg-background px-4 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                            @error('code') <p class="text-xs font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Email Address -->
                        <div class="space-y-2">
                            <label for="email"
                                class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Email
                                Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $warehouse->email) }}"
                                class="flex h-11 w-full rounded-xl border border-border bg-background px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                            @error('email') <p class="text-xs font-medium text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="space-y-2">
                            <label for="phone"
                                class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Phone
                                Number</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $warehouse->phone) }}"
                                class="flex h-11 w-full rounded-xl border border-border bg-background px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
                                placeholder="+1 (555) 000-0000">
                            @error('phone') <p class="text-xs font-medium text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Detailed Address -->
                        <div class="space-y-2 md:col-span-2">
                            <label for="address"
                                class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Detailed
                                Address</label>
                            <textarea name="address" id="address" rows="3"
                                class="flex min-h-[100px] w-full rounded-xl border border-border bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">{{ old('address', $warehouse->address) }}</textarea>
                            @error('address') <p class="text-xs font-medium text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="md:col-span-2 pt-2">
                            <label class="relative inline-flex items-center cursor-pointer group">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-muted peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                </div>
                                <span
                                    class="ml-3 text-sm font-semibold text-foreground group-hover:text-primary transition-colors">Warehouse
                                    Active Status</span>
                            </label>
                            <p class="text-xs text-muted-foreground mt-1.5 ml-14">Inactive warehouses cannot be used for new
                                orders or stock movements.</p>
                        </div>
                    </div>

                    <!-- Submit Area -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-border/40">
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-8 py-2.5 text-sm font-bold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                <polyline points="17 21 17 13 7 13 7 21" />
                                <polyline points="7 3 7 8 15 8" />
                            </svg>
                            Update Warehouse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection