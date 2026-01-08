@extends('layouts.authenticated')

@section('title', '- Detail Perubahan IP Subdomain')
@section('header-title', 'Detail Perubahan IP Subdomain')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.subdomain.ip-change.index') }}"
            class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ticket and Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-6 pb-6 border-b">
                    <div>
                        <h3 class="text-sm text-gray-600 mb-1">Nomor Tiket</h3>
                        <p class="text-2xl font-mono font-bold text-blue-600">{{ $request->ticket_number }}</p>
                    </div>
                    <div class="text-right">
                        <h3 class="text-sm text-gray-600 mb-2">Status</h3>
                        <span
                            class="px-4 py-2 rounded-full text-sm font-semibold
                            @if ($request->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($request->status == 'approved') bg-blue-100 text-blue-800
                            @elseif($request->status == 'completed') bg-green-100 text-green-800
                            @elseif($request->status == 'rejected') bg-red-100 text-red-800 @endif">
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
                <div class="space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-1">Nama Subdomain</h4>
                        <p class="text-lg font-semibold text-gray-900">{{ $request->subdomain_name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-600 mb-1">IP Address Lama</h4>
                            <p class="text-xl font-mono font-semibold text-red-600">{{ $request->old_ip_address }}</p>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-600 mb-1">IP Address Baru</h4>
                            <p class="text-xl font-mono font-semibold text-green-600">{{ $request->new_ip_address }}</p>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-1">Alasan Perubahan</h4>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $request->reason }}</p>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-1">Tanggal Pengajuan</h4>
                        <p class="text-gray-900">{{ $request->created_at->format('d F Y, H:i') }} WIB</p>
                    </div>
                </div>
            </div>

            <!-- Processing Information -->
            @if ($request->status != 'pending')
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemrosesan</h3>

                    <div class="space-y-4">
                        @if ($request->processedBy)
                            <div>
                                <h4 class="text-sm font-medium text-gray-600 mb-1">Diproses Oleh</h4>
                                <p class="text-gray-900 font-medium">{{ $request->processedBy->name }}</p>
                                <p class="text-sm text-gray-500">{{ $request->processedBy->email }}</p>
                            </div>
                        @endif

                        @if ($request->processed_at)
                            <div>
                                <h4 class="text-sm font-medium text-gray-600 mb-1">Tanggal Diproses</h4>
                                <p class="text-gray-900">{{ $request->processed_at->format('d F Y, H:i') }} WIB</p>
                            </div>
                        @endif

                        @if ($request->admin_notes)
                            <div>
                                <h4 class="text-sm font-medium text-gray-600 mb-1">
                                    @if ($request->status == 'rejected')
                                        Alasan Penolakan
                                    @else
                                        Catatan Admin
                                    @endif
                                </h4>
                                <div
                                    class="p-4 rounded-lg @if ($request->status == 'rejected') bg-red-50 border border-red-200 @else bg-blue-50 border border-blue-200 @endif">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $request->admin_notes }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Pemohon Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemohon</h3>
                <div class="space-y-3">
                    <div>
                        <h4 class="text-sm font-medium text-gray-600">Nama</h4>
                        <p class="text-gray-900 font-medium">{{ $request->user->name }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-600">Email</h4>
                        <p class="text-gray-900">{{ $request->user->email }}</p>
                    </div>
                    @if ($request->user->nik)
                        <div>
                            <h4 class="text-sm font-medium text-gray-600">NIK</h4>
                            <p class="text-gray-900 font-mono">{{ $request->user->nik }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            @if ($request->status == 'pending')
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi</h3>

                    <!-- Approve Form -->
                    <form action="{{ route('admin.subdomain.ip-change.approve', $request->id) }}" method="POST"
                        class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label for="approve_notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Catatan (opsional)
                            </label>
                            <textarea name="admin_notes" id="approve_notes" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                        </div>
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-3 rounded-lg transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Setujui Permohonan
                        </button>
                    </form>

                    <!-- Reject Form -->
                    <form action="{{ route('admin.subdomain.ip-change.reject', $request->id) }}" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin menolak permohonan ini?')">
                        @csrf
                        <div class="mb-3">
                            <label for="reject_notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Alasan Penolakan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="admin_notes" id="reject_notes" rows="3" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                                placeholder="Jelaskan alasan penolakan..."></textarea>
                            @error('admin_notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-3 rounded-lg transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Tolak Permohonan
                        </button>
                    </form>
                </div>
            @elseif($request->status == 'approved')
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi</h3>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-blue-800">
                            Permohonan sudah disetujui. Setelah melakukan perubahan IP di sistem, tandai sebagai selesai.
                        </p>
                    </div>

                    <!-- Complete Form -->
                    <form action="{{ route('admin.subdomain.ip-change.complete', $request->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="complete_notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Catatan Tambahan (opsional)
                            </label>
                            <textarea name="admin_notes" id="complete_notes" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                placeholder="Tambahkan catatan penyelesaian..."></textarea>
                        </div>
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-3 rounded-lg transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Tandai Selesai
                        </button>
                    </form>
                </div>
            @endif

            <!-- Timeline -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Timeline</h3>
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
                            <p class="text-xs text-gray-600">{{ $request->created_at->format('d F Y, H:i') }} WIB</p>
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
                                <p class="text-xs text-gray-600">
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
                                <p class="text-sm font-semibold text-gray-500">Menunggu Persetujuan</p>
                                <p class="text-xs text-gray-400">Belum diproses</p>
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
                                <p class="text-xs text-gray-600">IP address telah berhasil diubah</p>
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
                                <p class="text-xs text-gray-400">Admin sedang melakukan perubahan IP</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript untuk auto hide alert -->
    <script>
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-100.border-green-400, .bg-red-100.border-red-400');
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
