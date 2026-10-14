<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arsitektur SPBE') - Pemerintah Provinsi Kalimantan Utara</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 sm:px-6 py-4 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('arsitektur.index') }}" class="flex items-center gap-3">
                <img src="{{ asset('logokaltarafix.png') }}" alt="Logo Kaltara" class="w-12 h-12">
                <div>
                    <div class="text-lg font-bold text-gray-800 leading-tight">Arsitektur SPBE</div>
                    <div class="text-sm text-gray-600 leading-tight">Pemerintah Provinsi Kalimantan Utara</div>
                </div>
            </a>
            <a href="{{ url('/') }}" class="text-sm font-semibold text-green-700 hover:underline">← Beranda E-Layanan</a>
        </div>
    </header>

    <main class="container mx-auto px-4 sm:px-6 py-6">
        @yield('content')
    </main>

    <footer class="container mx-auto px-4 sm:px-6 py-6 text-center text-xs text-gray-500">
        Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Kalimantan Utara
    </footer>

    @stack('scripts')
</body>
</html>
