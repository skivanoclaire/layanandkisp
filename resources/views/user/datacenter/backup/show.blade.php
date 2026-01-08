@extends('layouts.authenticated')
@section('title', '- Detail Backup')
@section('header-title', 'Detail Backup')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan Backup</h1>
        <a href="{{ route('user.datacenter.backup.index') }}"
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
                <p class="text-gray-800 font-mono text-purple-600 font-bold">{{ $backupRequest->ticket_no }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status:</label>
                @if($backupRequest->status === 'menunggu')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                @elseif($backupRequest->status === 'proses')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Sedang Diproses</span>
                @elseif($backupRequest->status === 'selesai')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Diajukan:</label>
                <p class="text-gray-800">{{ $backupRequest->created_at->format('d/m/Y H:i') }} WITA</p>
            </div>
        </div>

        <!-- Informasi Pemohon -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemohon</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama:</label>
                    <p class="text-gray-800">{{ $backupRequest->nama }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NIP:</label>
                    <p class="text-gray-800">{{ $backupRequest->nip }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi:</label>
                    <p class="text-gray-800">{{ $backupRequest->unitKerja->nama ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Konfigurasi Backup -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Konfigurasi Backup</h2>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2">Tipe Backup:</label>
                        <div class="space-y-1">
                            @if($backupRequest->backup_virtual_machine)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-800">Virtual Machine</span>
                                </div>
                            @endif
                            @if($backupRequest->backup_aplikasi)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-800">Aplikasi</span>
                                </div>
                            @endif
                            @if($backupRequest->backup_database)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-800">Database</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2">Jadwal & Retensi:</label>
                        <p class="text-lg font-bold text-purple-700">{{ $backupRequest->jadwal_backup }}</p>
                        <p class="text-sm text-gray-700 mt-1">Retensi: <span class="font-semibold">{{ $backupRequest->retensi_hari }} hari</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keterangan -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Keterangan</h2>
            <div class="bg-gray-50 p-4 rounded-lg border">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $backupRequest->keterangan }}</p>
            </div>
        </div>

        <!-- Status Timeline -->
        @if($backupRequest->status !== 'menunggu')
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Timeline Status</h2>
            <div class="space-y-3">
                @if($backupRequest->processing_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $backupRequest->processing_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-blue-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-blue-800">Sedang Diproses</p>
                        @if($backupRequest->processedBy)
                        <p class="text-sm text-gray-600">oleh {{ $backupRequest->processedBy->name }}</p>
                        @endif
                    </div>
                </div>
                @endif

                @if($backupRequest->completed_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $backupRequest->completed_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-green-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-green-800">Selesai - Backup Dikonfigurasi</p>
                    </div>
                </div>
                @endif

                @if($backupRequest->rejected_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $backupRequest->rejected_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-red-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-red-800">Ditolak</p>
                        @if($backupRequest->processedBy)
                        <p class="text-sm text-gray-600">oleh {{ $backupRequest->processedBy->name }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Keterangan Admin -->
        @if($backupRequest->keterangan_admin)
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <h3 class="text-sm font-semibold text-green-700 mb-2">Keterangan Admin:</h3>
            <p class="text-green-900 whitespace-pre-wrap">{{ $backupRequest->keterangan_admin }}</p>
        </div>
        @endif

        <!-- Admin Notes -->
        @if($backupRequest->admin_notes)
        <div class="bg-gray-50 p-4 rounded border">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Catatan Admin:</h3>
            <p class="text-gray-800 whitespace-pre-wrap">{{ $backupRequest->admin_notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
