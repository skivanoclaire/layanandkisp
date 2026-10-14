@extends('layouts.authenticated')

@section('title', '- Validasi Pakar Tata Kelola Keamanan')
@section('header-title', 'Validasi Pakar Tata Kelola Keamanan')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('user.kuesioner-portal._flash')

        <h1 class="text-2xl font-semibold text-gray-900">Validasi Pakar</h1>
        <p class="text-sm text-gray-600 mt-1 mb-6">Bapak/Ibu didaftarkan sebagai pakar untuk menilai pemetaan COBIT 2019 – ISO/IEC 27001:2022 – regulasi keamanan SPBE.</p>

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Apa yang dinilai',
            'poin' => [
                'Butir pemetaan' => 'Setiap butir menghubungkan satu praktik COBIT 2019 dengan klausul/kontrol ISO/IEC 27001:2022 dan ketentuan regulasi. Setiap istilah disertai terjemahan dan penjelasan singkat dalam bahasa Indonesia.',
                'Penilaian' => 'Pilih <em>Setuju</em> bila unsur-unsur pada butir memang saling berkaitan, atau <em>Tidak setuju</em> bila tidak, lalu tuliskan saran perbaikan.',
                'Putaran' => 'Penilaian dilakukan dalam beberapa putaran. Pada putaran berikutnya, Bapak/Ibu akan melihat ringkasan pendapat panel sebelumnya (tanpa nama).',
            ],
        ])

        @forelse ($pakars as $p)
            <div class="bg-white rounded-lg shadow-sm p-6 mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $p->evaluasi->nama }}</h2>
                    <p class="text-sm text-gray-600">{{ $p->evaluasi->instansi }}</p>
                    @if ($p->evaluasi->keterangan)
                        <p class="text-sm text-gray-500 mt-1">{{ $p->evaluasi->keterangan }}</p>
                    @endif
                </div>
                @if ($putaranAktif[$p->id])
                    <a href="{{ route('tata-kelola-keamanan.pakar.show', $p->evaluasi) }}" class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Nilai putaran {{ $putaranAktif[$p->id]->nomor }}</a>
                @else
                    <span class="text-sm text-gray-500">Belum ada putaran yang dibuka</span>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-6 text-sm text-gray-600">Akun Anda belum didaftarkan sebagai pakar.</div>
        @endforelse
    </div>
@endsection
