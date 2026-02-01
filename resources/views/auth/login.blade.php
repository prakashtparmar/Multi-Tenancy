<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - {{ tenant('id') ? ucfirst(tenant('id')) : 'Central' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-background font-sans antialiased selection:bg-primary/20 selection:text-primary overflow-y-auto relative">

    <!-- Animated Background -->
    <div class="absolute inset-0 z-0 w-full h-full overflow-hidden bg-[#fafafa] dark:bg-[#09090b]">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-primary/5 blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-blue-500/5 blur-[100px] animate-pulse delay-1000"></div>
        <!-- Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>
    </div>

    <!-- Top Center Notifications -->
    <div class="fixed top-6 left-1/2 -translate-x-1/2 z-50 w-full max-w-md px-4 space-y-4 pointer-events-none">
        @if (session('error'))
            <div class="pointer-events-auto rounded-xl bg-red-50 dark:bg-red-900/10 p-4 border border-red-100 dark:border-red-900/20 shadow-lg animate-in slide-in-from-top-4 fade-in duration-300">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                            {{ session('error') }}
                        </h3>
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="pointer-events-auto rounded-xl bg-green-50 dark:bg-green-900/10 p-4 border border-green-100 dark:border-green-900/20 shadow-lg animate-in slide-in-from-top-4 fade-in duration-300">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                            {{ session('success') }}
                        </h3>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="relative z-10 flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md relative">

            <!-- Logo/Icon -->
            <div class="mx-auto h-16 w-16 rounded-2xl bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center shadow-lg shadow-primary/20 mb-8 transform transition-transform hover:scale-105 duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 text-white">
                    <path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3"></path>
                </svg>
            </div>

            <!-- Login Card -->
            <div class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-3xl shadow-2xl ring-1 ring-gray-900/5 dark:ring-white/10 p-8 sm:p-10 transition-all duration-300 hover:shadow-primary/5">

                <h2 class="text-2xl font-heading font-bold tracking-tight text-center text-foreground mb-1">
                    Welcome back
                </h2>
                <p class="text-sm text-center text-muted-foreground mb-8">
                    @if(tenant('id'))
                        Sign in to <span class="text-primary font-semibold">{{ ucfirst(tenant('id')) }}</span> workspace
                    @else
                        Sign in to your account
                    @endif
                </p>

                <form class="space-y-6" action="{{ tenant() ? request()->getSchemeAndHttpHost() . '/login' : config('app.url').'/login' }}" method="POST">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium leading-6 text-foreground">Email address</label>
                        <div class="mt-2 relative">
                            <input id="email" name="email" type="email" autocomplete="email" required
                                value="{{ old('email', \Illuminate\Support\Facades\Cookie::get('saved_email')) }}"
                                class="block w-full rounded-xl border-0 bg-secondary/30 py-3 px-4 text-foreground shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-white/10 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all duration-200 outline-none hover:bg-secondary/50">
                            @error('email')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-sm font-medium leading-6 text-foreground">Password</label>
                            <div class="text-sm">
                                <a href="#" class="font-semibold text-primary hover:text-primary/80 transition-colors">Forgot password?</a>
                            </div>
                        </div>
                        <div class="mt-2 text-red-500">
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                class="block w-full rounded-xl border-0 bg-secondary/30 py-3 px-4 text-foreground shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-white/10 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all duration-200 outline-none hover:bg-secondary/50">
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="remember-me" name="remember" type="checkbox"
                            {{ \Illuminate\Support\Facades\Cookie::get('saved_email') ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary bg-secondary/30 cursor-pointer">
                        <label for="remember-me" class="ml-2 block text-sm leading-6 text-muted-foreground cursor-pointer select-none">Remember me</label>
                    </div>

                    <div>
                        <button type="submit"
                            class="flex w-full justify-center rounded-xl bg-primary px-3 py-3 text-sm font-semibold leading-6 text-primary-foreground shadow-sm hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98]">
                            Sign in
                        </button>
                    </div>
                </form>

                @if(tenant('id'))
                <div class="mt-8 text-center">
                    <p class="text-xs text-muted-foreground">
                        Not the right workspace?
                        <a href="{{ config('app.url') }}" class="font-semibold text-primary hover:text-primary/80 transition-colors ml-1">Switch Workspace</a>
                    </p>
                </div>
                @endif
            </div>

            <!-- Footer -->
            <p class="mt-8 text-center text-xs text-muted-foreground">
                Protected by enterprise-grade security
            </p>
        </div>
    </div>
</body>
</html>
