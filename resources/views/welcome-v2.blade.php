@extends('layouts.app')

@section('headerClass', 'v2-header')
@section('mainClass', 'v2-main')

@section('content')
    @php
        $layanan = [
            ['icon' => '✉️', 'label' => 'Email', 'anak' => ['Permohonan Email', 'Reset Password Email']],
            ['icon' => '📄', 'label' => 'Rekomendasi Aplikasi', 'anak' => ['Usulan Pertimbangan', 'Fase Pengembangan']],
            ['icon' => '🌐', 'label' => 'Subdomain', 'anak' => ['Pendaftaran Baru', 'Perubahan IP', 'Perubahan Nama', 'Pembaruan Data']],
            ['icon' => '✂️', 'label' => 'Pemendek Tautan', 'anak' => []],
            ['icon' => '🆔', 'label' => 'Pendaftaran Sistem Elektronik (PSE)', 'anak' => []],
            ['icon' => '🏛️', 'label' => 'Pembaruan Data Arsitektur', 'anak' => []],
            ['icon' => '🤖', 'label' => 'Konsultasi SPBE Berbasis AI', 'anak' => []],
            ['icon' => '🛡️', 'label' => 'Tanda Tangan Elektronik (TTE)', 'anak' => ['Pendampingan Aktivasi dan Penggunaan', 'Pendaftaran Akun Baru', 'Reset Passphrase', 'Pembaruan Sertifikat']],
            ['icon' => '🎥', 'label' => 'Zoom/YouTube Live', 'anak' => []],
            ['icon' => '📡', 'label' => 'Internet', 'anak' => ['Lapor Gangguan', 'Starlink Jelajah', 'Pasang Baru']],
            ['icon' => '📅', 'label' => 'Peminjaman Ruang Rapat', 'anak' => []],
            ['icon' => '🔗', 'label' => 'Jaringan Privat/VPN', 'anak' => ['Pendaftaran Baru', 'Reset Akun', 'Akses JIP PDNS']],
            ['icon' => '🗄️', 'label' => 'Pusat Data/Komputasi', 'anak' => ['Kunjungan/Colocation', 'VPS/VM', 'Backup', 'Cloud Storage']],
            ['icon' => '🧩', 'label' => 'Integrasi (SPLP)', 'anak' => ['Endpoint Penyedia', 'Akses Konsumen', 'Uji Coba (Sandbox)', 'Perubahan/Perpanjangan', 'Penonaktifan/Pencabutan']],
            ['icon' => '🗃️', 'label' => 'Berbagi Pakai Data (DTSEN)', 'anak' => ['Akun Layanan', 'Permintaan Data', 'Perpanjangan Akses', 'Laporan Pemanfaatan', 'Pemusnahan Data', 'Insiden Keamanan', 'Pengaduan & Masukan']],
            ['icon' => '📊', 'label' => 'Survei & Kuesioner', 'anak' => ['Survei Kepuasan Layanan', 'Kuesioner Kualitas Portal', 'Kuesioner Tata Kelola Keamanan']],
        ];

        $informasi = [
            ['icon' => '📈', 'label' => 'Portal Data', 'link' => 'https://data.kaltaraprov.go.id'],
            ['icon' => '⚖️', 'label' => 'Manajemen Risiko SPBE', 'link' => 'http://s.id/manrisk-spbe-kaltara'],
            ['icon' => '📖', 'label' => 'Arsitektur SPBE', 'link' => '/arsitektur'],
            ['icon' => '🏛️', 'label' => 'Pengaduan (LAPOR!)', 'link' => 'https://www.lapor.go.id/'],
            ['icon' => '📘', 'label' => 'PPID', 'link' => 'https://ppid.kaltaraprov.go.id'],
        ];

        // Layanan yang punya isi detail di partial beranda (label => slug)
        $slugDetail = [
            'Email' => 'email',
            'Rekomendasi Aplikasi' => 'rekomendasi',
            'Subdomain' => 'subdomain',
            'Pendaftaran Sistem Elektronik (PSE)' => 'pse',
            'Pemendek Tautan' => 'pemendek-tautan',
            'Tanda Tangan Elektronik (TTE)' => 'tte',
            'Internet' => 'jaringan-internet',
            'Jaringan Privat/VPN' => 'vpn',
            'Pusat Data/Komputasi' => 'pusatdata',
            'Pembaruan Data Arsitektur' => 'arsitektur',
            'Konsultasi SPBE Berbasis AI' => 'konsultasi-ai',
            'Zoom/YouTube Live' => 'zoom',
            'Peminjaman Ruang Rapat' => 'peminjaman-ruang',
            'Integrasi (SPLP)' => 'splp',
            'Berbagi Pakai Data (DTSEN)' => 'dtsen',
            'Survei & Kuesioner' => 'survei',
        ];

        $ssoAktif = (bool) config('sso-portal.application_id');
        $tujuanMasuk = $ssoAktif ? route('sso-portal.login') : route('login');
    @endphp

    <div class="v2">
        {{-- Hero: langit malam hutan Kalimantan, pola ukir Dayak, dan enggang terbang --}}
        <section class="v2-hero">
            <div class="v2-glow" aria-hidden="true"></div>
            <img class="v2-mehndi" src="{{ asset('images/pola-mehndi.webp') }}" alt="" aria-hidden="true">

            <div class="v2-enggang-jalur" aria-hidden="true">
                <div class="v2-enggang-goyang">
                    <svg class="v2-enggang" viewBox="0 0 220 130" width="220" height="130" role="img" aria-label="Burung enggang terbang">
                        {{-- ekor --}}
                        <path d="M52 66 L8 86 L14 94 L58 76 Z" fill="#f8fafc" />
                        <path d="M52 66 L20 80" stroke="#f5c542" stroke-width="2" fill="none" />
                        {{-- badan --}}
                        <ellipse cx="100" cy="68" rx="52" ry="27" fill="#0f172a" />
                        <path d="M74 78 Q102 100 130 78 Q104 88 74 78 Z" fill="#f8fafc" opacity=".85" />
                        {{-- leher & kepala --}}
                        <ellipse cx="150" cy="48" rx="20" ry="19" fill="#0f172a" />
                        {{-- casque (tanduk di paruh) --}}
                        <path d="M158 34 Q186 26 180 46 L164 46 Z" fill="#f5c542" />
                        {{-- paruh --}}
                        <path d="M164 44 Q196 44 206 58 Q186 60 164 56 Z" fill="#f59e0b" />
                        <circle cx="156" cy="44" r="3" fill="#f8fafc" />
                        <circle cx="157" cy="44" r="1.4" fill="#0f172a" />
                        {{-- sayap (dikepak) --}}
                        <g class="v2-sayap">
                            <path d="M96 56 Q78 8 16 16 Q60 34 86 62 Z" fill="#1e293b" />
                            <path d="M96 56 Q76 26 30 26" stroke="#f5c542" stroke-width="2" fill="none" opacity=".7" />
                        </g>
                    </svg>
                </div>
            </div>

            <div class="v2-isi">
                <p class="v2-kicker">Pemerintah Provinsi Kalimantan Utara · Diskominfo</p>
                <h1 class="v2-judul">Layanan Digital<br><span>Kalimantan Utara</span></h1>
                <p class="v2-lead">Untuk mengajukan layanan, masuk dulu dengan akun SSO Portal Kaltara. Setelah masuk, pilih layanan yang Anda butuhkan dan pantau statusnya dari dashboard.</p>

                <ol class="v2-langkah">
                    <li><span>1</span> Masuk dengan SSO</li>
                    <li><span>2</span> Pilih layanan</li>
                    <li><span>3</span> Ajukan &amp; pantau</li>
                </ol>

                <div class="v2-aksi">
                    <a href="{{ $tujuanMasuk }}" class="v2-tombol-utama">Masuk dengan SSO</a>
                    <a href="#layanan" class="v2-tombol-kedua">Lihat layanan</a>
                </div>
                <p class="v2-catatan">Belum punya akun? Akun dibuat otomatis saat pertama kali masuk dengan SSO.</p>
            </div>
        </section>

        <div class="v2-badan" id="layanan">
            <div class="v2-cari">
                <label for="cari-layanan" class="sr-only">Cari layanan</label>
                <input id="cari-layanan" type="search" placeholder="Cari layanan, misalnya VPN, TTE, atau email">
            </div>

            <h2 class="v2-judul-bagian">Layanan <small>perlu masuk</small></h2>
            <p class="v2-sub">Layanan yang tampil di menu Anda tergantung hak akses akun.</p>

            <div id="daftar-layanan" class="v2-kisi">
                @foreach ($layanan as $item)
                    @php $slug = $slugDetail[$item['label']] ?? null; @endphp
                    <div data-cari="{{ strtolower($item['label'].' '.implode(' ', $item['anak'])) }}" class="v2-kartu kartu-layanan">
                        <div class="v2-kartu-atas">
                            <span class="v2-ikon">{{ $item['icon'] }}</span>
                            <span class="v2-nama">{{ $item['label'] }}</span>
                        </div>
                        @if (! empty($item['anak']))
                            <div class="v2-anak">
                                @foreach ($item['anak'] as $anak)
                                    <span>{{ $anak }}</span>
                                @endforeach
                            </div>
                        @endif
                        @if ($slug)
                            <details class="v2-detail">
                                <summary>Baca selengkapnya</summary>
                                <div class="v2-detail-isi">
                                    @include('partials.beranda-detail', ['item' => ['slug' => $slug, 'label' => $item['label'], 'link' => $tujuanMasuk]])
                                </div>
                            </details>
                        @endif
                        <a href="{{ $tujuanMasuk }}" class="v2-tag">Masuk untuk mengajukan →</a>
                    </div>
                @endforeach
            </div>

            <h2 class="v2-judul-bagian">Informasi <small>tanpa masuk</small></h2>
            <div id="daftar-informasi" class="v2-kisi v2-kisi-kecil">
                @foreach ($informasi as $item)
                    @php $eksternal = str_starts_with($item['link'], 'http'); @endphp
                    <a href="{{ $item['link'] }}" @if ($eksternal) target="_blank" rel="noopener" @endif data-cari="{{ strtolower($item['label']) }}" class="v2-kartu kartu-layanan">
                        <div class="v2-kartu-atas">
                            <span class="v2-ikon">{{ $item['icon'] }}</span>
                            <span class="v2-nama">{{ $item['label'] }}</span>
                        </div>
                        <span class="v2-tag">{{ $eksternal ? 'Buka situs ↗' : 'Buka halaman →' }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <style>
        .v2 {
            --tinta: #04130f;
            --hutan: #0b3d2e;
            --zamrud: #34d399;
            --emas: #f5c542;
            --kaca: rgba(255, 255, 255, .06);
            --garis: rgba(255, 255, 255, .12);
            color: #e6fff6;
            background: #04130f;
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            margin: 0;
        }
        .v2 * { box-sizing: border-box; }
        /* Area konten utama: gelap penuh, tanpa padding putih dari layout */
        main.v2-main { padding: 0; margin: 0; background: #04130f; min-height: 60vh; }

        /* Hero */
        .v2-hero {
            position: relative;
            overflow: hidden;
            padding: 4rem 1.25rem 4.5rem;
            background:
                radial-gradient(900px 480px at 85% -10%, rgba(52, 211, 153, .32), transparent 60%),
                radial-gradient(700px 400px at -10% 110%, rgba(245, 197, 66, .18), transparent 60%),
                linear-gradient(165deg, #04130f 0%, #0b3d2e 55%, #072a3a 100%);
        }
        /* Ornamen mehndi (dari desain/illustration-mehndi-ornament.zip): satu sudut dekoratif di hero, bukan ubin berulang */
        .v2-mehndi {
            position: absolute; right: -4%; top: -6%; width: min(560px, 62%); height: auto;
            transform: scaleX(-1); opacity: .2; pointer-events: none;
        }
        @media (max-width: 767px) { .v2-mehndi { opacity: .12; width: 75%; right: -20%; top: -2%; } }

        .v2-glow {
            position: absolute; width: 520px; height: 520px; right: -140px; top: -160px; border-radius: 50%;
            background: radial-gradient(circle, rgba(52, 211, 153, .28), transparent 65%);
            filter: blur(10px); animation: v2-napas 9s ease-in-out infinite; pointer-events: none;
        }
        @keyframes v2-napas { 0%, 100% { transform: scale(1); opacity: .8; } 50% { transform: scale(1.12); opacity: 1; } }

        /* Enggang: terbang melintasi hero, sayap dikepak, badan naik-turun */

        .v2-enggang-jalur {
            position: absolute; top: 9%; left: 0; width: 100%; pointer-events: none;
            animation: v2-terbang 16s linear infinite;
        }
        .v2-enggang-goyang { animation: v2-goyang 2.8s ease-in-out infinite; }
        .v2-enggang { filter: drop-shadow(0 10px 18px rgba(245, 197, 66, .25)); opacity: .9; }
        .v2-sayap { transform-origin: 96px 56px; animation: v2-kepak .7s ease-in-out infinite alternate; }
        @keyframes v2-terbang { from { transform: translateX(-260px); } to { transform: translateX(calc(100vw + 260px)); } }
        @keyframes v2-goyang { 0%, 100% { transform: translateY(0) rotate(-2deg); } 50% { transform: translateY(-14px) rotate(2deg); } }
        @keyframes v2-kepak { from { transform: rotate(-12deg); } to { transform: rotate(16deg); } }

        .v2-isi { position: relative; max-width: 1100px; margin: 0 auto; }
        .v2-kicker { font-size: .8rem; letter-spacing: .18em; text-transform: uppercase; color: var(--zamrud); }
        .v2-judul { font-size: clamp(2.2rem, 6vw, 3.8rem); line-height: 1.05; font-weight: 800; margin: .6rem 0 1rem; }
        .v2-judul span {
            background: linear-gradient(90deg, #fde68a, var(--emas), #34d399);
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .v2-lead { max-width: 40rem; color: #b7f5dc; font-size: 1.05rem; line-height: 1.7; }
        .v2-langkah { list-style: none; padding: 0; margin: 1.5rem 0; display: flex; flex-wrap: wrap; gap: .75rem; }
        .v2-langkah li {
            display: flex; align-items: center; gap: .6rem; padding: .55rem .9rem; border-radius: 999px;
            background: var(--kaca); border: 1px solid var(--garis); font-size: .9rem;
        }
        .v2-langkah span {
            display: inline-flex; width: 1.6rem; height: 1.6rem; align-items: center; justify-content: center;
            border-radius: 999px; background: var(--emas); color: var(--tinta); font-weight: 700; font-size: .8rem;
        }
        .v2-aksi { display: flex; flex-wrap: wrap; gap: .75rem; }
        .v2-tombol-utama, .v2-tombol-kedua {
            display: inline-flex; align-items: center; padding: .85rem 1.4rem; border-radius: 12px;
            font-weight: 700; text-decoration: none; transition: transform .2s, box-shadow .2s;
        }
        .v2-tombol-utama { background: linear-gradient(135deg, #34d399, #10b981); color: var(--tinta); box-shadow: 0 10px 30px rgba(52, 211, 153, .35); }
        .v2-tombol-kedua { border: 1px solid rgba(245, 197, 66, .6); color: #fde68a; }
        .v2-tombol-utama:hover, .v2-tombol-kedua:hover { transform: translateY(-2px); }
        .v2-catatan { margin-top: .75rem; font-size: .8rem; color: #8fd9bd; }

        /* Badan halaman */
        .v2-badan { max-width: 1200px; margin: 0 auto; padding: 2.5rem 1.25rem 4rem; }
        .v2-cari { margin-bottom: 2rem; }
        .v2-cari input {
            width: 100%; padding: .95rem 1.2rem; border-radius: 14px; color: #e6fff6;
            background: var(--kaca); border: 1px solid var(--garis); outline: none;
        }
        .v2-cari input::placeholder { color: #7fbfa6; }
        .v2-cari input:focus { border-color: var(--zamrud); box-shadow: 0 0 0 4px rgba(52, 211, 153, .15); }
        .v2-judul-bagian { font-size: 1.5rem; font-weight: 800; margin: 0; }
        .v2-judul-bagian small { font-size: .85rem; font-weight: 500; color: var(--zamrud); margin-left: .4rem; }
        .v2-sub { color: #8fd9bd; font-size: .9rem; margin: .3rem 0 1.25rem; }
        .v2-kisi { display: grid; gap: 1rem; grid-template-columns: repeat(1, minmax(0, 1fr)); margin-bottom: 3rem; }
        @media (min-width: 640px) { .v2-kisi { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .v2-kisi { grid-template-columns: repeat(3, minmax(0, 1fr)); } .v2-kisi-kecil { grid-template-columns: repeat(5, minmax(0, 1fr)); } }

        .v2-kartu {
            display: flex; flex-direction: column; gap: .8rem; padding: 1.1rem 1.2rem; border-radius: 16px;
            background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.03));
            border: 1px solid var(--garis); color: inherit; text-decoration: none;
            backdrop-filter: blur(8px); transition: transform .25s, border-color .25s, box-shadow .25s;
        }
        .v2-kartu:hover { transform: translateY(-3px); border-color: rgba(52, 211, 153, .6); box-shadow: 0 16px 40px rgba(16, 185, 129, .18); }
        .v2-kartu-atas { display: flex; align-items: center; gap: .75rem; }
        .v2-ikon { font-size: 1.7rem; line-height: 1; }
        .v2-nama { font-weight: 700; font-size: .95rem; }
        .v2-anak { display: flex; flex-wrap: wrap; gap: .35rem; }
        .v2-anak span {
            font-size: .72rem; padding: .2rem .55rem; border-radius: 999px;
            background: rgba(245, 197, 66, .08); border: 1px solid rgba(245, 197, 66, .25); color: #fde68a;
        }
        .v2-tag { font-size: .78rem; color: var(--zamrud); margin-top: auto; }

        /* Detail layanan (dibuka dengan "Baca selengkapnya") */
        .v2-tag { font-size: .78rem; color: var(--zamrud); margin-top: auto; text-decoration: none; }
        .v2-detail summary { cursor: pointer; color: var(--zamrud); font-weight: 600; font-size: .85rem; list-style: none; }
        .v2-detail summary::-webkit-details-marker { display: none; }
        .v2-detail summary::after { content: ' ▾'; }
        .v2-detail[open] summary::after { content: ' ▴'; }
        .v2-detail-isi {
            margin-top: .6rem; padding: .9rem 1rem; border-radius: 12px; font-size: .88rem; line-height: 1.7;
            background: rgba(4, 19, 15, .55); border: 1px solid rgba(52, 211, 153, .2);
            max-height: 420px; overflow-y: auto; color: #d1fae5;
        }
        .v2-detail-isi p, .v2-detail-isi li, .v2-detail-isi strong, .v2-detail-isi em,
        .v2-detail-isi span, .v2-detail-isi h3, .v2-detail-isi h4, .v2-detail-isi div { color: #d1fae5 !important; }
        .v2-detail-isi a { color: #fde68a !important; text-decoration: underline; }
        .v2-detail-isi ol, .v2-detail-isi ul { list-style-position: outside; margin: .4rem 0; }
        .v2-detail-isi ol.list-decimal { list-style-type: decimal; }
        .v2-detail-isi ol, .v2-detail-isi ul { margin-left: 0 !important; padding-left: 1.4rem !important; }
        .v2-detail-isi li::marker { color: #fde68a; font-weight: 700; }

        /* Header gelap untuk beranda v2 (layout tetap sama, hanya warnanya) */
        header.v2-header {
            background:
                radial-gradient(600px 200px at 90% -40%, rgba(52, 211, 153, .28), transparent 60%),
                linear-gradient(120deg, #04130f 0%, #0b3d2e 70%, #072a3a 100%);
            border-bottom: 2px solid rgba(245, 197, 66, .55);
            color: #e6fff6;
        }
        header.v2-header h1 { color: #ffffff; }
        header.v2-header p { color: #8fd9bd !important; }
        header.v2-header a { box-shadow: 0 6px 18px rgba(16, 185, 129, .25); }

        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }

        @media (prefers-reduced-motion: reduce) {
            .v2-enggang-jalur, .v2-enggang-goyang, .v2-sayap, .v2-glow { animation: none; }
            .v2-enggang-jalur { left: 8%; }
        }
    </style>

    <script>
        (function () {
            const input = document.getElementById('cari-layanan');
            if (!input) return;
            input.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                document.querySelectorAll('.kartu-layanan').forEach(function (el) {
                    el.style.display = q !== '' && !(el.dataset.cari || '').includes(q) ? 'none' : '';
                });
            });
        })();
    </script>
@endsection
