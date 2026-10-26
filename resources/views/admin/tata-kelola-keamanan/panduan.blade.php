@extends('layouts.authenticated')

@section('title', '- Panduan Tata Kelola Keamanan Informasi')
@section('header-title', 'Tata Kelola Keamanan: Panduan Penggunaan Modul')

@section('content')
    @php
        $langkah = [
            [
                'judul' => 'Persiapan: role, evaluasi, dan responden',
                'siapa' => 'Pengelola',
                'isi' => [
                    'Modul memakai dua role khusus: <strong>Pengelola Tata Kelola Keamanan</strong> (mengelola seluruh tahapan; dapat diberikan di Kelola Pengguna) dan <strong>Responden Tata Kelola Keamanan</strong>. Role responden terpasang otomatis saat akun didaftarkan pada evaluasi.',
                    'Buat evaluasi di halaman utama modul (mis. "Evaluasi Tata Kelola Keamanan Informasi 2026"). Isi keterangan singkat; keterangan ini ditampilkan kepada responden. Evaluasi bertanda <em>SIMULASI</em> hanya untuk latihan.',
                    'Tab <strong>Responden</strong>: cari akun pimpinan, lalu centang <em>isi design factors</em>. Informan RACI ditugaskan setelah objektif dipilih (Tahap 2), karena penugasannya per objektif. Akun yang belum ada dapat dibuat terlebih dahulu lalu diverifikasi.',
                ],
            ],
            [
                'judul' => 'Tahap 1 — Design factors',
                'siapa' => 'Pimpinan mengisi; pengelola memandu diskusi',
                'isi' => [
                    'Setiap pimpinan mengisi 11 design factors dari akunnya (menu <em>Layanan Digital → Kuesioner Tata Kelola Keamanan</em>). Setiap butir berbahasa Inggris disertai terjemahan dan penjelasan.',
                    'Adakan diskusi kelompok terarah (FGD). Di tab <strong>1. Design Factors</strong>, pilih <em>Nilai akhir (FGD)</em>; kolom rata-rata responden membantu diskusi. Isi nilai yang disepakati.',
                    'Masukkan nilai akhir ke COBIT 2019 Design Toolkit (file Excel dari ISACA, diunduh dengan akun ISACA).',
                ],
            ],
            [
                'judul' => 'Tahap 2 — Prioritas objektif dan target',
                'siapa' => 'Pengelola',
                'isi' => [
                    'Salin skor kepentingan 40 objektif dari Design Toolkit ke tab <strong>2. Prioritas Objektif</strong>.',
                    'Klik <em>Terapkan saran pemilihan</em> (skor ≥ 50, terkait keamanan, maksimal 8), lalu periksa dan tulis alasan bila Anda mengubahnya. Target tingkat kapabilitas muncul otomatis: skor ≥ 75 → level 4, 50–74 → level 3, 25–49 → level 2, di bawahnya → level 1.',
                    'Kembali ke tab Responden dan tugaskan informan RACI ke objektif yang terpilih.',
                ],
            ],
            [
                'judul' => 'Tahap 3 — Pemetaan COBIT – ISO/IEC 27001:2022 – regulasi',
                'siapa' => 'Pengelola',
                'isi' => [
                    'Daftar praktik objektif terpilih sudah disiapkan. Untuk setiap praktik buat butir pemetaan: pilih klausul/kontrol ISO dan ketentuan regulasi yang benar-benar terkait, lalu tulis contoh bukti.',
                    'Gunakan buku COBIT 2019 Governance and Management Objectives dan standar ISO/IEC 27001:2022 (SNI) sebagai acuan; daftar dan terjemahan di aplikasi hanya alat bantu.',
                ],
            ],
            [
                'judul' => 'Tahap 4 — Validasi model (verifikasi dokumen dan FGD)',
                'siapa' => 'Pengelola memverifikasi; praktisi DKISP memutuskan dalam FGD',
                'isi' => [
                    'Di tab <strong>4. Validasi Model</strong> bagian A, verifikasi setiap butir terhadap dokumen acuan: related guidance COBIT 2019, tabel padanan ISO/IEC 27002:2022, teks pasal Peraturan BSSN 4/2021 dan Pergub Kaltara 20/2024, serta pemetaan Parera, dkk (2026). Centang dokumen yang mencantumkan keterkaitan butir dan tulis rujukannya.',
                    'Butir tanpa dukungan dokumen hanya dipertahankan bila keterkaitannya dijelaskan pada catatan (rancangan peneliti); selebihnya perbaiki di Tahap 3 atau keluarkan dari model beserta alasannya.',
                    'Bagian B: buka sesi FGD, catat tanggal dan informan yang hadir, lalu cetak <em>Lembar validasi model</em>. Catat keputusan forum per butir (setuju/tidak setuju secara mufakat) dan sarannya, kemudian tutup sesi. Butir yang disetujui otomatis disepakati.',
                    'Butir yang tidak disetujui diperbaiki di Tahap 3 berdasarkan saran peserta, diverifikasi ulang, lalu dibahas pada sesi lanjutan. Butir yang tetap tidak disepakati dikeluarkan dari model dan dicatat alasannya.',
                ],
            ],
            [
                'judul' => 'Tahap 5 — Asesmen kapabilitas dan status kontrol',
                'siapa' => 'Informan menjawab; pengelola memeriksa bukti dan menetapkan',
                'isi' => [
                    'Tab <strong>5a</strong>: masukkan aktivitas COBIT per tingkat (bisa diimpor sekaligus). Setelah itu informan dapat menjawab ya/belum/tidak tahu dari akunnya dan melampirkan berkas bukti (privat, maksimal 3 berkas per aktivitas).',
                    'Wawancarai informan, periksa dokumen, dan lakukan observasi. Untuk setiap aktivitas tetapkan <em>terpenuhi</em> atau <em>belum</em> dan catat buktinya. Jawaban informan hanya bahan; keputusan ada pada pengelola.',
                    'Tab <strong>5b</strong>: tetapkan status setiap kontrol Annex A yang terpetakan beserta buktinya.',
                ],
            ],
            [
                'judul' => 'Tahap 6 — Kesenjangan, rekomendasi, dan laporan',
                'siapa' => 'Pengelola; dikonfirmasi pimpinan',
                'isi' => [
                    'Tab <strong>6</strong> menampilkan kesenjangan secara otomatis. Tulis rekomendasi untuk aktivitas dan kontrol yang belum terpenuhi, kelompokkan ke jangka pendek, menengah, dan panjang.',
                    'Konfirmasikan rekomendasi bersama pimpinan.',
                    'Gunakan <strong>Laporan</strong> (cetak/PDF) dan <strong>Ekspor Excel</strong> sebagai bahan laporan hasil evaluasi.',
                ],
            ],
        ];
    @endphp
    <div class="container mx-auto px-4 py-6">
        @include('user.kuesioner-portal._flash')
        <a href="{{ route('admin.tata-kelola-keamanan.index') }}" class="text-sm text-green-700 hover:underline">&larr; Semua evaluasi</a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-1">Panduan penggunaan modul</h1>
        <p class="text-sm text-gray-600 mt-1 mb-6">Semua perhitungan (target, rating N/P/L/F, tingkat kapabilitas, kesenjangan) dilakukan otomatis oleh aplikasi;
            Anda cukup mengisi dan memeriksa.</p>

        <div class="space-y-4">
            @foreach ($langkah as $i => $l)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $i }}. {{ $l['judul'] }}</h2>
                    <p class="text-xs text-gray-500 mt-1">Pelaku: {{ $l['siapa'] }}</p>
                    <ul class="mt-3 list-disc list-inside space-y-1 text-sm text-gray-800">
                        @foreach ($l['isi'] as $t)
                            <li>{!! $t !!}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            <div class="bg-white rounded-lg shadow-sm p-6 text-sm text-gray-800">
                <h2 class="text-lg font-semibold text-gray-900">Catatan penting</h2>
                <ul class="mt-3 list-disc list-inside space-y-1">
                    <li>Uraian aktivitas COBIT 2019 tidak disertakan karena hak cipta ISACA; salin dari buku yang Anda miliki (boleh dalam bahasa Indonesia).</li>
                    <li>Skor kepentingan objektif dihitung oleh Design Toolkit ISACA, bukan oleh aplikasi ini.</li>
                    <li>Terjemahan istilah COBIT dan ISO di aplikasi adalah ringkasan untuk memudahkan pengisian; rujuk dokumen resmi untuk definisi baku.</li>
                    <li>Data sensitif (kerentanan, konfigurasi) cukup dicatat ringkas; lampiran bukti tersimpan privat dan hanya dapat diunduh pengelola.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
