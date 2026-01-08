@extends('layouts.authenticated')
@section('title', '- Detail Kunjungan/Colocation')
@section('header-title', 'Detail Kunjungan/Colocation')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan Kunjungan/Colocation</h1>
        <a href="{{ route('user.datacenter.visitation.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3">
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <!-- Ticket & Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-6 border-b">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">No. Tiket:</label>
                <p class="text-gray-800 font-mono text-purple-600 font-bold">{{ $visitation->ticket_no }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status:</label>
                @if($visitation->status === 'menunggu')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu Persetujuan</span>
                @elseif($visitation->status === 'disetujui')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Disetujui</span>
                @elseif($visitation->status === 'selesai')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Diajukan:</label>
                <p class="text-gray-800">{{ $visitation->created_at->format('d/m/Y H:i') }} WITA</p>
            </div>
        </div>

        <!-- Informasi Pemohon -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemohon</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama:</label>
                    <p class="text-gray-800">{{ $visitation->nama }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NIP:</label>
                    <p class="text-gray-800">{{ $visitation->nip }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi:</label>
                    <p class="text-gray-800">{{ $visitation->unitKerja->nama ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Informasi Kunjungan -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Kunjungan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tujuan Kunjungan:</label>
                    <p class="text-gray-800">{{ $visitation->tujuan_kunjungan }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Kunjungan:</label>
                    <p class="text-gray-800">{{ $visitation->tanggal_kunjungan->format('d/m/Y') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Mulai:</label>
                    <p class="text-gray-800">{{ $visitation->jam_mulai }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Selesai:</label>
                    <p class="text-gray-800">{{ $visitation->jam_selesai }}</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan:</label>
                <div class="bg-gray-50 p-4 rounded-lg border">
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $visitation->keterangan }}</p>
                </div>
            </div>
        </div>

        <!-- Data Aset (jika ada) -->
        @if($visitation->nama_aset || $visitation->nomor_aset || $visitation->catatan_aset)
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Data Aset</h2>
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                    @if($visitation->nama_aset)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Aset:</label>
                        <p class="text-gray-800">{{ $visitation->nama_aset }}</p>
                    </div>
                    @endif
                    @if($visitation->nomor_aset)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor Aset:</label>
                        <p class="text-gray-800">{{ $visitation->nomor_aset }}</p>
                    </div>
                    @endif
                </div>
                @if($visitation->catatan_aset)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Aset:</label>
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $visitation->catatan_aset }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Status Timeline -->
        @if($visitation->status !== 'menunggu')
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Timeline Status</h2>
            <div class="space-y-3">
                @if($visitation->approved_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $visitation->approved_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-blue-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-blue-800">Disetujui</p>
                        @if($visitation->processedBy)
                        <p class="text-sm text-gray-600">oleh {{ $visitation->processedBy->name }}</p>
                        @endif
                    </div>
                </div>
                @endif

                @if($visitation->completed_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $visitation->completed_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-green-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-green-800">Selesai</p>
                    </div>
                </div>
                @endif

                @if($visitation->rejected_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $visitation->rejected_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-red-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-red-800">Ditolak</p>
                        @if($visitation->processedBy)
                        <p class="text-sm text-gray-600">oleh {{ $visitation->processedBy->name }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Keterangan Admin -->
        @if($visitation->keterangan_admin)
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <h3 class="text-sm font-semibold text-green-700 mb-2">Keterangan Admin:</h3>
            <p class="text-green-900 whitespace-pre-wrap">{{ $visitation->keterangan_admin }}</p>
        </div>
        @endif

        <!-- Admin Notes -->
        @if($visitation->admin_notes)
        <div class="bg-gray-50 p-4 rounded border">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Catatan Admin:</h3>
            <p class="text-gray-800 whitespace-pre-wrap">{{ $visitation->admin_notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
