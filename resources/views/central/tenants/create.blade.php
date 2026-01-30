@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6 lg:p-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold font-heading">Create New Workspace</h1>
            <p class="text-muted-foreground">Provision a new dedicated environment.</p>
        </div>
        <a href="{{ route('tenants.index') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground">
            Cancel
        </a>
    </div>

    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
        <form action="{{ config('app.url') }}/tenants" method="POST" class="space-y-6">
            @csrf
            
            <div class="space-y-2">
                <label for="id" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Workspace I.D.</label>
                <p class="text-[0.8rem] text-muted-foreground">This will be the unique identifier for the database (e.g., 'acme').</p>
                <input type="text" name="id" id="id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" placeholder="acme">
                @error('id') <span class="text-destructive text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-2">
                <label for="domain_name" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Subdomain</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="domain_name" id="domain_name" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 text-right" placeholder="acme">
                    <span class="text-muted-foreground font-mono">.localhost</span>
                </div>
                @error('domain_name') <span class="text-destructive text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-2">
                <label for="email" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Admin Email</label>
                <p class="text-[0.8rem] text-muted-foreground">The initial administrator account.</p>
                <input type="email" name="email" id="email" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" placeholder="admin@acme.com">
                @error('email') <span class="text-destructive text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4">
                <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full">
                    Provision Workspace
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
