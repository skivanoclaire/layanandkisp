<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Administrator @yield('title')</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('kaltara.svg') }}">
    <!-- Fallback untuk browser lama -->
    <link rel="alternate icon" href="{{ asset('kaltara.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @stack('styles')
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex" x-data="{ sidebarOpen: true }">

    <!-- Overlay untuk mobile -->
    <div x-show="sidebarOpen" class="fixed inset-0 bg-black bg-opacity-40 z-40 md:hidden" @click="sidebarOpen = false">
    </div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="bg-green-800 text-white shadow-md min-h-screen w-64 transform fixed top-0 left-0 z-50 transition-transform duration-300 ease-in-out">
        <div class="p-6">
            <a href="/admin" class="text-2xl font-bold text-green-600" x-show="sidebarOpen">E-Layanan</a>
            <a href="/admin" class="text-2xl font-bold text-green-600" x-show="!sidebarOpen">EL</a>
        </div>
        <nav class="mt-6 space-y-2">
            {{-- Aset TIK (Admin + Admin Vidcon) --}}
            <a href="{{ route('admin.tik.assets.index') }}"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-green-100 hover:text-green-600
   {{ request()->routeIs('admin.tik.assets.*') || request()->routeIs('admin.tik.categories.*') ? 'bg-green-100 text-green-600' : '' }}">
                Inventaris Digital
            </a>
            <a href="{{ route('admin.tik.borrow.index') }}"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-green-100 hover:text-green-600
   {{ request()->routeIs('admin.tik.borrow.*') ? 'bg-green-100 text-green-600' : '' }}">
                Laporan Peminjaman
            </a>

        </nav>


    </aside>

    <!-- Main Content -->
    <div :class="sidebarOpen ? 'md:ml-64' : 'md:ml-0'" class="flex-1 flex flex-col transition-all duration-300">

        <!-- Navbar -->
        <header class="bg-green-200 shadow-md border-b p-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="text-2xl focus:outline-none transition-transform duration-300 transform"
                    :class="sidebarOpen ? '' : 'rotate-180'">
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

        {{-- FOOTER --}}
        <footer class="bg-green-200 border-t text-center text-gray-700 text-sm py-4">
            &copy; {{ date('Y') }}
            DKISP Kalimantan Utara - Bidang Aplikasi Informatika.
            All rights reserved.

            <div class="mt-2 flex items-center justify-center space-x-3 text-xs">
                <span class="text-gray-600">Dikembangkan oleh : Bayu Adi H.</span>

                <!-- LinkedIn -->
                <a href="https://www.linkedin.com/in/noclaire/" target="_blank"
                    class="text-blue-600 hover:text-blue-800" aria-label="LinkedIn">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M4.98 3.5C4.98 4.88 3.87 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1s2.48 1.12 2.48 2.5zM.5 8.5h4V24h-4V8.5zm7.5 0h3.8v2.1h.05c.53-1 1.82-2.05 3.75-2.05 4 0 4.75 2.63 4.75 6v9.45h-4V15.5c0-2.13 0-4.88-3-4.88s-3.48 2.25-3.48 4.73V24h-4V8.5z" />
                    </svg>
                </a>

                <!-- GitHub -->
                <a href="https://github.com/skivanoclaire" target="_blank" class="text-gray-800 hover:text-black"
                    aria-label="GitHub">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 .5C5.73.5.5 5.73.5 12c0 5.1 3.29 9.41 7.86 10.94.58.11.79-.25.79-.56 0-.27-.01-1.15-.02-2.09-3.2.7-3.88-1.54-3.88-1.54-.53-1.35-1.3-1.7-1.3-1.7-1.06-.72.08-.7.08-.7 1.17.08 1.78 1.2 1.78 1.2 1.04 1.78 2.73 1.27 3.4.97.11-.76.41-1.27.74-1.56-2.55-.29-5.23-1.28-5.23-5.7 0-1.26.45-2.28 1.19-3.08-.12-.29-.52-1.47.11-3.06 0 0 .97-.31 3.18 1.18a11.1 11.1 0 0 1 2.9-.39c.99 0 1.98.13 2.9.39 2.2-1.49 3.17-1.18 3.17-1.18.64 1.59.24 2.77.12 3.06.74.8 1.19 1.82 1.19 3.08 0 4.43-2.69 5.41-5.26 5.69.42.37.8 1.1.8 2.22 0 1.6-.02 2.89-.02 3.28 0 .31.21.67.8.56A10.99 10.99 0 0 0 23.5 12C23.5 5.73 18.27.5 12 .5z" />
                    </svg>
                </a>
            </div>
        </footer>


    </div>

    @stack('scripts')
</body>



</html>
