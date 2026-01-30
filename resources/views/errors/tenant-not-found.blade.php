<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Workspace Not Found</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased bg-zinc-950 text-white selection:bg-indigo-500/30 selection:text-indigo-200">
    
    <!-- Background Effects -->
    <div class="fixed inset-0 z-[-1] overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-500/5 blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-purple-500/5 blur-[100px] animate-pulse" style="animation-delay: 2s"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808008_1px,transparent_1px),linear-gradient(to_bottom,#80808008_1px,transparent_1px)] bg-[size:24px_24px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
    </div>

    <div class="min-h-full flex flex-col items-center justify-center p-4 sm:p-6 lg:p-8">
        
        <div class="w-full max-w-md relative group">
            <!-- Glass Card -->
            <div class="absolute -inset-0.5 bg-gradient-to-tr from-indigo-500/20 to-purple-500/20 rounded-3xl blur opacity-75 group-hover:opacity-100 transition duration-1000"></div>
            <div class="relative flex flex-col items-center text-center rounded-3xl border border-white/10 bg-zinc-900/50 backdrop-blur-2xl p-8 sm:p-12 shadow-2xl ring-1 ring-white/5">
                
                <!-- Icon -->
                <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-white/5 flex items-center justify-center mb-6 shadow-inner animate-in zoom-in duration-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-indigo-400 drop-shadow-[0_0_15px_rgba(99,102,241,0.3)]"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                </div>

                <h1 class="text-3xl sm:text-4xl font-heading font-bold bg-clip-text text-transparent bg-gradient-to-b from-white to-white/60 tracking-tight mb-3">
                    Workspace Not Found
                </h1>
                
                <p class="text-zinc-400 text-lg mb-8 leading-relaxed">
                    We couldn't find a workspace associated with <br/>
                    <span class="font-mono text-indigo-300 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20 text-sm mt-1 inline-block">{{ request()->getHost() }}</span>
                </p>

                <div class="space-y-4 w-full">
                    <a href="{{ config('app.url') }}" class="relative w-full inline-flex items-center justify-center px-8 py-3.5 text-sm font-semibold text-white transition-all duration-200 bg-indigo-600 rounded-xl hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 focus:ring-offset-zinc-900 overflow-hidden group/btn shadow-[0_0_20px_rgba(79,70,229,0.3)] hover:shadow-[0_0_30px_rgba(79,70,229,0.5)]">
                        <span class="absolute inset-0 w-full h-full bg-gradient-to-br from-white/20 to-transparent opacity-0 group-hover/btn:opacity-100 transition-opacity"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        Return to Central Home
                    </a>
                </div>

                <div class="mt-8 pt-8 border-t border-white/5 w-full">
                    <p class="text-xs text-zinc-500">
                        Need help? <a href="#" class="text-indigo-400 hover:text-indigo-300 transition-colors">Contact Support</a>
                    </p>
                </div>
            </div>
        </div>
        
    </div>
</body>
</html>
