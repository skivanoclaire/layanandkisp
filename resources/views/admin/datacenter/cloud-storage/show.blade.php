@extends('layouts.authenticated')
@section('title', '- Detail Cloud Storage')
@section('header-title', 'Detail Cloud Storage')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan Cloud Storage</h1>
        <a href="{{ route('admin.datacenter.cloud-storage.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
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
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Keterangan</h2>
            <div class="bg-gray-50 p-4 rounded-lg border">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $cloudStorageRequest->keterangan }}</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @if($cloudStorageRequest->status === 'menunggu')
    <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-yellow-800 mb-3">Tindakan untuk Permohonan Ini</h3>
        <div class="flex gap-3">
            <button onclick="document.getElementById('formProcess').style.display='block'; this.parentElement.style.display='none';"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
                Proses Permohonan
            </button>
            <button onclick="document.getElementById('formReject').style.display='block'; this.parentElement.style.display='none';"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">
                Tolak Permohonan
            </button>
        </div>

        <form id="formProcess" action="{{ route('admin.datacenter.cloud-storage.process', $cloudStorageRequest->id) }}" method="POST" style="display:none;" class="mt-4">
            @csrf
            <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan (opsional):</label>
            <textarea name="admin_notes" rows="3" class="w-full px-3 py-2 border border-blue-300 rounded-lg"></textarea>
            <div class="mt-3 flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Konfirmasi Proses</button>
                <button type="button" onclick="this.closest('form').style.display='none'; this.closest('.bg-yellow-50').querySelector('.flex.gap-3').style.display='flex';"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">Batal</button>
            </div>
        </form>

        <form id="formReject" action="{{ route('admin.datacenter.cloud-storage.reject', $cloudStorageRequest->id) }}" method="POST" style="display:none;" class="mt-4">
            @csrf
            <label class="block text-sm font-semibold text-red-700 mb-1">Alasan Penolakan: <span class="text-red-500">*</span></label>
            <textarea name="admin_notes" rows="3" required class="w-full px-3 py-2 border border-red-300 rounded-lg" placeholder="Jelaskan alasan penolakan..."></textarea>
            <div class="mt-3 flex gap-2">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">Konfirmasi Tolak</button>
                <button type="button" onclick="this.closest('form').style.display='none'; this.closest('.bg-yellow-50').querySelector('.flex.gap-3').style.display='flex';"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">Batal</button>
            </div>
        </form>
    </div>
    @endif

    @if($cloudStorageRequest->status === 'proses')
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-blue-800 mb-3">Berikan Akses Cloud Storage</h3>
        <form action="{{ route('admin.datacenter.cloud-storage.complete', $cloudStorageRequest->id) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-blue-700 mb-1">URL Akses: <span class="text-red-500">*</span></label>
                    <input type="text" name="akses_url" required
                           class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                           placeholder="Contoh: https://drive.google.com/... atau https://storage.kaltaraprov.go.id/...">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-blue-700 mb-1">Username (opsional):</label>
                    <input type="text" name="username"
                           class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                           placeholder="Username untuk akses cloud storage (jika ada)">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-blue-700 mb-1">Keterangan untuk User:</label>
                <textarea name="keterangan_admin" rows="3"
                          class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                          placeholder="Informasi cara akses, password, dll (opsional)">{{ $cloudStorageRequest->keterangan_admin }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan Internal (opsional):</label>
                <textarea name="admin_notes" rows="2"
                          class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                          placeholder="Catatan untuk admin">{{ $cloudStorageRequest->admin_notes }}</textarea>
            </div>
            <button type="submit" class="mt-3 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                Selesaikan & Berikan Akses
            </button>
        </form>
    </div>
    @endif

    @if(in_array($cloudStorageRequest->status, ['proses', 'selesai']))
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Catatan Admin</h3>
        <form action="{{ route('admin.datacenter.cloud-storage.update-notes', $cloudStorageRequest->id) }}" method="POST">
            @csrf
            <textarea name="admin_notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Tambahkan catatan...">{{ $cloudStorageRequest->admin_notes }}</textarea>
            <div class="mt-3">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold">
                    Update Catatan
                </button>
            </div>
        </form>

        @if($cloudStorageRequest->processedBy)
        <div class="mt-4 pt-4 border-t">
            <p class="text-sm text-gray-600">Diproses oleh: <span class="font-semibold">{{ $cloudStorageRequest->processedBy->name }}</span></p>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
