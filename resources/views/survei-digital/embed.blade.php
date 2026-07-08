@extends('layouts.authenticated')
@section('title', '- Beri Penilaian')
@section('header-title', 'Beri Penilaian')

@section('content')
<div class="max-w-7xl mx-auto">
  <div class="mb-6">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h1 class="text-2xl font-bold text-blue-700">{{ $heading }}</h1>
        @if (!empty($subtitle))
          <p class="text-gray-600 mt-1">Referensi: <span class="font-mono font-semibold">{{ $subtitle }}</span></p>
        @endif
      </div>
      <a href="{{ $backUrl }}"
         class="inline-flex items-center bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
         ← Kembali ke Daftar
      </a>
    </div>

    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-4">
      <p class="text-sm text-blue-800">
        <strong>Mohon luangkan waktu Anda</strong> untuk memberikan penilaian terhadap layanan yang telah Anda terima.
        Masukan Anda sangat berharga untuk meningkatkan kualitas layanan kami.
      </p>
    </div>
  </div>

  @if (empty($surveyUrl))
    <div class="bg-white rounded-lg shadow-lg p-8 text-center">
      <p class="text-gray-700 font-semibold mb-2">Survei belum tersedia saat ini.</p>
      <p class="text-gray-500 text-sm">Form survei sedang tidak aktif. Silakan coba lagi nanti atau hubungi administrator.</p>
    </div>
  @else
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
      <iframe
        src="{{ $surveyUrl }}"
        class="w-full border-0"
        style="min-height: 800px; height: calc(100vh - 250px);"
        title="Survey Kepuasan Layanan"
        loading="lazy"
        referrerpolicy="strict-origin-when-cross-origin"
      ></iframe>
    </div>

    <div class="mt-4 text-center text-gray-600 text-sm">
      <p>Jika form survey tidak muncul, silakan <a href="{{ $surveyUrl }}" target="_blank" class="text-blue-600 hover:underline">klik di sini untuk membuka di tab baru</a>.</p>
    </div>
  @endif
</div>
@endsection
