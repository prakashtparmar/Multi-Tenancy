@extends('layouts.app')

@section('content')
<div class="flex flex-1 flex-col space-y-8 p-6 md:p-8 max-w-7xl mx-auto w-full animate-in fade-in duration-500">
    
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ url('customers') }}" class="group flex h-10 w-10 items-center justify-center rounded-xl bg-background border border-border/50 shadow-sm transition-all hover:bg-accent hover:text-accent-foreground hover:scale-105 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-hover:text-foreground transition-colors"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                    Create New Customer
                </h1>
                <p class="text-muted-foreground text-sm">
                    Add a new customer profile with basic details, address, and agricultural data.
                </p>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl shadow-lg shadow-black/5 overflow-hidden">
        
        <!-- Decoration Line -->
        <div class="h-1 w-full bg-gradient-to-r from-primary/20 via-primary/50 to-primary/20"></div>

        <form action="/customers" method="POST" class="p-6 md:p-8 space-y-8">
            @csrf
            
            @if ($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-destructive/10 border border-destructive/20 text-destructive text-sm font-medium animate-in slide-in-from-top-2">
                <div class="flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                    <span>Please correct the following errors:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 opacity-90">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid gap-8 lg:grid-cols-3">
                
                <!-- Main Details Column -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Basic Info -->
                    <div>
                        <div class="flex items-center gap-2 mb-6 pb-2 border-b border-border/40">
                            <h2 class="text-lg font-semibold tracking-tight text-foreground/80">Basic Information</h2>
                        </div>
                        
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">First Name <span class="text-destructive">*</span></label>
                                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm @error('first_name') border-destructive/50 ring-destructive/20 @enderror">
                                @error('first_name') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Last Name</label>
                                <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                            <div class="space-y-2 sm:col-span-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Display Name</label>
                                <input type="text" name="display_name" id="display_name" value="{{ old('display_name') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Mobile <span class="text-destructive">*</span></label>
                                <input type="text" name="mobile" value="{{ old('mobile', request('mobile')) }}" required
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm @error('mobile') border-destructive/50 ring-destructive/20 @enderror">
                                @error('mobile') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Alternate Phone</label>
                                <input type="text" name="phone_number_2" value="{{ old('phone_number_2') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Relative Phone</label>
                                <input type="text" name="relative_phone" value="{{ old('relative_phone') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Business Details (Optional) -->
                    <div>
                        <div class="flex items-center gap-2 mb-6 pb-2 border-b border-border/40">
                             <h2 class="text-lg font-semibold tracking-tight text-foreground/80">Business Details (Optional)</h2>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                             <div class="space-y-2 lg:col-span-3">
                                <label class="text-sm font-medium leading-none text-foreground/80">Company/Shop Name</label>
                                <input type="text" name="company_name" value="{{ old('company_name') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                             <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">GST Number</label>
                                <input type="text" name="gst_number" value="{{ old('gst_number') }}" maxlength="15" style="text-transform: uppercase"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm @error('gst_number') border-destructive/50 ring-destructive/20 @enderror">
                                @error('gst_number') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                            </div>
                             <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">PAN Number</label>
                                <input type="text" name="pan_number" value="{{ old('pan_number') }}" maxlength="10" style="text-transform: uppercase"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm @error('pan_number') border-destructive/50 ring-destructive/20 @enderror">
                                @error('pan_number') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                         <div class="flex items-center gap-2 mb-6 pb-2 border-b border-border/40">
                            <h2 class="text-lg font-semibold tracking-tight text-foreground/80">Address</h2>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                             <div class="space-y-2 sm:col-span-2 lg:col-span-3">
                                <label class="text-sm font-medium leading-none text-foreground/80">Address Line 1</label>
                                <input type="text" name="address_line1" value="{{ old('address_line1') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                             <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Village/Town</label>
                                <input type="text" name="village" value="{{ old('village') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                             <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Taluka</label>
                                <input type="text" name="taluka" value="{{ old('taluka') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                             <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">District</label>
                                <input type="text" name="district" value="{{ old('district') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">State</label>
                                <input type="text" name="state" value="{{ old('state') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                             <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Pincode</label>
                                <input type="text" name="pincode" value="{{ old('pincode') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                             <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Country</label>
                                <input type="text" name="country" value="{{ old('country', 'India') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Agriculture & Finance -->
                    <div>
                         <div class="flex items-center gap-2 mb-6 pb-2 border-b border-border/40">
                            <h2 class="text-lg font-semibold tracking-tight text-foreground/80">Agriculture & Finance</h2>
                        </div>
                         <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                             <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Land Area</label>
                                <input type="number" name="land_area" min="0" step="0.01" value="{{ old('land_area') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                             <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Land Unit</label>
                                <select name="land_unit" class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                                    @foreach (['acre', 'hectare', 'bigha'] as $u)
                                    <option value="{{ $u }}" @selected(old('land_unit', 'acre') == $u)>{{ ucfirst($u) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Irrigation Type</label>
                                <select name="irrigation_type" class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                                    <option value="">Select</option>
                                    @foreach (['rainfed', 'canal', 'drip', 'sprinkler', 'borewell'] as $i)
                                    <option value="{{ $i }}" @selected(old('irrigation_type') == $i)>{{ ucfirst($i) }}</option>
                                    @endforeach
                                </select>
                            </div>

                             <div class="space-y-2 sm:col-span-2 lg:col-span-3">
                                <label class="text-sm font-medium leading-none text-foreground/80">Primary Crops</label>
                                <div id="primary-box" class="flex flex-wrap gap-1 p-2 min-h-10 rounded-xl border border-input bg-background/50 focus-within:ring-2 focus-within:ring-primary/30 focus-within:border-primary/50 transition-all shadow-sm">
                                   <input id="primary-input" type="text" list="crop-list"
                                      class="flex-1 min-w-[120px] bg-transparent outline-none text-sm placeholder:text-muted-foreground"
                                      placeholder="Type crop & press Enter" />
                                </div>
                                <input type="hidden" name="primary_crops" id="primary-hidden" value="{{ old('primary_crops') }}">
                             </div>
                             <div class="space-y-2 sm:col-span-2 lg:col-span-3">
                                <label class="text-sm font-medium leading-none text-foreground/80">Secondary Crops</label>
                                <div id="secondary-box" class="flex flex-wrap gap-1 p-2 min-h-10 rounded-xl border border-input bg-background/50 focus-within:ring-2 focus-within:ring-primary/30 focus-within:border-primary/50 transition-all shadow-sm">
                                   <input id="secondary-input" type="text" list="crop-list"
                                      class="flex-1 min-w-[120px] bg-transparent outline-none text-sm placeholder:text-muted-foreground"
                                      placeholder="Type crop & press Enter" />
                                </div>
                                <input type="hidden" name="secondary_crops" id="secondary-hidden" value="{{ old('secondary_crops') }}">
                             </div>

                             <div class="space-y-2 sm:col-span-2 lg:col-span-3">
                                <label class="text-sm font-medium leading-none text-foreground/80">Custom Tags / Labels</label>
                                <div id="tags-box" class="flex flex-wrap gap-1 p-2 min-h-10 rounded-xl border border-input bg-background/50 focus-within:ring-2 focus-within:ring-primary/30 focus-within:border-primary/50 transition-all shadow-sm">
                                   <input id="tags-input" type="text"
                                      class="flex-1 min-w-[120px] bg-transparent outline-none text-sm placeholder:text-muted-foreground"
                                      placeholder="Type tag & press Enter (e.g. High Value, Reliable)" />
                                </div>
                                <input type="hidden" name="tags" id="tags-hidden" value="{{ old('tags') }}">
                             </div>
                             
                             <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Credit Limit</label>
                                <input type="number" name="credit_limit" min="0" value="{{ old('credit_limit', 0) }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Credit Valid Till</label>
                                <input type="date" name="credit_valid_till" value="{{ old('credit_valid_till') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Aadhaar Last 4</label>
                                <input type="text" name="aadhaar_last4" maxlength="4" value="{{ old('aadhaar_last4') }}"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                         </div>
                         <datalist id="crop-list">
                            <option value="Wheat">
                            <option value="Rice">
                            <option value="Cotton">
                            <option value="Maize">
                            <option value="Bajra">
                            <option value="Jowar">
                            <option value="Sugarcane">
                            <option value="Groundnut">
                            <option value="Soybean">
                            <option value="Onion">
                            <option value="Potato">
                            <option value="Tomato">
                         </datalist>
                    </div>

                </div>

                <!-- Sidebar Column -->
                <div class="space-y-8">
                     <!-- Classification -->
                     <div class="rounded-xl border border-border/50 bg-muted/20 p-6 space-y-6">
                        <div class="flex items-center gap-2 pb-2 border-b border-border/40">
                             <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                             <h3 class="font-semibold text-foreground/80">Classification</h3>
                        </div>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Type</label>
                                <select name="type" class="flex h-10 w-full rounded-xl border border-input bg-background/80 px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                                    @foreach (['farmer', 'buyer', 'vendor', 'dealer'] as $t)
                                    <option value="{{ $t }}" @selected(old('type', 'farmer') == $t)>{{ ucfirst($t) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Category</label>
                                <select name="category" class="flex h-10 w-full rounded-xl border border-input bg-background/80 px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                                    <option value="individual" @selected(old('category', 'individual') === 'individual')>Individual</option>
                                    <option value="business" @selected(old('category') === 'business')>Business</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80">Source</label>
                                <input type="text" name="source" value="{{ old('source') }}" placeholder="e.g. Referral, Walk-in"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background/80 px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm">
                            </div>
                        </div>
                     </div>

                     <!-- Status -->
                     <div class="rounded-xl border border-border/50 bg-muted/20 p-6 space-y-6">
                         <div class="flex items-center gap-2 pb-2 border-b border-border/40">
                             <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                             <h3 class="font-semibold text-foreground/80">Status</h3>
                        </div>
                         <div class="space-y-3">
                             <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-background/50 transition-colors cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-primary/50 text-primary shadow focus:ring-offset-0 focus:ring-2 focus:ring-primary/20 cursor-pointer transition-all checked:bg-primary checked:border-primary" @checked(old('is_active', true))>
                                <span class="text-sm font-medium">Active Customer</span>
                             </label>
                             <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-background/50 transition-colors cursor-pointer">
                                <input type="checkbox" name="is_blacklisted" value="1" class="h-4 w-4 rounded border-destructive/50 text-destructive shadow focus:ring-offset-0 focus:ring-2 focus:ring-destructive/20 cursor-pointer transition-all checked:bg-destructive checked:border-destructive" @checked(old('is_blacklisted'))>
                                <span class="text-sm font-medium text-destructive">Blacklisted</span>
                             </label>
                             <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-background/50 transition-colors cursor-pointer">
                                <input type="checkbox" name="kyc_completed" value="1" class="h-4 w-4 rounded border-primary/50 text-primary shadow focus:ring-offset-0 focus:ring-2 focus:ring-primary/20 cursor-pointer transition-all checked:bg-primary checked:border-primary" @checked(old('kyc_completed'))>
                                <span class="text-sm font-medium">KYC Completed</span>
                             </label>
                         </div>
                     </div>

                     <!-- Internal Notes -->
                      <div class="space-y-2">
                        <label class="text-sm font-medium leading-none text-foreground/80">Internal Notes</label>
                        <textarea name="internal_notes" rows="4" placeholder="Add private notes related to this customer..."
                            class="flex w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm resize-none">{{ old('internal_notes') }}</textarea>
                    </div>

                </div>

            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-border/40">
                <a href="{{ url('customers') }}" class="inline-flex items-center justify-center rounded-xl bg-muted px-4 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted/80 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Save Customer</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .tag-remove:hover { color: #ef4444; }
</style>

<script>
   function tagInput(boxId, inputId, hiddenId) {
       const box = document.getElementById(boxId);
       const input = document.getElementById(inputId);
       const hidden = document.getElementById(hiddenId);

       let items = hidden.value ? hidden.value.split(',').filter(Boolean) : [];

       function render() {
           box.querySelectorAll('.tag').forEach(t => t.remove());
           items.forEach((c, i) => {
               const tag = document.createElement('span');
               tag.className =
                   'tag px-2.5 py-1 text-xs font-medium rounded-lg bg-primary/10 text-primary flex items-center gap-1.5 animate-in zoom-in-50 duration-200';
               tag.innerHTML = `${c} <button type="button" data-i="${i}" class="tag-remove opacity-60 hover:opacity-100 transition-opacity text-base leading-none">×</button>`;
               box.insertBefore(tag, input);
           });
           hidden.value = items.join(',');
       }

       function addItem(value) {
           const v = value.trim();
           if (!v) return;
           if (!items.includes(v)) {
               items.push(v);
               render();
           }
           input.value = '';
       }

       input.addEventListener('keydown', e => {
           if (e.key === 'Enter') {
               e.preventDefault();
               addItem(input.value);
           }
       });

       input.addEventListener('change', () => {
           addItem(input.value);
       });

       box.addEventListener('click', e => {
           if (e.target.tagName === 'BUTTON') {
               items.splice(e.target.dataset.i, 1);
               render();
           }
       });

       render();
   }

   tagInput('primary-box', 'primary-input', 'primary-hidden');
   tagInput('secondary-box', 'secondary-input', 'secondary-hidden');
   tagInput('tags-box', 'tags-input', 'tags-hidden');

   document.addEventListener('DOMContentLoaded', () => {
       const first = document.querySelector('input[name="first_name"]');
       const last = document.querySelector('input[name="last_name"]');
       const display = document.querySelector('input[name="display_name"]');

       if (!first || !last || !display) return;

       function updateDisplayName() {
           const f = first.value.trim();
           const l = last.value.trim();
           if(f || l) {
               display.value = [f, l].filter(Boolean).join(' ');
           }
       }

       first.addEventListener('input', updateDisplayName);
       last.addEventListener('input', updateDisplayName);
   });
</script>
@endsection
