@extends('layouts.authenticated')
@section('title', '- Detail VPS/VM')
@section('header-title', 'Detail VPS/VM')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan VPS/VM</h1>
        <a href="{{ route('admin.datacenter.vps.index') }}"
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
                </div>
            </div>
        </div>

        <!-- Keterangan -->
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Keterangan</h2>
            <div class="bg-gray-50 p-4 rounded-lg border">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $vpsRequest->keterangan }}</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons: Process/Reject -->
    @if($vpsRequest->status === 'menunggu')
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

        <!-- Form Process -->
        <form id="formProcess" action="{{ route('admin.datacenter.vps.process', $vpsRequest->id) }}" method="POST" style="display:none;" class="mt-4">
            @csrf
            <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan (opsional):</label>
            <textarea name="admin_notes" rows="3" class="w-full px-3 py-2 border border-blue-300 rounded-lg"></textarea>
            <div class="mt-3 flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Konfirmasi Proses</button>
                <button type="button" onclick="this.closest('form').style.display='none'; this.closest('.bg-yellow-50').querySelector('.flex.gap-3').style.display='flex';"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">Batal</button>
            </div>
        </form>

        <!-- Form Reject -->
        <form id="formReject" action="{{ route('admin.datacenter.vps.reject', $vpsRequest->id) }}" method="POST" style="display:none;" class="mt-4">
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

    <!-- Action: Complete with IP Assignment -->
    @if($vpsRequest->status === 'proses')
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-blue-800 mb-3">Selesaikan Permohonan VPS/VM</h3>
        <form action="{{ route('admin.datacenter.vps.complete', $vpsRequest->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-blue-700 mb-1">IP Public: <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <input type="text" name="ip_public" id="ip_public" required
                           class="flex-1 px-3 py-2 border border-blue-300 rounded-lg"
                           placeholder="Contoh: 103.xx.xx.xx">
                    <button type="button" onclick="autopickIP()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                        Auto-pick IP
                    </button>
                </div>
                <p class="mt-1 text-xs text-blue-600">Klik "Auto-pick IP" untuk otomatis memilih IP tersedia dari sistem monitoring</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-blue-700 mb-1">Keterangan untuk User:</label>
                <textarea name="keterangan_admin" rows="3"
                          class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                          placeholder="Informasi akses VPS, credentials, dll (opsional)">{{ $vpsRequest->keterangan_admin }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan Internal (opsional):</label>
                <textarea name="admin_notes" rows="2"
                          class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                          placeholder="Catatan untuk admin">{{ $vpsRequest->admin_notes }}</textarea>
            </div>
            <button type="submit" class="mt-3 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                Selesaikan & Berikan Akses VPS
            </button>
        </form>
    </div>
    @endif

    <!-- Update Notes Section -->
    @if(in_array($vpsRequest->status, ['proses', 'selesai']))
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Catatan Admin</h3>
        <form action="{{ route('admin.datacenter.vps.update-notes', $vpsRequest->id) }}" method="POST">
            @csrf
            <textarea name="admin_notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Tambahkan catatan...">{{ $vpsRequest->admin_notes }}</textarea>
            <div class="mt-3">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold">
                    Update Catatan
                </button>
            </div>
        </form>

        @if($vpsRequest->processedBy)
        <div class="mt-4 pt-4 border-t">
            <p class="text-sm text-gray-600">Diproses oleh: <span class="font-semibold">{{ $vpsRequest->processedBy->name }}</span></p>
        </div>
        @endif
    </div>
    @endif
</div>

<script>
async function autopickIP() {
    const ipInput = document.getElementById('ip_public');
    try {
        const response = await fetch('{{ route("admin.datacenter.vps.api.autopick-ip") }}');
        const data = await response.json();

        if (data.success && data.ip) {
            ipInput.value = data.ip;
            alert('IP berhasil dipilih: ' + data.ip);
        } else {
            alert('Gagal mendapatkan IP: ' + (data.message || 'Tidak ada IP tersedia'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>
@endsection
