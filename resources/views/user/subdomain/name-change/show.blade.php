@extends('layouts.authenticated')

@section('title', '- Detail Permohonan Perubahan Nama Subdomain')
@section('header-title', 'Detail Permohonan Perubahan Nama Subdomain')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('user.subdomain.name-change.index') }}"
                class="text-purple-600 hover:text-purple-800 font-medium">
                ← Kembali ke Daftar Permohonan
            </a>
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Header -->
        <div class="border-b pb-4 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $request->ticket_number }}</h1>
                    <p class="text-gray-600 mt-1">Diajukan pada: {{ $request->created_at->format('d F Y, H:i') }}</p>
                </div>
                <div>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold
                        @if ($request->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($request->status == 'approved') bg-blue-100 text-blue-800
                        @elseif($request->status == 'completed') bg-green-100 text-green-800
                        @elseif($request->status == 'rejected') bg-red-100 text-red-800
                        @endif">
                        @if ($request->status == 'pending') Menunggu Persetujuan
                        @elseif($request->status == 'approved') Disetujui
                        @elseif($request->status == 'completed') Selesai
                        @elseif($request->status == 'rejected') Ditolak
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Detail Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Subdomain Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-4 text-gray-800">Informasi Subdomain</h2>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-gray-600">Nama Lama:</label>
                        <p class="font-semibold text-gray-800">{{ $request->old_subdomain_name }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Nama Baru:</label>
                        <p class="font-semibold text-purple-700 text-lg">{{ $request->new_subdomain_name }}</p>
                    </div>
                    @if($request->webMonitor)
                    <div>
                        <label class="text-sm text-gray-600">Instansi:</label>
                        <p class="font-semibold text-gray-800">{{ $request->webMonitor->nama_instansi }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">IP Address:</label>
                        <p class="font-mono text-sm text-gray-800">{{ $request->webMonitor->ip_address }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Status Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-4 text-gray-800">Status Permohonan</h2>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-gray-600">Status Saat Ini:</label>
                        <p class="font-semibold">
                            @if ($request->status == 'pending')
                                <span class="text-yellow-700">Menunggu Persetujuan Admin</span>
                            @elseif($request->status == 'approved')
                                <span class="text-blue-700">Disetujui - Menunggu Eksekusi</span>
                            @elseif($request->status == 'completed')
                                <span class="text-green-700">Perubahan Selesai Dilakukan</span>
                            @elseif($request->status == 'rejected')
                                <span class="text-red-700">Ditolak</span>
                            @endif
                        </p>
                    </div>
                    @if($request->processed_at)
                    <div>
                        <label class="text-sm text-gray-600">Diproses Pada:</label>
                        <p class="text-gray-800">{{ $request->processed_at->format('d F Y, H:i') }}</p>
                    </div>
                    @endif
                    @if($request->processedBy)
                    <div>
                        <label class="text-sm text-gray-600">Diproses Oleh:</label>
                        <p class="text-gray-800">{{ $request->processedBy->name }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Reason -->
        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-semibold mb-3 text-gray-800">Alasan Perubahan</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $request->reason }}</p>
        </div>

        <!-- Admin Notes -->
        @if($request->admin_notes)
        <div class="mt-6 p-4 rounded-lg {{ $request->status == 'rejected' ? 'bg-red-50 border border-red-200' : 'bg-blue-50 border border-blue-200' }}">
            <h2 class="text-lg font-semibold mb-3 {{ $request->status == 'rejected' ? 'text-red-800' : 'text-blue-800' }}">
                Catatan Admin
            </h2>
            <p class="{{ $request->status == 'rejected' ? 'text-red-700' : 'text-blue-700' }} whitespace-pre-line">{{ $request->admin_notes }}</p>
        </div>
        @endif

        <!-- Completion Notice -->
        @if($request->status == 'completed')
        <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <h3 class="font-semibold text-green-800 mb-2">✓ Perubahan Berhasil Dilakukan</h3>
            <p class="text-sm text-green-700">
                Subdomain Anda telah berhasil diubah dari <strong>{{ $request->old_subdomain_name }}</strong>
                menjadi <strong>{{ $request->new_subdomain_name }}</strong>.
            </p>
            <p class="text-sm text-green-700 mt-2">
                <strong>Perhatian:</strong> DNS propagation dapat memakan waktu hingga 24-48 jam.
                Pastikan untuk menginformasikan perubahan ini kepada seluruh pengguna Anda.
            </p>
        </div>
        @endif

        <!-- Status Timeline -->
        <div class="mt-6">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">Timeline</h2>
            <div class="space-y-4">
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Permohonan Diajukan</p>
                        <p class="text-sm text-gray-600">{{ $request->created_at->format('d F Y, H:i') }}</p>
                    </div>
                </div>

                @if($request->processed_at)
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 {{ in_array($request->status, ['approved', 'completed']) ? 'bg-green-500' : 'bg-red-500' }} rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if(in_array($request->status, ['approved', 'completed']))
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                @endif
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">
                            @if($request->status == 'rejected') Permohonan Ditolak
                            @else Permohonan Disetujui
                            @endif
                        </p>
                        <p class="text-sm text-gray-600">{{ $request->processed_at->format('d F Y, H:i') }}</p>
                        @if($request->processedBy)
                            <p class="text-xs text-gray-500">oleh {{ $request->processedBy->name }}</p>
                        @endif
                    </div>
                </div>
                @endif

                @if($request->status == 'completed')
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Perubahan Selesai Dilakukan</p>
                        <p class="text-sm text-gray-600">{{ $request->updated_at->format('d F Y, H:i') }}</p>
                        <p class="text-xs text-gray-500">DNS record telah diupdate di Cloudflare</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
