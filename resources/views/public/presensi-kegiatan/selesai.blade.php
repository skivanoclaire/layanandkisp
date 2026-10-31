@extends('public.presensi-kegiatan.layout')

@section('title', $hasil['mode'] === 'konfirmasi' ? 'Konfirmasi Kehadiran' : 'Presensi')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-6 text-center">
        <div class="mx-auto w-16 h-16 rounded-full {{ $hasil['sudah'] ? 'bg-amber-100' : 'bg-green-100' }} flex items-center justify-center text-3xl">
            {{ $hasil['sudah'] ? 'ℹ️' : '✅' }}
        </div>

        @if($hasil['sudah'])
            <h2 class="text-lg font-bold mt-4">Anda sudah tercatat hadir</h2>
            <p class="text-gray-600 mt-1">{{ $hasil['nama'] }} telah melakukan presensi pada {{ $hasil['waktu'] }} WITA.</p>
        @elseif($hasil['mode'] === 'presensi')
            <h2 class="text-lg font-bold mt-4">Presensi berhasil</h2>
            <p class="text-gray-600 mt-1">Terima kasih, {{ $hasil['nama'] }}. Kehadiran Anda tercatat pada {{ $hasil['waktu'] }} WITA.</p>
        @elseif($hasil['konfirmasi'] === 'online')
            <h2 class="text-lg font-bold mt-4">Konfirmasi hadir online diterima</h2>
            @if($hasil['wa_terkirim'])
                <p class="text-gray-600 mt-1">Terima kasih, {{ $hasil['nama'] }}. Tautan rapat online telah dikirim ke WhatsApp <strong>{{ $hasil['no_hp'] }}</strong>.</p>
            @else
                <p class="text-gray-600 mt-1">Terima kasih, {{ $hasil['nama'] }}. Tautan rapat online akan dikirim panitia ke WhatsApp <strong>{{ $hasil['no_hp'] }}</strong> menjelang kegiatan.</p>
            @endif
            <p class="text-gray-500 text-sm mt-2">Saat kegiatan berlangsung, jangan lupa mengisi presensi melalui tautan yang tercantum di pesan WhatsApp tersebut.</p>
        @else
            <h2 class="text-lg font-bold mt-4">Konfirmasi kehadiran diterima</h2>
            <p class="text-gray-600 mt-1">Terima kasih, {{ $hasil['nama'] }}. Sampai jumpa di lokasi kegiatan. Jangan lupa mengisi presensi saat tiba dengan memindai QR Code di lokasi.</p>
        @endif

        <a href="{{ route('kegiatan.show', $kegiatan) }}" class="inline-block mt-6 text-sm font-semibold text-green-700 hover:underline">← Kembali ke informasi kegiatan</a>
    </div>
@endsection
