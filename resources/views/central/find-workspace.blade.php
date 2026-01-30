<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Find Your Workspace - Central</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-background font-sans antialiased overflow-hidden relative selection:bg-primary/20 selection:text-primary">
    
    <!-- Animated Background -->
    <div class="absolute inset-0 z-0 w-full h-full overflow-hidden bg-[#fafafa] dark:bg-[#09090b]">
        <!-- Orbs -->
        <div class="absolute top-[20%] left-[20%] w-[50%] h-[50%] rounded-full bg-primary/5 blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-[10%] right-[10%] w-[40%] h-[40%] rounded-full bg-purple-500/5 blur-[120px] animate-pulse delay-700"></div>
        
        <!-- Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:40px_40px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 flex flex-col items-center justify-center min-h-screen px-4 sm:px-6 lg:px-8">
        
        <!-- Header/Nav -->
        <nav class="absolute top-0 left-0 w-full p-6 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center shadow-lg shadow-primary/20">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-white">
                        <path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3"></path>
                    </svg>
                </div>
                <span class="font-heading font-bold text-lg tracking-tight">Acme INC.</span>
            </div>
            <a href="{{ config('app.url') }}/login" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">Sign In to Central</a>
        </nav>

        <!-- Hero Section -->
        <div class="w-full max-w-3xl text-center space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-1000">
            
            <div class="space-y-4">
                <div class="inline-flex items-center rounded-full border border-primary/20 bg-primary/5 px-3 py-1 text-xs font-medium text-primary mb-4 backdrop-blur-sm">
                    <span class="flex h-2 w-2 rounded-full bg-primary mr-2"></span>
                    Multi-Tenant System Ready
                </div>
                
                <h1 class="text-5xl md:text-7xl font-heading font-extrabold tracking-tight text-foreground text-pretty">
                    Find your <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-purple-600 bg-300% animate-gradient">workspace</span>
                </h1>
                
                <p class="text-lg md:text-xl text-muted-foreground max-w-2xl mx-auto text-pretty">
                    Enter your organization's workspace url to continue to your dashboard. Enterprise-grade security included.
                </p>
            </div>

            <!-- Search Box -->
            <div class="max-w-md mx-auto relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-primary to-purple-600 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-1000 group-hover:duration-200"></div>
                
                <form action="{{ config('app.url') }}/find-workspace" method="POST" class="relative bg-white dark:bg-zinc-900 rounded-xl shadow-xl ring-1 ring-gray-900/5 dark:ring-white/10 p-2 flex items-center transition-all duration-300 hover:scale-[1.01]">
                    @csrf
                    
                    <div class="flex-1 relative">
                        <input type="text" name="workspace" required autofocus
                            class="block w-full border-0 bg-transparent py-4 pl-4 pr-12 text-foreground placeholder:text-gray-400 focus:ring-0 sm:text-base sm:leading-6 font-medium outline-none"
                            placeholder="workspace-name">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-400 text-sm font-medium">.localhost</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="ml-2 inline-flex items-center justify-center rounded-lg bg-primary p-3 text-sm font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-all duration-200 aspect-square">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-12 text-left">
                <div class="p-4 rounded-2xl bg-secondary/30 backdrop-blur-sm border border-white/5 hover:bg-secondary/50 transition-colors">
                    <div class="h-10 w-10 rounded-lg bg-blue-500/10 flex items-center justify-center mb-3 text-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path></svg>
                    </div>
                    <h3 class="font-heading font-semibold text-foreground">Secure by Design</h3>
                    <p class="text-sm text-muted-foreground mt-1">Tenant isolation ensures your data remains yours.</p>
                </div>
                <div class="p-4 rounded-2xl bg-secondary/30 backdrop-blur-sm border border-white/5 hover:bg-secondary/50 transition-colors">
                    <div class="h-10 w-10 rounded-lg bg-purple-500/10 flex items-center justify-center mb-3 text-purple-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></svg>
                    </div>
                    <h3 class="font-heading font-semibold text-foreground">Blazing Fast</h3>
                    <p class="text-sm text-muted-foreground mt-1">Optimized performance for heavy workloads.</p>
                </div>
                <div class="p-4 rounded-2xl bg-secondary/30 backdrop-blur-sm border border-white/5 hover:bg-secondary/50 transition-colors">
                    <div class="h-10 w-10 rounded-lg bg-emerald-500/10 flex items-center justify-center mb-3 text-emerald-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </div>
                    <h3 class="font-heading font-semibold text-foreground">Modern Stack</h3>
                    <p class="text-sm text-muted-foreground mt-1">Built with Laravel 11 and Tailwind v4.</p>
                </div>
            </div>

        </div>
        
    </div>
</body>
</html>
