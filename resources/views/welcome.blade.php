@extends('layouts.app')

@section('bodyClass', 'hero-bg-animated')

@section('content')
    {{-- Cosmic Hybrid background: nebula + stars + solar system --}}
    <div class="cosmic-bg" aria-hidden="true">
        <div class="nebula nebula-1"></div>
        <div class="nebula nebula-2"></div>
        <div class="nebula nebula-3"></div>

        @for ($i = 0; $i < 80; $i++)
            @php
                $top = rand(0, 100);
                $left = rand(0, 100);
                $size = rand(1, 3);
                $delay = rand(0, 60) / 10;
                $duration = rand(20, 50) / 10;
            @endphp
            <span class="star" style="top:{{ $top }}%;left:{{ $left }}%;width:{{ $size }}px;height:{{ $size }}px;animation-delay:-{{ $delay }}s;animation-duration:{{ $duration }}s;"></span>
        @endfor

        <div class="solar-system">
            <div class="sun"></div>
            <div class="orbit orbit-1"><div class="planet planet-1"></div></div>
            <div class="orbit orbit-2"><div class="planet planet-2"></div></div>
            <div class="orbit orbit-3"><div class="planet planet-3"></div></div>
            <div class="orbit orbit-4"><div class="planet planet-4"></div></div>
        </div>
    </div>
    <div class="w-full max-w-7xl mx-auto px-4 md:px-8 lg:px-16 py-10">
        {{-- Langkah awal: pengguna baru perlu masuk dulu sebelum mengajukan layanan --}}
        <div class="bg-white border border-emerald-200 shadow rounded-2xl p-6 md:p-8 mb-8 flex flex-col md:flex-row items-center gap-6">
            <div class="flex-1">
                <h1 class="text-2xl md:text-3xl font-bold text-green-800">Layanan Digital Diskominfo Kaltara</h1>
                <p class="mt-2 text-gray-700">Untuk mengajukan layanan, masuk dulu dengan akun SSO Portal Kaltara. Setelah masuk, Anda diarahkan ke dashboard dan dapat memilih layanan yang dibutuhkan.</p>

                <ol class="mt-5 grid gap-3 sm:grid-cols-3 text-sm text-gray-800">
                    <li class="flex items-start gap-3 bg-green-50 rounded-lg p-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-700 text-white font-bold">1</span>
                        <span>Masuk dengan akun SSO Portal Kaltara</span>
                    </li>
                    <li class="flex items-start gap-3 bg-green-50 rounded-lg p-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-700 text-white font-bold">2</span>
                        <span>Pilih layanan yang dibutuhkan</span>
                    </li>
                    <li class="flex items-start gap-3 bg-green-50 rounded-lg p-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-700 text-white font-bold">3</span>
                        <span>Ajukan dan pantau statusnya di dashboard</span>
                    </li>
                </ol>

                <div class="mt-6">
                    <a href="{{ config('sso-portal.application_id') ? route('sso-portal.login') : route('login') }}"
                        class="inline-flex items-center px-5 py-3 rounded-md bg-green-700 text-white font-semibold shadow hover:bg-green-800">
                        Masuk dengan SSO
                    </a>
                    <p class="mt-3 text-xs text-gray-500">Belum punya akun? Akun dibuat otomatis saat pertama kali masuk dengan SSO.</p>
                </div>
            </div>
            <img src="{{ asset('layanankaltara2.png') }}" alt="Ilustrasi layanan" class="hidden md:block w-56 lg:w-64 shrink-0">
        </div>

        <div class="mb-8">
            <label for="cari-layanan" class="sr-only">Cari layanan</label>
            <input id="cari-layanan" type="search" placeholder="Cari layanan, misalnya VPN, TTE, atau email"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-green-600 focus:ring-green-600">
        </div>
                @php
                    $items = [
                        [
                            'icon' => '📄',
                            'label' => 'Rekomendasi',
                            'slug' => 'rekomendasi',
                            'image' => 'rekomendasi.png',
                            'link' => '/login',
                        ],
                        ['icon' => '🛡️', 'label' => 'TTE', 'slug' => 'tte', 'image' => 'tte.png', 'link' => '/login'],

                        [
                            'icon' => '🌐',
                            'label' => 'Subdomain dan PSE',
                            'slug' => 'subdomain',
                            'image' => 'subdomain.png',
                            'link' => '/login',
                        ],
                        [
                            'icon' => '🗄️',
                            'label' => 'Pusat Data',
                            'slug' => 'pusatdata',
                            'image' => 'hosting.png',
                            'link' => '/login',
                        ],
                        [
                            'icon' => '✉️',
                            'label' => 'Email',
                            'slug' => 'email',
                            'image' => 'email.png',
                            'link' => '/login',
                        ],
                        [
                            'icon' => '✂️',
                            'label' => 'Pemendek Tautan',
                            'slug' => 'pemendek-tautan',
                            'image' => 'subdomain.png',
                            'link' => '/login',
                        ],

                        [
                            'icon' => '📈',
                            'label' => 'Portal Data',
                            'slug' => 'portal-data',
                            'image' => 'portal-data.png',
                            'link' => 'https://data.kaltaraprov.go.id',
                        ],
                        [
                            'icon' => '⚖️',
                            'label' => 'Manajemen Risiko SPBE',
                            'slug' => 'manajemen-risiko-spbe-kaltara',
                            'image' => 'manrisk-spbe.png',
                            'link' => 'http://s.id/manrisk-spbe-kaltara',
                        ],
                        [
                            'icon' => '📖',
                            'label' => 'SPBE',
                            'slug' => 'SPBE',
                            'image' => 'spbe.png',
                            'link' => '/arsitektur',
                        ],
                        // ['icon' => '📹', 'label' => 'Peliputan', 'slug' => 'peliputan', 'image' => 'peliputan.png', 'link' => '/login'],
                        // ['icon' => '📰', 'label' => 'Publikasi', 'slug' => 'publikasi', 'image' => 'publikasi.png', 'link' => '/login'],
                        // ['icon' => '🎞️', 'label' => 'Konten Multimedia', 'slug' => 'konten-multimedia', 'image' => 'konten-multimedia.png', 'link' => '/login'],
                        [
                            'icon' => '🏛️',
                            'label' => 'Pengaduan',
                            'slug' => 'span-lapor',
                            'image' => 'lapor.png',
                            'link' => 'https://www.lapor.go.id/',
                        ],

                        // ['icon' => '🖧', 'label' => 'SPLP', 'slug' => 'splp', 'image' => 'splp.png', 'link' => '/login'],

                        [
                            'icon' => '📘',
                            'label' => 'PPID',
                            'slug' => 'ppid',
                            'image' => 'ppid.png',
                            'link' => 'https://ppid.kaltaraprov.go.id',
                        ],
                        [
                            'icon' => '📡',
                            'label' => 'Jaringan Internet',
                            'slug' => 'jaringan-internet',
                            'image' => 'jaringan-internet.png',
                            'link' => '/login',
                        ],
                        ['icon' => '🔗', 'label' => 'VPN', 'slug' => 'vpn', 'image' => 'vpn.png', 'link' => '/login'],
                        [
                            'icon' => '☁️',
                            'label' => 'Cloud Storage',
                            'slug' => 'cloud-storage',
                            'image' => 'cloud-storage.png',
                            'link' => '/login',
                        ],
                        [
                            'icon' => '🎥',
                            'label' => 'Zoom/Youtube Live Streaming',
                            'slug' => 'zoom',
                            'image' => 'zoom.png',
                            'link' => '/login',
                        ],
                                                ];
                @endphp

        @php
            $slugInformasi = ['portal-data', 'manajemen-risiko-spbe-kaltara', 'SPBE', 'span-lapor', 'ppid'];
            $informasi = array_values(array_filter($items, fn ($i) => in_array($i['slug'], $slugInformasi, true)));

            // Mengikuti urutan menu User (SidebarMenuService). Isi menu sebenarnya tergantung hak akses akun.
            // slug = bagian detail di bawah halaman; kosong berarti langsung ke halaman masuk.
            $layanan = [
                ['icon' => '✉️', 'label' => 'Email', 'slug' => 'email', 'anak' => ['Permohonan Email', 'Reset Password Email']],
                ['icon' => '📄', 'label' => 'Rekomendasi Aplikasi', 'slug' => 'rekomendasi', 'anak' => ['Usulan Pertimbangan', 'Fase Pengembangan']],
                ['icon' => '🌐', 'label' => 'Subdomain', 'slug' => 'subdomain', 'anak' => ['Pendaftaran Baru', 'Perubahan IP', 'Perubahan Nama', 'Pembaruan Data']],
                ['icon' => '✂️', 'label' => 'Pemendek Tautan', 'slug' => 'pemendek-tautan', 'anak' => []],
                ['icon' => '🆔', 'label' => 'Pendaftaran Sistem Elektronik (PSE)', 'slug' => '', 'anak' => []],
                ['icon' => '🏛️', 'label' => 'Pembaruan Data Arsitektur', 'slug' => '', 'anak' => []],
                ['icon' => '🤖', 'label' => 'Konsultasi SPBE Berbasis AI', 'slug' => '', 'anak' => []],
                ['icon' => '🛡️', 'label' => 'Tanda Tangan Elektronik (TTE)', 'slug' => 'tte', 'anak' => ['Pendampingan Aktivasi dan Penggunaan', 'Pendaftaran Akun Baru', 'Reset Passphrase', 'Pembaruan Sertifikat']],
                ['icon' => '🎥', 'label' => 'Zoom/YouTube Live', 'slug' => 'zoom', 'anak' => []],
                ['icon' => '📡', 'label' => 'Internet', 'slug' => 'jaringan-internet', 'anak' => ['Lapor Gangguan', 'Starlink Jelajah', 'Pasang Baru']],
                ['icon' => '📅', 'label' => 'Peminjaman Ruang Rapat', 'slug' => '', 'anak' => []],
                ['icon' => '🔗', 'label' => 'Jaringan Privat/VPN', 'slug' => 'vpn', 'anak' => ['Pendaftaran Baru', 'Reset Akun', 'Akses JIP PDNS']],
                ['icon' => '🗄️', 'label' => 'Pusat Data/Komputasi', 'slug' => 'pusatdata', 'anak' => ['Kunjungan/Colocation', 'VPS/VM', 'Backup', 'Cloud Storage']],
                ['icon' => '🧩', 'label' => 'Integrasi (SPLP)', 'slug' => '', 'anak' => ['Endpoint Penyedia', 'Akses Konsumen', 'Uji Coba (Sandbox)', 'Perubahan/Perpanjangan', 'Penonaktifan/Pencabutan']],
                ['icon' => '🗃️', 'label' => 'Berbagi Pakai Data (DTSEN)', 'slug' => '', 'anak' => ['Akun Layanan', 'Permintaan Data', 'Perpanjangan Akses', 'Laporan Pemanfaatan', 'Pemusnahan Data', 'Insiden Keamanan', 'Pengaduan & Masukan']],
                ['icon' => '📊', 'label' => 'Survei & Kuesioner', 'slug' => '', 'anak' => ['Survei Kepuasan Layanan', 'Kuesioner Kualitas Portal', 'Kuesioner Tata Kelola Keamanan']],
            ];
        @endphp

        <h2 class="text-xl font-bold text-gray-900">Layanan <span class="text-sm font-normal text-green-700">(perlu masuk)</span></h2>
        <p class="text-sm text-gray-600 mb-4">Klik layanan untuk membaca penjelasannya, lalu masuk untuk mengajukan. Layanan yang tampil di menu Anda tergantung hak akses akun.</p>
        <div id="daftar-layanan" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-10">
            @foreach ($layanan as $item)
                <a href="{{ $item['slug'] !== '' ? '#'.$item['slug'] : route('login') }}"
                    data-cari="{{ strtolower($item['label'].' '.implode(' ', $item['anak'])) }}"
                    class="kartu-layanan bg-white border border-gray-200 shadow rounded-xl p-4 flex flex-col text-left hover:shadow-lg hover:border-emerald-400 transition">
                    <div class="flex items-center gap-3">
                        <div class="text-3xl">{{ $item['icon'] }}</div>
                        <div class="text-sm font-semibold text-gray-800">{{ $item['label'] }}</div>
                    </div>
                    @if (! empty($item['anak']))
                        <ul class="mt-3 text-xs text-gray-600 list-disc list-inside space-y-0.5">
                            @foreach ($item['anak'] as $anak)
                                <li>{{ $anak }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <span class="mt-3 text-xs text-green-700">Perlu masuk</span>
                </a>
            @endforeach
        </div>

        <h2 class="text-xl font-bold text-gray-900">Informasi dan tautan <span class="text-sm font-normal text-gray-600">(tanpa masuk)</span></h2>
        <p class="text-sm text-gray-600 mb-4">Dapat dibuka langsung tanpa akun.</p>
        <div id="daftar-informasi" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-10">
            @foreach ($informasi as $item)
                @php $langsung = str_starts_with($item['link'], 'http') || str_starts_with($item['link'], '/'); @endphp
                <a href="{{ $langsung ? $item['link'] : '#'.$item['slug'] }}"
                    @if (str_starts_with($item['link'], 'http')) target="_blank" rel="noopener" @endif
                    data-cari="{{ strtolower($item['label']) }}"
                    class="kartu-layanan bg-white border border-gray-200 shadow rounded-xl p-4 flex flex-col items-center text-center hover:shadow-lg hover:border-emerald-400 transition">
                    <div class="text-3xl mb-2">{{ $item['icon'] }}</div>
                    <div class="text-sm font-semibold text-gray-800">{{ $item['label'] }}</div>
                    <span class="mt-1 text-xs text-gray-500">{{ str_starts_with($item['link'], 'http') ? 'Buka situs' : 'Buka halaman' }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <script>
        (function () {
            const input = document.getElementById('cari-layanan');
            if (!input) return;
            input.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                document.querySelectorAll('.kartu-layanan').forEach(function (el) {
                    el.classList.toggle('hidden', q !== '' && !(el.dataset.cari || '').includes(q));
                });
            });
        })();
    </script>

    <!-- Section Target Content -->
    <div class="max-w-7xl mx-auto mt-20 space-y-1">
        @foreach ($items as $item)
            <section id="{{ $item['slug'] }}" class="pt-24 scroll-mt-24">
                <div
                    class="bg-gradient-to-r from-green-100 to-blue-100 p-8 rounded-lg shadow flex flex-col md:flex-row items-start gap-8"
                    data-aos="fade-up" data-aos-duration="600">
                    <div class="w-32 flex-shrink-0 hidden md:block">
                        <img src="{{ asset($item['image']) }}" alt="{{ $item['label'] }}" class="w-full">
                    </div>
                    <div class="flex-grow">
                        <h2 class="text-4xl font-bold text-green-800 mb-6">{{ $item['label'] }}</h2>
                        <div class="text-justify text-lg text-gray-800 leading-relaxed space-y-4">
                            @include('partials.beranda-detail', ['item' => $item])
                            @if ($item['slug'] === 'subdomain')
                                @include('partials.beranda-detail', ['item' => ['slug' => 'pse', 'label' => 'PSE', 'link' => '/login']])
                            @endif
                        </div>

                        <div class="mt-6">
                            <a href="{{ $item['link'] }}"
                                class="inline-block bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-md text-sm font-semibold">
                                Ajukan Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @endforeach
    </div>
@endsection
