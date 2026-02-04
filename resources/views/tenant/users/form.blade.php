@extends('layouts.app')

@section('content')
<div class="flex flex-1 flex-col space-y-8 p-6 md:p-8 max-w-7xl mx-auto w-full animate-in fade-in duration-500">
    
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ $indexUrl }}" class="group flex h-10 w-10 items-center justify-center rounded-xl bg-background border border-border/50 shadow-sm transition-all hover:bg-accent hover:text-accent-foreground hover:scale-105 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-hover:text-foreground transition-colors"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                    {{ isset($user) ? 'Edit User' : 'Create New User' }}
                </h1>
                <p class="text-muted-foreground text-sm">
                    {{ isset($user) ? 'Update user details, roles, and permissions.' : 'Onboard a new member to your organization.' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl shadow-lg shadow-black/5 overflow-hidden">
        
        <!-- Decoration Line -->
        <div class="h-1 w-full bg-gradient-to-r from-primary/20 via-primary/50 to-primary/20"></div>

        <form action="{{ $actionUrl }}" method="POST" class="p-6 md:p-8 space-y-8"
              x-data="{ 
                  showPassword: false, 
                  showConfirm: false,
                  selectedRoles: @json(old('roles', isset($user) ? $user->roles->pluck('name') : []))
              }">
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif

            <div class="grid gap-8 lg:grid-cols-3">
                
                <!-- Account Details Column -->
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight">Account Information</h2>
                        <p class="text-sm text-muted-foreground">Basic identity details for login and display.</p>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <!-- Full Name -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="name">
                                Full Name <span class="text-destructive">*</span>
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </span>
                                <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="name" name="name" placeholder="John Doe" required 
                                       value="{{ old('name', $user->name ?? '') }}">
                            </div>
                            @error('name') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Email -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="email">
                                Email Address <span class="text-destructive">*</span>
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                </span>
                                <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="email" name="email" type="email" placeholder="name@company.com" required 
                                       value="{{ old('email', $user->email ?? '') }}">
                            </div>
                            @error('email') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="password">
                                {!! 'Password ' . (isset($user) ? '(Optional)' : '<span class="text-destructive">*</span>') !!}
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </span>
                                <input :type="showPassword ? 'text' : 'password'" 
                                       class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 pr-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="password" name="password" placeholder="••••••••" {{ isset($user) ? '' : 'required' }}>
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-2.5 text-muted-foreground hover:text-foreground transition-colors focus:outline-none">
                                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                                    <svg x-show="showPassword" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                            @if(isset($user))
                                <p class="text-[0.8rem] text-muted-foreground">Leave blank to keep existing password.</p>
                            @endif
                           @error('password') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">
                             <label class="text-sm font-medium leading-none text-foreground/80" for="password_confirmation">
                                {!! 'Confirm Password ' . (isset($user) ? '(Optional)' : '<span class="text-destructive">*</span>') !!}
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </span>
                                <input :type="showConfirm ? 'text' : 'password'" 
                                       class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 pr-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="password_confirmation" name="password_confirmation" placeholder="••••••••" {{ isset($user) ? '' : 'required' }}>
                                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-2.5 text-muted-foreground hover:text-foreground transition-colors focus:outline-none">
                                    <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                                    <svg x-show="showConfirm" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roles Column -->
                <div class="space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight">Access Control</h2>
                        <p class="text-sm text-muted-foreground">Assign roles to define permissions.</p>
                    </div>

                    <div class="space-y-3">
                         @foreach($roles as $role)
                        <!-- Improved Interactive Card: Uses Alpine 'selectedRoles' to drive the class -->
                        <label class="group relative flex cursor-pointer rounded-xl border bg-background/40 p-4 shadow-sm transition-all hover:bg-accent hover:border-primary/30"
                               :class="selectedRoles.includes('{{ $role->name }}') ? 'border-primary bg-primary/5 shadow-primary/10' : 'border-border'">
                            
                            <div class="flex items-start lg:items-center gap-4 w-full">
                                <div class="flex h-5 items-center">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                           x-model="selectedRoles"
                                           class="h-4 w-4 rounded border-primary/50 text-primary shadow focus:ring-offset-0 focus:ring-2 focus:ring-primary/20 cursor-pointer transition-all checked:bg-primary checked:border-primary">
                                </div>
                                <div class="space-y-1 w-full">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-foreground transition-colors"
                                              :class="selectedRoles.includes('{{ $role->name }}') ? 'text-primary' : ''">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full border border-border px-2 py-0.5 text-xs font-medium text-muted-foreground bg-muted/50"
                                              :class="selectedRoles.includes('{{ $role->name }}') ? 'bg-background/80' : ''">
                                            {{ $role->guard_name }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground/80 line-clamp-2"
                                       :class="selectedRoles.includes('{{ $role->name }}') ? 'text-foreground/70' : ''">
                                        Grants access to {{ $role->name }} features and permissions.
                                    </p>
                                </div>
                            </div>
                            <!-- Selection Highlight Border -->
                            <div class="absolute inset-0 rounded-xl border-2 border-primary opacity-0 transition-opacity pointer-events-none"
                                 :class="selectedRoles.includes('{{ $role->name }}') ? 'opacity-100' : 'opacity-0'"></div>
                        </label>
                        @endforeach
                    </div>
                    @error('roles') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-border/40">
                <a href="{{ $indexUrl }}" class="inline-flex items-center justify-center rounded-xl bg-muted px-4 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted/80 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>{{ isset($user) ? 'Save Changes' : 'Create Account' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
