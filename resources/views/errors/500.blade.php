<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Server Error</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased bg-zinc-950 text-white selection:bg-rose-500/30 selection:text-rose-200">
    
    <!-- Background Effects -->
    <div class="fixed inset-0 z-[-1] overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-rose-500/5 blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-orange-500/5 blur-[100px] animate-pulse" style="animation-delay: 2s"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808008_1px,transparent_1px),linear-gradient(to_bottom,#80808008_1px,transparent_1px)] bg-[size:24px_24px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
    </div>

    <div class="min-h-full flex flex-col items-center justify-center p-4 sm:p-6 lg:p-8">
        
        <div class="w-full max-w-md relative group">
            <!-- Glass Card -->
            <div class="absolute -inset-0.5 bg-gradient-to-tr from-rose-500/20 to-orange-500/20 rounded-3xl blur opacity-75 group-hover:opacity-100 transition duration-1000"></div>
            <div class="relative flex flex-col items-center text-center rounded-3xl border border-white/10 bg-zinc-900/50 backdrop-blur-2xl p-8 sm:p-12 shadow-2xl ring-1 ring-white/5">
                
                <!-- Icon -->
                <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-rose-500/10 to-orange-500/10 border border-white/5 flex items-center justify-center mb-6 shadow-inner animate-bounce duration-[3000ms]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-rose-400 drop-shadow-[0_0_15px_rgba(244,63,94,0.3)]"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>

                <h1 class="text-3xl sm:text-4xl font-heading font-bold bg-clip-text text-transparent bg-gradient-to-b from-white to-white/60 tracking-tight mb-3">
                    Internal Glitch
                </h1>
                
                <p class="text-zinc-400 text-lg mb-8 leading-relaxed">
                    Something went wrong on our end. Our engineers have been notified and are on it.
                </p>

                <div class="space-y-4 w-full">
                    <button onclick="window.location.reload()" class="relative w-full inline-flex items-center justify-center px-8 py-3.5 text-sm font-semibold text-white transition-all duration-200 bg-rose-600 rounded-xl hover:bg-rose-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-600 focus:ring-offset-zinc-900 overflow-hidden group/btn shadow-[0_0_20px_rgba(225,29,72,0.3)]">
                        <span class="absolute inset-0 w-full h-full bg-gradient-to-br from-white/20 to-transparent opacity-0 group-hover/btn:opacity-100 transition-opacity"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                        Try to Refresh
                    </button>
                    
                    <a href="{{ url('/dashboard') }}" class="inline-flex w-full items-center justify-center px-8 py-3.5 text-sm font-semibold text-zinc-400 hover:text-white transition-colors duration-200">
                        Take me Home
                    </a>
                </div>

                <div class="mt-8 pt-8 border-t border-white/5 w-full">
                    <p class="text-xs text-zinc-500">
                        Status Code: <span class="font-mono bg-white/5 px-1.5 py-0.5 rounded">500</span> Error ID: <span class="font-mono bg-white/5 px-1.5 py-0.5 rounded">{{ substr(md5(now()->toDateTimeString()), 0, 8) }}</span>
                    </p>
                </div>
            </div>
        </div>
        
    </div>
</body>
</html>
