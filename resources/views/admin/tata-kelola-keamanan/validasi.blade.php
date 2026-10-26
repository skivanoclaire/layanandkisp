@extends('layouts.authenticated')

@section('title', '- Validasi Model')
@section('header-title', 'Tata Kelola Keamanan: Validasi Model (Verifikasi Dokumen dan FGD)')

@section('content')
    @php
        $dokumenAcuan = config('tata_kelola_keamanan.dokumen_acuan');
        $warnaVerifikasi = [
            'didukung' => 'bg-green-100 text-green-800',
            'rancangan_peneliti' => 'bg-blue-100 text-blue-800',
            'tidak_didukung' => 'bg-red-100 text-red-700',
        ];
        $terkunci = (bool) $sesiAktif;
    @endphp
    <div class="container mx-auto px-4 py-6">
        @include('admin.tata-kelola-keamanan._nav')

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Tahap 4 — Validasi model',
            'poin' => [
                'Dua tahap' => 'Validasi tidak memakai panel pakar eksternal. <strong>(A)</strong> Setiap butir pemetaan diverifikasi silang terhadap dokumen acuan, lalu <strong>(B)</strong> butir yang lolos dibahas dalam FGD bersama praktisi DKISP.',
                'A. Verifikasi dokumen' => 'Centang dokumen acuan yang mencantumkan keterkaitan butir dan tulis rujukannya (halaman, tabel, atau pasal). Butir <em>didukung</em> bila tercantum pada sekurang-kurangnya satu dokumen acuan. Bila tidak ditemukan, butir hanya boleh dipertahankan bila keterkaitannya dijelaskan pada catatan (dicatat sebagai <em>rancangan peneliti</em>); jika tidak, perbaiki butir di tab 3 atau keluarkan dari model.',
                'B. FGD' => '1) Klik <strong>Buka sesi FGD</strong> dan isi tanggal serta peserta (informan yang menangani keamanan informasi, aplikasi, dan infrastruktur TIK). 2) Cetak <strong>Lembar validasi model</strong> untuk peserta. 3) Catat keputusan forum per butir: <em>setuju</em> bila forum mufakat bahwa butir relevan dan dapat diterapkan di DKISP, atau <em>tidak setuju</em> beserta saran. 4) Klik <strong>Tutup sesi</strong>.',
                'Sesudah sesi' => 'Butir yang disetujui menjadi <em>disepakati</em>. Butir yang tidak disetujui diperbaiki di tab 3 sesuai saran (verifikasinya diulang), lalu dibahas pada sesi lanjutan. Butir yang tetap tidak disepakati dikeluarkan dari model beserta alasannya.',
                'Kunci' => 'Selama sesi FGD dibuka, verifikasi dan pemetaan dikunci agar butir yang dibahas tidak berubah.',
            ],
        ])

        {{-- Ringkasan --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-900">Ringkasan</h3>
                <a href="{{ route('admin.tata-kelola-keamanan.validasi.lembar', [$evaluasi] + ($sesi ? ['sesi' => $sesi->nomor] : [])) }}" target="_blank"
                   class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Lembar validasi model (cetak)</a>
            </div>
            <dl class="mt-3 grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                <div class="p-3 rounded-lg bg-gray-50"><dt class="text-gray-500 text-xs">Belum diverifikasi</dt><dd class="text-xl font-semibold">{{ $ringkasan['belum'] }}</dd></div>
                <div class="p-3 rounded-lg bg-green-50"><dt class="text-gray-500 text-xs">Didukung dokumen</dt><dd class="text-xl font-semibold">{{ $ringkasan['didukung'] }}</dd></div>
                <div class="p-3 rounded-lg bg-blue-50"><dt class="text-gray-500 text-xs">Rancangan peneliti</dt><dd class="text-xl font-semibold">{{ $ringkasan['rancangan_peneliti'] }}</dd></div>
                <div class="p-3 rounded-lg bg-red-50"><dt class="text-gray-500 text-xs">Tidak didukung</dt><dd class="text-xl font-semibold">{{ $ringkasan['tidak_didukung'] }}</dd></div>
                <div class="p-3 rounded-lg bg-gray-50"><dt class="text-gray-500 text-xs">Disepakati FGD</dt><dd class="text-xl font-semibold">{{ $pemetaans->where('status', 'disepakati')->count() }} / {{ $pemetaans->where('status', '!=', 'dikeluarkan')->count() }}</dd></div>
            </dl>
        </div>

        {{-- A. Verifikasi dokumen --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="font-semibold text-gray-900">A. Verifikasi silang terhadap dokumen acuan</h3>
            <p class="text-sm text-gray-600 mt-1">Dokumen acuan: {{ implode('; ', $dokumenAcuan) }}.</p>
            @if ($terkunci)
                <p class="mt-3 p-3 rounded-lg bg-yellow-50 border border-yellow-200 text-sm text-yellow-800">Sesi FGD {{ $sesiAktif->nomor }} sedang dibuka. Verifikasi dikunci sampai sesi ditutup.</p>
            @endif

            @forelse ($pemetaans as $p)
                <div class="mt-3 p-3 rounded-lg border text-sm {{ $p->status === 'dikeluarkan' ? 'bg-gray-50 text-gray-500' : '' }}">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="font-medium"><span class="font-mono">{{ $p->praktik->kode }}</span> {{ $p->praktik->nama }}</p>
                            <p class="text-xs text-gray-600 mt-0.5">ISO: {{ implode('; ', array_map([\App\Models\TataKelolaKeamanan\Pemetaan::class, 'labelIso'], $p->iso_items ?? [])) }}</p>
                            @if ($p->regulasi_items)
                                <p class="text-xs text-gray-600">Regulasi: {{ implode('; ', array_map(fn ($k) => \App\Services\TataKelolaKeamanan\Istilah::regulasi($k), $p->regulasi_items)) }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-1 items-center">
                            @include('admin.tata-kelola-keamanan._status-pemetaan', ['status' => $p->status, 'sesi' => $p->sesi_disepakati])
                            <span class="px-2 py-0.5 text-xs rounded {{ $warnaVerifikasi[$p->verifikasi] ?? 'bg-gray-100 text-gray-600' }}">{{ \App\Models\TataKelolaKeamanan\Pemetaan::labelVerifikasi($p->verifikasi) }}</span>
                        </div>
                    </div>

                    @if ($p->dokumen_acuan)
                        <ul class="mt-2 text-xs list-disc list-inside text-gray-700">
                            @foreach ($p->dokumen_acuan as $k => $rujukan)
                                <li>{{ $dokumenAcuan[$k] ?? $k }}{{ $rujukan ? ' — '.$rujukan : '' }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if ($p->catatan_verifikasi)
                        <p class="mt-1 text-xs"><span class="text-gray-500">Catatan verifikasi:</span> {{ $p->catatan_verifikasi }}</p>
                    @endif
                    @if ($p->alasan_dikeluarkan)
                        <p class="mt-1 text-xs text-red-700"><span class="text-gray-500">Alasan dikeluarkan:</span> {{ $p->alasan_dikeluarkan }}</p>
                    @endif

                    @if ($p->status === 'draf' && ! $terkunci)
                        <details class="mt-2" @if (! $p->verifikasi) open @endif>
                            <summary class="cursor-pointer text-xs text-green-700">{{ $p->verifikasi ? 'ubah verifikasi' : 'verifikasi butir ini' }}</summary>
                            <form method="POST" action="{{ route('admin.tata-kelola-keamanan.validasi.verifikasi', [$evaluasi, $p]) }}" class="mt-2 space-y-2">
                                @csrf @method('PUT')
                                @foreach ($dokumenAcuan as $k => $judul)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 items-center">
                                        <label class="text-xs"><input type="checkbox" name="dokumen[]" value="{{ $k }}" @checked(array_key_exists($k, $p->dokumen_acuan ?? []))> {{ $judul }}</label>
                                        <input name="rujukan[{{ $k }}]" value="{{ $p->dokumen_acuan[$k] ?? '' }}" placeholder="Rujukan: halaman, tabel, atau pasal" class="rounded border-gray-300 text-xs">
                                    </div>
                                @endforeach
                                <textarea name="catatan_verifikasi" rows="2" placeholder="Catatan verifikasi (wajib bila butir dipertahankan tanpa dukungan dokumen)" class="w-full rounded border-gray-300 text-xs">{{ $p->catatan_verifikasi }}</textarea>
                                <label class="block text-xs"><input type="hidden" name="pertahankan" value="0"><input type="checkbox" name="pertahankan" value="1" @checked($p->verifikasi === 'rancangan_peneliti')>
                                    Bila tidak ada dokumen yang dicentang: pertahankan sebagai rancangan peneliti (keterkaitan dijelaskan pada catatan)</label>
                                <button class="px-3 py-1.5 text-xs font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan verifikasi</button>
                            </form>
                        </details>
                        <details class="mt-1">
                            <summary class="cursor-pointer text-xs text-red-600">keluarkan dari model</summary>
                            <form method="POST" action="{{ route('admin.tata-kelola-keamanan.validasi.keluarkan', [$evaluasi, $p]) }}" class="mt-2 flex flex-col md:flex-row gap-2" onsubmit="return confirm('Keluarkan butir ini dari model?')">
                                @csrf
                                <input name="alasan_dikeluarkan" required placeholder="Alasan (mis. tidak didukung dokumen acuan / tidak disepakati FGD sesi 2)" class="w-full rounded border-gray-300 text-xs">
                                <button class="px-3 py-1.5 text-xs font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 whitespace-nowrap">Keluarkan</button>
                            </form>
                        </details>
                    @endif
                </div>
            @empty
                <p class="mt-3 text-sm text-gray-500">Belum ada butir pemetaan. Selesaikan tahap 3 terlebih dahulu.</p>
            @endforelse
        </div>

        {{-- B. Sesi FGD --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <h3 class="font-semibold text-gray-900">B. FGD bersama praktisi DKISP</h3>
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-sm text-gray-700">Sesi:</span>
                    @forelse ($sesiList as $s)
                        <a href="{{ route('admin.tata-kelola-keamanan.validasi', [$evaluasi, 'sesi' => $s->nomor]) }}"
                           class="px-3 py-1 text-sm rounded-full border {{ $sesi && $sesi->id === $s->id ? 'bg-green-600 text-white border-green-600' : 'border-gray-300 text-gray-700' }}">
                            {{ $s->nomor }} ({{ $s->status }})
                        </a>
                    @empty
                        <span class="text-sm text-gray-500">belum ada</span>
                    @endforelse
                </div>
            </div>

            @unless ($sesiAktif)
                <details class="mb-4 p-3 rounded-lg border" @if ($errors->any()) open @endif>
                    <summary class="cursor-pointer text-sm font-semibold text-green-700">+ Buka sesi FGD baru</summary>
                    <form method="POST" action="{{ route('admin.tata-kelola-keamanan.sesi-fgd.buka', $evaluasi) }}" class="mt-3 space-y-3">
                        @csrf
                        @include('admin.tata-kelola-keamanan._form-sesi-fgd', ['s' => null])
                        <button class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Buka sesi</button>
                    </form>
                </details>
            @endunless

            @if ($sesi)
                @php $dibuka = $sesi->status === 'dibuka'; @endphp
                <div class="text-sm text-gray-700 mb-3">
                    <p><strong>Sesi {{ $sesi->nomor }}</strong> · {{ $sesi->tanggal?->translatedFormat('d F Y') ?? 'tanggal belum diisi' }}{{ $sesi->tempat ? ' · '.$sesi->tempat : '' }} · {{ $dibuka ? 'dibuka' : 'ditutup '.$sesi->ditutup_at?->translatedFormat('d F Y') }}</p>
                    <p class="text-xs text-gray-600">Peserta: {{ $sesi->daftarPeserta() }}</p>
                    @if (! $dibuka && $sesi->catatan)
                        <p class="text-xs text-gray-600 mt-1">Catatan: {{ $sesi->catatan }}</p>
                    @endif
                </div>

                <form method="POST" action="{{ $dibuka ? route('admin.tata-kelola-keamanan.sesi-fgd.simpan', [$evaluasi, $sesi]) : '#' }}">
                    @if ($dibuka)
                        @csrf @method('PUT')
                        <details class="mb-3 p-3 rounded-lg border">
                            <summary class="cursor-pointer text-xs text-green-700">ubah data sesi (tanggal, peserta, catatan)</summary>
                            <div class="mt-3 space-y-3">
                                @include('admin.tata-kelola-keamanan._form-sesi-fgd', ['s' => $sesi])
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Catatan jalannya diskusi</label>
                                    <textarea name="catatan" rows="3" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ old('catatan', $sesi->catatan) }}</textarea>
                                </div>
                            </div>
                        </details>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-gray-500 border-b">
                                    <th class="py-2 pr-3">Praktik</th>
                                    <th class="py-2 px-3">Klausul / kontrol</th>
                                    <th class="py-2 px-3">Verifikasi</th>
                                    <th class="py-2 px-3">Keputusan forum</th>
                                    <th class="py-2 px-3">Saran peserta</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y align-top">
                                @forelse ($rekap as $r)
                                    @php $id = $r['pemetaan']->id; @endphp
                                    <tr>
                                        <td class="py-2 pr-3 font-mono">{{ $r['pemetaan']->praktik->kode }}</td>
                                        <td class="py-2 px-3 text-xs">{{ implode(', ', $r['pemetaan']->iso_items ?? []) }}</td>
                                        <td class="py-2 px-3 text-xs">{{ \App\Models\TataKelolaKeamanan\Pemetaan::labelVerifikasi($r['pemetaan']->verifikasi) }}</td>
                                        <td class="py-2 px-3 text-xs whitespace-nowrap">
                                            @if ($dibuka)
                                                <label class="block"><input type="radio" name="keputusan[{{ $id }}]" value="" @checked(! $r['keputusan'])> belum dibahas</label>
                                                <label class="block"><input type="radio" name="keputusan[{{ $id }}]" value="setuju" @checked($r['keputusan'] === 'setuju')> setuju (S)</label>
                                                <label class="block"><input type="radio" name="keputusan[{{ $id }}]" value="tidak_setuju" @checked($r['keputusan'] === 'tidak_setuju')> tidak setuju (TS)</label>
                                            @elseif ($r['keputusan'] === 'setuju')
                                                <span class="px-2 py-0.5 rounded bg-green-100 text-green-800">setuju</span>
                                            @elseif ($r['keputusan'] === 'tidak_setuju')
                                                <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800">tidak setuju</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 text-xs">
                                            @if ($dibuka)
                                                <textarea name="saran[{{ $id }}]" rows="2" class="w-full min-w-[16rem] rounded border-gray-300 text-xs">{{ $r['saran'] }}</textarea>
                                            @else
                                                {{ $r['saran'] }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="py-3 text-sm text-gray-500">Tidak ada butir yang dibahas pada sesi ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($dibuka)
                        <button class="mt-3 px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan hasil FGD</button>
                    @endif
                </form>

                @if ($dibuka)
                    <form method="POST" action="{{ route('admin.tata-kelola-keamanan.sesi-fgd.tutup', [$evaluasi, $sesi]) }}" class="mt-3"
                          onsubmit="return confirm('Simpan dulu perubahan keputusan. Tutup sesi dan terapkan keputusan forum?')">
                        @csrf
                        <button class="px-4 py-2 text-sm font-medium rounded-lg bg-yellow-600 text-white hover:bg-yellow-700">Tutup sesi {{ $sesi->nomor }}</button>
                    </form>
                @endif
            @endif
        </div>
    </div>
@endsection
