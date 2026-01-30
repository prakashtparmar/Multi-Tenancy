<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
          sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
          mobileMenuOpen: false,
          theme: localStorage.getItem('theme') || 'system',
          colorTheme: localStorage.getItem('colorTheme') || 'zinc',
          
          init() {
              this.$watch('theme', val => localStorage.setItem('theme', val));
              this.$watch('colorTheme', val => localStorage.setItem('colorTheme', val));
              
              // Watch for system preference changes if in system mode
              window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                  if (this.theme === 'system') {
                      // Trigger re-evaluation
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
      }" 
      :class="[
          isDark ? 'dark' : '', 
          colorTheme !== 'zinc' ? 'theme-' + colorTheme : ''
      ]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ tenant('id') ? ucfirst(tenant('id')) . ' - ' : (auth()->check() ? 'Central - ' : '') }}{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                scrollbar-gutter: stable;
            }

            /* Robust Layout Padding Classes */
            /* Robust Layout Padding Classes - Critical Fallback */
            @media (min-width: 768px) {
                .layout-padding-expanded { padding-left: 18rem !important; } /* w-72 */
                .layout-padding-collapsed { padding-left: 4.5rem !important; } /* w-[4.5rem] */
                /* Enforce margin if Tailwind fails */
                main.pl-72 { padding-left: 18rem; } 
            }

            h1, h2, h3, h4, .font-heading {
                font-family: 'Outfit', sans-serif;
            }

            /* Custom Premium Scrollbar */
            ::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }
            ::-webkit-scrollbar-track {
                background: transparent;
            }
            ::-webkit-scrollbar-thumb {
                background-color: rgba(161, 161, 170, 0.2); /* muted-foreground/20 approx */
                border-radius: 9999px;
                transition: background-color 0.3s;
            }
            ::-webkit-scrollbar-thumb:hover {
                background-color: rgba(161, 161, 170, 0.4); /* muted-foreground/40 approx */
            }

            /* Selection highlight */
            ::selection {
                background-color: rgba(var(--primary), 0.2);
                color: rgb(var(--primary));
            }

            /* Smooth scrolling */
            html {
                scroll-behavior: smooth;
            }
        </style>
    </head>
    <body class="min-h-svh w-full bg-background text-foreground antialiased overflow-x-hidden selection:bg-primary/20 selection:text-primary transition-colors duration-300">
        
        <!-- Global Background Pattern -->
        <div class="fixed inset-0 z-[-1] bg-[#fafafa] dark:bg-[#09090b]">
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808008_1px,transparent_1px),linear-gradient(to_bottom,#80808008_1px,transparent_1px)] bg-[size:24px_24px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
        </div>
        
        <x-ui.toaster />

        <div class="relative flex min-h-svh flex-col">
            <div class="flex flex-1">
                <!-- Sidebar Component -->
                <x-layout.app-sidebar />

                <div class="flex flex-1 flex-col transition-all duration-300 ease-in-out md:pl-72" 
                     :class="sidebarCollapsed ? 'md:pl-[4.5rem]' : 'md:pl-72'">
                    
                    <x-layout.header />
                    
                    <main class="flex flex-1 flex-col relative w-full max-w-full overflow-hidden">
                        <div class="relative flex-1 flex flex-col">
                            @yield('content')
                        </div>
                        
                        <!-- Footer -->
                        <footer class="py-6 px-8 text-center text-xs text-muted-foreground border-t border-border/40 mt-auto">
                            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                        </footer>
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
