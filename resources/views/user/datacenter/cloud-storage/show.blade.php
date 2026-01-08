@extends('layouts.authenticated')
@section('title', '- Detail Cloud Storage')
@section('header-title', 'Detail Cloud Storage')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan Cloud Storage</h1>
        <a href="{{ route('user.datacenter.cloud-storage.index') }}"
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
                <p class="text-gray-800 font-mono text-purple-600 font-bold">{{ $cloudStorageRequest->ticket_no }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status:</label>
                @if($cloudStorageRequest->status === 'menunggu')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                @elseif($cloudStorageRequest->status === 'proses')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Sedang Diproses</span>
                @elseif($cloudStorageRequest->status === 'selesai')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Diajukan:</label>
                <p class="text-gray-800">{{ $cloudStorageRequest->created_at->format('d/m/Y H:i') }} WITA</p>
            </div>
        </div>

        <!-- Informasi Pemohon -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemohon</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama:</label>
                    <p class="text-gray-800">{{ $cloudStorageRequest->nama }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NIP:</label>
                    <p class="text-gray-800">{{ $cloudStorageRequest->nip }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi:</label>
                    <p class="text-gray-800">{{ $cloudStorageRequest->unitKerja->nama ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Spesifikasi Cloud Storage -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Spesifikasi Cloud Storage</h2>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kapasitas Maksimal</label>
                        <p class="text-2xl font-bold text-purple-700">{{ $cloudStorageRequest->kapasitas_gb }} GB</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe</label>
                        <p class="text-lg font-bold text-purple-700">{{ $cloudStorageRequest->tipe }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keterangan -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Keterangan</h2>
            <div class="bg-gray-50 p-4 rounded-lg border">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $cloudStorageRequest->keterangan }}</p>
            </div>
        </div>

        <!-- Akses Cloud Storage (jika sudah selesai) -->
        @if($cloudStorageRequest->status === 'selesai' && $cloudStorageRequest->akses_url)
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <h3 class="text-lg font-semibold text-green-800 mb-3">Akses Cloud Storage Anda</h3>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">URL Akses:</label>
                    <a href="{{ $cloudStorageRequest->akses_url }}" target="_blank"
                       class="text-green-900 font-mono bg-white px-3 py-2 rounded border border-green-300 inline-block hover:bg-green-100">
                        {{ $cloudStorageRequest->akses_url }} →
                    </a>
                </div>
                @if($cloudStorageRequest->username)
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">Username:</label>
                    <p class="text-green-900 font-mono bg-white px-3 py-2 rounded border border-green-300 inline-block">{{ $cloudStorageRequest->username }}</p>
                </div>
                @endif
            </div>
            @if($cloudStorageRequest->keterangan_admin)
            <div class="mt-4">
                <label class="block text-sm font-semibold text-green-700 mb-1">Keterangan Admin:</label>
                <div class="bg-white p-3 rounded border border-green-300">
                    <p class="text-green-900 whitespace-pre-wrap">{{ $cloudStorageRequest->keterangan_admin }}</p>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Status Timeline -->
        @if($cloudStorageRequest->status !== 'menunggu')
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Timeline Status</h2>
            <div class="space-y-3">
                @if($cloudStorageRequest->processing_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $cloudStorageRequest->processing_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-blue-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-blue-800">Sedang Diproses</p>
                        @if($cloudStorageRequest->processedBy)
                        <p class="text-sm text-gray-600">oleh {{ $cloudStorageRequest->processedBy->name }}</p>
                        @endif
                    </div>
                </div>
                @endif

                @if($cloudStorageRequest->completed_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $cloudStorageRequest->completed_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-green-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-green-800">Selesai - Akses Diberikan</p>
                    </div>
                </div>
                @endif

                @if($cloudStorageRequest->rejected_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $cloudStorageRequest->rejected_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-red-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-red-800">Ditolak</p>
                        @if($cloudStorageRequest->processedBy)
                        <p class="text-sm text-gray-600">oleh {{ $cloudStorageRequest->processedBy->name }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Admin Notes -->
        @if($cloudStorageRequest->admin_notes)
        <div class="bg-gray-50 p-4 rounded border">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Catatan Admin:</h3>
            <p class="text-gray-800 whitespace-pre-wrap">{{ $cloudStorageRequest->admin_notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
