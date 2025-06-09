<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Layanan DKISP Provinsi Kalimantan Utara</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="scroll-smooth bg-white font-sans text-gray-800">

    {{-- HEADER --}}
    <header class="border-b shadow-sm px-6 py-6
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
            <a href="http://helpdesk.kaltaraprov.go.id/"
                class="bg-emerald-500 hover:bg-emerald-600 text-white text-base sm:text-lg px-6 py-3 rounded-md">
                Manual
            </a>
            <a href="https://wa.me/6282253731353"
                class="bg-emerald-500 hover:bg-emerald-600 text-white text-base sm:text-lg px-6 py-3 rounded-md">
                WhatsApp
            </a>
            <a href="{{ route('login') }}"
                class="bg-emerald-500 hover:bg-emerald-600 text-white text-base sm:text-lg px-6 py-3 rounded-md">
                Login
            </a>
        </div>
    </header>

    {{-- KONTEN HALAMAN --}}
    <main class="px-6 py-12">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="text-center py-8 text-gray-500 text-base border-t">
        &copy; {{ now()->year }} DKISP Provinsi Kalimantan Utara. All rights reserved.
    </footer>

</body>

</html>