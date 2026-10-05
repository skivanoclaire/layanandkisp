@extends('layouts.authenticated')

@section('title', '- Analisis Kuesioner Portal')
@section('header-title', 'Kuesioner Kualitas Layanan Portal')

@php
    $f = fn ($v, $d = 2) => $v === null ? '-' : number_format($v, $d, ',', '.');
    $r = $hasil['ringkasan'];
    $atribut = $hasil['atribut'];
    $garis = $hasil['garis'];
    $titikIpa = collect($atribut)->filter(fn ($a) => $a['kinerja'] !== null)
        ->map(fn ($a, $k) => ['x' => round($a['kinerja'], 3), 'y' => round($a['kepentingan'], 3), 'label' => $k])->values();
    $titikBw = collect($atribut)->filter(fn ($a) => $a['kano']['better'] !== null)
        ->map(fn ($a, $k) => ['x' => round(abs($a['kano']['worse']), 3), 'y' => round($a['kano']['better'], 3), 'label' => $k])->values();
    $blokUtama = ['kepentingan' => 'Blok kepentingan (Bagian B)', 'kinerja' => 'Blok kinerja (Bagian C)', 'kepuasan' => 'Kepuasan pengguna'];
    $namaDimensi = collect(config('kuesioner_portal.dimensi'))->map(fn ($d) => $d['nama']);
    $warnaKano = ['M' => 'bg-red-100 text-red-800', 'O' => 'bg-blue-100 text-blue-800', 'A' => 'bg-green-100 text-green-800', 'I' => 'bg-gray-100 text-gray-700', 'R' => 'bg-purple-100 text-purple-800'];
    $pindah = collect($atribut)->filter(fn ($a) => $a['pindah_kuadran']);
@endphp

