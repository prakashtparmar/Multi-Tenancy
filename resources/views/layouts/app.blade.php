<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ 
          sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
          mobileMenuOpen: false,
          theme: localStorage.getItem('theme') || 'system',
          colorTheme: localStorage.getItem('colorTheme') || 'zinc',
          
          init() {
              this.$watch('theme', val => localStorage.setItem('theme', val));
              this.$watch('colorTheme', val => localStorage.setItem('colorTheme', val));
              
              window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                  if (this.theme === 'system') {
                      this.theme = 'system'; 
                  }
              });
          },
          
          get isDark() {
              return this.theme === 'dark' || (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
          },
          
          toggleSidebar() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
          }
      }" :class="[
          isDark ? 'dark' : '', 
          colorTheme !== 'zinc' ? 'theme-' + colorTheme : ''
      ]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ tenant('id') ? ucfirst(tenant('id')) . ' - ' : (auth()->check() ? 'Central - ' : '') }}{{ config('app.name', 'Laravel') }}
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            scrollbar-gutter: stable;
        }

        h1,
        h2,
        h3,
        h4,
        .font-heading {
            font-family: 'Outfit', sans-serif;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background-color: rgba(161, 161, 170, 0.2);
            border-radius: 9999px;
            transition: background-color 0.3s;
        }

        ::-webkit-scrollbar-thumb:hover {
            background-color: rgba(161, 161, 170, 0.4);
        }

        ::selection {
            background-color: rgba(var(--primary), 0.2);
            color: rgb(var(--primary));
        }

        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body
    class="min-h-svh w-full bg-background text-foreground antialiased overflow-x-hidden selection:bg-primary/20 selection:text-primary transition-colors duration-300">

    <!-- Global Background Pattern -->
    <div class="fixed inset-0 z-[-1] bg-[#fafafa] dark:bg-[#09090b]">
        <div
            class="absolute inset-0 bg-[linear-gradient(to_right,#80808008_1px,transparent_1px),linear-gradient(to_bottom,#80808008_1px,transparent_1px)] bg-[size:24px_24px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]">
        </div>
    </div>

    <x-ui.toaster />

    <!-- Sidebar Component (Fixed Position, Independent of Content Flow) -->
    <x-layout.app-sidebar />

    <!-- Main Content Wrapper -->
    <!-- We use a static padding class (pl-0 md:pl-72) as a default to prevent layout shift before JS loads. -->
    <!-- Then we use Alpine to tighten it if collapsed. -->
    <div class="relative min-h-svh flex flex-col transition-all duration-300 ease-in-out md:pl-72"
        :class="sidebarCollapsed ? 'md:!pl-[4.5rem]' : ''">

        <!-- Header (Sticky) -->
        <x-layout.header />

        <!-- Page Content -->
        <main class="flex-1 w-full max-w-full relative overflow-hidden">
            <div class="relative flex-1 flex flex-col min-h-full">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-6 px-8 text-center text-xs text-muted-foreground border-t border-border/40 mt-auto">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </footer>
    </div>

    @include('partials.chat_widget')

</body>

</html>