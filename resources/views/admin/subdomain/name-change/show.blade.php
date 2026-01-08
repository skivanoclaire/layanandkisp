@extends('layouts.authenticated')

@section('title', '- Detail Permohonan Perubahan Nama Subdomain')
@section('header-title', 'Detail Permohonan Perubahan Nama Subdomain')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('admin.subdomain.name-change.index') }}"
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
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Header -->
        <div class="border-b pb-4 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $request->ticket_number }}</h1>
                    <p class="text-gray-600 mt-1">Diajukan oleh: <strong>{{ $request->user->name }}</strong></p>
                    <p class="text-gray-500 text-sm">Pada: {{ $request->created_at->format('d F Y, H:i') }}</p>
                </div>
                <div>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold
                        @if ($request->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($request->status == 'approved') bg-blue-100 text-blue-800
                        @elseif($request->status == 'completed') bg-green-100 text-green-800
                        @elseif($request->status == 'rejected') bg-red-100 text-red-800
                        @endif">
                        {{ strtoupper($request->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Detail Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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
                    <div>
                        <label class="text-sm text-gray-600">Cloudflare Record ID:</label>
                        <p class="font-mono text-xs text-gray-600">{{ $request->webMonitor->cloudflare_record_id ?? '-' }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- User Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-4 text-gray-800">Informasi Pemohon</h2>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-gray-600">Nama:</label>
                        <p class="font-semibold text-gray-800">{{ $request->user->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Email:</label>
                        <p class="text-gray-800">{{ $request->user->email }}</p>
                    </div>
                    @if($request->processed_at)
                    <div class="pt-3 border-t">
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
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h2 class="text-lg font-semibold mb-3 text-gray-800">Alasan Perubahan</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $request->reason }}</p>
        </div>

        <!-- Admin Actions -->
        @if($request->status == 'pending')
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4 text-orange-800">Aksi Admin</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Approve Form -->
                <form action="{{ route('admin.subdomain.name-change.approve', $request->id) }}" method="POST"
                    onsubmit="return confirm('Yakin ingin menyetujui permohonan ini?')">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                        <textarea name="admin_notes" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                            placeholder="Tambahkan catatan jika diperlukan"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-3 rounded-lg">
                        ✓ Setujui Permohonan
                    </button>
                </form>

                <!-- Reject Form -->
                <form action="{{ route('admin.subdomain.name-change.reject', $request->id) }}" method="POST"
                    onsubmit="return confirm('Yakin ingin menolak permohonan ini?')">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                        <textarea name="admin_notes" rows="3" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                            placeholder="Jelaskan alasan penolakan"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-3 rounded-lg">
                        ✗ Tolak Permohonan
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Complete Action (Approved Status) -->
        @if($request->status == 'approved')
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4 text-blue-800">Eksekusi Perubahan</h2>
            <p class="text-sm text-blue-700 mb-4">
                Permohonan telah disetujui. Klik tombol di bawah untuk melakukan perubahan nama subdomain di Cloudflare.
            </p>
            <div class="bg-yellow-50 border border-yellow-300 rounded p-3 mb-4">
                <p class="text-sm text-yellow-800">
                    <strong>⚠️ Perhatian:</strong> Tindakan ini akan mengupdate DNS record di Cloudflare dan mengubah:
                </p>
                <ul class="list-disc list-inside text-sm text-yellow-700 mt-2">
                    <li>Nama subdomain dari <strong>{{ $request->old_subdomain_name }}</strong> ke <strong>{{ $request->new_subdomain_name }}</strong></li>
                    <li>Konfigurasi akan diset ke DNS Only (tidak di-proxy)</li>
                    <li>Perubahan ini akan mempengaruhi akses ke website</li>
                </ul>
            </div>

            <form action="{{ route('admin.subdomain.name-change.complete', $request->id) }}" method="POST"
                onsubmit="return confirm('PERHATIAN: Tindakan ini akan mengubah DNS record di Cloudflare dan tidak dapat di-undo dengan mudah. Yakin ingin melanjutkan?')">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Tambahan (Opsional)</label>
                    <textarea name="admin_notes" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="Tambahkan catatan jika diperlukan">{{ $request->admin_notes }}</textarea>
                </div>
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg">
                    🚀 Selesaikan Perubahan (Update Cloudflare)
                </button>
            </form>
        </div>
        @endif

        <!-- Admin Notes Display -->
        @if($request->admin_notes && in_array($request->status, ['completed', 'rejected']))
        <div class="p-4 rounded-lg mb-6 {{ $request->status == 'rejected' ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200' }}">
            <h2 class="text-lg font-semibold mb-3 {{ $request->status == 'rejected' ? 'text-red-800' : 'text-green-800' }}">
                Catatan Admin
            </h2>
            <p class="{{ $request->status == 'rejected' ? 'text-red-700' : 'text-green-700' }} whitespace-pre-line">{{ $request->admin_notes }}</p>
        </div>
        @endif

        <!-- Completion Status -->
        @if($request->status == 'completed')
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <h3 class="font-semibold text-green-800 mb-2">✓ Perubahan Berhasil Dilakukan</h3>
            <p class="text-sm text-green-700">
                Subdomain telah berhasil diubah dari <strong>{{ $request->old_subdomain_name }}</strong>
                menjadi <strong>{{ $request->new_subdomain_name }}</strong> pada {{ $request->updated_at->format('d F Y, H:i') }}.
            </p>
            <p class="text-sm text-green-700 mt-2">
                DNS record di Cloudflare telah diupdate. Propagation mungkin memakan waktu hingga 24-48 jam.
            </p>
        </div>
        @endif
    </div>
@endsection
