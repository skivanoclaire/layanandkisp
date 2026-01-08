@extends('layouts.authenticated')

@section('title', '- Rekomendasi')
@section('header-title', 'Rekomendasi')

@section('content')
    <h1 class="text-2xl font-bold mb-6 text-green-700">Pilih Jenis Rekomendasi</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-6">
        <!-- Rekomendasi TIK -->
        <div class="bg-gray-100 opacity-50 rounded-lg p-6 text-center cursor-not-allowed border border-gray-200">
            <div class="text-center">
                <div class="text-4xl mb-3">💻</div>
                <div class="text-lg font-semibold text-gray-800 mb-2">Rekomendasi TIK</div>
                <p class="text-sm text-gray-600">
                    Untuk pengadaan barang/jasa seperti <strong>laptop, PC, storage, server, colocation</strong> atau
                    <strong>belanja TIK lainnya</strong>.
                </p>
                <div class="text-sm text-gray-500 mt-2 italic">(Belum tersedia)</div>
            </div>
        </div>

        <!-- Rekomendasi Aplikasi -->
        <a href="{{ route('user.rekomendasi.aplikasi.index') }}">
            <div
                class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition duration-300 cursor-pointer border border-gray-100">
                <div class="text-center">
                    <div class="text-4xl mb-3">🧩</div>
                    <div class="text-lg font-semibold text-gray-800 mb-2">Rekomendasi Aplikasi</div>
                    <p class="text-sm text-gray-600">
                        Khusus untuk pengadaan <strong>aplikasi SPBE</strong> yang menunjang transformasi digital di
                        instansi
                        pemerintah.
                    </p>
                </div>
            </div>
        </a>
    </div>
@endsection
