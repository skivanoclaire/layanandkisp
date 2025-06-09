<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengguna - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @stack('styles')
</head>

<body class="bg-green-50 min-h-screen flex text-gray-800" x-data="{ sidebarOpen: true }">
<!-- Overlay untuk mobile -->
<div x-show="sidebarOpen" class="fixed inset-0 bg-black bg-opacity-40 z-40 md:hidden" @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="bg-green-800 text-white shadow-md min-h-screen w-64 transform fixed z-50 md:static transition-transform duration-300 ease-in-out">

        <div class="p-6">
            <a href="/dashboard" class="text-2xl font-bold" x-show="sidebarOpen">E-Layanan</a>
            <a href="/dashboard" class="text-2xl font-bold" x-show="!sidebarOpen">EL</a>
        </div>
        <nav class="mt-6 space-y-2">
            <a href="/dashboard"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-green-600 {{ request()->is('dashboard') ? 'bg-green-600' : '' }}">
                Dashboard
            </a>
            <a href="/profile"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-green-600 {{ request()->is('profile') ? 'bg-green-600' : '' }}">
                Profile Pengguna
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">

        <!-- Navbar -->
        <header class="bg-white shadow p-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="text-2xl focus:outline-none text-green-700">
                    ☰
                </button>
                <img src="/logokaltarafix.png" alt="Logo" class="w-8 h-10">
                <span class="text-lg font-semibold text-green-700">Dashboard Pengguna</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="hidden md:block text-gray-600">Halo, {{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm font-semibold">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-6 bg-white shadow-inner">


            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-green-500 text-white text-center text-sm py-4">
            &copy; {{ date('Y') }} Aptika-DKISP Kalimantan Utara. All rights reserved.
        </footer>
    </div>

    @stack('scripts')
</body>

</html>