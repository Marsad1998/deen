<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'DEEN Electronics'))</title>

    <!-- Tailwind CSS (CDN for standalone & shared-hosting compatibility) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        deen: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</head>
<body class="flex flex-col min-h-full text-slate-800 antialiased font-sans">
    <!-- Top Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-deen-600 flex items-center justify-center text-white font-bold text-xl shadow-md">
                        D
                    </div>
                    <div>
                        <a href="{{ url('/') }}" class="text-xl font-bold tracking-tight text-slate-900 hover:text-deen-600 transition">
                            DEEN <span class="text-deen-600 font-semibold">Electronics</span>
                        </a>
                        <p class="text-xs text-slate-500 hidden sm:block">Laravel 13 Architecture</p>
                    </div>
                </div>

                <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
                    <a href="{{ url('/') }}" class="text-deen-600 hover:text-deen-800 transition">Home</a>
                    <a href="{{ url('/health') }}" class="text-slate-600 hover:text-deen-600 transition" target="_blank">Health API</a>
                    <a href="{{ url('/health/db') }}" class="text-slate-600 hover:text-deen-600 transition" target="_blank">Database Health</a>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        Laravel v{{ app()->version() }}
                    </span>
                </nav>

                <div class="flex md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="text-slate-500 hover:text-slate-700 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div class="md:hidden pb-3 border-t border-slate-100 mt-2" x-show="mobileMenuOpen" x-cloak>
                <div class="flex flex-col space-y-2 pt-2">
                    <a href="{{ url('/') }}" class="text-deen-600 font-medium px-2 py-1">Home</a>
                    <a href="{{ url('/health') }}" class="text-slate-600 font-medium px-2 py-1" target="_blank">Health API</a>
                    <a href="{{ url('/health/db') }}" class="text-slate-600 font-medium px-2 py-1" target="_blank">Database Health</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-8 border-t border-slate-800 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 text-sm">
            <div>
                &copy; {{ date('Y') }} <strong>DEEN Electronics</strong>. Built with Laravel {{ app()->version() }} &amp; PHP {{ PHP_VERSION }}.
            </div>
            <div class="flex space-x-6 text-xs text-slate-400">
                <span>cPanel / Shared Hosting Ready</span>
                <span>•</span>
                <span>Zero VPS Dependency</span>
                <span>•</span>
                <span>Standard MySQL / PDO</span>
            </div>
        </div>
    </footer>
</body>
</html>
