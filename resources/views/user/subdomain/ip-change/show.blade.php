@extends('layouts.authenticated')

@section('title', '- Detail Perubahan IP Subdomain')
@section('header-title', 'Detail Perubahan IP Subdomain')

@section('content')
    <div class="bg-blue-100 p-6 rounded-lg shadow border border-blue-200 mb-6">
        <h2 class="text-2xl font-bold text-blue-800 mb-2">Detail Permohonan Perubahan IP</h2>
        <p>Informasi lengkap mengenai permohonan perubahan IP address subdomain Anda.</p>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <!-- Success Message -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Ticket Number and Status -->
        <div class="flex items-center justify-between mb-6 pb-6 border-b">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Nomor Tiket</h3>
                <p class="text-2xl font-mono font-bold text-blue-600">{{ $request->ticket_number }}</p>
            </div>
            <div class="text-right">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Status</h3>
                <span
                    class="px-4 py-2 rounded-full text-sm font-semibold
                    @if ($request->status == 'pending') bg-yellow-100 text-yellow-800
                    @elseif($request->status == 'approved') bg-blue-100 text-blue-800
                    @elseif($request->status == 'completed') bg-green-100 text-green-800
                    @elseif($request->status == 'rejected') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800 @endif">
                    @if ($request->status == 'pending')
                        Menunggu Persetujuan
                    @elseif($request->status == 'approved')
                        Disetujui - Dalam Proses
                    @elseif($request->status == 'completed')
                        Selesai
                    @elseif($request->status == 'rejected')
                        Ditolak
                    @endif
                </span>
            </div>
        </div>

        <!-- Request Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Subdomain Name -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-600 mb-2">Nama Subdomain</h4>
                <p class="text-lg font-semibold text-gray-900">{{ $request->subdomain_name }}</p>
            </div>

            <!-- Date -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-600 mb-2">Tanggal Pengajuan</h4>
                <p class="text-lg font-semibold text-gray-900">
                    {{ $request->created_at->format('d F Y, H:i') }} WIB
                </p>
            </div>

            <!-- Old IP Address -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-600 mb-2">IP Address Lama</h4>
                <p class="text-lg font-mono font-semibold text-red-600">{{ $request->old_ip_address }}</p>
            </div>

            <!-- New IP Address -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-600 mb-2">IP Address Baru</h4>
                <p class="text-lg font-mono font-semibold text-green-600">{{ $request->new_ip_address }}</p>
            </div>
        </div>

        <!-- Reason -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h4 class="text-sm font-medium text-gray-600 mb-2">Alasan Perubahan</h4>
            <p class="text-gray-900 whitespace-pre-wrap">{{ $request->reason }}</p>
        </div>

        <!-- Processing Information -->
        @if ($request->status != 'pending')
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemrosesan</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <!-- Processed By -->
                    @if ($request->processedBy)
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-600 mb-2">Diproses Oleh</h4>
                            <p class="text-lg font-semibold text-gray-900">{{ $request->processedBy->name }}</p>
                        </div>
                    @endif

                    <!-- Processed At -->
                    @if ($request->processed_at)
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-600 mb-2">Tanggal Diproses</h4>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $request->processed_at->format('d F Y, H:i') }} WIB
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Admin Notes -->
                @if ($request->admin_notes)
                    <div
                        class="p-4 rounded-lg @if ($request->status == 'rejected') bg-red-50 border border-red-200 @else bg-blue-50 border border-blue-200 @endif">
                        <h4 class="text-sm font-medium text-gray-600 mb-2">
                            @if ($request->status == 'rejected')
                                Alasan Penolakan
                            @else
                                Catatan Admin
                            @endif
                        </h4>
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $request->admin_notes }}</p>
                    </div>
                @endif
            </div>
        @endif

        <!-- Status Timeline -->
        <div class="border-t pt-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Timeline Status</h3>
            <div class="space-y-4">
                <!-- Submitted -->
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-green-500 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-semibold text-gray-900">Permohonan Diajukan</p>
                        <p class="text-sm text-gray-600">{{ $request->created_at->format('d F Y, H:i') }} WIB</p>
                    </div>
                </div>

                <!-- Processing -->
                @if (in_array($request->status, ['approved', 'completed', 'rejected']))
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div
                                class="flex items-center justify-center w-8 h-8 @if ($request->status == 'rejected') bg-red-500 @else bg-green-500 @endif rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-gray-900">
                                @if ($request->status == 'rejected')
                                    Permohonan Ditolak
                                @else
                                    Permohonan Disetujui
                                @endif
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ $request->processed_at ? $request->processed_at->format('d F Y, H:i') . ' WIB' : '-' }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-8 h-8 bg-gray-300 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-gray-500">Menunggu Persetujuan Admin</p>
                            <p class="text-sm text-gray-400">Belum diproses</p>
                        </div>
                    </div>
                @endif

                <!-- Completed -->
                @if ($request->status == 'completed')
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-8 h-8 bg-green-500 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-gray-900">Perubahan IP Selesai</p>
                            <p class="text-sm text-gray-600">IP address telah berhasil diubah</p>
                        </div>
                    </div>
                @elseif($request->status == 'approved')
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-8 h-8 bg-gray-300 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-gray-500">Sedang Diproses</p>
                            <p class="text-sm text-gray-400">Admin sedang melakukan perubahan IP</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Button -->
        <div class="mt-6 pt-6 border-t">
            <a href="{{ route('user.subdomain.ip-change.index') }}"
                class="inline-flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-6 py-3 rounded-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- JavaScript untuk auto hide alert -->
    <script>
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-100.border-green-400');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
@endsection
