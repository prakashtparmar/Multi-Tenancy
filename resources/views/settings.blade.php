@extends('layouts.app')

@section('content')
<div class="flex flex-1 flex-col transition-all duration-300">
    <!-- Settings Header -->
    <div class="relative overflow-hidden bg-background px-4 py-8 lg:px-6 lg:py-10 border-b">
        <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-primary/10 blur-3xl opacity-50"></div>
        <div class="relative flex flex-col gap-2">
            <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl text-foreground">Settings</h1>
            <p class="text-muted-foreground">Manage your account settings and preferences.</p>
        </div>
    </div>

    <div class="p-4 lg:p-6 max-w-4xl mx-auto w-full space-y-6">
        <div class="grid gap-6">
            <!-- Appearance Section -->
            <div class="group relative rounded-3xl border bg-card/60 backdrop-blur-sm p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg bg-primary/10 p-2 text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/><path d="M12 3v18"/><path d="M12 15h.01"/><path d="M12 11h.01"/><path d="M12 7h.01"/></svg>
                            </div>
                            <h3 class="text-lg font-bold">Appearance</h3>
                        </div>
                        <p class="text-sm text-muted-foreground">Customize how the dashboard looks and feels on your device.</p>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between gap-4 bg-muted/40 p-4 rounded-2xl border">
                            <div class="space-y-0.5">
                                <p class="text-sm font-bold">Theme Mode</p>
                                <p class="text-[11px] text-muted-foreground">Toggle between light and dark themes.</p>
                            </div>
                            <x-layout.theme-switch />
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl border bg-background/50 space-y-3 grayscale hover:grayscale-0 transition-all opacity-60 hover:opacity-100 cursor-not-allowed group">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-widest text-muted-foreground">Language</span>
                            <span class="text-xs font-bold text-primary">English</span>
                        </div>
                        <p class="text-[11px] text-muted-foreground flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            Spanish, French, More...
                        </p>
                        <div class="absolute inset-0 flex items-center justify-center bg-background/10 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl">
                             <span class="text-[10px] font-black uppercase text-primary-foreground bg-primary px-2 py-1 rounded">Pro Feature</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl border bg-background/50 space-y-3 grayscale hover:grayscale-0 transition-all opacity-60 hover:opacity-100 cursor-not-allowed group">
                         <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-widest text-muted-foreground">Font Size</span>
                            <span class="text-xs font-bold text-primary">Default</span>
                        </div>
                        <p class="text-[11px] text-muted-foreground flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                            Small, Medium, Large
                        </p>
                        <div class="absolute inset-0 flex items-center justify-center bg-background/10 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl">
                             <span class="text-[10px] font-black uppercase text-primary-foreground bg-primary px-2 py-1 rounded">Pro Feature</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Section -->
            <div class="group relative rounded-3xl border bg-card/60 backdrop-blur-sm p-8 shadow-sm text-center">
                 <div class="rounded-2xl bg-muted/50 p-12 border-2 border-dashed border-muted flex flex-col items-center justify-center gap-4">
                    <div class="rounded-full bg-blue-500/10 p-4 text-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-8"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Account Settings</h3>
                        <p class="text-sm text-muted-foreground mt-2 max-w-xs mx-auto">Access your personal information, security settings, and billing data.</p>
                    </div>
                    <button class="mt-4 px-8 py-2.5 rounded-xl bg-primary text-primary-foreground text-sm font-bold shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">Enable Pro Features</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
