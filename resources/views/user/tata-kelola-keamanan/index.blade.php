@extends('layouts.authenticated')

@section('title', '- Kuesioner Tata Kelola Keamanan')
@section('header-title', 'Kuesioner Tata Kelola Keamanan Informasi')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('user.kuesioner-portal._flash')

        <h1 class="text-2xl font-semibold text-gray-900">Kuesioner Tata Kelola Keamanan Informasi</h1>
        <p class="text-sm text-gray-600 mt-1 mb-6">Bapak/Ibu didaftarkan sebagai responden evaluasi tata kelola keamanan informasi (COBIT 2019 dan ISO/IEC 27001:2022).
            Jawaban hanya digunakan untuk keperluan evaluasi dan dapat diubah selama evaluasi berjalan.</p>

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Cara mengisi',
            'poin' => [
                'Kuesioner design factors' => 'Untuk pimpinan. Berisi 11 bagian tentang strategi, tujuan, risiko, dan kondisi TI di instansi. Isi sesuai kondisi saat ini menurut penilaian Bapak/Ibu; tidak ada jawaban benar atau salah. Perkiraan waktu 20–30 menit dan dapat disimpan bertahap.',
                'Kuesioner aktivitas' => 'Untuk pejabat/pelaksana yang menjalankan proses keamanan informasi. Untuk setiap aktivitas, pilih <em>Ya</em> bila sudah dilakukan, <em>Belum</em> bila belum, atau <em>Tidak tahu</em>. Bila menjawab Ya, sebutkan bukti yang dapat ditunjukkan (nama dokumen, aplikasi, atau catatan).',
                'Tindak lanjut' => 'Pengelola akan menghubungi Bapak/Ibu untuk wawancara singkat dan melihat bukti. Penilaian akhir dilakukan pengelola berdasarkan bukti tersebut.',
                'Kerahasiaan' => 'Jangan menuliskan kata sandi, alamat IP, atau detail konfigurasi. Cukup sebutkan nama dokumen atau jenis bukti.',
            ],
        ])

        @forelse ($respondens as $r)
            @php $rk = $ringkasan[$r->id]; @endphp
            <div class="bg-white rounded-lg shadow-sm p-6 mb-4">
                <h2 class="text-lg font-semibold text-gray-900">{{ $r->evaluasi->nama }}</h2>
                <p class="text-sm text-gray-600">{{ $r->evaluasi->instansi }} · sebagai {{ $r->jabatan }}</p>
                @if ($r->evaluasi->keterangan)
                    <p class="text-sm text-gray-500 mt-1">{{ $r->evaluasi->keterangan }}</p>
                @endif
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if ($r->isi_df)
                        <a href="{{ route('tata-kelola-keamanan.responden.design-factors', $r->evaluasi) }}" class="block p-4 rounded-lg border hover:border-green-500">
                            <p class="font-medium text-gray-900">Kuesioner design factors</p>
                            <p class="text-sm text-gray-600">{{ $rk['df'] }} dari {{ count($definisi) }} bagian lengkap</p>
                        </a>
                    @endif
                    @if ($rk['objektif'])
                        <a href="{{ route('tata-kelola-keamanan.responden.aktivitas', $r->evaluasi) }}" class="block p-4 rounded-lg border hover:border-green-500">
                            <p class="font-medium text-gray-900">Kuesioner aktivitas ({{ implode(', ', $rk['objektif']) }})</p>
                            <p class="text-sm text-gray-600">{{ $r->jawabans_count }} dari {{ $rk['aktivitas'] }} aktivitas dijawab
                                @if ($rk['aktivitas'] === 0) — aktivitas belum disiapkan pengelola @endif</p>
                        </a>
                    @endif
                    @if (! $r->isi_df && ! $rk['objektif'])
                        <p class="text-sm text-gray-500">Belum ada kuesioner yang ditugaskan.</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-6 text-sm text-gray-600">Akun Anda belum didaftarkan sebagai responden.</div>
        @endforelse
    </div>
@endsection
