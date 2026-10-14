@extends('layouts.authenticated')

@section('title', '- Kuesioner Design Factors')
@section('header-title', 'Kuesioner Design Factors')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('user.kuesioner-portal._flash')
        @if ($errors->any())
            <div class="mb-4 p-4 rounded-lg border bg-red-50 border-red-200 text-red-800 text-sm">
                @foreach ($errors->all() as $e)
                    <p>{{ $e }}</p>
                @endforeach
            </div>
        @endif

        <a href="{{ route('tata-kelola-keamanan.responden.index') }}" class="text-sm text-green-700 hover:underline">&larr; Kembali</a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-1">Kuesioner Design Factors</h1>
        <p class="text-sm text-gray-600 mb-6">{{ $evaluasi->nama }} · {{ $responden->jabatan }}</p>

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Cara mengisi kuesioner ini',
            'poin' => [
                'Dasar penilaian' => 'Isi berdasarkan kondisi instansi saat ini menurut pengetahuan dan penilaian Bapak/Ibu. Tidak ada jawaban benar atau salah.',
                'Jenis isian' => '<strong>Angka skala</strong> (mis. 1–5): makin besar makin penting/serius. <strong>Persentase</strong>: bagi 100 ke beberapa pilihan, jumlahnya harus tepat 100. <strong>Dampak dan kemungkinan</strong>: dampak bila risiko terjadi (1 sangat kecil – 5 sangat besar) dan seberapa mungkin terjadi (1 sangat jarang – 5 hampir pasti).',
                'Contoh' => 'Bagian DF5: bila menurut Bapak/Ibu ancaman siber terhadap instansi cukup sering, isi Normal 60 dan High 40.',
                'Menyimpan' => 'Klik <strong>Simpan jawaban</strong> di bagian bawah. Bagian yang belum diisi boleh dilengkapi nanti; jawaban dapat diubah kembali.',
            ],
        ])

        <form method="POST" action="{{ route('tata-kelola-keamanan.responden.design-factors.simpan', $evaluasi) }}">
            @csrf
            @include('admin.tata-kelola-keamanan._form-df', ['definisi' => $definisi, 'isian' => $isian, 'readonly' => false, 'rataRata' => null])
            <button class="px-6 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan jawaban</button>
        </form>
    </div>
@endsection
