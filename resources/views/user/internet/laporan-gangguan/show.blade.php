@extends('layouts.authenticated')
@section('title', '- Detail Laporan Gangguan')
@section('header-title', 'Detail Laporan Gangguan')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-purple-700">Detail Laporan Gangguan</h1>
        <a href="{{ route('user.internet.laporan-gangguan.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-semibold">
            Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-300 bg-green-50 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <!-- Informasi Status -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">No. Tiket</p>
                <p class="font-bold text-lg text-purple-600">{{ $laporanGangguan->ticket_no }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status</p>
                @if($laporanGangguan->status === 'menunggu')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        Menunggu Ditindaklanjuti
                    </span>
                @elseif($laporanGangguan->status === 'proses')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        Sedang Diproses
                    </span>
                @elseif($laporanGangguan->status === 'selesai')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        Selesai Ditangani
                    </span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        Ditolak
                    </span>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-600">Tanggal Laporan</p>
                <p class="font-semibold">{{ $laporanGangguan->created_at->format('d F Y, H:i') }} WITA</p>
            </div>
            @if($laporanGangguan->completed_at)
            <div>
                <p class="text-sm text-gray-600">Tanggal Selesai</p>
                <p class="font-semibold">{{ $laporanGangguan->completed_at->format('d F Y, H:i') }} WITA</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Informasi Pelapor -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Informasi Pelapor</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Nama</p>
                <p class="font-semibold">{{ $laporanGangguan->nama }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">NIP</p>
                <p class="font-semibold">{{ $laporanGangguan->nip }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Instansi</p>
                <p class="font-semibold">{{ $laporanGangguan->unitKerja->nama ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">No. HP/WhatsApp</p>
                <p class="font-semibold">{{ $laporanGangguan->no_hp }}</p>
            </div>
        </div>
    </div>

    <!-- Detail Permasalahan -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Detail Permasalahan</h2>

        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Uraian Permasalahan</p>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $laporanGangguan->uraian_permasalahan }}</p>
            </div>
        </div>

        @if($laporanGangguan->lokasi_koordinat)
        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Lokasi / Koordinat</p>
            <p class="font-semibold">{{ $laporanGangguan->lokasi_koordinat }}</p>
        </div>
        @endif

        @if($laporanGangguan->lampiran_foto && count($laporanGangguan->lampiran_foto) > 0)
        <div>
            <p class="text-sm text-gray-600 mb-2">Lampiran Foto</p>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($laporanGangguan->lampiran_foto as $foto)
                    <a href="{{ asset('storage/' . $foto) }}" target="_blank" class="block">
                        <img src="{{ asset('storage/' . $foto) }}"
                             alt="Lampiran Foto"
                             class="w-full h-32 object-cover rounded-lg border border-gray-300 hover:shadow-lg transition-shadow">
                    </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Tanggapan Admin -->
    @if($laporanGangguan->admin_notes || $laporanGangguan->processedBy)
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Tanggapan Tim</h2>

        @if($laporanGangguan->processedBy)
        <div class="mb-4">
            <p class="text-sm text-gray-600">Ditangani Oleh</p>
            <p class="font-semibold">{{ $laporanGangguan->processedBy->name }}</p>
        </div>
        @endif

        @if($laporanGangguan->admin_notes)
        <div>
            <p class="text-sm text-gray-600 mb-2">Catatan</p>
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $laporanGangguan->admin_notes }}</p>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
