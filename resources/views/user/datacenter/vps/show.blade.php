@extends('layouts.authenticated')
@section('title', '- Detail VPS/VM')
@section('header-title', 'Detail VPS/VM')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan VPS/VM</h1>
        <a href="{{ route('user.datacenter.vps.index') }}"
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
                <p class="text-gray-800 font-mono text-purple-600 font-bold">{{ $vpsRequest->ticket_no }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status:</label>
                @if($vpsRequest->status === 'menunggu')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                @elseif($vpsRequest->status === 'proses')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Sedang Diproses</span>
                @elseif($vpsRequest->status === 'selesai')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Diajukan:</label>
                <p class="text-gray-800">{{ $vpsRequest->created_at->format('d/m/Y H:i') }} WITA</p>
            </div>
        </div>

        <!-- Informasi Pemohon -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemohon</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama:</label>
                    <p class="text-gray-800">{{ $vpsRequest->nama }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NIP:</label>
                    <p class="text-gray-800">{{ $vpsRequest->nip }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi:</label>
                    <p class="text-gray-800">{{ $vpsRequest->unitKerja->nama ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Spesifikasi VPS -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Spesifikasi VPS/VM</h2>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Total vCPU</label>
                        <p class="text-2xl font-bold text-purple-700">{{ $vpsRequest->vcpu }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Jumlah Socket</label>
                        <p class="text-2xl font-bold text-purple-700">{{ $vpsRequest->jumlah_socket }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">vCPU per Socket</label>
                        <p class="text-2xl font-bold text-purple-700">{{ $vpsRequest->vcpu_per_socket }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">RAM</label>
                        <p class="text-2xl font-bold text-purple-700">{{ $vpsRequest->ram_gb }} GB</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Storage</label>
                        <p class="text-2xl font-bold text-purple-700">{{ $vpsRequest->storage_gb }} GB</p>
                    </div>
                    @if($vpsRequest->ip_public)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">IP Public</label>
                        <p class="text-lg font-bold text-purple-700 font-mono">{{ $vpsRequest->ip_public }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Keterangan -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Keterangan</h2>
            <div class="bg-gray-50 p-4 rounded-lg border">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $vpsRequest->keterangan }}</p>
            </div>
        </div>

        <!-- Status Timeline -->
        @if($vpsRequest->status !== 'menunggu')
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Timeline Status</h2>
            <div class="space-y-3">
                @if($vpsRequest->processing_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $vpsRequest->processing_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-blue-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-blue-800">Sedang Diproses</p>
                        @if($vpsRequest->processedBy)
                        <p class="text-sm text-gray-600">oleh {{ $vpsRequest->processedBy->name }}</p>
                        @endif
                    </div>
                </div>
                @endif

                @if($vpsRequest->completed_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $vpsRequest->completed_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-green-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-green-800">Selesai - VPS/VM Tersedia</p>
                    </div>
                </div>
                @endif

                @if($vpsRequest->rejected_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $vpsRequest->rejected_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-red-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-red-800">Ditolak</p>
                        @if($vpsRequest->processedBy)
                        <p class="text-sm text-gray-600">oleh {{ $vpsRequest->processedBy->name }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Keterangan Admin -->
        @if($vpsRequest->keterangan_admin)
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <h3 class="text-sm font-semibold text-green-700 mb-2">Keterangan Admin:</h3>
            <p class="text-green-900 whitespace-pre-wrap">{{ $vpsRequest->keterangan_admin }}</p>
        </div>
        @endif

        <!-- Admin Notes -->
        @if($vpsRequest->admin_notes)
        <div class="bg-gray-50 p-4 rounded border">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Catatan Admin:</h3>
            <p class="text-gray-800 whitespace-pre-wrap">{{ $vpsRequest->admin_notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
