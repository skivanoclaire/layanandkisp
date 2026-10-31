@extends('public.presensi-kegiatan.layout')

@section('title', 'Informasi Kegiatan')

@section('content')
    @if($kegiatan->deskripsi)
        <div class="bg-white rounded-xl shadow-sm p-4 mb-5 text-sm text-gray-700 whitespace-pre-line">{{ $kegiatan->deskripsi }}</div>
    @endif

    <div class="grid sm:grid-cols-2 gap-4">
        <a href="{{ route('kegiatan.konfirmasi', $kegiatan) }}"
           class="block bg-white rounded-xl shadow-sm p-5 border-2 {{ $kegiatan->konfirmasi_aktif ? 'border-green-500 hover:bg-green-50' : 'border-transparent opacity-60' }}">
            <div class="text-3xl mb-2">📨</div>
            <div class="font-bold text-gray-800">Konfirmasi Kehadiran</div>
            <p class="text-sm text-gray-600 mt-1">Isi sebelum hari pelaksanaan untuk menyatakan hadir di lokasi atau hadir online.</p>
            <span class="inline-block mt-3 text-xs font-semibold px-2 py-1 rounded-full {{ $kegiatan->konfirmasi_aktif ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                {{ $kegiatan->konfirmasi_aktif ? 'Dibuka' : 'Ditutup' }}
            </span>
        </a>

        <a href="{{ route('kegiatan.presensi', $kegiatan) }}"
           class="block bg-white rounded-xl shadow-sm p-5 border-2 {{ $kegiatan->presensi_aktif ? 'border-green-500 hover:bg-green-50' : 'border-transparent opacity-60' }}">
            <div class="text-3xl mb-2">✍️</div>
            <div class="font-bold text-gray-800">Presensi (Daftar Hadir)</div>
            <p class="text-sm text-gray-600 mt-1">Isi saat tiba di lokasi acara, lengkap dengan tanda tangan.</p>
            <span class="inline-block mt-3 text-xs font-semibold px-2 py-1 rounded-full {{ $kegiatan->presensi_aktif ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                {{ $kegiatan->presensi_aktif ? 'Dibuka' : 'Belum dibuka' }}
            </span>
        </a>
    </div>
@endsection
