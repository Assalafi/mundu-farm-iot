<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mundu Farm IoT @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
    <nav class="bg-gray-800 border-b border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" class="text-emerald-400 font-bold text-xl">Mundu Farm IoT</a>
                    <div class="hidden md:flex space-x-4">
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Dashboard</a>
                        <a href="{{ route('sensors.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('sensors.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Sensor History</a>
                        <a href="{{ route('pump.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('pump.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Pump Control</a>
                    </div>
                </div>
                <div>
                    <a href="/api/v1/sensors/latest" class="text-xs text-gray-500 hover:text-gray-300 border border-gray-600 px-3 py-1 rounded">API Docs</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div class="mb-4 bg-emerald-900 border border-emerald-700 text-emerald-300 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