@section('content')
    <div class="container mx-auto px-4 py-6 space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <a href="{{ route('admin.kuesioner-portal.index') }}" class="text-sm text-green-700 hover:underline">← Kembali</a>
                <h1 class="text-2xl font-semibold text-gray-900 mt-1">Analisis — {{ $periode->nama }} @include('admin.kuesioner-portal._info', ['k' => 'halaman'])</h1>
                <p class="text-sm text-gray-600 mt-1 max-w-3xl">Hasil ini untuk pemantauan selama pengumpulan data. Analisis final tesis dijalankan di Jupyter
                    dari berkas ekspor, lalu dicocokkan dengan angka di halaman ini. Gap, Tk, dan Wilcoxon memakai pasangan jawaban lengkap
                    per atribut; p-value Wilcoxon memakai pendekatan normal.</p>
            </div>
            <div class="flex items-center">
                <a href="{{ route('admin.kuesioner-portal.export', $periode) }}" class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Ekspor Excel</a>
                @include('admin.kuesioner-portal._info', ['k' => 'ekspor'])
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
            @foreach ([
                ['Respons masuk', $r['masuk'], 'masuk'],
                ['Menolak', $r['menolak'], 'menolak'],
                ['Belum selesai', $r['belum_selesai'], 'belum_selesai'],
                ['Selesai', $r['selesai'], 'selesai'],
                ['Dianalisis (IPA)', $r['dianalisis'], 'dianalisis'],
                ['Dianalisis (Kano)', $r['dianalisis_kano'], 'dianalisis_kano'],
            ] as [$label, $nilai, $kunci])
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <p class="text-xs text-gray-500">{{ $label }} @include('admin.kuesioner-portal._info', ['k' => $kunci])</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $nilai }}</p>
                </div>
            @endforeach
        </div>

        @if ($r['dianalisis'] < 3)
            <div class="p-4 rounded-lg border bg-yellow-50 border-yellow-200 text-yellow-800 text-sm">
                Data yang lolos pra-pemrosesan baru {{ $r['dianalisis'] }} respons. Sebagian besar statistik memerlukan minimal 3 respons.
            </div>
        @endif

        {{-- 3.3.1 Uji instrumen --}}
        <section class="bg-white rounded-lg shadow-sm p-6 overflow-x-auto">
            <h2 class="text-lg font-semibold text-gray-900">Uji validitas dan reliabilitas @include('admin.kuesioner-portal._info', ['k' => 'uji_instrumen'])</h2>
            <p class="text-sm text-gray-600 mb-4">Korelasi Pearson corrected item-total dibandingkan dengan r tabel (α = 5%, dua sisi); reliabel bila Cronbach's α ≥ 0,70.
                Dihitung pada responden yang menjawab lengkap blok tersebut (n), sehingga blok kinerja hanya memuat responden yang pernah menghubungi petugas.</p>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                @foreach ($blokUtama as $kunci => $judul)
                    @php $b = $hasil['instrumen'][$kunci]; @endphp
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium text-gray-900">{{ $judul }}</h3>
                        <p class="text-xs text-gray-500 mt-1">n = {{ $b['n'] }} @include('admin.kuesioner-portal._info', ['k' => 'n_blok']) · r tabel = {{ $f($b['r_tabel'], 3) }} @include('admin.kuesioner-portal._info', ['k' => 'r_tabel']) ·
                            α @include('admin.kuesioner-portal._info', ['k' => 'alpha']) = <span class="{{ $b['reliabel'] === false ? 'text-red-700 font-semibold' : '' }}">{{ $f($b['alpha'], 3) }}</span></p>
                        <table class="w-full text-xs mt-3">
                            <thead>
                                <tr class="text-gray-500 border-b">
                                    <th class="py-1 text-left font-normal">Butir</th>
                                    <th class="py-1 text-right font-normal">r item-total @include('admin.kuesioner-portal._info', ['k' => 'r_item'])</th>
                                    <th class="py-1 text-right font-normal">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($b['item'] as $kode => $item)
                                    <tr>
                                        <td class="py-1 font-mono">{{ $kode }}</td>
                                        <td class="py-1 text-right">{{ $f($item['r'], 3) }}</td>
                                        <td class="py-1 text-right">
                                            @if ($item['valid'] === true) <span class="text-green-700">valid</span>
                                            @elseif ($item['valid'] === false) <span class="text-red-700 font-semibold">tidak valid</span>
                                            @else - @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
            <h3 class="font-medium text-gray-900 mt-5 mb-2">Cronbach's α per dimensi @include('admin.kuesioner-portal._info', ['k' => 'alpha_dimensi'])</h3>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4">Dimensi</th>
                        <th class="py-2 px-3 text-right">α kepentingan (n)</th>
                        <th class="py-2 px-3 text-right">α kinerja (n)</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($namaDimensi as $kd => $nama)
                        @php
                            $bi = $hasil['instrumen']["kepentingan_$kd"];
                            $bk = $hasil['instrumen']["kinerja_$kd"];
                        @endphp
                        <tr>
                            <td class="py-2 pr-4">{{ $nama }}</td>
                            <td class="py-2 px-3 text-right">{{ $f($bi['alpha'], 3) }} ({{ $bi['n'] }})</td>
                            <td class="py-2 px-3 text-right">{{ $f($bk['alpha'], 3) }} ({{ $bk['n'] }})</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        {{-- 3.3.3 Deskriptif, gap, Tk --}}
        <section class="bg-white rounded-lg shadow-sm p-6 overflow-x-auto">
            <h2 class="text-lg font-semibold text-gray-900">Deskriptif, gap, dan tingkat kesesuaian @include('admin.kuesioner-portal._info', ['k' => 'deskriptif'])</h2>
            <p class="text-sm text-gray-600 mb-4">Gap = X̄ (kinerja) − Ȳ (kepentingan); Tk = ΣX/ΣY × 100%. Uji Wilcoxon signed-rank memeriksa apakah gap tidak terjadi karena kebetulan.</p>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-3">Kode</th>
                        <th class="py-2 px-2 text-right">n @include('admin.kuesioner-portal._info', ['k' => 'n_pasangan'])</th>
                        <th class="py-2 px-2 text-right">Kepentingan @include('admin.kuesioner-portal._info', ['k' => 'kepentingan'])</th>
                        <th class="py-2 px-2 text-right">Kinerja @include('admin.kuesioner-portal._info', ['k' => 'kinerja'])</th>
                        <th class="py-2 px-2 text-right">Gap @include('admin.kuesioner-portal._info', ['k' => 'gap'])</th>
                        <th class="py-2 px-2 text-right">Tk @include('admin.kuesioner-portal._info', ['k' => 'tk'])</th>
                        <th class="py-2 px-2">Interpretasi Tk @include('admin.kuesioner-portal._info', ['k' => 'interpretasi_tk'])</th>
                        <th class="py-2 px-2 text-right">z @include('admin.kuesioner-portal._info', ['k' => 'z'])</th>
                        <th class="py-2 px-2 text-right">p @include('admin.kuesioner-portal._info', ['k' => 'p'])</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($atribut as $kode => $a)
                        <tr>
                            <td class="py-2 pr-3 font-mono" title="{{ $a['pernyataan'] }}">{{ $kode }}</td>
                            <td class="py-2 px-2 text-right">{{ $a['n'] }}</td>
                            <td class="py-2 px-2 text-right">{{ $f($a['kepentingan']) }} <span class="text-xs text-gray-400">{{ $a['kelas_kepentingan'] }}</span></td>
                            <td class="py-2 px-2 text-right">{{ $f($a['kinerja']) }} <span class="text-xs text-gray-400">{{ $a['kelas_kinerja'] }}</span></td>
                            <td class="py-2 px-2 text-right {{ ($a['gap'] ?? 0) < 0 ? 'text-red-700' : '' }}">{{ $f($a['gap']) }}</td>
                            <td class="py-2 px-2 text-right">{{ $a['tk'] === null ? '-' : $f($a['tk'], 1) . '%' }}</td>
                            <td class="py-2 px-2 text-xs">{{ $a['interpretasi_tk'] ?? '-' }}</td>
                            <td class="py-2 px-2 text-right">{{ $f($a['wilcoxon']['z']) }}</td>
                            <td class="py-2 px-2 text-right {{ ($a['wilcoxon']['p'] ?? 1) < 0.05 ? 'font-semibold' : '' }}">{{ $f($a['wilcoxon']['p'], 3) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Per dimensi @include('admin.kuesioner-portal._info', ['k' => 'per_dimensi'])</h3>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b">
                                <th class="py-2 pr-3">Dimensi</th>
                                <th class="py-2 px-2 text-right">Kepentingan</th>
                                <th class="py-2 px-2 text-right">Kinerja</th>
                                <th class="py-2 px-2 text-right">Gap</th>
                                <th class="py-2 px-2 text-right">Tk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($hasil['dimensi'] as $d)
                                <tr>
                                    <td class="py-2 pr-3">{{ $d['nama'] }}</td>
                                    <td class="py-2 px-2 text-right">{{ $f($d['kepentingan']) }}</td>
                                    <td class="py-2 px-2 text-right">{{ $f($d['kinerja']) }}</td>
                                    <td class="py-2 px-2 text-right">{{ $f($d['gap']) }}</td>
                                    <td class="py-2 px-2 text-right">{{ $d['tk'] === null ? '-' : $f($d['tk'], 1) . '%' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Kepuasan pengguna @include('admin.kuesioner-portal._info', ['k' => 'kepuasan'])</h3>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y">
                            @foreach ($hasil['kepuasan'] as $kode => $k)
                                <tr>
                                    <td class="py-2 pr-3 font-mono">{{ $kode }}</td>
                                    <td class="py-2 px-2 text-xs text-gray-600">{{ config("kuesioner_portal.kepuasan.$kode") }}</td>
                                    <td class="py-2 px-2 text-right">{{ $f($k['rata_rata']) }}</td>
                                    <td class="py-2 px-2 text-xs text-gray-500">{{ $k['kelas'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- 3.3.4 IPA --}}
        <section class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900">Importance-Performance Analysis @include('admin.kuesioner-portal._info', ['k' => 'ipa'])</h2>
            <p class="text-sm text-gray-600 mb-4">Sumbu X = kinerja, sumbu Y = kepentingan. Garis potong = rata-rata seluruh atribut
                (X̄ = {{ $f($garis['x'], 3) }}, Ȳ = {{ $f($garis['y'], 3) }}). @include('admin.kuesioner-portal._info', ['k' => 'garis'])</p>
            <div class="relative h-[420px]"><canvas id="grafikIpa"></canvas></div>
            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 text-sm">
                @foreach (\App\Services\KuesionerPortal\Analisis::KUADRAN as $kq => $label)
                    <div class="border rounded-lg p-3">
                        <p class="font-medium text-gray-900">{{ $label }} @include('admin.kuesioner-portal._info', ['k' => 'kuadran_' . $kq])</p>
                        <p class="text-gray-700 mt-1 font-mono text-xs">{{ collect($atribut)->filter(fn ($a) => $a['kuadran'] === $kq)->keys()->implode(', ') ?: '-' }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 text-sm">
                <p class="font-medium text-gray-900">Uji ketahanan: garis potong titik tengah skala (3,0; 3,0) @include('admin.kuesioner-portal._info', ['k' => 'ketahanan'])</p>
                @if ($pindah->isEmpty())
                    <p class="text-gray-600">Tidak ada atribut yang berpindah kuadran.</p>
                @else
                    <ul class="list-disc ml-5 text-gray-700">
                        @foreach ($pindah as $kode => $a)
                            <li><span class="font-mono">{{ $kode }}</span>: kuadran {{ $a['kuadran'] }} → {{ $a['kuadran_titik_tengah'] }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        {{-- 3.3.5 Kano --}}
        <section class="bg-white rounded-lg shadow-sm p-6 overflow-x-auto">
            <h2 class="text-lg font-semibold text-gray-900">Analisis model Kano @include('admin.kuesioner-portal._info', ['k' => 'kano'])</h2>
            <p class="text-sm text-gray-600 mb-4">Kategori ditetapkan dengan aturan Berger dkk (1993); jawaban Q dikeluarkan dari n.
                Bila |a − b| &lt; 1,65 × √[(a + b)(2n − a − b) / 2n] (Fong, 1996), atribut dilaporkan sebagai kategori campuran.</p>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-3">Kode</th>
                        @foreach (['A', 'O', 'M', 'I', 'R', 'Q'] as $k)
                            <th class="py-2 px-2 text-right whitespace-nowrap">{{ $k }} @include('admin.kuesioner-portal._info', ['k' => 'kano_' . $k])</th>
                        @endforeach
                        <th class="py-2 px-2 text-right">n @include('admin.kuesioner-portal._info', ['k' => 'n_kano'])</th>
                        <th class="py-2 px-2">Kategori @include('admin.kuesioner-portal._info', ['k' => 'kategori_kano'])</th>
                        <th class="py-2 px-2 text-right whitespace-nowrap">|a−b| @include('admin.kuesioner-portal._info', ['k' => 'fong_selisih'])</th>
                        <th class="py-2 px-2 text-right">Batas Fong @include('admin.kuesioner-portal._info', ['k' => 'fong_batas'])</th>
                        <th class="py-2 px-2 text-right">Better @include('admin.kuesioner-portal._info', ['k' => 'better'])</th>
                        <th class="py-2 px-2 text-right">Worse @include('admin.kuesioner-portal._info', ['k' => 'worse'])</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($atribut as $kode => $a)
                        @php $k = $a['kano']; @endphp
                        <tr>
                            <td class="py-2 pr-3 font-mono" title="{{ $a['pernyataan'] }}">{{ $kode }}</td>
                            @foreach (['A', 'O', 'M', 'I', 'R', 'Q'] as $c)
                                <td class="py-2 px-2 text-right {{ $c === ($k['kategori'] ?? null) ? 'font-semibold' : '' }}">{{ $k['frekuensi'][$c] }}</td>
                            @endforeach
                            <td class="py-2 px-2 text-right">{{ $k['n'] }}</td>
                            <td class="py-2 px-2">
                                @if ($k['kategori'])
                                    <span class="px-2 py-0.5 rounded-full text-xs {{ $warnaKano[$k['kategori']] ?? '' }}">{{ $k['kategori_tampil'] }}</span>
                                @else - @endif
                            </td>
                            <td class="py-2 px-2 text-right">{{ $k['fong']['selisih'] ?? '-' }}</td>
                            <td class="py-2 px-2 text-right">{{ $f($k['fong']['batas'] ?? null) }}</td>
                            <td class="py-2 px-2 text-right">{{ $f($k['better'], 3) }}</td>
                            <td class="py-2 px-2 text-right">{{ $f($k['worse'], 3) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-xs text-gray-500 mt-2">A = attractive, O = one-dimensional, M = must-be, I = indifferent, R = reverse, Q = questionable.</p>

            <h3 class="font-medium text-gray-900 mt-6 mb-2">Diagram better-worse @include('admin.kuesioner-portal._info', ['k' => 'diagram_bw'])</h3>
            <div class="relative h-[420px]"><canvas id="grafikBw"></canvas></div>
        </section>

        {{-- 3.3.6 Matriks IPA-Kano --}}
        <section class="bg-white rounded-lg shadow-sm p-6 overflow-x-auto">
            <h2 class="text-lg font-semibold text-gray-900">Matriks prioritas IPA-Kano @include('admin.kuesioner-portal._info', ['k' => 'matriks'])</h2>
            <p class="text-sm text-gray-600 mb-4">Tindak lanjut mengikuti Tabel 3.5. Dalam kelompok yang sama, atribut diurutkan menurut |worse| (M/O),
                better (A), lalu gap terbesar. Kategori campuran dipetakan memakai kategori dominannya.</p>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-3 whitespace-nowrap"># @include('admin.kuesioner-portal._info', ['k' => 'urutan'])</th>
                        <th class="py-2 pr-3">Kode</th>
                        <th class="py-2 pr-3">Atribut</th>
                        <th class="py-2 px-2">Kuadran @include('admin.kuesioner-portal._info', ['k' => 'kuadran_kolom'])</th>
                        <th class="py-2 px-2">Kano @include('admin.kuesioner-portal._info', ['k' => 'kano_kolom'])</th>
                        <th class="py-2 px-2 text-right">Gap @include('admin.kuesioner-portal._info', ['k' => 'gap'])</th>
                        <th class="py-2 px-2 text-right whitespace-nowrap">|Worse| @include('admin.kuesioner-portal._info', ['k' => 'worse'])</th>
                        <th class="py-2 px-2 text-right">Better @include('admin.kuesioner-portal._info', ['k' => 'better'])</th>
                        <th class="py-2 px-2">Tindak lanjut @include('admin.kuesioner-portal._info', ['k' => 'tindak_lanjut'])</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($hasil['urutan_prioritas'] as $i => $kode)
                        @php $a = $atribut[$kode]; @endphp
                        <tr class="{{ $a['prioritas'] <= 2 ? 'bg-red-50' : '' }}">
                            <td class="py-2 pr-3">{{ $i + 1 }}</td>
                            <td class="py-2 pr-3 font-mono">{{ $kode }}</td>
                            <td class="py-2 pr-3">{{ $a['pernyataan'] }}</td>
                            <td class="py-2 px-2">{{ $a['kuadran'] ?? '-' }}</td>
                            <td class="py-2 px-2">{{ $a['kano']['kategori_tampil'] }}</td>
                            <td class="py-2 px-2 text-right">{{ $f($a['gap']) }}</td>
                            <td class="py-2 px-2 text-right">{{ $a['kano']['worse'] === null ? '-' : $f(abs($a['kano']['worse']), 3) }}</td>
                            <td class="py-2 px-2 text-right">{{ $f($a['kano']['better'], 3) }}</td>
                            <td class="py-2 px-2 text-xs">{{ $a['tindak_lanjut'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-sm text-gray-700 mt-4">
                Korelasi peringkat Spearman antara rata-rata kepentingan dan |worse| @include('admin.kuesioner-portal._info', ['k' => 'spearman']):
                @if ($hasil['spearman_kepentingan_worse'])
                    <strong>ρ = {{ $f($hasil['spearman_kepentingan_worse']['rho'], 3) }}</strong> (n = {{ $hasil['spearman_kepentingan_worse']['n'] }} atribut).
                    Korelasi yang lemah menunjukkan kepentingan yang dinyatakan tidak sepenuhnya mencerminkan dampak atribut terhadap ketidakpuasan.
                @else
                    belum dapat dihitung.
                @endif
            </p>
        </section>

        {{-- Profil responden --}}
        <section class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Profil responden yang dianalisis @include('admin.kuesioner-portal._info', ['k' => 'profil'])</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 text-sm">
                @foreach (['status_kepegawaian' => 'Status kepegawaian', 'peran' => 'Peran', 'lama_penggunaan' => 'Lama menggunakan portal', 'frekuensi' => 'Frekuensi penggunaan', 'layanan' => 'Layanan yang pernah diajukan'] as $field => $judul)
                    <div>
                        <h3 class="font-medium text-gray-900 mb-1">{{ $judul }}</h3>
                        <table class="w-full">
                            @foreach ($hasil['profil'][$field] as $k => $jumlah)
                                <tr><td class="py-0.5 text-gray-700">{{ $profilLabel[$field][$k] ?? $k }}</td><td class="py-0.5 text-right">{{ $jumlah }}</td></tr>
                            @endforeach
                        </table>
                    </div>
                @endforeach
                <div>
                    <h3 class="font-medium text-gray-900 mb-1">Pernah menghubungi petugas</h3>
                    <table class="w-full">
                        <tr><td class="py-0.5 text-gray-700">Ya</td><td class="py-0.5 text-right">{{ $hasil['profil']['pernah_hubungi_petugas']['ya'] }}</td></tr>
                        <tr><td class="py-0.5 text-gray-700">Tidak</td><td class="py-0.5 text-right">{{ $hasil['profil']['pernah_hubungi_petugas']['tidak'] }}</td></tr>
                    </table>
                    <h3 class="font-medium text-gray-900 mt-4 mb-1">Perangkat daerah</h3>
                    <table class="w-full">
                        @foreach ($hasil['profil']['unit_kerja'] as $nama => $jumlah)
                            <tr><td class="py-0.5 text-gray-700">{{ $nama }}</td><td class="py-0.5 text-right">{{ $jumlah }}</td></tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            // Label kode atribut di samping setiap titik
            const labelTitik = {
                id: 'labelTitik',
                afterDatasetsDraw(chart) {
                    const ctx = chart.ctx;
                    ctx.save();
                    ctx.font = '11px sans-serif';
                    ctx.fillStyle = '#374151';
                    chart.getDatasetMeta(0).data.forEach((p, i) => {
                        ctx.fillText(chart.data.datasets[0].data[i].label, p.x + 6, p.y - 6);
                    });
                    ctx.restore();
                },
            };
            // Garis potong vertikal (x) dan horizontal (y)
            const garisPotong = (gx, gy) => ({
                id: 'garisPotong',
                afterDraw(chart) {
                    const { ctx, chartArea: a, scales: { x, y } } = chart;
                    ctx.save();
                    ctx.strokeStyle = '#9ca3af';
                    ctx.setLineDash([5, 4]);
                    ctx.beginPath();
                    ctx.moveTo(x.getPixelForValue(gx), a.top);
                    ctx.lineTo(x.getPixelForValue(gx), a.bottom);
                    ctx.moveTo(a.left, y.getPixelForValue(gy));
                    ctx.lineTo(a.right, y.getPixelForValue(gy));
                    ctx.stroke();
                    ctx.restore();
                },
            });
            const sebar = (id, data, gx, gy, judulX, judulY, batas) => {
                const el = document.getElementById(id);
                if (!el || !data.length || gx === null || gy === null) return;
                new Chart(el, {
                    type: 'scatter',
                    data: { datasets: [{ data, backgroundColor: '#16a34a', pointRadius: 5 }] },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: c => `${c.raw.label}: (${c.raw.x}; ${c.raw.y})` } },
                        },
                        scales: {
                            x: { title: { display: true, text: judulX }, ...batas.x },
                            y: { title: { display: true, text: judulY }, ...batas.y },
                        },
                    },
                    plugins: [labelTitik, garisPotong(gx, gy)],
                });
            };

            const ipa = @json($titikIpa);
            const ax = ipa.map(p => p.x), ay = ipa.map(p => p.y);
            const pad = 0.15;
            sebar('grafikIpa', ipa, @json($garis['x']), @json($garis['y']), 'Kinerja (X)', 'Kepentingan (Y)', {
                x: { min: Math.max(1, Math.min(...ax) - pad), max: Math.min(5, Math.max(...ax) + pad) },
                y: { min: Math.max(1, Math.min(...ay) - pad), max: Math.min(5, Math.max(...ay) + pad) },
            });

            sebar('grafikBw', @json($titikBw), 0.5, 0.5, '|Worse|', 'Better', {
                x: { min: 0, max: 1 }, y: { min: 0, max: 1 },
            });
        })();
    </script>
@endpush
