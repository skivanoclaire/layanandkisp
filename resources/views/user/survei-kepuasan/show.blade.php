@extends('layouts.authenticated')

@section('title', '- Detail Survei Kepuasan')
@section('header-title', 'Detail Survei Kepuasan')

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-4xl">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Detail Survei Kepuasan</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $survey->created_at->format('d F Y, H:i') }} WIB</p>
            </div>
            <a href="{{ route('survei-kepuasan.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>

        {{-- Subdomain Info --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Informasi Layanan</h2>
            <div class="space-y-2">
                <div class="flex">
                    <span class="text-sm font-medium text-gray-500 w-40">Subdomain:</span>
                    <span class="text-sm text-gray-900 font-medium">{{ $survey->webMonitor->subdomain }}.kaltaraprov.go.id</span>
                </div>
                @if($survey->webMonitor->nama_aplikasi)
                <div class="flex">
                    <span class="text-sm font-medium text-gray-500 w-40">Nama Aplikasi:</span>
                    <span class="text-sm text-gray-900">{{ $survey->webMonitor->nama_aplikasi }}</span>
                </div>
                @endif
                @if($survey->webMonitor->nama_instansi)
                <div class="flex">
                    <span class="text-sm font-medium text-gray-500 w-40">Instansi:</span>
                    <span class="text-sm text-gray-900">{{ $survey->webMonitor->nama_instansi }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Rating Summary --}}
        <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-lg shadow-sm p-6 mb-6">
            <div class="text-center">
                <div class="text-5xl font-bold text-green-600 mb-2">{{ $survey->average_rating }}</div>
                <div class="flex justify-center mb-2">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-6 h-6 {{ $i <= round($survey->average_rating) ? 'text-yellow-400' : 'text-gray-300' }}"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <p class="text-sm text-gray-600">Rata-rata Penilaian Anda</p>
            </div>
        </div>

        {{-- Detailed Ratings --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Detail Penilaian</h2>
            <div class="space-y-4">
                @php
                    $ratings = [
                        ['label' => 'Kecepatan Akses Layanan', 'value' => $survey->rating_kecepatan],
                        ['label' => 'Kemudahan Penggunaan', 'value' => $survey->rating_kemudahan],
                        ['label' => 'Kualitas Layanan', 'value' => $survey->rating_kualitas],
                        ['label' => 'Responsivitas Layanan', 'value' => $survey->rating_responsif],
                        ['label' => 'Keamanan Layanan', 'value' => $survey->rating_keamanan],
                        ['label' => 'Kepuasan Keseluruhan', 'value' => $survey->rating_keseluruhan],
                    ];
                @endphp

                @foreach ($ratings as $rating)
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">{{ $rating['label'] }}</span>
                            <span class="text-sm font-bold text-green-600">{{ $rating['value'] }}/5</span>
                        </div>
                        <div class="flex items-center">
                            <div class="flex-1 bg-gray-200 rounded-full h-2 mr-3">
                                <div class="bg-green-500 rounded-full h-2" style="width: {{ ($rating['value'] / 5) * 100 }}%"></div>
                            </div>
                            <div class="flex">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $rating['value'] ? 'text-yellow-400' : 'text-gray-300' }}"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Feedback --}}
        @if($survey->kelebihan || $survey->kekurangan || $survey->saran)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Masukan dan Saran</h2>
                <div class="space-y-4">
                    @if($survey->kelebihan)
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Kelebihan Layanan
                            </h3>
                            <p class="text-sm text-gray-600 bg-green-50 border border-green-200 rounded-lg p-3">{{ $survey->kelebihan }}</p>
                        </div>
                    @endif

                    @if($survey->kekurangan)
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Kekurangan Layanan
                            </h3>
                            <p class="text-sm text-gray-600 bg-yellow-50 border border-yellow-200 rounded-lg p-3">{{ $survey->kekurangan }}</p>
                        </div>
                    @endif

                    @if($survey->saran)
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                                </svg>
                                Saran Perbaikan
                            </h3>
                            <p class="text-sm text-gray-600 bg-blue-50 border border-blue-200 rounded-lg p-3">{{ $survey->saran }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
