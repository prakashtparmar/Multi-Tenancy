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
                    {{ isset($role) ? 'Edit Role' : 'Create Role' }}
                </h1>
                <p class="text-muted-foreground text-sm">
                    {{ isset($role) ? 'Modify permissions and access levels.' : 'Define a new role and its operational capabilities.' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl shadow-lg shadow-black/5 overflow-hidden">
        
        <!-- Decoration Line -->
        <div class="h-1 w-full bg-gradient-to-r from-primary/20 via-primary/50 to-primary/20"></div>

        <form action="{{ $actionUrl }}" 
              method="POST" 
              class="p-6 md:p-8 space-y-8"
              x-data='{ 
                  selectedPermissions: @json(old('permissions', isset($role) ? $role->permissions->pluck('name') : [])),
                  toggleGroup(groupName, permissionNames) {
                      const allSelected = permissionNames.every(p => this.selectedPermissions.includes(p));
                      if (allSelected) {
                          // Deselect all
                          this.selectedPermissions = this.selectedPermissions.filter(p => !permissionNames.includes(p));
                      } else {
                          // Select all
                          const newPerms = permissionNames.filter(p => !this.selectedPermissions.includes(p));
                          this.selectedPermissions.push(...newPerms);
                      }
                  },
                  isGroupSelected(permissionNames) {
                      return permissionNames.every(p => this.selectedPermissions.includes(p));
                  }
              }'>
            @csrf
            @if(isset($role))
                @method('PUT')
            @endif

            <!-- Role Details -->
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold tracking-tight">Role Identity</h2>
                    <p class="text-sm text-muted-foreground">The display name used throughout the application.</p>
                </div>

                <div class="max-w-md space-y-2">
                    <label class="text-sm font-medium leading-none text-foreground/80" for="name">
                        Role Name <span class="text-destructive">*</span>
                    </label>
                    <div class="relative group">
                        <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </span>
                        <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                               id="name" name="name" placeholder="e.g. Content Manager" required 
                               value="{{ old('name', $role->name ?? '') }}">
                    </div>
                    @error('name') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="border-t border-border/40"></div>

            <!-- Permissions Grid -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight">Permissions</h2>
                        <p class="text-sm text-muted-foreground">Select capabilities granted to this role.</p>
                    </div>
                    <!-- Global Tools could go here -->
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($permissions as $group => $perms)
                        @php
                            $groupPermNames = $perms->pluck('name')->toArray();
                            $jsonGroupPerms = json_encode($groupPermNames);
                        @endphp
                        <div class="rounded-xl border border-border/40 bg-card/30 overflow-hidden hover:border-border/80 transition-colors">
                            <!-- Group Header -->
                            <div class="px-4 py-3 bg-muted/30 border-b border-border/40 flex items-center justify-between">
                                <h4 class="font-semibold text-sm capitalize flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-primary/40"></span>
                                    {{ $group }}
                                </h4>
                                <button type="button" 
                                        @click="toggleGroup('{{ $group }}', {{ $jsonGroupPerms }})"
                                        class="text-[10px] font-medium uppercase tracking-wider text-muted-foreground hover:text-primary transition-colors">
                                    <span x-text="isGroupSelected({{ $jsonGroupPerms }}) ? 'Deselect All' : 'Select All'"></span>
                                </button>
                            </div>

                            <!-- Permissions List -->
                            <div class="p-2 space-y-1">
                                @foreach($perms as $permission)
                                <label class="group flex items-center gap-3 rounded-lg px-3 py-2 cursor-pointer transition-colors hover:bg-accent/50">
                                    <div class="relative flex h-4 w-4 items-center justify-center">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" 
                                               x-model="selectedPermissions"
                                               class="peer h-4 w-4 appearance-none rounded border border-muted-foreground/40 bg-transparent checked:border-primary checked:bg-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                                        <svg class="absolute h-3 w-3 text-primary-foreground opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    </div>
                                    <span class="text-sm text-muted-foreground group-hover:text-foreground peer-checked:text-foreground transition-colors select-none">
                                        {{ $permission->name }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 text-right">
                <a href="{{ $indexUrl }}" class="inline-flex items-center justify-center rounded-xl bg-muted px-4 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted/80 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>{{ isset($role) ? 'Save Role' : 'Create Role' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
