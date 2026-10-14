@extends('layouts.authenticated')

@section('title', '- Kuesioner Aktivitas')
@section('header-title', 'Kuesioner Aktivitas Tata Kelola Keamanan')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('user.kuesioner-portal._flash')

        <a href="{{ route('tata-kelola-keamanan.responden.index') }}" class="text-sm text-green-700 hover:underline">&larr; Kembali</a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-1">Kuesioner Aktivitas</h1>
        <p class="text-sm text-gray-600 mb-4">{{ $evaluasi->nama }} · {{ $responden->jabatan }}</p>

        @if ($errors->any())
            <div class="mb-4 p-4 rounded-lg border bg-red-50 border-red-200 text-red-800 text-sm">
                @foreach ($errors->all() as $e)
                    <p>{{ $e }}</p>
                @endforeach
            </div>
        @endif

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Cara mengisi kuesioner ini',
            'poin' => [
                'Pertanyaan' => 'Untuk setiap aktivitas: apakah aktivitas ini sudah dilakukan di instansi Bapak/Ibu?',
                'Pilihan jawaban' => '<strong>Ya</strong> = sudah dilakukan dan ada buktinya; <strong>Belum</strong> = belum dilakukan atau baru sebagian kecil; <strong>Tidak tahu</strong> = di luar tugas Bapak/Ibu.',
                'Keterangan' => 'Bila menjawab Ya, sebutkan bukti yang dapat ditunjukkan, mis. "SK Kepala Dinas tentang kebijakan keamanan informasi tahun 2025" atau "laporan pemindaian kerentanan bulanan". Jangan menuliskan kata sandi atau detail konfigurasi.',
                'Unggah bukti' => 'Bila menjawab Ya, Bapak/Ibu dapat melampirkan dokumen pendukung (paling banyak 3 berkas per aktivitas, maksimal 10 MB per berkas, tipe PDF, JPG, PNG, Word, atau Excel). Berkas hanya dapat dibuka oleh Bapak/Ibu dan pengelola evaluasi.',
                'Data sensitif' => 'Sebelum mengunggah, samarkan atau hapus informasi sensitif seperti alamat IP, kata sandi, nomor seri perangkat, dan detail konfigurasi. Yang diperlukan cukup bukti bahwa kegiatan dilakukan, mis. halaman pengesahan dan daftar isi dokumen.',
                'Menyimpan' => 'Jawaban dan berkas disimpan per objektif dengan tombol di bagian bawah. Bila Bapak/Ibu ditugaskan lebih dari satu objektif, pindah objektif melalui tombol di atas daftar.',
            ],
        ])

        @if (count($tugas) > 1)
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach ($tugas as $t)
                    <a href="{{ route('tata-kelola-keamanan.responden.aktivitas', [$evaluasi, 'objektif' => $t]) }}"
                       class="px-3 py-1 text-sm rounded-full border {{ $t === $kode ? 'bg-green-600 text-white border-green-600' : 'border-gray-300 text-gray-700' }}">{{ $t }}</a>
                @endforeach
            </div>
        @endif

        <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ $kode }} {{ config("tata_kelola_keamanan.objektif.$kode.nama") }} <span class="font-normal text-gray-600">({{ \App\Services\TataKelolaKeamanan\Istilah::objektif($kode)['nama_id'] }})</span></h2>
        <p class="text-sm text-gray-600 -mt-2 mb-4">{{ \App\Services\TataKelolaKeamanan\Istilah::objektif($kode)['penjelasan'] }}</p>

        @if ($praktiks->flatMap->aktivitas->isEmpty())
            <div class="bg-white rounded-lg shadow-sm p-6 text-sm text-gray-600">Aktivitas untuk objektif ini belum disiapkan pengelola. Silakan kembali lagi nanti.</div>
        @else
            <form method="POST" action="{{ route('tata-kelola-keamanan.responden.aktivitas.simpan', $evaluasi) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="objektif" value="{{ $kode }}">
                @php $no = 0; @endphp
                @foreach ($praktiks as $pr)
                    @continue($pr->aktivitas->isEmpty())
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-4">
                        <h3 class="font-semibold text-gray-900"><span class="font-mono">{{ $pr->kode }}</span> {{ $pr->nama }}<br><span class="text-sm font-normal text-gray-600">{{ $pr->nama_id }}</span></h3>
                        @foreach ($pr->aktivitas as $a)
                            @php $j = $jawaban[$a->id] ?? null; $no++; @endphp
                            <div class="mt-3 pt-3 border-t text-sm">
                                <p class="text-gray-900">{{ $no }}. {{ $a->uraian }}</p>
                                @if ($a->bukti_diharapkan)
                                    <p class="text-xs text-gray-500">Contoh bukti: {{ $a->bukti_diharapkan }}</p>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-5">
                                    @foreach (\App\Models\TataKelolaKeamanan\JawabanAktivitas::JAWABAN as $val => $label)
                                        <label><input type="radio" name="jawaban[{{ $a->id }}]" value="{{ $val }}" @checked($j?->jawaban === $val)> {{ $label }}</label>
                                    @endforeach
                                </div>
                                <input name="keterangan[{{ $a->id }}]" value="{{ $j?->keterangan }}" placeholder="Bukti / keterangan (opsional)" class="mt-2 w-full rounded-lg border-gray-300 text-sm">
                                @if ($j && $j->lampirans->isNotEmpty())
                                    <ul class="mt-2 text-xs space-y-1">
                                        @foreach ($j->lampirans as $l)
                                            <li class="flex flex-wrap items-center gap-2">
                                                <a href="{{ route('tata-kelola-keamanan.responden.lampiran.unduh', [$evaluasi, $l]) }}" class="text-green-700 hover:underline">{{ $l->nama_asli }}</a>
                                                <span class="text-gray-400">{{ $l->ukuranTerbaca() }}</span>
                                                <button type="submit" form="hapus-lampiran-{{ $l->id }}" class="text-red-600 hover:underline">hapus</button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                @php $sisa = \App\Models\TataKelolaKeamanan\LampiranJawaban::MAKS_PER_AKTIVITAS - ($j?->lampirans->count() ?? 0); @endphp
                                @if ($sisa > 0)
                                    <label class="mt-2 block text-xs text-gray-600">Lampirkan bukti (opsional, sisa {{ $sisa }} berkas):
                                        <input type="file" name="lampiran[{{ $a->id }}][]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="mt-1 block text-xs">
                                    </label>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
                <button class="px-6 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan jawaban {{ $kode }}</button>
            </form>

            {{-- Form hapus berkas ditempatkan di luar form utama (form bersarang tidak diizinkan HTML) --}}
            @foreach ($jawaban as $j)
                @foreach ($j->lampirans as $l)
                    <form id="hapus-lampiran-{{ $l->id }}" method="POST" action="{{ route('tata-kelola-keamanan.responden.lampiran.hapus', [$evaluasi, $l]) }}"
                          onsubmit="return confirm('Hapus berkas ini?')" class="hidden">
                        @csrf @method('DELETE')
                    </form>
                @endforeach
            @endforeach
        @endif
    </div>
@endsection
