<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Layanan DKISP Provinsi Kalimantan Utara</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('kaltara.svg') }}">
    <!-- Fallback untuk browser lama -->
    <link rel="alternate icon" href="{{ asset('kaltara.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="scroll-smooth bg-white font-sans text-gray-800">

    {{-- HEADER --}}
    <header
        class="shadow-sm px-6 py-6
                    flex flex-col sm:flex-row sm:justify-between sm:items-center
                    space-y-6 sm:space-y-0">

        {{-- Logo + Judul --}}
        <div class="flex items-center gap-6">
            <img src="{{ asset('logokaltarafix.png') }}" alt="Logo" class="w-16 h-20">
            <div class="text-center sm:text-left">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight">
                    E-Layanan<br class="sm:hidden"> DKISP
                </h1>
                <p class="text-base sm:text-lg text-gray-500 leading-snug">
                    Layanan Digital Dinas Komunikasi Informatika<br class="hidden lg:inline">
                    Statistik dan Persandian Provinsi Kalimantan Utara
                </p>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex flex-wrap gap-4 sm:gap-5">
            <a href="/kebijakanlayanan.pdf"
                class="bg-emerald-500 hover:bg-emerald-600 text-white text-base sm:text-lg px-6 py-3 rounded-md">
                Kebijakan&nbsp;Layanan
            </a>
            <a href="/manual-e-layanan.pdf"
                class="bg-emerald-500 hover:bg-emerald-600 text-white text-base sm:text-lg px-6 py-3 rounded-md">
                Manual
            </a>
            <a href="https://wa.me/6282253731353"
                class="bg-emerald-500 hover:bg-emerald-600 text-white text-base sm:text-lg px-6 py-3 rounded-md">
                WhatsApp
            </a>
            <a href="https://chatgpt.com/g/g-68d4e245a8348191b95faca91144169f-asisten-spbe-kalimantan-utara"
                class="bg-emerald-500 hover:bg-emerald-600 text-white text-base sm:text-lg px-6 py-3 rounded-md">
                AI SPBE Kaltara
            </a>
            @auth
                @if (Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                        class="bg-emerald-500 hover:bg-emerald-600 text-white text-base sm:text-lg px-6 py-3 rounded-md">
                        Admin Dashboard
                    </a>
                @elseif (Auth::user()->role === 'user')
                    <a href="{{ route('user.dashboard') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white text-base sm:text-lg px-6 py-3 rounded-md">
                        User Dashboard
                    </a>
                @elseif (Auth::user()->role === 'operator-vidcon')
                    <a href="{{ route('op.tik.borrow.index') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white text-base sm:text-lg px-6 py-3 rounded-md">
                        Operator Dashboard
                    </a>
                @elseif (Auth::user()->role === 'admin-vidcon')
                    <a href="{{ route('admin.tik.borrow.index') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white text-base sm:text-lg px-6 py-3 rounded-md">
                        Admin Vidcon Dashboard
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}"
                    class="bg-emerald-500 hover:bg-emerald-600 text-white text-base sm:text-lg px-6 py-3 rounded-md">
                    Login
                </a>
            @endauth

        </div>
    </header>

    {{-- KONTEN HALAMAN --}}
    <main class="px-6 py-12">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="bg-green-200 text-center text-gray-700 text-sm py-4">
        &copy; {{ date('Y') }}
        DKISP Kalimantan Utara - Bidang Aplikasi Informatika.
        All rights reserved.

        <div class="mt-2 flex items-center justify-center space-x-3 text-xs">
            <span class="text-gray-600">Dikembangkan oleh : Bayu Adi H.</span>

            <!-- LinkedIn -->
            <a href="https://www.linkedin.com/in/noclaire/" target="_blank" class="text-blue-600 hover:text-blue-800"
                aria-label="LinkedIn">
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

</body>

</html>
