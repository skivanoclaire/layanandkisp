@extends('layouts.authenticated')

@section('title', '- Pemetaan COBIT–ISO')
@section('header-title', 'Tata Kelola Keamanan: Pemetaan COBIT 2019 – ISO/IEC 27001:2022 – Regulasi')

@section('content')
    @php $praktikList = $praktiks->flatten(); @endphp
    <div class="container mx-auto px-4 py-6">
        @include('admin.tata-kelola-keamanan._nav')

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Tahap 3 — Membuat butir pemetaan',
            'poin' => [
                'Satu butir' => 'Satu butir = satu praktik COBIT yang dihubungkan dengan beberapa klausul/kontrol ISO/IEC 27001:2022 dan ketentuan regulasi. Butir inilah yang nanti dinilai pakar.',
                'Cara memilih kontrol' => 'Pilih kontrol yang memenuhi tiga kriteria (Parera dkk, 2026): (1) tujuan kontrol sejalan dengan tujuan praktik; (2) sama-sama berada di tingkat tata kelola atau manajemen yang sesuai; (3) menangani jenis risiko yang sama.',
                'Contoh butir' => '<em>DSS05.04 Manage user identity and logical access</em> → A.5.15 Access control, A.5.16 Identity management, A.5.18 Access rights, A.8.2 Privileged access rights, A.8.5 Secure authentication → Pergub Kaltara 20/2024 Pasal 15 ayat 2 huruf g (pengelolaan akses kontrol). Contoh bukti: prosedur hak akses, rekap tinjauan akun.',
                'Saran' => 'Pilih 1–6 kontrol yang paling relevan per butir; terlalu banyak kontrol membuat pakar sulit menilai. Klausul 4–10 dipakai untuk praktik yang bersifat sistem manajemen (kebijakan, risiko, evaluasi).',
                'Kontrol baru ★' => 'Perhatikan 11 kontrol baru versi 2022 (mis. 5.7 threat intelligence, 8.16 monitoring activities) karena sering belum diterapkan.',
                'Kunci' => 'Butir tidak bisa diubah ketika putaran pakar sedang dibuka, dan butir yang sudah disepakati tidak bisa diubah lagi.',
                'Berikutnya' => 'Setelah semua praktik punya butir, lanjut ke tab 4. Validasi Pakar.',
            ],
        ])

        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Tahap 3 — Perancangan pemetaan</h2>
            <p class="text-sm text-gray-600 mt-1">Untuk setiap praktik objektif terpilih, pilih klausul/kontrol ISO/IEC 27001:2022 yang bersesuaian
                (kriteria: keselarasan kontrol dengan tujuan praktik, keselarasan fungsi tata kelola/manajemen, konsistensi domain risiko),
                lalu kaitkan dengan ketentuan regulasi. Cocokkan dengan buku COBIT 2019 dan standar ISO sebelum diajukan ke pakar.</p>
            @if ($putaranAktif)
                <p class="mt-3 p-3 rounded-lg bg-yellow-50 border border-yellow-200 text-sm text-yellow-800">Putaran {{ $putaranAktif->nomor }} penilaian pakar sedang dibuka. Pemetaan dikunci sampai putaran ditutup.</p>
            @endif
        </div>

        @if ($objektifs->isEmpty())
            <div class="bg-white rounded-lg shadow-sm p-6 text-sm text-gray-600">Belum ada objektif yang dipilih. Selesaikan tahap 2 terlebih dahulu.</div>
        @else
            @if (! $putaranAktif && $praktikList->isNotEmpty())
                <details class="bg-white rounded-lg shadow-sm p-6 mb-6" @if ($errors->any()) open @endif>
                    <summary class="cursor-pointer font-semibold text-green-700">+ Tambah butir pemetaan</summary>
                    <form method="POST" action="{{ route('admin.tata-kelola-keamanan.pemetaan.store', $evaluasi) }}" class="mt-4">
                        @csrf
                        @include('admin.tata-kelola-keamanan._form-pemetaan', ['pemetaan' => null, 'praktikId' => null])
                        <button class="mt-3 px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan butir</button>
                    </form>
                </details>
            @endif

            @foreach ($objektifs as $o)
                <div class="bg-white rounded-lg shadow-sm p-6 mb-4">
                    <h3 class="font-semibold text-gray-900">{{ $o->kode }} {{ $o->nama }} <span class="font-normal text-gray-600">({{ $o->nama_id }})</span>
                        <span class="text-sm font-normal text-gray-500">— target level {{ $o->target_level ?? '-' }}</span></h3>

                    @forelse ($praktiks[$o->kode] ?? [] as $pr)
                        <div class="mt-4 border-t pt-3">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-medium text-gray-800"><span class="font-mono">{{ $pr->kode }}</span> {{ $pr->nama }}<br><span class="text-sm font-normal text-gray-600">{{ $pr->nama_id }}</span></p>
                                @if ($pr->pemetaans->isEmpty())
                                    <form method="POST" action="{{ route('admin.tata-kelola-keamanan.praktik.destroy', [$evaluasi, $pr]) }}">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-600 hover:underline">hapus praktik</button>
                                    </form>
                                @endif
                            </div>

                            @forelse ($pr->pemetaans as $p)
                                <div class="mt-2 ml-4 p-3 rounded-lg border text-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        @include('admin.tata-kelola-keamanan._status-pemetaan', ['status' => $p->status, 'putaran' => $p->putaran_disepakati])
                                        @if ($p->status === 'draf' && ! $putaranAktif)
                                            <form method="POST" action="{{ route('admin.tata-kelola-keamanan.pemetaan.destroy', [$evaluasi, $p]) }}" onsubmit="return confirm('Hapus butir ini?')">
                                                @csrf @method('DELETE')
                                                <button class="text-xs text-red-600 hover:underline">hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                    <p class="mt-2"><span class="text-gray-500">ISO:</span>
                                        {{ implode('; ', array_map([\App\Models\TataKelolaKeamanan\Pemetaan::class, 'labelIso'], $p->iso_items ?? [])) }}</p>
                                    @if ($p->regulasi_items)
                                        <p class="mt-1"><span class="text-gray-500">Regulasi:</span>
                                            {{ implode('; ', array_map(fn ($k) => \App\Services\TataKelolaKeamanan\Istilah::regulasi($k), $p->regulasi_items)) }}</p>
                                    @endif
                                    @if ($p->bukti_diharapkan)
                                        <p class="mt-1"><span class="text-gray-500">Bukti:</span> {{ $p->bukti_diharapkan }}</p>
                                    @endif
                                    @if ($p->catatan_revisi)
                                        <p class="mt-1 text-yellow-800"><span class="text-gray-500">Catatan revisi:</span> {{ $p->catatan_revisi }}</p>
                                    @endif

                                    @if ($p->status === 'draf' && ! $putaranAktif)
                                        <details class="mt-2">
                                            <summary class="cursor-pointer text-xs text-green-700">ubah</summary>
                                            <form method="POST" action="{{ route('admin.tata-kelola-keamanan.pemetaan.update', [$evaluasi, $p]) }}" class="mt-2">
                                                @csrf @method('PUT')
                                                @include('admin.tata-kelola-keamanan._form-pemetaan', ['pemetaan' => $p, 'praktikId' => $p->praktik_id])
                                                <button class="mt-3 px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan perubahan</button>
                                            </form>
                                        </details>
                                    @endif
                                </div>
                            @empty
                                <p class="ml-4 mt-1 text-xs text-gray-400">Belum ada butir pemetaan.</p>
                            @endforelse
                        </div>
                    @empty
                        <p class="mt-2 text-sm text-gray-500">Praktik belum tersedia di referensi. Tambahkan praktik dari buku COBIT 2019 di bawah.</p>
                    @endforelse
                </div>
            @endforeach

            <details class="bg-white rounded-lg shadow-sm p-6">
                <summary class="cursor-pointer font-semibold text-gray-700">+ Tambah praktik manual</summary>
                <form method="POST" action="{{ route('admin.tata-kelola-keamanan.praktik.store', $evaluasi) }}" class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-2">
                    @csrf
                    <select name="objektif_kode" class="rounded-lg border-gray-300 text-sm">
                        @foreach ($objektifs as $o)
                            <option value="{{ $o->kode }}">{{ $o->kode }}</option>
                        @endforeach
                    </select>
                    <input name="kode" placeholder="Kode (mis. APO10.01)" class="rounded-lg border-gray-300 text-sm" required>
                    <input name="nama" placeholder="Nama praktik" class="md:col-span-2 rounded-lg border-gray-300 text-sm" required>
                    <button class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700 md:col-span-1">Tambah</button>
                </form>
            </details>
        @endif
    </div>
@endsection
