@extends('layouts.authenticated')

@section('title', '- Detail Survei Kepuasan')
@section('header-title', 'Detail Survei Kepuasan')

@section('content')
    @php
        $kategori = [
            'rating_kecepatan' => 'Kecepatan',
            'rating_kemudahan' => 'Kemudahan',
            'rating_kualitas' => 'Kualitas',
            'rating_responsif' => 'Responsivitas',
            'rating_keamanan' => 'Keamanan',
            'rating_keseluruhan' => 'Keseluruhan',
        ];
        $aplikasi = $survey->webMonitor?->nama_sistem ?? $survey->webMonitor?->subdomain ?? 'N/A';
    @endphp

    <div class="container mx-auto px-4 py-6 max-w-4xl">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Detail Survei Kepuasan</h1>
                <p class="text-sm text-gray-600 mt-1">Dikirim {{ $survey->created_at?->translatedFormat('d F Y H:i') }}</p>
            </div>
            <a href="{{ route('admin.survei-kepuasan.index') }}"
               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium">
                Kembali ke Daftar
            </a>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Identitas Responden</h2>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Nama</dt>
                    <dd class="text-gray-900 font-medium">{{ $survey->user?->name ?? 'Pengguna tidak dikenal' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="text-gray-900">{{ $survey->user?->email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Layanan / Aplikasi</dt>
                    <dd class="text-gray-900">{{ $aplikasi }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">IP Address</dt>
                    <dd class="text-gray-900">{{ $survey->ip_address ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Penilaian</h2>
            <div class="divide-y divide-gray-200">
                @foreach ($kategori as $kolom => $label)
                    <div class="flex items-center justify-between py-3">
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                        <span class="text-sm font-semibold text-gray-900">
                            {{ $survey->{$kolom} ?? '-' }} / 5
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Masukan</h2>
            <div class="space-y-4 text-sm">
                <div>
                    <p class="text-gray-500">Kelebihan</p>
                    <p class="text-gray-900 whitespace-pre-line">{{ $survey->kelebihan ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Kekurangan</p>
                    <p class="text-gray-900 whitespace-pre-line">{{ $survey->kekurangan ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Saran</p>
                    <p class="text-gray-900 whitespace-pre-line">{{ $survey->saran ?: '-' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
