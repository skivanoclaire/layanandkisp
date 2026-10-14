@extends('layouts.authenticated')

@section('title', '- Tata Kelola Keamanan Informasi')
@section('header-title', 'Tata Kelola Keamanan: Integrasi COBIT 2019 dan ISO/IEC 27001:2022')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('admin.tata-kelola-keamanan._nav')

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Cara memakai modul ini',
            'buka' => false,
            'poin' => [
                'Urutan' => 'Kerjakan tab dari kiri ke kanan: Responden → 1 Design Factors → 2 Prioritas Objektif → 3 Pemetaan → 4 Validasi Pakar → 5a/5b Asesmen → 6 Kesenjangan & Rekomendasi. Lingkaran hijau di bawah menandai tahap yang sudah selesai.',
                'Panduan lengkap' => 'Buka tombol <strong>Panduan</strong> di kanan atas untuk penjelasan setiap tahap, siapa yang mengisi, dan contoh pengisiannya.',
                'Perhitungan' => 'Target level, rating N/P/L/F, tingkat kapabilitas, kesepakatan pakar, dan kesenjangan dihitung otomatis. Anda cukup mengisi dan memeriksa.',
                'Hasil' => 'Tombol <strong>Laporan</strong> (cetak/PDF) dan <strong>Ekspor Excel</strong> berisi tabel-tabel sebagai bahan laporan.',
            ],
        ])

        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Kemajuan tahapan evaluasi</h2>
            <p class="text-sm text-gray-500 mb-4">Alur: design factors → model integrasi → validasi pakar → demonstrasi (asesmen) → kesenjangan dan peta jalan.</p>
            <ol class="space-y-3">
                @foreach ($tahapan as $i => $t)
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-sm font-semibold {{ $t['selesai'] ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-600' }}">{{ $i + 1 }}</span>
                        <div>
                            <a href="{{ route($t['route'], $evaluasi) }}" class="font-medium text-gray-900 hover:text-green-700">{{ $t['judul'] }}</a>
                            <p class="text-sm text-gray-500">{{ $t['keterangan'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Data evaluasi</h2>
                <form method="POST" action="{{ route('admin.tata-kelola-keamanan.update', $evaluasi) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <input name="nama" required value="{{ $evaluasi->nama }}" class="w-full rounded-lg border-gray-300 text-sm">
                    <input name="instansi" value="{{ $evaluasi->instansi }}" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Instansi">
                    <textarea name="keterangan" rows="3" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Keterangan (ditampilkan kepada responden dan pakar)">{{ $evaluasi->keterangan }}</textarea>
                    <button class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan</button>
                </form>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-red-700 mb-3">Hapus evaluasi</h2>
                <p class="text-sm text-gray-600 mb-3">Seluruh data evaluasi (design factors, pemetaan, penilaian pakar, asesmen, rekomendasi) akan terhapus permanen.</p>
                <form method="POST" action="{{ route('admin.tata-kelola-keamanan.destroy', $evaluasi) }}" class="space-y-3">
                    @csrf @method('DELETE')
                    <input name="konfirmasi" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Ketik nama evaluasi: {{ $evaluasi->nama }}">
                    <button class="px-4 py-2 text-sm font-medium rounded-lg bg-red-600 text-white hover:bg-red-700">Hapus</button>
                </form>
            </div>
        </div>
    </div>
@endsection
