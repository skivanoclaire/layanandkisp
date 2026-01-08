@extends('layouts.authenticated')
@section('title', '- Detail Permohonan Starlink')
@section('header-title', 'Detail Permohonan Starlink')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-purple-700">Detail Permohonan Starlink Jelajah</h1>
        <a href="{{ route('user.internet.starlink.index') }}"
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
                <p class="font-bold text-lg text-purple-600">{{ $starlinkRequest->ticket_no }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status</p>
                @if($starlinkRequest->status === 'menunggu')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        Menunggu Diproses
                    </span>
                @elseif($starlinkRequest->status === 'proses')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        Sedang Diproses
                    </span>
                @elseif($starlinkRequest->status === 'selesai')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        Disetujui
                    </span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        Ditolak
                    </span>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-600">Tanggal Pengajuan</p>
                <p class="font-semibold">{{ $starlinkRequest->created_at->format('d F Y, H:i') }} WITA</p>
            </div>
            @if($starlinkRequest->completed_at)
            <div>
                <p class="text-sm text-gray-600">Tanggal Disetujui</p>
                <p class="font-semibold">{{ $starlinkRequest->completed_at->format('d F Y, H:i') }} WITA</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Informasi Pemohon -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Informasi Pemohon</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Nama</p>
                <p class="font-semibold">{{ $starlinkRequest->nama }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">NIP</p>
                <p class="font-semibold">{{ $starlinkRequest->nip }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Instansi</p>
                <p class="font-semibold">{{ $starlinkRequest->unitKerja->nama ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">No. HP/WhatsApp</p>
                <p class="font-semibold">{{ $starlinkRequest->no_hp }}</p>
            </div>
        </div>
    </div>

    <!-- Detail Kegiatan -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Detail Kegiatan</h2>

        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Uraian Kegiatan</p>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $starlinkRequest->uraian_kegiatan }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600 mb-2">Periode Kegiatan</p>
                <p class="font-semibold">
                    {{ $starlinkRequest->tanggal_mulai->format('d F Y') }} -
                    {{ $starlinkRequest->tanggal_selesai->format('d F Y') }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-2">Waktu Kegiatan</p>
                <p class="font-semibold">
                    {{ \Carbon\Carbon::parse($starlinkRequest->jam_mulai)->format('H:i') }} -
                    {{ \Carbon\Carbon::parse($starlinkRequest->jam_selesai)->format('H:i') }} WITA
                </p>
            </div>
        </div>
    </div>

    <!-- Tanggapan Admin -->
    @if($starlinkRequest->admin_notes || $starlinkRequest->processedBy)
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Tanggapan Tim</h2>

        @if($starlinkRequest->processedBy)
        <div class="mb-4">
            <p class="text-sm text-gray-600">Diproses Oleh</p>
            <p class="font-semibold">{{ $starlinkRequest->processedBy->name }}</p>
        </div>
        @endif

        @if($starlinkRequest->admin_notes)
        <div>
            <p class="text-sm text-gray-600 mb-2">Catatan</p>
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $starlinkRequest->admin_notes }}</p>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
