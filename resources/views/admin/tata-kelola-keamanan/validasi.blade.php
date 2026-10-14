@extends('layouts.authenticated')

@section('title', '- Validasi Pakar')
@section('header-title', 'Tata Kelola Keamanan: Validasi Pakar (Prinsip Delphi)')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('admin.tata-kelola-keamanan._nav')

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Tahap 4 — Menjalankan validasi pakar',
            'poin' => [
                'Akun pakar' => 'Pakar menilai dari akun portal. Bila pakar belum punya akun, buatkan atau minta ia mendaftar, lalu verifikasi akunnya di Kelola Pengguna. Saat didaftarkan di sini, role <em>Pakar Tata Kelola Keamanan</em> otomatis dipasang.',
                'Langkah' => '1) Cari akun pakar, isi instansi dan keahlian, klik <strong>Daftarkan</strong>. 2) Klik <strong>Buka putaran baru</strong>. 3) Beri tahu pakar untuk membuka menu <em>Layanan Digital → Validasi Pakar Tata Kelola Keamanan</em>. 4) Pantau kolom <em>Menilai di putaran ini</em>. 5) Bila semua sudah menilai atau batas waktu habis, klik <strong>Tutup putaran</strong>. 6) Perbaiki butir yang perlu revisi di tab 3 sesuai saran, lalu buka putaran berikutnya.',
                'Aturan' => 'Butir disepakati bila minimal dua pertiga pakar yang menilai menyatakan setuju. Paling banyak tiga putaran; sesudah putaran ketiga, butir yang tetap belum disepakati dikeluarkan dari model.',
                'Kriteria pakar' => 'Dari luar unit yang dievaluasi, berpengalaman minimal 5 tahun atau bersertifikat (mis. ISO/IEC 27001 Lead Auditor/Implementer, COBIT). ',
                'Jumlah pakar' => 'Ditentukan pengelola; satu pakar pun dapat dipakai. Butir disepakati bila minimal dua pertiga pakar yang menilai setuju, sehingga dengan satu pakar keputusan pakar tersebut langsung menjadi hasil validasi. Makin banyak pakar, makin kuat dasar kesepakatannya.',
                'Contoh pesan undangan' => '<span class="block p-2 bg-white rounded border text-xs">Yth. Bapak/Ibu [nama], kami memohon kesediaan Bapak/Ibu menjadi pakar untuk memvalidasi pemetaan COBIT 2019 – ISO/IEC 27001:2022 – regulasi keamanan SPBE pada evaluasi tata kelola keamanan informasi [instansi]. Silakan masuk ke portal E-Layanan dengan akun Bapak/Ibu, lalu buka menu Layanan Digital → Validasi Pakar Tata Kelola Keamanan. Penilaian cukup dengan memilih setuju/tidak setuju dan memberi saran pada setiap butir. Putaran ini dibuka sampai [tanggal]. Terima kasih.</span>',
            ],
        ])

        {{-- Panel pakar --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6 overflow-x-auto">
            <h3 class="font-semibold text-gray-900 mb-3">Panel pakar ({{ $pakars->where('aktif', true)->count() }} aktif)</h3>
            @if ($pakars->isNotEmpty())
                <table class="min-w-full text-sm mb-4">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b">
                            <th class="py-2 pr-3">Nama / akun</th>
                            <th class="py-2 px-3">Instansi / keahlian</th>
                            <th class="py-2 px-3 text-center">Menilai di putaran ini</th>
                            <th class="py-2 px-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($pakars as $pk)
                            <tr class="{{ $pk->aktif ? '' : 'text-gray-400' }}">
                                <td class="py-2 pr-3">{{ $pk->nama }} @unless ($pk->aktif) (nonaktif) @endunless<br><span class="text-xs text-gray-500">{{ $pk->user->email ?? '' }}</span></td>
                                <td class="py-2 px-3">{{ $pk->instansi }}<br><span class="text-xs text-gray-500">{{ $pk->keahlian }}</span></td>
                                <td class="py-2 px-3 text-center">{{ $putaran ? (in_array($pk->id, $sudahMenilai) ? 'sudah' : 'belum') : '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.tata-kelola-keamanan.pakar.update', [$evaluasi, $pk]) }}" class="inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="instansi" value="{{ $pk->instansi }}">
                                        <input type="hidden" name="keahlian" value="{{ $pk->keahlian }}">
                                        <input type="hidden" name="aktif" value="{{ $pk->aktif ? 0 : 1 }}">
                                        <button class="text-xs text-green-700 hover:underline">{{ $pk->aktif ? 'nonaktifkan' : 'aktifkan' }}</button>
                                    </form>
                                    @if ($pk->penilaians_count === 0)
                                        <form method="POST" action="{{ route('admin.tata-kelola-keamanan.pakar.destroy', [$evaluasi, $pk]) }}" class="inline ml-2">
                                            @csrf @method('DELETE')
                                            <button class="text-xs text-red-600 hover:underline">hapus</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <form method="GET" action="{{ route('admin.tata-kelola-keamanan.validasi', $evaluasi) }}" class="flex gap-2 mb-3">
                <input name="q" value="{{ $q }}" placeholder="Cari akun pakar: nama, email, atau NIP (minimal 3 huruf)" class="w-full md:w-96 rounded-lg border-gray-300 text-sm">
                <button class="px-4 py-2 text-sm rounded-lg border border-gray-300">Cari</button>
            </form>
            @if ($q !== '' && $hasilCari->isEmpty())
                <p class="text-sm text-gray-500">Tidak ada akun terverifikasi yang cocok (akun yang sudah menjadi pakar tidak ditampilkan).</p>
            @endif
            @foreach ($hasilCari as $u)
                <form method="POST" action="{{ route('admin.tata-kelola-keamanan.pakar.store', $evaluasi) }}" class="mb-2 p-3 rounded-lg border grid grid-cols-1 md:grid-cols-5 gap-2 items-center text-sm">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                    <div class="md:col-span-2"><p class="font-medium">{{ $u->name }}</p><p class="text-xs text-gray-500">{{ $u->email }}</p></div>
                    <input name="instansi" placeholder="Instansi" class="rounded border-gray-300 text-sm">
                    <input name="keahlian" placeholder="Keahlian / sertifikasi" class="rounded border-gray-300 text-sm">
                    <button class="px-3 py-2 text-sm rounded-lg bg-green-600 text-white hover:bg-green-700">Daftarkan</button>
                </form>
            @endforeach
        </div>

        {{-- Putaran --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-sm text-gray-700">Putaran:</span>
                    @forelse ($putarans as $pt)
                        <a href="{{ route('admin.tata-kelola-keamanan.validasi', [$evaluasi, 'putaran' => $pt->nomor]) }}"
                           class="px-3 py-1 text-sm rounded-full border {{ $putaran && $putaran->id === $pt->id ? 'bg-green-600 text-white border-green-600' : 'border-gray-300 text-gray-700' }}">
                            {{ $pt->nomor }} ({{ $pt->status }})
                        </a>
                    @empty
                        <span class="text-sm text-gray-500">belum ada</span>
                    @endforelse
                </div>
                <div class="flex gap-2">
                    @if ($putaran && $putaran->status === 'dibuka')
                        <form method="POST" action="{{ route('admin.tata-kelola-keamanan.putaran.tutup', [$evaluasi, $putaran]) }}" onsubmit="return confirm('Tutup putaran dan terapkan keputusan kesepakatan?')">
                            @csrf
                            <button class="px-4 py-2 text-sm font-medium rounded-lg bg-yellow-600 text-white hover:bg-yellow-700">Tutup putaran {{ $putaran->nomor }}</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.tata-kelola-keamanan.putaran.buka', $evaluasi) }}">
                            @csrf
                            <button class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Buka putaran baru</button>
                        </form>
                    @endif
                </div>
            </div>

            @if ($putaran)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b">
                                <th class="py-2 pr-3">Praktik</th>
                                <th class="py-2 px-3">Klausul / kontrol</th>
                                <th class="py-2 px-3 text-center">Setuju</th>
                                <th class="py-2 px-3 text-center">Tidak</th>
                                <th class="py-2 px-3 text-center">Keputusan</th>
                                <th class="py-2 px-3">Saran pakar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y align-top">
                            @foreach ($rekap as $r)
                                <tr>
                                    <td class="py-2 pr-3 font-mono">{{ $r['pemetaan']->praktik->kode }}</td>
                                    <td class="py-2 px-3">{{ implode(', ', $r['pemetaan']->iso_items ?? []) }}</td>
                                    <td class="py-2 px-3 text-center">{{ $r['setuju'] }}</td>
                                    <td class="py-2 px-3 text-center">{{ $r['tidak_setuju'] }}</td>
                                    <td class="py-2 px-3 text-center">
                                        @if ($r['jumlah_penilai'] === 0)
                                            <span class="text-xs text-gray-400">belum dinilai</span>
                                        @elseif ($r['disepakati'])
                                            <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-800">disepakati</span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-800">perlu revisi</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-xs">
                                        @foreach ($r['saran'] as $s)
                                            <p class="mb-1"><strong>{{ $s['pakar'] }}</strong> ({{ $s['setuju'] ? 'setuju' : 'tidak' }}): {{ $s['saran'] }}</p>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
