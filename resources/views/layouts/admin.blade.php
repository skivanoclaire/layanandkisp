<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Administrator @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @stack('styles')
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex" x-data="{ sidebarOpen: true }">

    <!-- Overlay untuk mobile -->
    <div x-show="sidebarOpen" class="fixed inset-0 bg-black bg-opacity-40 z-40 md:hidden" @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="bg-green-800 text-white shadow-md min-h-screen w-64 transform fixed top-0 left-0 z-50 transition-transform duration-300 ease-in-out">
        <div class="p-6">
            <a href="/admin" class="text-2xl font-bold text-green-600" x-show="sidebarOpen">E-Layanan</a>
            <a href="/admin" class="text-2xl font-bold text-green-600" x-show="!sidebarOpen">EL</a>
        </div>
        <nav class="mt-6 space-y-2">
            <a href="/admin"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-green-100 hover:text-green-600 {{ request()->is('admin') ? 'bg-green-100 text-green-600' : '' }}">
                Dashboard
            </a>
            <a href="/admin/permohonan"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-green-100 hover:text-green-600 {{ request()->is('admin/permohonan*') ? 'bg-green-100 text-green-600' : '' }}">
                Permohonan
            </a>
            <a href="/admin/monitorweb"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-green-100 hover:text-green-600 {{ request()->is('admin/web-monitor*') ? 'bg-green-100 text-green-600' : '' }}">
                Web Monitor
            </a>
            <a href="/admin/users"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-green-100 hover:text-green-600 {{ request()->is('admin/users*') ? 'bg-green-100 text-green-600' : '' }}">
                User Management
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div :class="sidebarOpen ? 'md:ml-64' : 'md:ml-0'" class="flex-1 flex flex-col transition-all duration-300">

        <!-- Navbar -->
        <header class="bg-green-200 shadow-md border-b p-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="text-2xl focus:outline-none transition-transform duration-300 transform" :class="sidebarOpen ? '' : 'rotate-180'">
                    ☰
                </button>
                <img src="/logokaltarafix.png" alt="Logo" class="w-8 h-10">
                <span class="text-lg font-semibold text-green-600">Dashboard Admin</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="hidden md:block text-gray-600">Halo, {{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="bg-green-500 hover:bg-green-600 text-black px-4 py-2 rounded-md text-sm font-semibold">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-6 bg-white shadow-sm rounded-md">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t text-center text-black-400 text-sm py-4">
            &copy; {{ date('Y') }} DKISP Kalimantan Utara. All rights reserved.
        </footer>

    </div>

    @stack('scripts')
</body>



</html>