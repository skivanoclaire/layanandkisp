<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ $kegiatan->judul }}</title>
    <meta name="description" content="{{ $kegiatan->judul }} — {{ $kegiatan->tanggal_label }}, {{ $kegiatan->tempat }}">
    <link rel="icon" href="{{ asset('logokaltarafix.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-green-50 to-gray-100 min-h-screen text-gray-800">
    <header class="bg-white shadow-sm">
        <div class="max-w-3xl mx-auto px-4 py-3 flex items-center gap-3">
            <a href="{{ route('kegiatan.show', $kegiatan) }}" class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('logokaltarafix.png') }}" alt="Logo Kaltara" class="w-10 h-10 shrink-0">
                <div class="min-w-0">
                    <div class="text-sm font-bold text-gray-800 leading-tight">E-Layanan DKISP</div>
                    <div class="text-xs text-gray-600 leading-tight">Pemerintah Provinsi Kalimantan Utara</div>
                </div>
            </a>
            <div class="ml-auto text-xs text-right">
                @auth
                    <span class="text-gray-500">Masuk sebagai</span>
                    <div class="font-semibold text-gray-700 truncate max-w-[10rem]">{{ auth()->user()->name }}</div>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-6">
        <section class="bg-green-700 text-white rounded-2xl p-5 shadow mb-5">
            <div class="text-xs uppercase tracking-wider text-green-200 font-semibold">@yield('title')</div>
            <h1 class="text-lg sm:text-xl font-bold leading-snug mt-1">{{ $kegiatan->judul }}</h1>
            <dl class="mt-3 grid gap-1.5 text-sm text-green-50">
                <div class="flex gap-2"><dt class="w-20 shrink-0 text-green-200">Tanggal</dt><dd>{{ $kegiatan->tanggal_label }}</dd></div>
                @if($kegiatan->waktu)
                    <div class="flex gap-2"><dt class="w-20 shrink-0 text-green-200">Waktu</dt><dd>{{ $kegiatan->waktu }}</dd></div>
                @endif
                <div class="flex gap-2"><dt class="w-20 shrink-0 text-green-200">Tempat</dt><dd>{{ $kegiatan->tempat }}</dd></div>
            </dl>
        </section>

        @yield('content')
    </main>

    <footer class="max-w-3xl mx-auto px-4 pb-8 text-center text-xs text-gray-500">
        @if($kegiatan->kontak)
            <p class="mb-1">Informasi: {{ $kegiatan->kontak }}</p>
        @endif
        {{ $kegiatan->penyelenggara ?: 'Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Kalimantan Utara' }}
    </footer>

    @stack('scripts')
</body>
</html>
